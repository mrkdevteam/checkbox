<?php
/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) exit; 

if ( ! class_exists( 'MRKV_CHECKBOX_ADMIN_ASSETS' ) ) {

    /**
     * Class MRKV_CHECKBOX_ADMIN_ASSETS
     * Handles plugin admin styles and scripts with extensibility.
     */
    class MRKV_CHECKBOX_ADMIN_ASSETS {

        public function __construct() {
            add_action( 'admin_enqueue_scripts', [ $this, 'mrkv_checkbox_styles_and_scripts' ] );
        }

        /**
         * Enqueue admin assets.
         */
        public function mrkv_checkbox_styles_and_scripts( $hook ) {
            global $pagenow, $typenow;
            
            $screen = get_current_screen();
            $screen_id = ( $screen instanceof \WP_Screen ) ? $screen->id : '';
            $nonce  = wp_create_nonce( 'mrkv_checkbox_sent_nonce' );
            
            $localization_data = apply_filters( 'mrkv_checkbox_assets_localization', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => $nonce,
            ] );

            /**
             * 1. Order Related Pages Logic (Global Helpers)
             */
            $is_order_page = (
                in_array( $pagenow, [ 'edit.php', 'admin.php', 'post.php' ], true ) && 
                ( 'shop_order' === $typenow || in_array( $screen_id, [ 'woocommerce_page_wc-orders', 'woocommerce_page_shop_order' ], true ) )
            );

            if ( apply_filters( 'mrkv_checkbox_assets_load_global', $is_order_page, $hook ) ) {
                
                do_action( 'mrkv_checkbox_assets_before_global_enqueue' );

                wp_enqueue_style( 'global-mrkv-checkbox', MRKV_CHECKBOX_ASSETS_URL . '/css/global/global-mrkv-checkbox.css', [], MRKV_CHECKBOX_PLUGIN_VERSION );
                
                wp_enqueue_script( 'admin-checkbox-select2-js', MRKV_CHECKBOX_ASSETS_URL . '/js/global/select2.min.js', [ 'jquery' ], MRKV_CHECKBOX_PLUGIN_VERSION, true );
                
                wp_enqueue_script( 'global-mrkv-checkbox', MRKV_CHECKBOX_ASSETS_URL . '/js/global/global-checkbox.js', [ 'jquery', 'jquery-ui-autocomplete', 'admin-checkbox-select2-js' ], MRKV_CHECKBOX_PLUGIN_VERSION, true );

                wp_localize_script( 'global-mrkv-checkbox', 'mrkv_checkbox_helper', $localization_data );

                do_action( 'mrkv_checkbox_assets_after_global_enqueue' );
            }

            /**
             * 2. Specific Plugin Settings/Receipts Pages
             */
            $plugin_pages = apply_filters( 'mrkv_checkbox_assets_admin_pages', [
                'toplevel_page_mrkv_checkbox_settings', 
                'mrkv-checkbox_page_mrkv_checkbox_all_receipts'
            ] );

            if ( in_array( $hook, $plugin_pages, true ) ) {
                
                do_action( 'mrkv_checkbox_assets_before_admin_enqueue' );

                wp_enqueue_style( 'admin-mrkv-checkbox-select2', MRKV_CHECKBOX_ASSETS_URL . '/css/global/select2.min.css', [], MRKV_CHECKBOX_PLUGIN_VERSION );
                wp_enqueue_style( 'admin-mrkv-checkbox', MRKV_CHECKBOX_ASSETS_URL . '/css/admin/admin-mrkv-checkbox.css', [], MRKV_CHECKBOX_PLUGIN_VERSION );
                
                if ( ! wp_script_is( 'admin-checkbox-select2-js', 'enqueued' ) ) {
                    wp_enqueue_script( 'admin-checkbox-select2-js', MRKV_CHECKBOX_ASSETS_URL . '/js/global/select2.min.js', [ 'jquery' ], MRKV_CHECKBOX_PLUGIN_VERSION, true );
                }

                wp_enqueue_script( 'admin-mrkv-checkbox', MRKV_CHECKBOX_ASSETS_URL . '/js/admin/admin-mrkv-checkbox.js', [ 'jquery', 'admin-checkbox-select2-js' ], MRKV_CHECKBOX_PLUGIN_VERSION, true );

                wp_localize_script( 'admin-mrkv-checkbox', 'mrkv_checkbox_helper', $localization_data );

                do_action( 'mrkv_checkbox_assets_after_admin_enqueue' );
            }
        }
    }
}