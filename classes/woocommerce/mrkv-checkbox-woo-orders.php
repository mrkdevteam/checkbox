<?php
/**
 * Class for managing Checkbox actions in the WooCommerce Order List.
 * Optimized for HPOS and Legacy Compatibility with strict Security.
 */

# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! class_exists( 'MRKV_CHECKBOX_WOO_ORDERS' ) ) {

    class MRKV_CHECKBOX_WOO_ORDERS {
        
        /** @var string URL base for Checkbox receipts */
        private $receipt_url_base = 'https://check.checkbox.ua/';
        
        /** @var array Plugin settings */
        private $settings;

        /**
         * Constructor: Initialize hooks and load settings
         */
        public function __construct() {
            $this->settings = get_option( 'mrkv_checkbox', [] );

            $is_hpos = ( class_exists( OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled() );
            
            $column_hook = $is_hpos ? 'manage_woocommerce_page_wc-orders_columns' : 'manage_edit-shop_order_columns';
            $data_hook   = $is_hpos ? 'manage_woocommerce_page_wc-orders_custom_column' : 'manage_shop_order_posts_custom_column';
            $bulk_hook   = $is_hpos ? 'woocommerce_page_wc-orders' : 'edit-shop_order';

            add_filter( $column_hook, [ $this, 'add_custom_column' ] );
            add_action( $data_hook, [ $this, 'render_column_data' ], 10, 2 );
        }

        public function add_custom_column( $columns ) {
            $columns['mrkv_checkbox_receipt'] = esc_html__( 'Checkbox', 'checkbox' );
            return $columns;
        }

        public function render_column_data( $column, $post_or_order ) {
            if ( $column !== 'mrkv_checkbox_receipt' ) return;

            $order = ( $post_or_order instanceof \WC_Order ) ? $post_or_order : wc_get_order( $post_or_order );
            if ( ! $order ) return;

            echo '<div class="mrkv-checkbox-orders-col" data-order-id="' . esc_attr( $order->get_id() ) . '">';
            
            do_action( 'mrkv_checkbox_orders_column_before_content', $order );

            $receipt_id  = $order->get_meta( 'receipt_id' );

            if ( $receipt_id ) {
                $this->render_receipt_link( $receipt_id, '', $order );
            }

            do_action( 'mrkv_checkbox_orders_column_after_content', $order );
            
            echo '</div>';
        }

        private function render_receipt_link( $id, $label, $order ) {
            $url = $this->receipt_url_base . $id;
            
            do_action( 'mrkv_checkbox_orders_before_receipt_link', $id, $order );

            printf(
                '<a href="%1$s" target="_blank" class="mrkv-receipt-link" style="display:block; margin-bottom:4px; text-decoration:none;">
                    <span style="font-size:10px; color:#666; display:block;">%2$s</span>
                    <strong>%3$s</strong>
                    <img src="%4$s" style="width:12px; vertical-align:middle; margin-left:4px;" alt="link">
                </a>',
                esc_url( $url ),
                esc_html( $label ),
                esc_html( $id ),
                esc_url( MRKV_CHECKBOX_IMG_URL . '/global/external-link.svg' )
            );

            do_action( 'mrkv_checkbox_orders_after_receipt_link', $id, $order );
        }
    }
}