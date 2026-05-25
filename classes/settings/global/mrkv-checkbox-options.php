<?php
/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'MRKV_CHECKBOX_OPTIONS' ) ) {

    /**
     * Class MRKV_CHECKBOX_OPTIONS
     * Manages plugin options registration and automated cashier authentication.
     */
    class MRKV_CHECKBOX_OPTIONS {

        public function __construct() {
            add_action( 'admin_init', [ $this, 'mrkv_checkbox_register_settings' ] );
            add_filter( 'pre_update_option_mrkv_checkbox', [ $this, 'mrkv_checkbox_process_changes' ], 10, 2 );
        }

        /**
         * Register settings with the WordPress Settings API.
         */
        public function mrkv_checkbox_register_settings() {
            register_setting( 'mrkv-checkbox-settings-group', 'mrkv_checkbox', [
                'sanitize_callback' => [ $this, 'mrkv_checkbox_sanitize_settings' ]
            ] );
        }

        /**
         * Recursive sanitization for complex settings arrays.
         */
        public function mrkv_checkbox_sanitize_settings( $value ) {
            if ( is_array( $value ) ) {
                return array_map( [ $this, 'mrkv_checkbox_sanitize_settings' ], $value );
            }
            return sanitize_text_field( wp_unslash( (string) $value ) );
        }

        /**
         * Process and intercept settings before saving.
         */
        public function mrkv_checkbox_process_changes( $new_value, $old_value ) {
            if ( ! is_array( $new_value ) ) {
                return $new_value;
            }

            $old_value = (array) $old_value;

            // 1. Process Production Cashiers
            if ( ! empty( $new_value['cashiers'] ) && is_array( $new_value['cashiers'] ) ) {
                foreach ( $new_value['cashiers'] as $slug => &$cashier ) {
                    $cashier = $this->handle_cashier_update( 
                        $cashier, 
                        $old_value['cashiers'][ $slug ] ?? null, 
                        false 
                    );
                }
            }

            // 2. Process Test Mode Cashier
            if ( isset( $new_value['test_mode'] ) && is_array( $new_value['test_mode'] ) ) {
                $new_value['test_mode'] = $this->handle_cashier_update( 
                    $new_value['test_mode'], 
                    $old_value['test_mode'] ?? null, 
                    true 
                );
            }

            return apply_filters( 'mrkv_checkbox_settings_before_save', $new_value, $old_value );
        }

        /**
         * Logic to determine if credentials changed and request a new token.
         */
        private function handle_cashier_update( $cashier, $old_cashier, $is_dev ) {
            if ( ! is_array( $cashier ) || empty( $cashier['cashier_login'] ) ) {
                return $cashier;
            }

            $old_login = $old_cashier['cashier_login'] ?? '';
            $old_pass  = $old_cashier['cashier_password'] ?? '';

            // Check if credentials actually changed or if the token (signin) is missing
            $credentials_changed = ( $cashier['cashier_login'] !== $old_login || $cashier['cashier_password'] !== $old_pass );
            $token_missing       = empty( $cashier['signin'] );

            if ( $credentials_changed || $token_missing ) {
                $token_data = $this->mrkv_checkbox_get_new_auth_token( $cashier, $is_dev );
                
                if ( $token_data ) {
                    $cashier['signin'] = $token_data;
                } else {
                    // Optional: add a flag or error notice here if auth fails
                    do_action( 'mrkv_checkbox_auth_failed', $cashier );
                }
            } else {
                // Keep the old token if credentials didn't change
                $cashier['signin'] = $old_cashier['signin'] ?? '';
            }

            require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/settings/admin/mrkv-checkbox-activation-deactivation.php';
            $activation = new MRKV_CHECKBOX_ACTIVATION_DEACTIVATION();
            
            do_action( 'mrkv_checkbox_before_sync_request', $cashier, $old_cashier );
            
            $activation->mrkv_checkbox_send_request( 'updated' );
            
            do_action( 'mrkv_checkbox_after_sync_request', $cashier );

            return $cashier;
        }

        /**
         * Communicates with Checkbox API to fetch a token.
         */
        private function mrkv_checkbox_get_new_auth_token( $data, $is_dev ) {
            $api_path = MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/api/mrkv-checkbox-api.php';
            $auth_path = MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/authorization/mrkv-checkbox-authorization.php';

            if ( ! file_exists( $api_path ) || ! file_exists( $auth_path ) ) {
                return false;
            }

            require_once $api_path;
            require_once $auth_path;
            
            $api = new MRKV_CHECKBOX_API( $data['register_key'] ?? '', '', $is_dev );
            $auth = new MRKV_CHECKBOX_AUTHORIZATION( '', $data['cashier_login'] ?? '', $data['cashier_password'] ?? '', $api );

            return $auth->mrkv_checkbox_get_authorization_token(); 
        }
    }
}