<?php

/**
 * Plugin Name: Checkbox Integration
 * Plugin URI: https://morkva.co.ua/shop-2/checkbox
 * Description: WooCommerce Checkbox Integration
 * Version: 0.0.1
 * Author: MORKVA
 * Author URI: https://morkva.co.ua
 * License: GPL v2 or later
 */

if ( ! defined('ABSPATH')) {
  exit;
}

use Automattic\WooCommerce\Admin\Overrides\Order;

require_once( 'api/Mrkv_CheckboxApi.php');

if ( !function_exists( 'mrkv_checkbox_register_mysettings' ) ) {
    function mrkv_checkbox_register_mysettings() {
        register_setting( 'ppo-settings-group', 'ppo_login' );
        register_setting( 'ppo-settings-group', 'ppo_password' );
        register_setting( 'ppo-settings-group', 'ppo_cashbox_key' );
        register_setting( 'ppo-settings-group', 'ppo_auto_create');
    }
}
add_action( 'admin_init', 'mrkv_checkbox_register_mysettings' );

if ( !function_exists( 'mrkv_checkbox_registerPluginPageInMenu' ) ) {
    function mrkv_checkbox_registerPluginPageInMenu(){
        add_menu_page("PPO", "PPO settings", "manage_woocommerce", "ppoSettings", "mrkv_checkbox_showPluginAdminPage");
    }
}
add_action( 'admin_menu', 'mrkv_checkbox_registerPluginPageInMenu');

// create menu in order details page
if ( !function_exists( 'mrkv_checkbox_wc_add_order_meta_box_action' ) ) {
    function mrkv_checkbox_wc_add_order_meta_box_action( $actions ) {
        $actions['create_bill_action'] = 'Create receipt';
        return $actions;
    }
}
add_action( 'woocommerce_order_actions', 'mrkv_checkbox_wc_add_order_meta_box_action' );

/* @var Order $order  */
if ( !function_exists( 'mrkv_checkbox_wc_process_order_meta_box_action' ) ) {
function mrkv_checkbox_wc_process_order_meta_box_action($order) {
        $login = get_option('ppo_login');
        $password = get_option('ppo_password');
        $cashbox_key = get_option('ppo_cashbox_key');
        if (!$login) {
            $order->add_order_note( "Укажите логин в настройках модуля PPO", $is_customer_note = 0, $added_by_user = false );
            return;

        }
        if (!$password) {
            $order->add_order_note( "Укажите пароль в настройках модуля PPO", $is_customer_note = 0, $added_by_user = false );
            return;

        }
        if (!$cashbox_key) {
            $order->add_order_note( "Укажите ключ кассы в настройках модуля PPO", $is_customer_note = 0, $added_by_user = false );
            return;
        }

        $api = new Mrkv_CheckboxApi($login,$password,$cashbox_key,true);
        // Check current status
        $current_shift = $api->getCurrentCashierShift();
        if (isset($current_shift['status'])&&($current_shift['status']!='OPENED')) {
            $order->add_order_note( "У вас не открыта смена ", $is_customer_note = 0, $added_by_user = false );
            return;
        }

        $status = mrkv_checkbox_create_reciept($api,$order);
        // save order ID
        if ($status) {
            $order->add_order_note( "Reciept created", $is_customer_note = 0, $added_by_user = false );
        } else {
            $order->add_order_note( "Error create reciept", $is_customer_note = 0, $added_by_user = false );
        }

    }
}
add_action( 'woocommerce_order_action_create_bill_action', 'mrkv_checkbox_wc_process_order_meta_box_action' );

if ( !function_exists( 'mrkv_checkbox_wc_new_order_column' ) ) {
    function mrkv_checkbox_wc_new_order_column( $columns ) {
        $columns['receipt_column'] = 'Receipt';
        return $columns;
    }
}
add_filter( 'manage_edit-shop_order_columns', 'mrkv_checkbox_wc_new_order_column' );

/**
 * Adds 'Receipt' column content to 'Orders' page
 *
 * @param string[] $column name of column being displayed
 */
if ( !function_exists( 'mrkv_checkbox_wc_cogs_add_order_receipt_column_content' ) ) {
    function mrkv_checkbox_wc_cogs_add_order_receipt_column_content( $column ) {
        global $post;

        if ( 'receipt_column' === $column ) {

            $order = wc_get_order( $post->ID );
            $receipt_id = get_post_meta($order->get_id(),'reciept_id');
            echo  isset($receipt_id[0]) ? $receipt_id[0] : '';
        }
    }
}
add_action( 'manage_shop_order_posts_custom_column', 'mrkv_checkbox_wc_cogs_add_order_receipt_column_content' );

////// Make auto create receipt if order complete
if ( !function_exists( 'mrkv_checkbox_auto_create_receipt' ) ) {
    function mrkv_checkbox_auto_create_receipt($order_id, $old_status, $new_status) {
        $is_auto_create = get_option('ppo_auto_create');
        if (($new_status == 'completed')&&$is_auto_create) {
            $order = wc_get_order($order_id);

            $login = get_option('ppo_login');
            $password = get_option('ppo_password');
            $cashbox_key = get_option('ppo_cashbox_key');

            if ($login&&$password&&$cashbox_key) {
                $api = new Mrkv_CheckboxApi($login,$password,$cashbox_key,true);
                $shift = $api->getCurrentCashierShift();
                if (isset($shift['status'])&&($shift['status']=='OPENED')){
                    // create receipt
                    mrkv_checkbox_create_reciept($api,$order);
                }
            }
        }
    }
}
add_action( 'woocommerce_order_status_changed', 'mrkv_checkbox_auto_create_receipt', 99, 3 );
////// End auto create receipt if order complete

/* @var Mrkv_CheckboxApi $api  */
/* @var Order $order */
if ( !function_exists( 'mrkv_checkbox_create_reciept' ) ) {
    function mrkv_checkbox_create_reciept($api,$order) {
        $user = wp_get_current_user();
        $email = $user->user_email;
        $cashier_name = $user->first_name.' '.$user->last_name;
        $departament = 'store';

        $params = [];
        $order_data = $order->get_data();
        $goods_items = $order->get_items();

        $goods = [];
        /* @var WC_Order_Item_Product $item */
        foreach ($goods_items as $item) {
            $good = [
                'code'=>$item->get_id().'-'.$item->get_name(),
                'name'=>$item->get_name(),
                'price'=>$item->get_total()*1000
            ];

            $goods[] = [
                'good'=>$good,
                'quantity'=>$item->get_quantity()
            ];
        }

        $params['goods'] = $goods;
        $params['cashier_name'] = $cashier_name;
        $params['departament'] = $departament;
        $params['delivery'] = ['email'=>$email];
        $params['payments'][] = [
            'type'=>'CASH',
            'value'=>$order_data['total']*1000
        ];

        $reciept = $api->create_receipt($params);
        // save order ID
        if (isset($reciept['id'])) {
            // save receipt id in meta
            update_post_meta( $order->get_id(), 'reciept_id', sanitize_text_field( $reciept['id']));
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
}

// Регистрация виджета "Отображения статуса подключения и кнопки подключения"
add_action( 'wp_dashboard_setup', 'mrkv_checkbox_ppo_status_dashboard_widget' );
if ( !function_exists( 'mrkv_checkbox_ppo_status_dashboard_widget' ) ) {
    function mrkv_checkbox_ppo_status_dashboard_widget() {
        // Регистрируем виджет только для администраторов сайта
        if ( current_user_can( 'activate_plugins' ) ) {
            wp_add_dashboard_widget( 'status_widget', 'PPO status', 'mrkv_checkbox_status_widget_form' );
        }
    }
}

if ( !function_exists( 'mrkv_checkbox_status_widget_form' ) ) {
    // Отображение виджета "Мои заметки"
    function mrkv_checkbox_status_widget_form() {
        $shift_id = '';
        $is_connected = false;
        $status = 'CLOSED';

        $login = get_option('ppo_login');
        $password = get_option('ppo_password');
        $cashbox_key = get_option('ppo_cashbox_key');

        if ($login&&$password&&$cashbox_key)
        {
            $api = new Mrkv_CheckboxApi($login,$password,$cashbox_key,true);
            $shift = $api->getCurrentCashierShift();
            if ($shift) {
                $shift_id = isset($shift['id'])? $shift['id'] : '';
                $is_connected = isset($shift['status'])&&($shift['status'] == 'OPENED')? true:false;
                $status = isset($shift['status'])?$shift['status']:'';
            }
        }

        ?>

        <form>
            STATUS : <b><span id="ppo_status" class="status"><?php echo $status; ?></span></b>
            <br>
            <br>
            <input type="hidden" id="ppo_shift_id" value="<?php echo $shift_id;?>">
            <div id="ppo_proccess" style="display: none" >
                <img src="/wp-content/plugins/ppo/img/proccess.webp" width="50px" height="50px" alt="proccess imgage" />
            </div>
            <?php if (!$is_connected): ?>
                <button type="button" id="ppo_button_connect" class="start button button-secondary">Start work day</button>
                <button type="button" id="ppo_button_disconnect" style="display: none" class="end button button-secondary">End work day</button>
            <?php else : ?>
                <button type="button" id="ppo_button_connect" style="display: none" class="start button button-secondary">Start work day</button>
                <button type="button" id="ppo_button_disconnect" class="end button button-secondary">End work day</button>
            <?php endif ?>
        </form>

        <?php
    }
}

if ( !function_exists( 'mrkv_checkbox_start_connect' ) ) {
    // Ajax function
    function mrkv_checkbox_start_connect() {
        $res = [];
        $login = get_option('ppo_login');
        $password = get_option('ppo_password');
        $cashbox_key = get_option('ppo_cashbox_key');

        if ($login&&$password&&$cashbox_key) {
            $api = new Mrkv_CheckboxApi($login,$password,$cashbox_key,true);

            $shift = $api->connect();
            if (isset($shift['id'])) {
                $res['shift_id'] = $shift['id'];
                $res['status'] = $shift['status'];
                $res['message'] = '';
                wp_send_json_success($res);
            } else {
                $res['shift_id'] = '';
                $res['message'] = $shift['message'];
                wp_send_json_error($res);
            }

        } else {
            wp_send_json_error( [
                'message' => 'You missed filled login or password or cashbox_key field. Please go to settings PPO plugin and fill value.',
            ] );
        }
    }
}
// Register Ajax action
add_action('wp_ajax_mrkv_checkbox_start_connect','mrkv_checkbox_start_connect');

if ( !function_exists( 'wporg_init' ) ) {
    function mrkv_checkbox_check_connect() {
        $res = [];
        $login = get_option('ppo_login');
        $password = get_option('ppo_password');
        $cashbox_key = get_option('ppo_cashbox_key');

        if ($login && $password && $cashbox_key) {
            $shift_id = isset($_POST['shift_id']) ? $_POST['shift_id']:'';
            if ($shift_id) {
                $api = new Mrkv_CheckboxApi($login,$password,$cashbox_key,true);
                $response = $api->checkConnection($shift_id);
                $status = isset($response['status'])?$response['status']:'';
                $res['status'] = $status;
                $res['message'] = '';
                wp_send_json_success($res);
            } else {
                wp_send_json_error( [
                    'message' => 'Missed shift id parametr',
                ] );
            }
        } else {
            wp_send_json_error( [
                'message' => 'You missed filled login or password or cashbox_key field. Please go to settings PPO plugin and fill value.',
            ] );
        }
    }
}
// Register Ajax action
add_action('wp_ajax_mrkv_checkbox_check_connect','mrkv_checkbox_check_connect');

if ( !function_exists( 'mrkv_checkbox_disconnect' ) ) {
    // Ajax function
    function mrkv_checkbox_disconnect() {
        $res = [];
        $login = get_option('ppo_login');
        $password = get_option('ppo_password');
        $cashbox_key = get_option('ppo_cashbox_key');

        if ($login&&$password&&$cashbox_key) {
            $api = new Mrkv_CheckboxApi($login,$password,$cashbox_key,true);

            $shift = $api->disconnect();
            if (isset($shift['id'])) {
                $res['shift_id'] = $shift['id'];
                $res['status'] = $shift['status'];
                $res['message'] = '';
                wp_send_json_success($res);
            } else {
                $res['shift_id'] = '';
                $res['message'] = $shift['message'];
                wp_send_json_error($res);
            }

        } else {
            wp_send_json_error( [
                'message' => 'You missed filled login or password or cashbox_key field. Please go to settings PPO plugin and fill value.',
            ] );
        }

    }
}
// Register Ajax action
add_action('wp_ajax_mrkv_checkbox_disconnect','mrkv_checkbox_disconnect');

if ( !function_exists( 'mrkv_checkbox_ppo_start_connect_script' ) ) {
    // function show JS script
    function mrkv_checkbox_ppo_start_connect_script() {
        // Если это не главная страница админки - прекращаем выполнение функции
        if ( 'dashboard' != get_current_screen()->base ) {
            return;
        }
        ?>

        <script>
            jQuery(document).ready(function ($) {

            $('#ppo_button_connect').click(
                    function () {

                        // Ajax запрос
                        var request = $.post(
                            ajaxurl,
                            {
                                action: 'mrkv_checkbox_start_connect',
                                security: '<?php echo wp_create_nonce( "ppo_nonce" ); ?>'
                            }
                        );

                        request.done(
                            function (response) {
                                var result = response.data.message;
                                if (response.success) {
                                    var shift_id = response.data.shift_id;
                                    var status = response.data.status;
                                    $('#ppo_status').html(status);
                                    $('#ppo_shift_id').val(shift_id);
                                    $('#ppo_button_connect').hide();
                                    $('#ppo_proccess').show();

                                }
                            }
                        );

                        request.fail(
                            function (response) {
                                var result = response.data.message;
                                    alert(result);
                            }
                        );
                    }
                );

                $('#ppo_button_disconnect').click(
                    function () {

                        // Ajax запрос
                        var request = $.post(
                            ajaxurl,
                            {
                                action: 'mrkv_checkbox_disconnect',
                                security: '<?php echo wp_create_nonce( "ppo_nonce" ); ?>'
                            }
                        );

                        request.done(
                            function (response) {
                                var result = response.data.message;
                                if (response.success) {
                                    console.log('end session');
                                    var status = response.data.status;
                                    $('#ppo_status').html(status);
                                    $('#ppo_button_disconnect').hide();
                                    $('#ppo_proccess').show();
                                }
                            }
                        );

                        request.fail(
                            function (response) {
                                var result = response.data.message;
                                alert(result);
                            }
                        );
                    }
                );

                var timer = setInterval(() => checkStatus(),5000);

                function checkStatus() {
                    var shift_id = $('#ppo_shift_id').val();
                    if (shift_id) {
                        var request = $.post(
                            ajaxurl,{
                                action: 'mrkv_checkbox_check_connect',
                                shift_id:shift_id,
                                security: '<?php echo wp_create_nonce( "ppo_nonce" ); ?>'
                            }
                        );

                        request.done(
                            function (response) {
                                if (response.success) {
                                    var status = response.data.status;
                                    $('#ppo_status').html(status);
                                    if (status == 'OPENED') {
                                        $('#ppo_button_connect').hide();
                                        $('#ppo_button_disconnect').show();
                                        $('#ppo_proccess').hide();

                                    }
                                    if (status == 'CLOSED') {
                                        $('#ppo_button_connect').show();
                                        $('#ppo_button_disconnect').hide();
                                        $('#ppo_proccess').hide();
                                        $('#ppo_shift_id').val('');
                                    }
                                }
                            }
                        );
                    }
                }
            }); // ready()
        </script>

    <?php
    }
}
// Register JS script
add_action( 'admin_print_scripts', 'mrkv_checkbox_ppo_start_connect_script', 999 );

if ( !function_exists( 'mrkv_checkbox_showPluginAdminPage' ) ) {
    function mrkv_checkbox_showPluginAdminPage() {
        ?>
        <style>
            .table_input{
                width: 350px
            }
            .table_storagesList{
                width: 200px
            }
        </style>

        <div class="wrap">
            <h2>PPO settings</h2>

            <form method="post" action="options.php">
                <? settings_fields( 'ppo-settings-group' ); ?>

                <table class="form-table">

                    <tr valign="top">
                        <th scope="row">Login</th>
                        <td><input class="table_input" type="text" name="ppo_login" value="<?php echo get_option('ppo_login'); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Password</th>
                        <td><input class="table_input" type="password" name="ppo_password" value="<?php echo get_option('ppo_password'); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Cashbox key</th>
                        <td><input class="table_input" type="text" name="ppo_cashbox_key" value="<?php echo get_option('ppo_cashbox_key'); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Auto create receipt</th>
                        <td><input class="table_input" type="checkbox" name="ppo_auto_create"    <?php echo get_option('ppo_auto_create') ? "checked":'';?> /></td>
                    </tr>

                </table>

                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="page_options" value="ppo_login,ppo_password,ppo_cashbox_key,ppo_auto_create" />

                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                </p>

            </form>
        </div>
        <?
    }
}
