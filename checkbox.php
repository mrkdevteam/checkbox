<?php
/**
 * Plugin Name: Checkbox Integration
 * Plugin URI: https://morkva.co.ua/shop-2/checkbox?utm_source=checkbox-plugin
 * Description: WooCommerce Checkbox Integration
 * Version: 0.3.1
 * Tested up to: 5.6
 * Author: MORKVA
 * Author URI: https://morkva.co.ua
 * License: GPL v2 or later
 */

if ( ! function_exists( 'mrkv_checkbox_fs' ) ) {
    // Create a helper function for easy SDK access.
    function mrkv_checkbox_fs() {
        global $mrkv_checkbox_fs;

        if ( ! isset( $mrkv_checkbox_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $mrkv_checkbox_fs = fs_dynamic_init( array(
                'id'                  => '7363',
                'slug'                => 'checkbox',
                'type'                => 'plugin',
                'public_key'          => 'pk_427357af1906f728486bdb1f5f4a3',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'ppoSettings',
                    'first-path'     => 'admin.php?page=ppoSettings',
                    'account'        => false,
                    'contact'        => false,
                    'support'        => false,
                ),
            ) );
        }

        return $mrkv_checkbox_fs;
    }

    // Init Freemius.
    mrkv_checkbox_fs();
    // Signal that SDK was initiated.
    do_action( 'mrkv_checkbox_fs_loaded' );
}

if ( ! defined('ABSPATH')) {
  exit;
}

use Automattic\WooCommerce\Admin\Overrides\Order;

require_once( 'api/Mrkv_CheckboxApi.php');

if ( !function_exists( 'mrkv_checkbox_register_mysettings' ) ) {
    function mrkv_checkbox_register_mysettings() {
        register_setting( 'ppo-settings-group', 'ppo_login' );
        register_setting( 'ppo-settings-group', 'ppo_password' );
        register_setting( 'ppo-settings-group', 'ppo_cashier_name');
        register_setting( 'ppo-settings-group', 'ppo_cashier_surname');
        register_setting( 'ppo-settings-group', 'ppo_cashbox_key' );
        register_setting( 'ppo-settings-group', 'ppo_auto_create');
        register_setting( 'ppo-settings-group', 'ppo_payment_type');
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
        $actions['create_bill_action'] = 'Створити чек';
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

        $api = new Mrkv_CheckboxApi($login,$password,$cashbox_key);
        // Check current status
        $current_shift = $api->getCurrentCashierShift();
        if (isset($current_shift['status'])&&($current_shift['status']!='OPENED')) {
            $order->add_order_note( "У вас не відкрита зміна ", $is_customer_note = 0, $added_by_user = false );
            return;
        }

        $status = mrkv_checkbox_create_reciept($api,$order);
        // save order ID
        if ($status) {
            $order->add_order_note( "Чек створено", $is_customer_note = 0, $added_by_user = false );
        } else {
            $order->add_order_note( "Помилка створення чека", $is_customer_note = 0, $added_by_user = false );
        }

    }
}
add_action( 'woocommerce_order_action_create_bill_action', 'mrkv_checkbox_wc_process_order_meta_box_action' );

if ( !function_exists( 'mrkv_checkbox_wc_new_order_column' ) ) {
    function mrkv_checkbox_wc_new_order_column( $columns ) {
        $columns['receipt_column'] = 'ID Чека';
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
                $api = new Mrkv_CheckboxApi($login,$password,$cashbox_key);
                $shift = $api->getCurrentCashierShift();
                if (isset($shift['status'])&&($shift['status']=='OPENED')){
                    // create receipt
                    if (!mrkv_checkbox_create_reciept($api,$order)){
                    $order->add_order_note( "Помилка створення чеку ", $is_customer_note = 0, $added_by_user = false );
                    }
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

        $payment_settings = get_option('ppo_payment_type');
        $user = wp_get_current_user();

        $cashier_name = get_option('ppo_cashier_name').' '.get_option('ppo_cashier_surname');
        $departament = 'store';

        $params = [];
        $order_data = $order->get_data();
        $goods_items = $order->get_items();
        $payment_medthod = $order->get_payment_method();

        $email = isset($order_data['billing']['email']) ? $order_data['billing']['email'] : $user->user_email;

        //ppre($order_data);

        $payment_type = isset($payment_settings[$payment_medthod]) ? mb_strtoupper($payment_settings[$payment_medthod]):'CASHLESS';

        $goods = [];
        $totalPrice = 0;
        /* @var WC_Order_Item_Product $item */
        foreach ($goods_items as $item) {

            $price = ($item->get_total()/$item->get_quantity());

            $good = [
                'code'=>$item->get_id().'-'.$item->get_name(),
                'name'=>$item->get_name(),
                'price'=>$price*100
            ];

            $totalPrice = $totalPrice + $price*100*$item->get_quantity();

            $goods[] = [
                'good'=>$good,
                'quantity'=>(int)($item->get_quantity()*1000)
            ];
        }

        $params['goods'] = $goods;
        $params['cashier_name'] = $cashier_name;
        $params['departament'] = $departament;
        $params['delivery'] = ['email'=>$email];
        $params['payments'][] = [
            'type'=>$payment_type,
            'value'=>$totalPrice
        ];

        //ppre($params);

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
            $api = new Mrkv_CheckboxApi($login,$password,$cashbox_key);
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
                <img src="<?php echo plugins_url( 'img/proccess.webp' , __FILE__ ) ?>" width="50px" height="50px" alt="proccess imgage" />
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
            $api = new Mrkv_CheckboxApi($login,$password,$cashbox_key);

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
            $shift_id = isset($_POST['shift_id']) ? sanitize_text_field( $_POST['shift_id'] ):'';
            if ($shift_id) {
                $api = new Mrkv_CheckboxApi($login,$password,$cashbox_key);
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
            $api = new Mrkv_CheckboxApi($login,$password,$cashbox_key);

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
                <?php settings_fields( 'ppo-settings-group' ); ?>

                <table class="form-table">

                    <tr valign="top">
                        <th scope="row">Логін касира</th>
                        <td><input class="table_input" type="text" name="ppo_login" value="<?php echo get_option('ppo_login'); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Пароль користувача</th>
                        <td><input class="table_input" type="password" name="ppo_password" value="<?php echo get_option('ppo_password'); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Ім'я касира</th>
                        <td><input class="table_input" type="text" name="ppo_cashier_name" value="<?php echo get_option('ppo_cashier_name'); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Призвище касира</th>
                        <td><input class="table_input" type="text" name="ppo_cashier_surname" value="<?php echo get_option('ppo_cashier_surname'); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Ключ каси</th>
                        <td><input class="table_input" type="text" name="ppo_cashbox_key" value="<?php echo get_option('ppo_cashbox_key'); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Створювати чеки автоматично при статусі Виконано</th>
                        <td><input class="table_input" type="checkbox" name="ppo_auto_create"    <?php echo get_option('ppo_auto_create') ? "checked":'';?> /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Налаштування статусу платіжної системи (CASH або CASHLESS)</th>
                        <td>
                    <?php
                    $gateways = WC()->payment_gateways->get_available_payment_gateways();
                    $ppo_payment_type = get_option('ppo_payment_type');
                    foreach ($gateways as $gateway) :
                    if ($gateway->enabled == 'yes'):
                    ?>

                        <div>
                            <table>
                                <td style="width: 60%;">
                                        <?php echo $gateway->title ?>
                                </td>
                                <td>
                                        <input type="radio" name="ppo_payment_type[<?php echo $gateway->id ?>]"  <?php if (isset($ppo_payment_type[$gateway->id])&&($ppo_payment_type[$gateway->id] == 'cash' )) { echo "checked"; }  ?>  value="cash" ><label>CASH</label>
                                </td>
                                <td>
                                        <input type="radio" name="ppo_payment_type[<?php echo $gateway->id ?>]"  <?php if (isset($ppo_payment_type[$gateway->id])&&($ppo_payment_type[$gateway->id] == 'cashless' )) { echo "checked"; }  ?>  value="cashless" ><label>CASHLESS</label>

                                </td>
                            </table>
                        </div>


                    <?php
                        endif;
                        endforeach;
                    ?>
                        </td>
                    </tr>



                </table>

                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="page_options" value="ppo_login,ppo_password,ppo_cashbox_key,ppo_auto_create" />

                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Зберегти') ?>" />
                </p>

            </form>
        </div>
        <?php
    }
}
