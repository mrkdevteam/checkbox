<?php
/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) exit; 

if ( ! class_exists( 'MRKV_CHECKBOX_MENU' ) ) {

    class MRKV_CHECKBOX_MENU {
        /**
         * Slug for the main settings page.
         */
        private $slug = 'mrkv_checkbox_settings';

        /**
         * Allowed HTML tags for kses.
         */
        private static $allowed_tags = [];

        public function __construct() {
            $this->init_allowed_tags();
            
            add_action( 'admin_menu', [ $this, 'mrkv_checkbox_register_plugin_page' ], 99 );
            add_action('admin_init', [$this, 'mrkv_checkbox_register_mysettings']);
        }

        public function mrkv_checkbox_register_mysettings()
        {
            $mrkv_checkbox_new_settings = get_option('mrkv_checkbox', []);

            if(empty($mrkv_checkbox_new_settings))
            {
                require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/mrkv-checkbox-old-data-checker.php';
                $old_checker = new MRKV_CHECKBOX_OLD_CHECKER();
                $old_checker->update_new_settings_with_old_data();
            }
        }

        /**
         * Initialize allowed tags once.
         */
        private function init_allowed_tags() {
            if ( ! empty( self::$allowed_tags ) ) return;

            self::$allowed_tags = apply_filters( 'mrkv_checkbox_admin_page_allowed_tags', [
                'label'    => [ 'for' => true, 'class' => true ],
                'input'    => [
                    'type' => true, 'id' => true, 'name' => true, 'value' => true,
                    'placeholder' => true, 'step' => true, 'min' => true, 'max' => true,
                    'checked' => true, 'disabled' => true, 'readonly' => true,
                    'multiple' => true, 'onwheel' => true, 'class' => true, 'data-*' => true,
                ],
                'select'   => [
                    'id' => true, 'name' => true, 'multiple' => true, 'disabled' => true,
                    'class' => true, 'data-*' => true, 'aria-hidden' => true, 'tabindex' => true,
                ],
                'option'   => [ 'value' => true, 'selected' => true, 'class' => true ],
                'textarea' => [
                    'id' => true, 'name' => true, 'placeholder' => true, 'readonly' => true,
                    'class' => true, 'rows' => true, 'cols' => true, 'data-*' => true,
                ],
                'p'        => [ 'class' => true ],
                'div'      => [ 'class' => true, 'id' => true, 'data-*' => true ],
                'span'     => [ 'class' => true, 'data-*' => true ],
            ]);
        }

        public function mrkv_checkbox_register_plugin_page() {
            add_menu_page(
                __( 'morkva Checkbox', 'checkbox' ),
                __( 'morkva Checkbox', 'checkbox' ),
                'manage_options',
                $this->slug,
                [ $this, 'mrkv_checkbox_get_plugin_settings_content' ],
                MRKV_CHECKBOX_IMG_URL . '/global/morkva-icon-20x20.svg'
            );
        }

        /**
         * Main Settings Page Content.
         */
        public function mrkv_checkbox_get_plugin_settings_content() {
            $mrkv_checkbox_tabs = apply_filters( 'mrkv_checkbox_admin_page_tabs', [
                'cashiers_settings'   => __( 'Cashiers', 'checkbox' ),
                'automation_settings' => __( 'Automation', 'checkbox' ),
                'advanced_settings'   => __( 'Advanced', 'checkbox' ),
                'log'                 => __( 'Test/Debug', 'checkbox' )
            ]);
            $mrkv_checkbox_settings_name = 'mrkv_checkbox';
            $mrkv_checkbox_settings = (array) get_option( 'mrkv_checkbox', [] );
            $allowed_tags           = self::$allowed_tags;

            // Generate field options
            $mrkv_checkbox_delivery_types = [
                'item_type'     => __( 'Add as a separate item', 'checkbox' ),
                'discount_type' => __( 'Add as a supplement', 'checkbox' )
            ];

            // Attributes
            $mrkv_checkbox_attributes = [];
            foreach ( wc_get_attribute_taxonomies() as $attr ) {
                $mrkv_checkbox_attributes[ $attr->attribute_name ] = $attr->attribute_label;
            }

            // Intervals
            $custom_interval_autocreate = apply_filters( 'mrkv_checkbox_admin_page_intervals', [
                'every_two_minute'  => __( 'Every 2 minutes', 'checkbox' ),
                'every_five_minute' => __( 'Every 5 minutes', 'checkbox' ),
                'every_ten_minute'  => __( 'Every 10 minutes', 'checkbox' ),
            ]);

            $mrkv_checkbox_product_codes_type = ['id' => 'ID', 'sku' => 'SKU'];

            // Order Statuses
            $mrkv_checkbox_order_statuses = [];
            foreach ( wc_get_order_statuses() as $slug => $label ) {
                $mrkv_checkbox_order_statuses[ str_replace( 'wc-', '', $slug ) ] = $label;
            }

            // Load Cashiers logic
            $mrkv_checkbox_cashier_list = $mrkv_checkbox_settings['cashiers'] ?? [];
            if ( empty( $mrkv_checkbox_cashier_list ) ) {
                require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/mrkv-checkbox-old-data-checker.php';
                $old_checker = new MRKV_CHECKBOX_OLD_CHECKER();
                $mrkv_checkbox_cashier_list['default'] = $old_checker->mrkv_checkbox_get_default_cashbox() ?: [];
            }

            $cash_register_list = array_map( function( $item ) {
                return ( is_array( $item ) && isset( $item['register_name'] ) ) ? $item['register_name'] : '';
            }, $mrkv_checkbox_cashier_list );
            require_once MRKV_CHECKBOX_PLUGIN_PATH . 'static/constant-payments.php';
            $mrkv_checkbox_labels = [];
            if ( defined( 'MRKV_CHECKBOX_PAYMENT_LABELS' ) && is_array( MRKV_CHECKBOX_PAYMENT_LABELS ) ) {
                foreach ( MRKV_CHECKBOX_PAYMENT_LABELS as $mrkv_label => $mrlv_code ) {
                    $mrkv_checkbox_labels[ $mrkv_label ] = $mrkv_label;
                }
            }

            require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/settings/global/mrkv-checkbox-option-fields.php';
            $field_generator = new MRKV_CHECKBOX_OPTION_FILEDS();

            $enabled_gateways = [];
            if ( function_exists( 'WC' ) && method_exists( WC(), 'payment_gateways' ) && WC()->payment_gateways() ) {
                $enabled_gateways = array_filter( WC()->payment_gateways->payment_gateways(), function ( $gateway ) {
                    return 'yes' === $gateway->enabled;
                });
            }

            require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/shift/mrkv-checkbox-shift-status.php';
            require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/api/mrkv-checkbox-api.php';

            foreach ( $mrkv_checkbox_cashier_list as $key => $cashier ) {
                if ( isset( $cashier['register_key'] ) && isset( $cashier['signin'] ) ) {
                    $mrkv_checkbox_api = new MRKV_CHECKBOX_API( $cashier['register_key'] ?? '', $cashier['signin'] ?? '' );
                    $mrkv_checkbox_shift_status_controller = new MRKV_CHECKBOX_SHIFT_STATUS( $mrkv_checkbox_api );
                    $mrkv_checkbox_actual_shift_status = $mrkv_checkbox_shift_status_controller->check_shift_status();

                    $mrkv_checkbox_status_str = is_array( $mrkv_checkbox_actual_shift_status ) ? ( $mrkv_checkbox_actual_shift_status['status'] ?? 'closed' ) : 'closed';

                    $mrkv_checkbox_cashier_list[ $key ]['shift_status'] = $mrkv_checkbox_status_str;
                    $mrkv_checkbox_settings['cashiers'][ $key ]['shift_status'] = $mrkv_checkbox_status_str;
                }
            }

            update_option( 'mrkv_checkbox', $mrkv_checkbox_settings );

            do_action( 'mrkv_checkbox_admin_page_before_settings_form' );
            include MRKV_CHECKBOX_PLUGIN_PATH_TEMP . '/settings/template-mrkv-checkbox-settings.php';
            do_action( 'mrkv_checkbox_admin_page_after_settings_form' );
        }
    }
}