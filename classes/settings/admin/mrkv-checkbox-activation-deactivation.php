<?php
/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'MRKV_CHECKBOX_ACTIVATION_DEACTIVATION' ) ) {

    /**
     * Class MRKV_CHECKBOX_ACTIVATION_DEACTIVATION
     * Handles plugin lifecycle events and remote status reporting.
     */
    class MRKV_CHECKBOX_ACTIVATION_DEACTIVATION {

        const REQUEST_STATUS_UPDATE   = 'updated';
        const REQUEST_STATUS_ACTIVE   = 'activated';
        const REQUEST_STATUS_DEACTIVE = 'deactivated';
        const REQUEST_PLUGIN_TYPE     = 'plugin';
        const API_URL_REGISTER        = 'https://api.morkva.co.ua/v1/register';
        
        private $plugin_path;

        public function __construct() {
            $this->plugin_path = plugin_dir_path( __FILE__ );
            add_filter( 'pre_update_option_ppo_cashbox_key', [ $this, 'mrkv_checkbox_update_data_main' ], 10, 3 );
            add_action( 'upgrader_process_complete', [ $this, 'mrkv_checkbox_upgrade' ], 10, 2 );
        }

        /**
         * Triggered when the main API key option is updated.
         */
        public function mrkv_checkbox_update_data_main( $value, $old_value, $option ) {
            if ( $value !== $old_value ) {
                $status = apply_filters( 'mrkv_checkbox_active_deactive_update_status', self::REQUEST_STATUS_UPDATE );
                $this->mrkv_checkbox_send_request( $status );
            }
            return $value;
        }

        /**
         * Triggered after plugin update.
         */
        public function mrkv_checkbox_upgrade( $upgrader_object, $options ) {
            $current_plugin = plugin_basename( __FILE__ );

            if ( isset( $options['action'], $options['type'], $options['plugins'] ) && 
                 $options['action'] === 'update' && 
                 $options['type'] === self::REQUEST_PLUGIN_TYPE ) {
                
                if ( in_array( $current_plugin, $options['plugins'], true ) ) {
                    do_action( 'mrkv_checkbox_active_deactive_before_upgrade', $options );
                    $this->mrkv_checkbox_send_request( self::REQUEST_STATUS_UPDATE );
                    do_action( 'mrkv_checkbox_active_deactive_after_upgrade', $options );
                }
            }
        }

        /**
         * Cleanup crons and report activation.
         */
        public function mrkv_checkbox_activation_cb() {
            do_action( 'mrkv_checkbox_active_deactive_before_activation' );

            $hooks = apply_filters( 'mrkv_checkbox_active_deactive_cleanup_hooks', [ 'checkbox_close_shift', 'checkbox_open_shift' ] );
            
            foreach ( $hooks as $hook ) {
                wp_clear_scheduled_hook( $hook );
            }

            $this->mrkv_checkbox_send_request( self::REQUEST_STATUS_ACTIVE );
            
            do_action( 'mrkv_checkbox_active_deactive_after_activation' );
        }

        /**
         * Close active shifts and report deactivation.
         */
        public function mrkv_checkbox_deactivation_cb() {
            do_action( 'mrkv_checkbox_active_deactive_before_deactivation' );

            $settings_data = get_option( 'mrkv_checkbox' );
            
            if ( ! empty( $settings_data['cashiers'] ) && is_array( $settings_data['cashiers'] ) ) {
                $this->load_dependencies();
                foreach ( $settings_data['cashiers'] as $cashbox_id => $cashbox_data ) {
                    if ( empty( $cashbox_data['signin'] ) && ! empty( $cashbox_data['cashier_login'] ) ) {
                        $temp_api = new MRKV_CHECKBOX_API( $cashbox_data['register_key'] ?? '', '' );
                        $auth     = new MRKV_CHECKBOX_AUTHORIZATION( '', $cashbox_data['cashier_login'], $cashbox_data['cashier_password'] ?? '', $temp_api );
                        $token    = $auth->mrkv_checkbox_get_authorization_token();

                        if ( $token ) {
                            $cashbox_data['signin'] = $token;
                            $settings_data['cashiers'][$cashbox_id]['signin'] = $token;
                        }
                    }

                    if ( ! empty( $cashbox_data['signin'] ) ) {
                        $api    = new MRKV_CHECKBOX_API( $cashbox_data['register_key'] ?? '', $cashbox_data['signin'], false );
                        $result = ( new MRKV_CHECKBOX_DISCONNECT( $api ) )->connect();

                        if ( ! empty( $result['status'] ) ) {
                            $settings_data['cashiers'][$cashbox_id]['shift_status'] = 'closed';
                        }
                    }
                }

                update_option( 'mrkv_checkbox', $settings_data );
            }

            $this->mrkv_checkbox_send_request( self::REQUEST_STATUS_DEACTIVE );
            
            do_action( 'mrkv_checkbox_active_deactive_after_deactivation' );
        }

        /**
         * Load required classes for deactivation logic.
         */
        private function load_dependencies() {
            $files = [
                'classes/controller/api/mrkv-checkbox-api.php',
                'classes/controller/authorization/mrkv-checkbox-authorization.php',
                'classes/controller/shift/mrkv-checkbox-disconnect.php'
            ];

            foreach ( $files as $file ) {
                if ( file_exists( $this->plugin_path . $file ) ) {
                    require_once $this->plugin_path . $file;
                }
            }
        }

        /**
         * Unified method to send status reports to remote API.
         */
        public function mrkv_checkbox_send_request( string $status ) {
            $settings = get_option( 'mrkv_checkbox' );
            $default  = $settings['cashiers']['default'] ?? [];

            $server_addr = isset( $_SERVER['SERVER_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) : '';
            $remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

            $ip = ! empty( $server_addr ) ? $server_addr : ( ! empty( $remote_addr ) ? $remote_addr : '127.0.0.1' );

            $body = apply_filters( 'mrkv_checkbox_active_deactive_request_body', [
                'ip'      => $ip,
                'domain'  => wp_parse_url( home_url(), PHP_URL_HOST ),
                'product' => 'checkbox',
                'version' => defined( 'MRKV_CHECKBOX_PLUGIN_VERSION' ) ? MRKV_CHECKBOX_PLUGIN_VERSION : '1.0.0',
                'license' => 'pro',
                'info'    => sprintf( '%s - %s', $default['register_edrpou'] ?? 'N/A', $default['register_key'] ?? 'N/A' ),
                'status'  => $status
            ] );

            return wp_remote_post( self::API_URL_REGISTER, [
                'method'      => 'POST',
                'timeout'     => 10,
                'redirection' => 5,
                'blocking'    => true,
                'headers'     => [ 'Accept' => 'application/json' ],
                'body'        => $body,
            ]);
        }
    }
}