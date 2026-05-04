<?php
/**
 * Exit if accessed directly to prevent security vulnerabilities.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class MRKV_CHECKBOX_AJAX
 * Handles all plugin AJAX requests with optimized context loading and security.
 */
if ( ! class_exists( 'MRKV_CHECKBOX_AJAX' ) ) {

    class MRKV_CHECKBOX_AJAX {

        private $is_hpos;
        private $plugin_path;

        public function __construct() {
            $this->plugin_path = MRKV_CHECKBOX_PLUGIN_PATH;
            $this->is_hpos = class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) 
                             && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();

            $this->init_hooks();
        }

        private function init_hooks() {
            $actions = [
                'mrkv_checkbox_change_shift_status',
                'mrkv_checkbox_remove_receipt',
                'mrkv_checkbox_custom_receipt',
                'mrkv_checkbox_create_receipt',
                'mrkv_checkbox_dismiss_notice'
            ];

            foreach ( $actions as $action ) {
                add_action( "wp_ajax_{$action}", [ $this, "{$action}_func" ] );
            }
        }

        /**
         * Security: Verify Nonce and Capabilities
         */
        private function verify_request( $capability = 'manage_woocommerce' ) {
            check_ajax_referer( 'mrkv_checkbox_sent_nonce', 'nonce' );
            if ( ! current_user_can( $capability ) ) {
                wp_send_json_error( __( 'Permissions check failed.', 'checkbox' ), 403 );
            }
        }

        /**
         * Context Loader: Centralizes API, Settings, and Auth logic.
         */
        private function get_context( $cashbox_id = 'default' ) {
            $settings = get_option( 'mrkv_checkbox', [] );
            $is_dev   = ! empty( $settings['test_mode']['enabled'] );
            $data     = $is_dev ? ( $settings['test_mode'] ?? [] ) : ( $settings['cashiers'][$cashbox_id] ?? [] );

            // Legacy Data Fallback
            if ( empty( $data ) ) {
                require_once $this->plugin_path . 'classes/controller/mrkv-checkbox-old-data-checker.php';
                $checker = new \MRKV_CHECKBOX_OLD_CHECKER();
                $data = ( $cashbox_id === 'default' ) ? $checker->mrkv_checkbox_get_default_cashbox() : [];
            }

            if ( empty( $data ) ) {
                wp_send_json_error( __( 'Configuration not found.', 'checkbox' ) );
            }

            require_once $this->plugin_path . 'classes/controller/api/mrkv-checkbox-api.php';
            
            // Auto-refresh token if signin is missing
            if ( empty( $data['signin'] ) && ! empty( $data['register_key'] ) ) {
                require_once $this->plugin_path . 'classes/controller/authorization/mrkv-checkbox-authorization.php';
                $temp_api = new \MRKV_CHECKBOX_API( $data['register_key'], '', $is_dev );
                $auth     = new \MRKV_CHECKBOX_AUTHORIZATION( '', $data['cashier_login'] ?? '', $data['cashier_password'] ?? '', $temp_api );
                $token    = $auth->mrkv_checkbox_get_authorization_token();
                
                if ( $token ) {
                    $data['signin'] = $token;
                    if ( $is_dev ) { $settings['test_mode']['signin'] = $token; } 
                    else { $settings['cashiers'][$cashbox_id]['signin'] = $token; }
                    update_option( 'mrkv_checkbox', $settings );
                }
            }

            return [
                'api'      => new \MRKV_CHECKBOX_API( $data['register_key'] ?? '', $data['signin'] ?? '', $is_dev ),
                'settings' => $settings,
                'is_dev'   => $is_dev
            ];
        }

        /**
         * AJAX: Manually saves a custom receipt ID to the order.
         */
        public function mrkv_checkbox_custom_receipt_func() {
            check_ajax_referer( 'mrkv_checkbox_sent_nonce', 'nonce' );
            $this->verify_request();

            $order_id       = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
            $custom_receipt = isset( $_POST['custom_receipt'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_receipt'] ) ) : '';
            
            if ( $order_id && $custom_receipt ) {
                $this->update_order_meta( $order_id, 'receipt_id', $custom_receipt );
                
                $order = wc_get_order( $order_id );
                if ( $order ) {
                    $order->add_order_note( __( 'Custom receipt ID saved manually.', 'checkbox' ) );
                    $order->save();
                }
                
                wp_send_json_success( __( 'Custom receipt saved successfully', 'checkbox' ) );
            }

            wp_send_json_error( __( 'Error saving custom receipt: missing data.', 'checkbox' ) );
        }

        /**
         * AJAX: Generic Receipt Removal
         */
        public function mrkv_checkbox_remove_receipt_func() {
            check_ajax_referer( 'mrkv_checkbox_sent_nonce', 'nonce' );
            $this->verify_request();

            $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
            
            if ( $order_id ) {
                // Your logic to remove a receipt by Order ID
                $this->update_order_meta( $order_id, 'receipt_id', '' );
                wp_send_json_success( __( 'Receipt reference removed.', 'checkbox' ) );
            }

            wp_send_json_error( __( 'Invalid Order ID.', 'checkbox' ) );
        }

        /**
         * AJAX: Shift Management
         */
        public function mrkv_checkbox_change_shift_status_func() {
            // Check nonce directly in the function scope to satisfy scanner
            check_ajax_referer( 'mrkv_checkbox_sent_nonce', 'nonce' );
            $this->verify_request();

            $cashbox_id = isset( $_POST['cashbox_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cashbox_id'] ) ) : 'default';
            $new_status = isset( $_POST['new_status'] ) ? sanitize_text_field( wp_unslash( $_POST['new_status'] ) ) : '';
            
            $ctx = $this->get_context( $cashbox_id );

            if ( $new_status === 'open' ) {
                require_once $this->plugin_path . 'classes/controller/shift/mrkv-checkbox-connect.php';
                $action = new \MRKV_CHECKBOX_CONNECT( $ctx['api'] );
                $target = 'opened';
            } else {
                require_once $this->plugin_path . 'classes/controller/shift/mrkv-checkbox-disconnect.php';
                $action = new \MRKV_CHECKBOX_DISCONNECT( $ctx['api'] );
                $target = 'closed';
            }

            $res = $action->connect();

            if ( ! empty( $res['status'] ) ) {
                $settings = $ctx['settings'];
                if ( $ctx['is_dev'] ) {
                    $settings['test_mode']['shift_status'] = $target;
                } else {
                    $settings['cashiers'][$cashbox_id]['shift_status'] = $target;
                }
                update_option( 'mrkv_checkbox', $settings );
                wp_send_json_success( [ 'new_status' => $target, 'cashbox_id' => $cashbox_id ] );
            }
            elseif(! empty( $res['status_code'] ) && $res['status_code'] == 401)
            {
                $settings = $ctx['settings'];
                $settings['cashiers'][$cashbox_id]['signin'] = '';
                update_option( 'mrkv_checkbox', $settings );
                $ctx = $this->get_context( $cashbox_id );

                if ( $new_status === 'open' ) {
                    require_once $this->plugin_path . 'classes/controller/shift/mrkv-checkbox-connect.php';
                    $action = new \MRKV_CHECKBOX_CONNECT( $ctx['api'] );
                    $target = 'opened';
                } else {
                    require_once $this->plugin_path . 'classes/controller/shift/mrkv-checkbox-disconnect.php';
                    $action = new \MRKV_CHECKBOX_DISCONNECT( $ctx['api'] );
                    $target = 'closed';
                }

                $res = $action->connect();

                if ( ! empty( $res['status'] ) ) {
                    $settings = $ctx['settings'];
                    if ( $ctx['is_dev'] ) {
                        $settings['test_mode']['shift_status'] = $target;
                    } else {
                        $settings['cashiers'][$cashbox_id]['shift_status'] = $target;
                    }
                    update_option( 'mrkv_checkbox', $settings );
                    wp_send_json_success( [ 'new_status' => $target, 'cashbox_id' => $cashbox_id ] );
                }
            }

            wp_send_json_error( $res['message'] ?? __( 'Shift action failed', 'checkbox' ) );
        }

        /**
         * AJAX: Receipt Creation
         */
        public function mrkv_checkbox_create_receipt_func() {
            check_ajax_referer( 'mrkv_checkbox_sent_nonce', 'nonce' );
            $this->verify_request();

            $order_id   = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
            $cashbox_id = isset( $_POST['cashbox_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cashbox_id'] ) ) : '';
            $order      = wc_get_order( $order_id );

            if ( $order && $cashbox_id ) {
                require_once $this->plugin_path . 'classes/controller/mrkv-checkbox-old-data-checker.php';
                require_once $this->plugin_path . 'classes/controller/receipt/mrkv-checkbox-receipt-creator.php';
                
                $creator = new \MRKV_CHECKBOX_RECEIPT_CREATOR( $cashbox_id, $order, get_option('mrkv_checkbox', []), new \MRKV_CHECKBOX_OLD_CHECKER() );
                $creator->create_receipt();
                wp_send_json_success( __( 'Receipt created', 'checkbox' ) );
            }
            wp_send_json_error( __( 'Order not found', 'checkbox' ) );
        }

        public function mrkv_checkbox_dismiss_notice_func() {
            check_ajax_referer( 'mrkv_checkbox_sent_nonce', 'nonce' );
            $this->verify_request();

            update_user_meta( get_current_user_id(), 'mrkv_checkbox_notice_dismissed', true );
            wp_die();
        }

        /**
         * Helper: HPOS Compatible Meta Update
         */
        private function update_order_meta( $order_id, $key, $value ) {
            $order = wc_get_order( $order_id );
            if ( $order ) {
                $order->update_meta_data( $key, $value );
                $order->save();
            }
        }
    }
}