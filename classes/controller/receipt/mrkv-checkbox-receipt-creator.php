<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

if ( ! class_exists( 'MRKV_CHECKBOX_RECEIPT_CREATOR' ) ) {
    class MRKV_CHECKBOX_RECEIPT_CREATOR 
    {
        private $cashbox_id;
        private $order;
        private $settings_data;
        private $is_dev;
        private $has_saved_settings;
        private $old_checker;
        private $cashbox_data;
        private $order_products_total;
    
        public function __construct($cashbox_id, $order, $settings_data, $old_checker) 
        {
            $this->cashbox_id           = $cashbox_id;
            $this->order                = $order;
            $this->settings_data        = $settings_data;
            $this->old_checker          = $old_checker;
            $this->has_saved_settings   = !empty($settings_data) && is_array($settings_data);
            $this->is_dev               = !empty($this->get_setting('test_mode', 'enabled'));
            $this->cashbox_data         = apply_filters( 'mrkv_checkbox_credential_cashbox_data', $this->get_current_cashbox(), $this->order, $this->settings_data);
            $this->order_products_total = 0;
        }
    
        private function get_setting($section, $key, $legacy_option = '', $added_key = '') {
            if ($this->has_saved_settings) {
                if ($added_key) {
                    return $this->settings_data[$section][$key][$added_key] ?? '';
                }
                return $this->settings_data[$section][$key] ?? '';
            }
            return $legacy_option ? get_option($legacy_option) : '';
        }

        /**
         * Main entry point to create receipts with support for Prepay/Afterpay
         * * @param string $type 'sell', 'prepay', or 'afterpay'
         * @param float|int $pay_amount Amount for partial payments
         */
        public function create_receipt()
        {
            if (!$this->cashbox_data) {
                $this->order->add_order_note('[morkva] Checkbox ' . __('Error: Cashbox data empty', 'checkbox'));
                $this->order->save();
                return false;
            }
            

            $args = [
                'cashier_name' => $this->get_cashier_full_name(),
                'goods'        => $this->get_goods(),
            ];

            $args = $this->check_order_total($args);
            $args = $this->add_payment_data($args);
            $args = $this->add_footer_data($args, $type);

            $args = apply_filters('mrkv_checkbox_create_receipt_args', $args, $this->order);

            require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/api/mrkv-checkbox-api.php';

            $has_settings_change = false;

            if (empty($this->cashbox_data['signin'])) {
                require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/authorization/mrkv-checkbox-authorization.php';
                $temp_api = new MRKV_CHECKBOX_API($this->cashbox_data['register_key'] ?? '', '', $this->is_dev);
                $auth     = new MRKV_CHECKBOX_AUTHORIZATION('', $this->cashbox_data['cashier_login'] ?? '', $this->cashbox_data['cashier_password'] ?? '', $temp_api);
                $access_token = $auth->mrkv_checkbox_get_authorization_token();

                if (!$access_token) {
                    $this->order->add_order_note('[morkva] Checkbox ' . __('Error: Authorization issue.', 'checkbox'));
                    $this->order->save();
                    return false;
                }

                $this->cashbox_data['signin'] = $access_token;
                if ($this->is_dev) {
                    $this->settings_data['test_mode']['signin'] = $access_token;
                } else {
                    $this->settings_data['cashiers'][$this->cashbox_id]['signin'] = $access_token;
                }
                $has_settings_change = true;
            }

            $api = new MRKV_CHECKBOX_API($this->cashbox_data['register_key'] ?? '', $this->cashbox_data['signin'], $this->is_dev);

            if ($this->cashbox_data['shift_status'] != 'opened' && $this->get_setting('automation', 'open_shift', 'ppo_autoopen_shift')) {
                require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/shift/mrkv-checkbox-connect.php';
                $connector = new MRKV_CHECKBOX_CONNECT($api);
                $result = $connector->connect();
                
                if (($result['status'] ?? false) === true) {
                    $target = $this->is_dev ? 'test_mode' : 'cashiers';
                    if ($this->is_dev) {
                        $this->settings_data['test_mode']['shift_status'] = 'opened';
                    } else {
                        $this->settings_data['cashiers'][$this->cashbox_id]['shift_status'] = 'opened';
                    }
                    $has_settings_change = true;
                    sleep(8);
                }
                elseif(! empty( $result['status_code'] ) && $result['status_code'] == 401)
                {
                    require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/authorization/mrkv-checkbox-authorization.php';
                    $temp_api = new MRKV_CHECKBOX_API($this->cashbox_data['register_key'] ?? '', '', $this->is_dev);
                    $auth     = new MRKV_CHECKBOX_AUTHORIZATION('', $this->cashbox_data['cashier_login'] ?? '', $this->cashbox_data['cashier_password'] ?? '', $temp_api);
                    $access_token = $auth->mrkv_checkbox_get_authorization_token();

                    if (!$access_token) {
                        $this->order->add_order_note('[morkva] Checkbox ' . __('Error: Authorization issue.', 'checkbox'));
                        $this->order->save();
                        return false;
                    }

                    $this->cashbox_data['signin'] = $access_token;
                    if ($this->is_dev) {
                        $this->settings_data['test_mode']['signin'] = $access_token;
                    } else {
                        $this->settings_data['cashiers'][$this->cashbox_id]['signin'] = $access_token;
                    }
                    $has_settings_change = true;

                    $api = new MRKV_CHECKBOX_API($this->cashbox_data['register_key'] ?? '', $this->cashbox_data['signin'], $this->is_dev);
                    $connector = new MRKV_CHECKBOX_CONNECT($api);
                    $result = $connector->connect();
                    
                    if (($result['status'] ?? false) === true) {
                        $target = $this->is_dev ? 'test_mode' : 'cashiers';
                        if ($this->is_dev) {
                            $this->settings_data['test_mode']['shift_status'] = 'opened';
                        } else {
                            $this->settings_data['cashiers'][$this->cashbox_id]['shift_status'] = 'opened';
                        }
                        $has_settings_change = true;
                        sleep(8);
                    }
                    else {
                        $this->order->add_order_note('[morkva] Checkbox ' . __('Error:', 'checkbox') . ' ' . ($result['message'] ?? 'Shift failed to open'));
                        $this->order->save();
                        return false;
                    }
                }
                else {
                    $this->order->add_order_note('[morkva] Checkbox ' . __('Error:', 'checkbox') . ' ' . ($result['message'] ?? 'Shift failed to open'));
                    $this->order->save();
                    return false;
                }
            }

            if ($has_settings_change) {
                update_option('mrkv_checkbox', $this->settings_data);
            }

            $validation = $this->mrkv_checkbox_validate_receipt_data($args);
            if (is_wp_error($validation)) {
                $this->order->add_order_note('[morkva] Checkbox ' . __('Data validation error Checkbox: ', 'checkbox') . $validation->get_error_message());
                $this->order->save();
                return false;
            }

            $request_result = $api->mrkv_checkbox_make_request('POST', '/api/v1/receipts/sell', $args);

            if (isset($request_result['error']) && isset($request_result['status_code']) && $request_result['status_code'] == 400 && $this->get_setting('automation', 'open_shift', 'ppo_autoopen_shift')) 
            {
                require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/shift/mrkv-checkbox-disconnect.php';
                $disconnector = new MRKV_CHECKBOX_DISCONNECT($api);
                $result = $disconnector->connect();
                sleep(4);
                require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/shift/mrkv-checkbox-connect.php';
                $connector = new MRKV_CHECKBOX_CONNECT($api);
                $result = $connector->connect();
                
                if (($result['status'] ?? false) === true) {
                    $target = $this->is_dev ? 'test_mode' : 'cashiers';
                    if ($this->is_dev) {
                        $this->settings_data['test_mode']['shift_status'] = 'opened';
                    } else {
                        $this->settings_data['cashiers'][$this->cashbox_id]['shift_status'] = 'opened';
                    }
                    $has_settings_change = true;
                    sleep(8);
                }
                elseif(! empty( $result['status_code'] ) && $result['status_code'] == 401)
                {
                    require_once MRKV_CHECKBOX_PLUGIN_PATH . 'classes/controller/authorization/mrkv-checkbox-authorization.php';
                    $temp_api = new MRKV_CHECKBOX_API($this->cashbox_data['register_key'] ?? '', '', $this->is_dev);
                    $auth     = new MRKV_CHECKBOX_AUTHORIZATION('', $this->cashbox_data['cashier_login'] ?? '', $this->cashbox_data['cashier_password'] ?? '', $temp_api);
                    $access_token = $auth->mrkv_checkbox_get_authorization_token();

                    if (!$access_token) {
                        $this->order->add_order_note('[morkva] Checkbox ' . __('Error: Authorization issue.', 'checkbox'));
                        $this->order->save();
                        return false;
                    }

                    $this->cashbox_data['signin'] = $access_token;
                    if ($this->is_dev) {
                        $this->settings_data['test_mode']['signin'] = $access_token;
                    } else {
                        $this->settings_data['cashiers'][$this->cashbox_id]['signin'] = $access_token;
                    }
                    $has_settings_change = true;

                    $api = new MRKV_CHECKBOX_API($this->cashbox_data['register_key'] ?? '', $this->cashbox_data['signin'], $this->is_dev);
                    $disconnector = new MRKV_CHECKBOX_DISCONNECT($api);
                    $result = $disconnector->connect();
                    sleep(4);
                    $connector = new MRKV_CHECKBOX_CONNECT($api);
                    $result = $connector->connect();
                    
                    if (($result['status'] ?? false) === true) {
                        $target = $this->is_dev ? 'test_mode' : 'cashiers';
                        if ($this->is_dev) {
                            $this->settings_data['test_mode']['shift_status'] = 'opened';
                        } else {
                            $this->settings_data['cashiers'][$this->cashbox_id]['shift_status'] = 'opened';
                        }
                        $has_settings_change = true;
                        sleep(8);
                    }
                    else {
                        $this->order->add_order_note('[morkva] Checkbox ' . __('Error:', 'checkbox') . ' ' . ($result['message'] ?? 'Shift failed to open'));
                        $this->order->save();
                        return false;
                    }
                }
                else {
                    $this->order->add_order_note('[morkva] Checkbox ' . __('Error:', 'checkbox') . ' ' . ($result['message'] ?? 'Shift failed to open'));
                    $this->order->save();
                    return false;
                }

                if ($has_settings_change) {
                    update_option('mrkv_checkbox', $this->settings_data);
                }
                
                $request_result = $api->mrkv_checkbox_make_request('POST', '/api/v1/receipts/sell', $args);
            }

            if (isset($request_result['error'])) {
                $this->handle_api_error($request_result);
                return false;
            }

            $receipt_id = $request_result['id'] ?? '';
            if ($receipt_id) {
                $meta_key_id = 'receipt_id';
                $meta_key_url = 'receipt_url';
                $this->order->update_meta_data($meta_key_id, $receipt_id);
                $this->order->update_meta_data($meta_key_url, 'https://check.checkbox.ua/' . $receipt_id);

                /* translators: 1: Type of receipt (e.g. Simple), 2: Receipt ID from Checkbox.ua */
                $mrkv_checkbox_note_text = '[morkva] ' . sprintf( __( 'Checkbox %1$s receipt created! ID: %2$s', 'checkbox' ), $type, $receipt_id );
                $this->order->add_order_note( $mrkv_checkbox_note_text );
                $this->order->save();
                return $request_result;
            }

            return false;
        }

        private function handle_api_error($result) {
            // translators: 1: HTTP status code or N/A, 2: Error message from Checkbox API.
            $msg_text = '[morkva] ' . __( 'Checkbox API Error (%1$s): %2$s', 'checkbox' );
            $msg = sprintf( $msg_text, $result['status_code'] ?? 'N/A', $result['error'] . ' <a href="' . admin_url('admin.php?page=mrkv_checkbox_settings#log-mrkv') . '" target="_blank">' . __( 'Log', 'checkbox' ) . '</a>');

            $this->order->add_order_note($msg);
            $this->order->save();
        }

        public function get_goods()
        {
            $goods = [];
            $liqpay_rate = 1;
            $exclude_zero = $this->get_setting('added_fields', 'zero_price', 'ppo_zero_product_exclude');
            $decimals = wc_get_price_decimals();
            $coupon_name = $this->get_setting('discount', 'label', 'ppo_receipt_coupon_text') ?? __('Coupon', 'checkbox');

            foreach ($this->order->get_items() as $item) {
                $product = $item->get_product();
                if (!$product) continue;

                $subtotal = (float) $item->get_subtotal();
                $quantity = (float) $item->get_quantity();
                $unit_price = $subtotal / max(1, $quantity);

                if ($exclude_zero && $unit_price <= 0) continue;

                $price_in_cents = (float) wc_format_decimal($unit_price, $decimals) * 100;
                if ($liqpay_rate) $price_in_cents *= $liqpay_rate;

                $good_data = [
                    'good' => [
                        'code'  => (string) $this->resolve_product_code($item),
                        'name'  => (string) $this->resolve_product_name($item, $product),
                        'price' => (int) round($price_in_cents),
                    ],
                    'quantity' => (int) ($quantity * 1000),
                ];

                if ($tax = $this->resolve_tax_codes($product)) $good_data['good']['tax'] = $tax;
                if ($barcode = $this->get_product_barcode($item)) $good_data['good']['barcode'] = $barcode;

                $item_total_cents = (float)$item->get_total() * 100 * ($liqpay_rate ?: 1);
                $item_subtotal_cents = $price_in_cents * $quantity;

                if (round($item_subtotal_cents) > round($item_total_cents)) {
                    $discount_value = round($item_subtotal_cents - $item_total_cents);
                    $coupons = $this->order->get_coupon_codes();
                    $good_data['discounts'][] = [
                        'type'  => 'DISCOUNT',
                        'mode'  => 'VALUE',
                        'value' => (int) $discount_value,
                        'name'  => $coupon_name
                    ];
                }

                $this->order_products_total += round($item_total_cents);
                $goods[] = $good_data;
            }
            return $goods;
        }

        private function get_current_cashbox() {
            if (!$this->has_saved_settings)
            {
                if ($this->cashbox_id === 'default') return $this->old_checker->mrkv_checkbox_get_default_cashbox();
            }
            
            return $this->is_dev ? ($this->settings_data['test_mode'] ?? '') : ($this->settings_data['cashiers'][$this->cashbox_id] ?? '');
        }

        private function get_cashier_full_name() {
            return trim(($this->cashbox_data['cashier_name'] ?? '') . ' ' . ($this->cashbox_data['cashier_lastname'] ?? ''));
        }

        private function resolve_product_name($item, $product) {
            return $item->get_name();
        }

        private function resolve_product_code($item) {
            return $item->get_id() . '-' . $item->get_name();
        }

        private function resolve_tax_codes($product) {
            $tax = $this->cashbox_data['register_tax_code'];

            return $tax ? explode(',', $tax) : null;
        }

        private function get_product_barcode($item) {
            if (!$this->get_setting('barcode', 'enabled', 'ppo_barcode')) return '';
            $type = $this->get_setting('barcode', 'type', 'ppo_barcode_type');
            $meta_key = $this->get_setting('barcode', 'title', 'ppo_barcode_from_meta');
            $target_id = $item->get_variation_id() ?: $item->get_product_id();

            switch ($type) {
                case 'barcode': return $this->order->get_meta('_global_unique_id');
                case 'meta': return $meta_key ? get_post_meta($target_id, $meta_key, true) : '';
                case 'attribute': return $meta_key ? $item->get_product()->get_attribute($meta_key) : '';
                default: return '';
            }
        }

        private function check_order_total($params) {
            $total_to_check = $this->order->get_total();
            $rate = 1;
            $order_total_cents = round($total_to_check * 100 * $rate);
            $discount_label = $this->get_setting('discount', 'label', 'ppo_delivery_type');

            if ($this->order_products_total > $order_total_cents) {
                $diff = (int)$this->order_products_total - $order_total_cents;
                $params['discounts'][] = [
                    'type'  => 'DISCOUNT',
                    'mode'  => 'VALUE',
                    'value' => $diff,
                    'name'  => $discount_label
                ];

                $this->order_products_total = $order_total_cents;
            }
            
            return $params;
        }

        private function add_payment_data($params) {
            $method = $this->order->get_payment_method();
            $custom_label = '';
            $payment_type = ($method === 'cod') ? 'CASH' : 'CASHLESS';
            $payment_code = ($method === 'cod') ? 0 : 1;
            $payment_label = ($method === 'cod') ? 'Готівка' : 'Електронний платіжний засіб';
            require_once MRKV_CHECKBOX_PLUGIN_PATH . 'static/constant-payments.php';

            if ($this->has_saved_settings) 
            {
                $custom_label = $this->settings_data['automation']['payments'][$method]['custom_label'] ?? '';
                $payment_type = $this->settings_data['automation']['payments'][$method]['form'] ?? $payment_type;
                $payment_label = $this->settings_data['automation']['payments'][$method]['label'] ?? $payment_label;
            }
            else{
                $ppo_payment_type_label = get_option( 'ppo_payment_type_label', [] );
                $custom_label = $ppo_payment_type_label[$method] ?? '';
                $ppo_payment_type = get_option( 'ppo_payment_type', [] );
                $payment_type =$ppo_payment_type[$method] ?? $payment_type;
                $payment_new_settings = get_option('ppo_payment_type_checkbox');
                $payment_label = $payment_new_settings[$payment_type]['label'] ?? $payment_label;
            }

            switch ($method) {
                case 'morkva-monopay':
                    $payment_code = 1;
                    $payment_label = !empty($custom_label) ? $custom_label : 'Платіж через інтегратора plata by mono';
                    break;

                case 'morkva-liqpay':
                    $payment_code = 1;
                    $payment_label = !empty($custom_label) ? $custom_label : 'Платіж LiqPay';
                    break;

                case 'morkva-monopay-prepay':
                case 'morkva-liqpay-prepay':
                    $payment_code = 1;
                    $payment_label = !empty($custom_label) ? $custom_label : 'Післяплата';
                    break;

                default:
                    if ($custom_label == 'Післяплата (з контролем оплати)') {
                        $payment_code = 1;
                        $payment_label = 'Платіж NovaPay';
                        break;
                    }
                    $payment_code   = MRKV_CHECKBOX_PAYMENT_LABELS[$payment_label] ?? $payment_code;
    			    $payment_label = $custom_label ?? $payment_label;

                    break;
            }

            $payment = [
                'type'  => $payment_type,
                'value' => (int) $this->order_products_total,
                'label' => $payment_label,
                'code'  => $payment_code,
            ];

            $meta_map = [
                'acquirer_and_seller' => ['_mrkv_liqpay_acq_id', 'mrkv_mopay_accuiring_tran_id'],
                'terminal'            => ['_mrkv_liqpay_terminal_id', 'mrkv_mopay_accuiring_terminal'],
                'card_mask'           => ['_mrkv_liqpay_sender_card_mask2', 'mrkv_mopay_accuiring_masked_pan'],
                'payment_system'      => ['_mrkv_liqpay_sender_card_type', 'mrkv_mopay_accuiring_payment_system'],
                'auth_code'           => ['_mrkv_liqpay_authcode_debit', 'mrkv_mopay_accuiring_approval_code'],
                'rrn'                 => ['_mrkv_liqpay_rrn_debit', '_mrkv_liqpay_liqpay_order_id', 'mrkv_mopay_accuiring_rrn']
            ];

            foreach ($meta_map as $key => $keys) {
                foreach ($keys as $k) {
                    if ($val = $this->order->get_meta($k)) {
                        $payment[$key] = $val;
                        break;
                    }
                }
            }

            $liqpay_fee = $this->order->get_meta('_mrkv_liqpay_agent_commission');
            $mono_fee   = $this->order->get_meta('mrkv_mopay_accuiring_fee');

            if (!empty($liqpay_fee)) {
                $payment['commission'] = (int)round($liqpay_fee * 100);
            } elseif (!empty($mono_fee)) {
                $payment['commission'] = (int)$mono_fee;
            }

            $params['payments'][] = $payment;
            return $params;
        }

        private function add_footer_data($params) {
            $footer = $this->get_setting('added_fields', 'description', 'ppo_receipt_footer');

            if (!$footer) return $params;

            $replace = [
                '[order_id]'           => $this->order->get_id(),
                '[website_title]'      => get_bloginfo('name'),
                '[order_created_date]' => $this->order->get_date_created() ? $this->order->get_date_created()->date('d-m-Y H:i:s') : '',
                '[order_paid_date]'    => $this->order->get_date_paid() ? $this->order->get_date_paid()->date('d-m-Y H:i:s') : '',
                '[novapost_pro_ttn]'   => $this->order->get_meta('novaposhta_ttn') ?: $this->order->get_meta('mrkv_ua_ship_invoice_number')
            ];

            $params['footer'] = strtr($footer, $replace);
            return $params;
        }

        private function mrkv_checkbox_validate_receipt_data( $args ) 
        {
            if ( empty( $args['goods'] ) || ! is_array( $args['goods'] ) ) {
                return new WP_Error( 'empty_goods', __( 'The goods list is empty.', 'checkbox' ) );
            }

            $total_goods_sum = 0;

            foreach ( $args['goods'] as $index => $item ) {
                $good     = $item['good'] ?? [];
                $name     = $good['name'] ?? '';
                $price    = $good['price'] ?? 0;
                $quantity = $item['quantity'] ?? 0;
                $discounts = $item['discounts'] ?? []; 

                if ( empty( $name ) ) {
                    // translators: %d: The index number of the item in the list.
                    $error_msg = sprintf( __( 'Item #%1$d is missing a name.', 'checkbox' ), $index + 1 );
                    return new WP_Error( 'invalid_good_name', $error_msg );
                }

                if ( $price <= 0 ) {
                    // translators: 1: Product name, 2: The invalid price value.
                    $error_msg = sprintf( __( 'Item "%1$s" has an invalid price (%2$d). Price must be greater than 0.', 'checkbox' ), $name, $price );
                    return new WP_Error( 'invalid_good_price', $error_msg );
                }

                if ( $quantity <= 0 ) {
                    // translators: 1: Product name, 2: The invalid quantity value.
                    $error_msg = sprintf( __( 'Item "%1$s" has an invalid quantity (%2$d).', 'checkbox' ), $name, $quantity );
                    return new WP_Error( 'invalid_good_qty', $error_msg );
                }

                $line_total = ( $price * $quantity ) / 1000;

                if ( ! empty( $discounts ) ) {
                    foreach ( $discounts as $discount ) {
                        $discount_value = $discount['value'] ?? 0;
                        $line_total -= $discount_value;
                    }
                }

                $total_goods_sum += round( $line_total );
            }

            if ( ! empty( $args['discounts'] ) ) {
                foreach ( $args['discounts'] as $discount ) {
                    if ( $discount['type'] === 'DISCOUNT' ) {
                        $total_goods_sum -= $discount['value'];
                    } else {
                        $total_goods_sum += $discount['value'];
                    }
                }
            }

            if ( empty( $args['payments'] ) || ! is_array( $args['payments'] ) ) {
                return new WP_Error( 'empty_payments', __( 'Payment data is missing.', 'checkbox' ) );
            }

            $total_payments_sum = 0;
            foreach ( $args['payments'] as $payment ) {
                $total_payments_sum += $payment['value'] ?? 0;
            }

            if ( abs( $total_goods_sum - $total_payments_sum ) > 1 ) {
            // translators: 1: Total sum of all goods, 2: Total sum of all payments.
                $msg = __( 'Sum mismatch detected! Goods Total: %1$.2f. Payments Total: %2$.2f.', 'checkbox' );
                $error_msg = sprintf( $msg, $total_goods_sum / 100, $total_payments_sum / 100 );

                return new WP_Error( 'sum_mismatch', $error_msg );
            }

            return true;
        }
    }
}