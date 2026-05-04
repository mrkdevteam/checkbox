<?php
/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) exit; 

if ( ! class_exists( 'MRKV_CHECKBOX_OLD_CHECKER' ) ) {

    /**
     * Class MRKV_CHECKBOX_OLD_CHECKER
     * Handles migration and retrieval of legacy plugin data.
     */
    class MRKV_CHECKBOX_OLD_CHECKER {

        /**
         * Get the default cashier data from legacy standalone options.
         * * @return array
         */
        public function mrkv_checkbox_get_default_cashbox() {
            $main_key = get_option( 'ppo_login' );
            
            if ( ! $main_key ) {
                return [];
            }

            // Map legacy single options to the new array structure
            $data = [
                'cashier_login'           => get_option( 'ppo_login' ),
                'cashier_password'        => get_option( 'ppo_password' ),
                'cashier_name'    => get_option( 'ppo_cashier_name' ),
                'cashier_lastname' => get_option( 'ppo_cashier_surname' ),
                'register_name'    => $main_key,
                'register_key'     => get_option( 'ppo_cashbox_key' ),
                'register_edrpou'  => get_option( 'ppo_cashbox_edrpou' ),
                'register_tax_code'        => get_option( 'ppo_tax_code' ),
                'shift_status'      => 'closed',
                'signin'            => '',
            ];

            return apply_filters( 'mrkv_checkbox_old_checker_default_cashbox', $data );
        }

        /**
         * Consolidate all legacy options into the new structured array
         */
        public function update_new_settings_with_old_data() {
            $new_settings = [];
            $new_settings['cashiers']['default'] = $this->mrkv_checkbox_get_default_cashbox() ?: [];
            $mrkv_checkbox_new_settings['added_fields']['description'] = get_option('ppo_receipt_footer') ?? '';
            $mrkv_checkbox_new_settings['automation']['open_shift'] = get_option('ppo_autoopen_shift') ? 'on' : '';
            $mrkv_checkbox_new_settings['discount']['label'] = get_option('ppo_receipt_coupon_text') ?? '';
            $mrkv_checkbox_new_settings['product_title']['enabled'] = get_option('ppo_change_name') ? 'on' : '';
            $mrkv_checkbox_new_settings['product_title']['type'] = get_option('ppo_autocreate_receipt_product_attribute') ?? '';
            $mrkv_checkbox_new_settings['hs_code']['enabled'] = get_option('ppo_add_ukt_zed') ? 'on' : '';
            $mrkv_checkbox_new_settings['hs_code']['type'] = get_option('ppo_autocreate_receipt_product_attribute_ukt_zed') ?? '';
            $mrkv_checkbox_new_settings['delivery']['enabled'] = get_option('ppo_delivery_include') ? 'on' : '';
            $mrkv_checkbox_new_settings['delivery']['type'] = get_option('ppo_delivery_type') ?? '';
            $mrkv_checkbox_new_settings['added_fields']['zero_price'] = get_option('ppo_zero_product_exclude') ? 'on' : '';
            $mrkv_checkbox_new_settings['delivery']['title'] = get_option('ppo_delivery_label') ?? '';
            $mrkv_checkbox_new_settings['added_fields']['code_type'] = get_option('ppo_receipt_product_code_type') ?? '';
            $mrkv_checkbox_new_settings['pdf']['enabled'] = get_option('ppo_receipt_save_image') ? 'on' : '';
            $mrkv_checkbox_new_settings['email']['receipt_added'] = get_option('ppo_receipt_send_email') ? 'on' : '';
            $mrkv_checkbox_new_settings['added_fields']['phone'] = get_option('ppo_receipt_send_phone') ? 'on' : '';
            $mrkv_checkbox_new_settings['added_fields']['barcode'] = get_option('ppo_barcode') ? 'on' : '';
            $mrkv_checkbox_new_settings['email']['cancelled']['enabled'] = get_option('ppo_email_notification_error_enabled') ? 'on' : '';
            $mrkv_checkbox_new_settings['email']['cancelled']['client'] = get_option('ppo_email_notification_error') ?? '';
            $mrkv_checkbox_new_settings['automation']['cron']['enabled'] = (get_option('ppo_autocreate_order') ?: [])['enabled'] ?? '';
            
            $mrkv_checkbox_ppo_rules_active = get_option('ppo_rules_active', []);
            $mrkv_checkbox_ppo_payment_type_checkbox = get_option('ppo_payment_type_checkbox', []);
            $mrkv_checkbox_ppo_payment_type_label = get_option('ppo_payment_type_label', []);
            $mrkv_checkbox_ppo_payment_type = get_option('ppo_payment_type', []);
            $mrkv_checkbox_ppo_autocreate_payment_order_statuses = get_option('ppo_autocreate_payment_order_statuses', []);
            $mrkv_checkbox_ppo_autocreate_checkbox_name = get_option('ppo_autocreate_checkbox_name', []);

            if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways ) return;

            $mrkv_checkbox_enabled_gateways = array_filter(WC()->payment_gateways->payment_gateways(), function ($gateway) {
	            return 'yes' === $gateway->enabled;
	        });

            foreach ($mrkv_checkbox_enabled_gateways as $mrkv_checkbox_gateway_id => $mrkv_checkbox_gateway_data)
            {
                $mrkv_checkbox_new_settings['automation']['payments'][$mrkv_checkbox_gateway_id]['enabled'] = (isset($mrkv_checkbox_ppo_rules_active[$mrkv_checkbox_gateway_id]) && $mrkv_checkbox_ppo_rules_active[$mrkv_checkbox_gateway_id]) ? 'on' : '';
                $mrkv_checkbox_new_settings['automation']['payments'][$mrkv_checkbox_gateway_id]['label'] = $mrkv_checkbox_ppo_payment_type_checkbox[$mrkv_checkbox_gateway_id]['label'] ?? '';
                $mrkv_checkbox_new_settings['automation']['payments'][$mrkv_checkbox_gateway_id]['custom_label'] = $mrkv_checkbox_ppo_payment_type_label[$mrkv_checkbox_gateway_id] ?? '';
                $mrkv_checkbox_new_settings['automation']['payments'][$mrkv_checkbox_gateway_id]['form'] = strtoupper($mrkv_checkbox_ppo_payment_type[$mrkv_checkbox_gateway_id] ?? '');
                $mrkv_checkbox_new_settings['automation']['payments'][$mrkv_checkbox_gateway_id]['register'] = $mrkv_checkbox_ppo_autocreate_checkbox_name[$mrkv_checkbox_gateway_id] ?? '';
                $mrkv_checkbox_new_settings['automation']['payments'][$mrkv_checkbox_gateway_id]['statuses'] = $mrkv_checkbox_ppo_autocreate_payment_order_statuses[$mrkv_checkbox_gateway_id] ?? '';
            }

            update_option( 'mrkv_checkbox', $mrkv_checkbox_new_settings );
        }
    }
}