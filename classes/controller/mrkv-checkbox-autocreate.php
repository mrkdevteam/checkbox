<?php
/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) exit; 

if ( ! class_exists( 'MRKV_CHECKBOX_AUTOCREATE' ) ) {

    /**
     * Class MRKV_CHECKBOX_AUTOCREATE
     * Manages automatic receipt creation based on order status changes and payment rules.
     */
    class MRKV_CHECKBOX_AUTOCREATE {

        private $plugin_path;

        public function __construct() {
            $this->plugin_path = MRKV_CHECKBOX_PLUGIN_PATH;

            add_action( 'woocommerce_order_status_changed', [ $this, 'mrkv_checkbox_auto_create_receipt' ], 99, 3 );
            add_action( 'mrkv_checkbox_create_delayed_receipt_action', [ $this, 'mrkv_checkbox_execute_receipt_creation' ], 10, 1 );
        }

        /**
         * Centralized logic to determine if an order qualifies for automatic receipt creation.
         */
        protected function should_autocreate( $order, $new_status, $settings ) {
            $payment_id = $order->get_payment_method();

            // Case 1: New Settings exist
            if ( ! empty( $settings ) ) {
                $pay_settings = $settings['automation']['payments'][ $payment_id ] ?? [];
                $enabled      = ! empty( $pay_settings['enabled'] );
                $statuses     = $pay_settings['statuses'] ?? [];
                
                return $enabled && in_array( $new_status, (array) $statuses );
            }

            // Case 2: Legacy Settings fallback
            $legacy_statuses = (array) get_option( 'ppo_autocreate_receipt_order_statuses', [] );
            $legacy_payment_statuses = (array) get_option( 'ppo_autocreate_payment_order_statuses', [] );
            $legacy_active_rules = (array) get_option( 'ppo_rules_active', [] );

            if ( ! array_key_exists( $payment_id, $legacy_active_rules ) ) {
                return false;
            }

            $in_general_status = in_array( $new_status, $legacy_statuses );
            $in_payment_status = isset( $legacy_payment_statuses[ $payment_id ] ) && in_array( $new_status, (array) $legacy_payment_statuses[ $payment_id ] );

            return $in_general_status || $in_payment_status;
        }

        public function is_time_restricted() {
            $current_time = current_time( 'H:i' );
            
            $start_restrict = apply_filters( 'mrkv_checkbox_restrict_start_time', '23:30' );
            $end_restrict   = apply_filters( 'mrkv_checkbox_restrict_end_time', '00:05' );

            if ( $start_restrict <= $end_restrict ) {
                return ( $current_time >= $start_restrict && $current_time <= $end_restrict );
            } else {
                return ( $current_time >= $start_restrict || $current_time <= $end_restrict );
            }
        }
        
        /**
         * Main hook for status changes.
         */
        public function mrkv_checkbox_auto_create_receipt( $order_id, $old_status, $new_status ) {
            $order = wc_get_order( $order_id );
            if ( ! $order ) return;

            $settings = get_option( 'mrkv_checkbox', [] );

            if ( ! $this->should_autocreate( $order, $new_status, $settings ) ) {
                return;
            }

            // Check if receipt already exists (HPOS compatible)
            if ( ! empty( $order->get_meta( 'receipt_id' ) ) ) {
                $order->add_order_note( __( 'Receipt already created.', 'checkbox' ) );
                return;
            }

            do_action( 'mrkv_checkbox_before_autocreate_receipt', $order, $settings );

            if ( $this->is_time_restricted() ) {
                if ( class_exists( 'ActionScheduler' ) ) {
                    $end_restrict = apply_filters( 'mrkv_checkbox_restrict_end_time', '00:05' );
                    $target_time = strtotime( 'today ' . $end_restrict, current_time( 'timestamp' ) );
                    
                    if ( $target_time <= current_time( 'timestamp' ) ) {
                        $target_time = strtotime( 'tomorrow ' . $end_restrict, current_time( 'timestamp' ) );
                    }

                    if ( ! as_next_scheduled_action( 'mrkv_checkbox_create_delayed_receipt_action', [ 'order_id' => $order_id ] ) ) {
                        as_schedule_single_action( $target_time, 'mrkv_checkbox_create_delayed_receipt_action', [ 'order_id' => $order_id ] );
                    }
                }
                return;
            }

            $cashbox_id = 'default';

            if(!$cashbox_id && !empty($settings) && (!isset($settings['cashiers']) || empty($settings['cashiers'])))
            {
                require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/mrkv-checkbox-old-data-checker.php';
                $checker = new \MRKV_CHECKBOX_OLD_CHECKER();
                $settings['cashiers']['default'] = $checker->mrkv_checkbox_get_default_cashbox();
                update_option( 'mrkv_checkbox', $settings );
            }
            
            $cron_enabled = empty( $settings ) 
                ? ( get_option( 'ppo_autocreate_order' )['enabled'] ?? false )
                : ( $settings['automation']['cron']['enabled'] ?? false );

            if ( $cron_enabled ) {
                $this->add_to_queue( $order_id );
                return;
            }

            $this->execute_creation( $cashbox_id, $order, $settings );
        }

        public function mrkv_checkbox_execute_receipt_creation($order_id)
        {
            $order = wc_get_order( $order_id );
            if ( ! $order ) return;

            $settings = get_option( 'mrkv_checkbox', [] );

            if ( ! empty( $order->get_meta( 'receipt_id' ) ) ) {
                $order->add_order_note( __( 'Receipt already created.', 'checkbox' ) );
                return;
            }

            do_action( 'mrkv_checkbox_before_autocreate_receipt', $order, $settings );

            $cashbox_id = 'default';

            if(!$cashbox_id && !empty($settings) && (!isset($settings['cashiers']) || empty($settings['cashiers'])))
            {
                require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/mrkv-checkbox-old-data-checker.php';
                $checker = new \MRKV_CHECKBOX_OLD_CHECKER();
                $settings['cashiers']['default'] = $checker->mrkv_checkbox_get_default_cashbox();
                update_option( 'mrkv_checkbox', $settings );
            }
            
            $cron_enabled = empty( $settings ) 
                ? ( get_option( 'ppo_autocreate_order' )['enabled'] ?? false )
                : ( $settings['automation']['cron']['enabled'] ?? false );

            if ( $cron_enabled ) {
                $this->add_to_queue( $order_id );
                return;
            }

            $this->execute_creation( $cashbox_id, $order, $settings );
        }

        /**
         * Helper to run the actual creator class.
         */
        private function execute_creation( $cashbox_id, $order, $settings, $type = 'sell', $amount = 0 ) {
            require_once $this->plugin_path . 'classes/controller/mrkv-checkbox-old-data-checker.php';
            require_once $this->plugin_path . 'classes/controller/receipt/mrkv-checkbox-receipt-creator.php';
            
            $old_checker = new MRKV_CHECKBOX_OLD_CHECKER();
            $creator = new MRKV_CHECKBOX_RECEIPT_CREATOR( $cashbox_id, $order, $settings, $old_checker );
            
            return ( $type === 'sell' ) 
                ? $creator->create_receipt() 
                : $creator->create_receipt( $type, $amount );
        }

        /**
         * Add order to the background processing table.
         */
        private function add_to_queue( $order_id ) {
            global $wpdb;
            $table = $wpdb->prefix . 'mrkv_checkbox_autocreate_list';
            $cache_key   = 'table_exists_' . $table;
            $cache_group = 'mrkv_checkbox';
            $check       = wp_cache_get( $cache_key, $cache_group );

            if ( false === $check ) {
                if ( false === get_transient( 'mrkv_checkbox_queue_table_exists' ) ) {
                    
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $check = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) );

                    if ( $check !== $table ) {
                        $this->create_queue_table( $table );
                    }

                    set_transient( 'mrkv_checkbox_queue_table_exists', 1, DAY_IN_SECONDS );
                } else {
                    $check = $table;
                }
                
                wp_cache_set( $cache_key, $check, $cache_group );
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->insert( 
                $table, 
                [
                    'order_id' => absint( $order_id ),
                    'datetime' => current_time( 'mysql' )
                ], 
                [ '%d', '%s' ] 
            );
        }

        private function create_queue_table( $table ) {
            global $wpdb;
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            $charset = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                order_id BIGINT(20) UNSIGNED NOT NULL,
                datetime DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY order_id (order_id)
            ) $charset;";
            dbDelta( $sql );
        }
    }
}