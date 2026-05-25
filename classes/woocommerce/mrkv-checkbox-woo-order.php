<?php
# Exit if accessed directly
if (!defined('ABSPATH')) exit;

use Automattic\WooCommerce\Utilities\OrderUtil;

if (!class_exists('MRKV_CHECKBOX_WOO_ORDER')) {
    /**
     * Class for setup Checkbox meta box on WooCommerce order page
     */
    class MRKV_CHECKBOX_WOO_ORDER {

        private $receipt_url_base = 'https://check.checkbox.ua/';

        public function __construct() {
            add_action('add_meta_boxes', [$this, 'mrkv_checkbox_add_meta_boxes'], 10, 2);
        }

        public function mrkv_checkbox_add_meta_boxes($post_type, $post) {
            $screen = (class_exists(OrderUtil::class) && OrderUtil::custom_orders_table_usage_is_enabled()) 
                      ? wc_get_page_screen_id('shop-order') 
                      : 'shop_order';

            add_meta_box(
                'mrkv_checkbox_data_box',
                __('morkva Checkbox', 'checkbox'),
                [$this, 'mrkv_checkbox_render_meta_box'],
                $screen,
                'side',
                'core'
            );
        }

        public function mrkv_checkbox_render_meta_box($post_or_order) 
        {
            $order_id = null;

            switch (true) {
                case $post_or_order instanceof \WC_Order:
                    $order_id = $post_or_order->get_id();
                    break;
                case $post_or_order instanceof \WP_Post:
                    $order_id = $post_or_order->ID;
                    break;
                case is_numeric($post_or_order):
                    $order_id = (int) $post_or_order;
                    break;
            }

            $order = $order_id ? wc_get_order($order_id) : null;
        
            if (!$order) return;

            $order_id = $order->get_id();
            
            $receipt_id          = $order->get_meta('receipt_id');

            echo '<div class="mrkv-checkbox-wrapper">';
            
            do_action('mrkv_checkbox_order_before_metabox_content', $order);

            if ($receipt_id) {
                $this->render_existing_receipt($order, $receipt_id);
            } else {
                $this->render_create_receipt_form($order_id);
            }

            do_action('mrkv_checkbox_order_after_metabox_content', $order);
            
            echo '</div>';
        }

        private function render_existing_receipt($order, $receipt_id) {
            $receipt_url = $this->receipt_url_base . $receipt_id;
            ?>
            <h3><?php esc_html_e('Receipt', 'checkbox'); ?></h3>
            <a class="mrkv_checkbox_link-receipt_id" href="<?php echo esc_url($receipt_url); ?>" target="_blank">
                <?php echo esc_html($receipt_id); ?>
            </a>
            
            <hr class="mrkv-hr-sidebar">
            
            <?php do_action('mrkv_checkbox_order_before_actions', $order); ?>

            <h3><?php esc_html_e('Receipt action', 'checkbox'); ?></h3>
            <div class="mrkv_ua_invoice_action_list">
                <a data-order="<?php echo esc_attr($order->get_id()); ?>" class="mrkv_checkbox_remove_receipt" style="cursor:pointer;">
                    <img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/trash-icon.svg'); ?>" alt="">
                    <?php esc_html_e('Remove receipt', 'checkbox'); ?>
                </a>
                <a href="<?php echo esc_url($receipt_url); ?>" class="mrkv_ua_ship_send_remove_ttn" target="_blank">
                    <img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/sticker-icon.svg'); ?>" alt="">
                    <?php esc_html_e('Open receipt', 'checkbox'); ?>
                </a>
            </div>

            <?php do_action('mrkv_checkbox_order_after_actions', $order); ?>

            <hr class="mrkv-hr-sidebar">
            
            <h3><?php esc_html_e('Manual Receipt Ref', 'checkbox'); ?></h3>
            <input type="text" name="mrkv_checkbox_custom_receipt" value="<?php echo esc_attr($receipt_id); ?>" class="widefat" style="margin: 5px 0;">
            <div data-order="<?php echo esc_attr($order->get_id()); ?>" class="mrkv_ua_ship_custom_receipt button">
                <?php esc_html_e('Sync Receipt ID', 'checkbox'); ?>
                <div class="mrkv_ua_ship_create_receipt__loader"></div>
            </div>
            <?php
        }

        private function render_create_receipt_form($order_id) {
            $settings = get_option('mrkv_checkbox');
            $cashiers = $settings['cashiers'] ?? [];
            
            do_action('mrkv_checkbox_order_before_form', $order_id);
            ?>
            <div class="mrkv_checkbox_create_receipt_form">
                <?php if (!empty($cashiers)) : ?>
                    <label for="mrkv_checkbox_cashiers"><?php esc_html_e('Select Cashbox', 'checkbox'); ?></label>
                    <select name="mrkv_checkbox_cashiers" id="mrkv_checkbox_cashiers" style="width:100%; margin:5px 0 10px; display:none;">
                        <?php foreach ($cashiers as $slug => $data) : ?>
                            <option value="<?php echo esc_attr($slug); ?>">
                                <?php 
                                    if (isset($data['register_name'])) {
                                        echo esc_html($data['register_name']);
                                    } else {
                                        /* translators: %s: Cashbox slug */
                                        printf(esc_html__('Cashbox %s', 'checkbox'), esc_html($slug));
                                    }
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <?php do_action('mrkv_checkbox__order_form_inside', $order_id); ?>

                <div data-order="<?php echo esc_attr($order_id); ?>" class="mrkv_ua_ship_global_create__receipt button button-primary button-large" style="width:100%; text-align:center;">
                    <span><?php esc_html_e('Create Receipt', 'checkbox'); ?></span>
                    <div class="mrkv_ua_ship_create_receipt__loader"></div>
                </div>
            </div>
            <?php
            do_action('mrkv_checkbox__order_after_form', $order_id);
        }
    }
}