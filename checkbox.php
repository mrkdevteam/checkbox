<?php
/**
 * Plugin Name: Checkbox Integration
 * Plugin URI: https://morkva.co.ua/shop-2/checkbox-woocommerce?utm_source=checkbox-plugin
 * Description: WooCommerce Checkbox Integration
 * Version: 0.4.0
 * Tested up to: 5.7.2
 * Requires at least: 5.2
 * Requires PHP: 5.6
 * Author: MORKVA
 * Author URI: https://morkva.co.ua
 * Text Domain: checkbox
 * Domain Path: /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 5.3.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Admin\Overrides\Order;

if ( ! function_exists( 'mrkv_checkbox_fs' ) ) {
	/** Create a helper function for easy SDK access. */
	function mrkv_checkbox_fs() {
		global $mrkv_checkbox_fs;

		if ( ! isset( $mrkv_checkbox_fs ) ) {
			// Include Freemius SDK.
			require_once dirname( __FILE__ ) . '/freemius/start.php';

			$mrkv_checkbox_fs = fs_dynamic_init(
				array(
					'id'             => '7363',
					'slug'           => 'checkbox',
					'type'           => 'plugin',
					'public_key'     => 'pk_427357af1906f728486bdb1f5f4a3',
					'is_premium'     => false,
					'has_addons'     => false,
					'has_paid_plans' => false,
					'menu'           => array(
						'slug'       => 'ppo_settings',
						'first-path' => 'admin.php?page=ppo_settings',
						'account'    => false,
						'contact'    => false,
						'support'    => false,
					),
				)
			);
		}

		return $mrkv_checkbox_fs;
	}

	// Init Freemius.
	mrkv_checkbox_fs();
	// Signal that SDK was initiated.
	do_action( 'mrkv_checkbox_fs_loaded' );
}

/**
 * INCLUDE CHECKBOX API LIBRARY
 */
require_once 'api/Mrkv_CheckboxApi.php';

// -----------------------------------------------------------------------//
// --------------------------------SETUP----------------------------------//
// -----------------------------------------------------------------------//

add_action( 'admin_init', 'mrkv_checkbox_register_mysettings' );
if ( ! function_exists( 'mrkv_checkbox_register_mysettings' ) ) {

	/** Register plugin options */
	function mrkv_checkbox_register_mysettings() {
		register_setting( 'ppo-settings-group', 'ppo_login' );
		register_setting( 'ppo-settings-group', 'ppo_password' );
		register_setting( 'ppo-settings-group', 'ppo_cashier_name' );
		register_setting( 'ppo-settings-group', 'ppo_cashier_surname' );
		register_setting( 'ppo-settings-group', 'ppo_cashbox_key' );
		register_setting( 'ppo-settings-group', 'ppo_auto_create' );
		register_setting( 'ppo-settings-group', 'ppo_payment_type' );
		register_setting( 'ppo-settings-group', 'ppo_connected' );
		register_setting( 'ppo-settings-group', 'ppo_autoopen_shift' );
	}
}

add_action( 'admin_menu', 'mrkv_checkbox_register_plugin_page_in_name' );
if ( ! function_exists( 'mrkv_checkbox_register_plugin_page_in_name' ) ) {

	/** Register plugin page in admin menu */
	function mrkv_checkbox_register_plugin_page_in_name() {
		add_menu_page( 'PPO', esc_html_e( 'PPO Checkbox', 'checkbox' ), 'manage_woocommerce', 'ppo_settings', 'mrkv_checkbox_show_plugin_admin_page' );
	}
}

/** Add action for plugin cron event 'end_work_day' */
add_action( 'end_work_day', 'mrkv_checkbox_disconnect' );

// -----------------------------------------------------------------------//
// -----------------------WOOCOMMERCE CUSTOMISATION-----------------------//
// -----------------------------------------------------------------------//

add_action( 'woocommerce_order_actions', 'mrkv_checkbox_wc_add_order_meta_box_action' );
if ( ! function_exists( 'mrkv_checkbox_wc_add_order_meta_box_action' ) ) {

	/** Add order metabox action */
	function mrkv_checkbox_wc_add_order_meta_box_action( $actions ) {
		$actions['create_bill_action'] = esc_html_e( 'Створити чек', 'checkbox' );
		return $actions;
	}
}

add_action( 'woocommerce_order_action_create_bill_action', 'mrkv_checkbox_wc_process_order_meta_box_action' );
if ( ! function_exists( 'mrkv_checkbox_wc_process_order_meta_box_action' ) ) {

	/** Process order metabox action */
	function mrkv_checkbox_wc_process_order_meta_box_action( $order ) {
		$login       = get_option( 'ppo_login' );
		$password    = get_option( 'ppo_password' );
		$cashbox_key = get_option( 'ppo_cashbox_key' );

		if ( ! $login ) {
			$order->add_order_note( esc_html_e( 'Вкажіть логін в налаштуваннях РРО Checkbox', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
			return;
		}
		if ( ! $password ) {
			$order->add_order_note( esc_html_e( 'Вкажіть пароль в налаштуваннях РРО Checkbox', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
			return;
		}
		if ( ! $cashbox_key ) {
			$order->add_order_note( esc_html_e( 'Вкажіть ліцензійний ключ віртуального касового апарату в налаштуваннях РРО Checkbox', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
			return;
		}

		$api = new Mrkv_CheckboxApi( $login, $password, $cashbox_key );

		/** Check current shift status */
		$current_shift = $api->getCurrentCashierShift();
		if ( ! isset( $current_shift['status'] ) && ( 'OPENED' !== $current_shift['status'] ) ) {
			$order->add_order_note( esc_html_e( 'Зміна не відкрита', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
			return;
		}

		$result = mrkv_checkbox_create_receipt( $api, $order );

		if( 0 === $result ) {
			$order->add_order_note( esc_html_e( 'Чек вже створено', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
			return;
		}

		if ( $result['success'] ) {
			$order->add_order_note( esc_html_e( 'Чек створено', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
		} else {
			$order->add_order_note( esc_html_e( 'Виникла помилка під час створення чека', 'checkbox' ) . '.' . esc_html_e( 'Повідомлення:', 'checkbox' ) . ' ' . $status['message'], $is_customer_note = 0, $added_by_user = false );
		}
	}
}

add_filter( 'manage_edit-shop_order_columns', 'mrkv_checkbox_wc_new_order_column' );
if ( ! function_exists( 'mrkv_checkbox_wc_new_order_column' ) ) {

	/** Add order admin column */
	function mrkv_checkbox_wc_new_order_column( $columns ) {
		$columns['receipt_column'] = 'ID Чека';
		return $columns;
	}
}

add_action( 'manage_shop_order_posts_custom_column', 'mrkv_checkbox_wc_cogs_add_order_receipt_column_content' );
if ( ! function_exists( 'mrkv_checkbox_wc_cogs_add_order_receipt_column_content' ) ) {

	/** Fill ID Receipt column */
	function mrkv_checkbox_wc_cogs_add_order_receipt_column_content( $column ) {
		global $the_order;

		if ( 'receipt_column' === $column ) {
			$receipt_id = get_post_meta( $the_order->get_id(), 'receipt_id', true );
			echo esc_html( $receipt_id ) ?? '';
		}
	}
}

// -----------------------------------------------------------------------//
// -----------------------------CREATE RECEIPT----------------------------//
// -----------------------------------------------------------------------//

add_action( 'woocommerce_order_status_changed', 'mrkv_checkbox_auto_create_receipt', 99, 3 );
if ( ! function_exists( 'mrkv_checkbox_auto_create_receipt' ) ) {
	/** Function for automatic receipt creation */
	function mrkv_checkbox_auto_create_receipt( $order_id, $old_status, $new_status ) {

		if ( 'completed' === $new_status && 1 == get_option( 'ppo_auto_create' ) ) {

			$order = wc_get_order( $order_id );

			$login       = get_option( 'ppo_login' );
			$password    = get_option( 'ppo_password' );
			$cashbox_key = get_option( 'ppo_cashbox_key' );

			if ( $login && $password && $cashbox_key ) {

				$api = new Mrkv_CheckboxApi( $login, $password, $cashbox_key );

				$shift = $api->getCurrentCashierShift();

				// if shift is opened
				if ( isset( $shift['status'] ) && ( 'OPENED' === $shift['status'] ) ) {

					$result = mrkv_checkbox_create_receipt( $api, $order );

					if( 0 === $result ) {
						$order->add_order_note( esc_html_e( 'Чек вже створено', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
					}

					if ( $result ) {
						$order->add_order_note( esc_html_e( 'Чек створено', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
					} else {
						$order->add_order_note( esc_html_e( 'Помилка створення чеку', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
					}

				} else {

					$order->add_order_note( esc_html_e( 'Зміна не відкрита', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );

				}

			}
		}

	}
}

if ( ! function_exists( 'mrkv_checkbox_create_receipt' ) ) {
	/** Function for creting receipt */
	function mrkv_checkbox_create_receipt( $api, $order ) {

		/** check if receipt is already created */
		if( ! empty( get_post_meta( $order->get_id(), 'receipt_id', true ) ) ) {
			return 0;
		}

		if ( 1 == get_option( 'ppo_autoopen_shift' ) && 0 == get_option( 'ppo_connected' ) ) {
			mrkv_checkbox_connect();
			sleep( 5 ); // wait for 5 sec while shift is opening
		}

		$payment_settings = get_option( 'ppo_payment_type' );
		$user             = wp_get_current_user();

		$cashier_name = get_option( 'ppo_cashier_name' ) . ' ' . get_option( 'ppo_cashier_surname' );
		$departament  = 'store';

		$params          = array();
		$order_data      = $order->get_data();
		$goods_items     = $order->get_items();
		$payment_method = $order->get_payment_method();

		$email = isset( $order_data['billing']['email'] ) ? $order_data['billing']['email'] : $user->user_email;

		// ppre($order_data);

		$payment_type = isset( $payment_settings[ $payment_method ] ) ? mb_strtoupper( $payment_settings[ $payment_method ] ) : 'CASHLESS';

		$goods       = array();
		$total_price = 0;
		/* @let WC_Order_Item_Product $item */
		foreach ( $goods_items as $item ) {

			$price = ( $item->get_total() / $item->get_quantity() );

			$good = array(
				'code'  => $item->get_id() . '-' . $item->get_name(),
				'name'  => $item->get_name(),
				'price' => $price * 100,
			);

			$total_price += $price * $item->get_quantity() * 100;

			$goods[] = array(
				'good'     => $good,
				'quantity' => (int) ( $item->get_quantity() * 1000 ),
			);
		}

		$params['goods']        = $goods;
		$params['cashier_name'] = $cashier_name;
		$params['departament']  = $departament;
		$params['delivery']     = array( 'email' => $email );
		$params['payments'][]   = array(
			'type'  => $payment_type,
			'value' => ceil( $total_price ),
		);

		// ppre($params);

		$result  = array();
		$receipt = $api->create_receipt( $params );

		/** Save order ID */
		if ( isset( $receipt['id'] ) ) {
			/** Save receipt ID in meta */
			update_post_meta( $order->get_id(), 'receipt_id', sanitize_text_field( $receipt['id'] ) );
			$result['success'] = true;
		} else {
			$result['success'] = false;
			$result['message'] = $receipt['message'];
		}

		return $result;
	}
}

// -----------------------------------------------------------------------//
// ---------------------------DASHBOARD WIDGET----------------------------//
// -----------------------------------------------------------------------//

add_action( 'wp_dashboard_setup', 'mrkv_checkbox_ppo_status_dashboard_widget' );
if ( ! function_exists( 'mrkv_checkbox_ppo_status_dashboard_widget' ) ) {
	/** Register plugin dashboard widget only for admin role */
	function mrkv_checkbox_ppo_status_dashboard_widget() {
		if ( current_user_can( 'activate_plugins' ) ) {
			wp_add_dashboard_widget( 'status_widget', esc_html_e( 'PPO Checkbox', 'checkbox' ), 'mrkv_checkbox_status_widget_form' );
		}
	}
}

if ( ! function_exists( 'mrkv_checkbox_status_widget_form' ) ) {
	/** Plugin dashboard widget functionality */
	function mrkv_checkbox_status_widget_form() {
		$shift        = '';
		$shift_id     = '';
		$is_connected = false;
		$status       = esc_html_e( 'Закрито', 'checkbox' );

		$login       = get_option( 'ppo_login' );
		$password    = get_option( 'ppo_password' );
		$cashbox_key = get_option( 'ppo_cashbox_key' );

		if ( $login && $password && $cashbox_key ) {
			$api   = new Mrkv_CheckboxApi( $login, $password, $cashbox_key );
			$shift = $api->getCurrentCashierShift();

			if ( $shift ) {
				$shift_id = ( isset( $shift['id'] ) ) ? $shift['id'] : '';

				if ( 'OPENED' === $shift['status'] ) {
					$is_connected = true;
					$status       = esc_html_e( 'Відкрито', 'checkbox' );
				}
			}
		}

		?>
		<form>
			<p><?php esc_html_e( 'Статус', 'checkbox' ); ?>: <span id="ppo_status" class="status" style="font-weight: 500; text-transform: uppercase;"><?php echo esc_html( $status ); ?></span></p>

			<div class="ppo_connect-group" style="<?php echo esc_html( ( $is_connected ) ? 'display: none;' : 'display: inline-flex;' ); ?> align-items: center;¨" >
				<button type="button" id="ppo_button_connect" class="start button button-secondary"><?php esc_html_e( 'Відкрити зміну', 'checkbox' ); ?></button>
				<img id="ppo_process" style="display: none; margin-left: 10px;" src="<?php echo esc_url( plugins_url( 'img/ajax-loader.gif', __FILE__ ) ); ?>" width="20px" height="20px" alt="proccess imgage" />
			</div>
			<div class="ppo_disconnect-group" style="<?php echo esc_html( ( ! $is_connected ) ? 'display: none;' : 'display: inline-flex;' ); ?> align-items: center;">
				<button type="button" id="ppo_button_disconnect" class="end button button-secondary"><?php esc_html_e( 'Закрити зміну', 'checkbox' ); ?></button>
				<img id="ppo_process" style="display: none; margin-left: 10px;" src="<?php echo esc_url( plugins_url( 'img/ajax-loader.gif', __FILE__ ) ); ?>" width="20px" height="20px" alt="proccess imgage" />
			</div>

			<input type="hidden" id="ppo_shift_id" value="<?php echo esc_html( $shift_id ); ?>">
		</form>

		<?php
	}
}

// -----------------------------------------------------------------------//
// --------------------------CONNECT AND DISCONNECT-----------------------//
// -----------------------------------------------------------------------//

add_action( 'wp_ajax_mrkv_checkbox_check_connection', 'mrkv_checkbox_check_connection' );
if ( ! function_exists( 'mrkv_checkbox_check_connection' ) ) {
	/** Function for checking connection */
	function mrkv_checkbox_check_connection() {

		check_ajax_referer( 'ppo_checkconnect' );

		$res = array();

		$login       = get_option( 'ppo_login' );
		$password    = get_option( 'ppo_password' );
		$cashbox_key = get_option( 'ppo_cashbox_key' );

		if ( $login && $password && $cashbox_key ) {

			$shift_id = isset( $_POST['shift_id'] ) ? sanitize_text_field( wp_unslash( $_POST['shift_id'] ) ) : '';

			if ( $shift_id ) {
				$api            = new Mrkv_CheckboxApi( $login, $password, $cashbox_key );
				$response       = $api->checkConnection( $shift_id );
				$status         = isset( $response['status'] ) ? $response['status'] : '';
				$res['status']  = $status;
				$res['message'] = '';

				wp_send_json_success( $res );

				return true;
			} else {
				wp_send_json_error(
					array(
						'message' => esc_html_e( 'Відсутній ID зміни', 'checkbox' ),
					)
				);
			}
		} else {

			wp_send_json_error(
				array(
					'message' => esc_html_e( "Будь ласка, заповніть обов'язкові поля в налаштуваннях плагіну PPO Checkbox", 'checkbox' ),
				)
			);

			return false;
		}
	}
}

add_action( 'wp_ajax_mrkv_checkbox_connect', 'mrkv_checkbox_connect' );
if ( ! function_exists( 'mrkv_checkbox_connect' ) ) {
	/** Function for shift opening */
	function mrkv_checkbox_connect() {
		if ( wp_doing_ajax() ) {
			check_ajax_referer( 'ppo_connect' );
		}

		$res = array();

		$login       = get_option( 'ppo_login' );
		$password    = get_option( 'ppo_password' );
		$cashbox_key = get_option( 'ppo_cashbox_key' );

		if ( $login && $password && $cashbox_key ) {

			$api = new Mrkv_CheckboxApi( $login, $password, $cashbox_key );

			$shift = $api->connect();

			if ( isset( $shift['id'] ) ) {

				$res['shift_id'] = $shift['id'];
				$res['status']   = ( 'CREATED' === $shift['status'] ) ? esc_html_e( 'Відкрито', 'checkbox' ) : $shift['status'];
				$res['message']  = '';

				update_option( 'ppo_connected', 1 );

				if ( wp_doing_ajax() ) {
					wp_send_json_success( $res );
				}

			} else {

				$res['shift_id'] = '';

				if( 'Not authenticated' === $shift['message'] ) {
					$res['message']  = esc_html_e( 'Невірний логін або пароль. Будь ласка, перевірте дані доступу до особистого кабінету касира на сервісі Checkbox.', 'checkbox');
				} else {
					$res['message']  = $shift['message'];
				}

				if ( wp_doing_ajax() ) {
					wp_send_json_error( $res );
				}

			}

		} else {

			if ( wp_doing_ajax() ) {
				wp_send_json_error(
					array(
						'message' => esc_html_e( "Будь ласка, заповніть обов'язкові поля в налаштуваннях плагіну PPO Checkbox", 'checkbox' ),
					)
				);
			}
		}
	}
}

add_action( 'wp_ajax_mrkv_checkbox_disconnect', 'mrkv_checkbox_disconnect' );
if ( ! function_exists( 'mrkv_checkbox_disconnect' ) ) {
	// this function is used by cron and wp ajax
	function mrkv_checkbox_disconnect() {

		if ( wp_doing_ajax() ) {
			check_ajax_referer( 'ppo_disconnect' );
		}

		$res = array();

		$login       = get_option( 'ppo_login' );
		$password    = get_option( 'ppo_password' );
		$cashbox_key = get_option( 'ppo_cashbox_key' );

		if ( $login && $password && $cashbox_key ) {

			$api = new Mrkv_CheckboxApi( $login, $password, $cashbox_key );

			$shift = $api->disconnect();

			if ( isset( $shift['id'] ) ) {

				$res['shift_id'] = $shift['id'];
				$res['status']   = ( 'CLOSING' === $shift['status'] ) ? esc_html_e( 'Закрито', 'checkbox' ) : $shift['status'];
				$res['message']  = '';

				update_option( 'ppo_connected', 0 );

				if ( wp_doing_ajax() ) {
					wp_send_json_success( $res );
				}
			} else {

				$res['shift_id'] = '';
				$res['message']  = $shift['message'];

				if ( wp_doing_ajax() ) {
					wp_send_json_error( $res );
				}
			}
		} else {

			if ( wp_doing_ajax() ) {
				wp_send_json_error(
					array(
						'message' => esc_html_e( "Будь ласка, заповніть обов'язкові поля в налаштуваннях плагіну PPO Checkbox", 'checkbox' ),
					)
				);
			}
		}
	}
}

add_action( 'admin_print_scripts', 'mrkv_checkbox_ppo_connect_script', 999 );
if ( ! function_exists( 'mrkv_checkbox_ppo_connect_script' ) ) {
	function mrkv_checkbox_ppo_connect_script() {

		if ( 'dashboard' !== get_current_screen()->base ) {
			return;
		}
		?>

		<script>
			jQuery(document).ready(function($) {

				$('#ppo_button_connect').on('click', function() {

					$.ajax({
						type: 'post',
						url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
						data: {
							action: 'mrkv_checkbox_connect',
							_wpnonce: '<?php echo wp_create_nonce( 'ppo_connect' ); ?>'
						},
						dataType: 'json',
						beforeSend: function() {
							$('.ppo_connect-group img#ppo_process').show();
						},
						error: function(response) {
							$('.ppo_connect-group img#ppo_process').hide();

							alert( esc_html_e('Виникла помилка. Спробуйте ще раз.', 'checkbox') );
						},
						success: function(response) {
							$('.ppo_connect-group img#ppo_process').hide();

							if (response.success) {
								let shift_id = response.data.shift_id;
								let status = response.data.status;
								$('#ppo_status').html(status);
								$('#ppo_shift_id').val(shift_id);
								$('.ppo_connect-group').hide();
								$('.ppo_disconnect-group').css('display', 'inline-flex');
							} else {
								alert(response.data.message);
							}
						}
					});

				});

				$('#ppo_button_disconnect').on('click', function() {

					$.ajax({
						type: 'post',
						url: ajaxurl,
						data: {
							action: 'mrkv_checkbox_disconnect',
							_wpnonce: '<?php echo wp_create_nonce( 'ppo_disconnect' ); ?>'
						},
						dataType: 'json',
						beforeSend: function() {
							$('.ppo_disconnect-group img#ppo_process').show();
						},
						error: function() {
							$('.ppo_disconnect-group img#ppo_process').hide();

							alert( esc_html_e('Виникла помилка. Спробуйте ще раз.', 'checkbox') );
						},
						success: function(response) {
							$('.ppo_disconnect-group img#ppo_process').hide();

							if (response.success) {
								let status = response.data.status;
								$('#ppo_status').html(status);
								$('.ppo_disconnect-group').hide();
								$('.ppo_connect-group').css('display', 'inline-flex');
							} else {
								alert(response.data.message);
							}
						}
					});

				});

				let timer = setInterval(() => checkStatus(), 5000);

				function checkStatus() {
					let shift_id = $('#ppo_shift_id').val();
					if (shift_id) {
						let request = $.post(
							ajaxurl, {
								action: 'mrkv_checkbox_check_connection',
								shift_id: shift_id,
								_wpnonce: '<?php echo wp_create_nonce( 'ppo_checkconnect' ); ?>'
							}
						);
					}
				}
			});
		</script>

		<?php
	}
}

if ( ! function_exists( 'mrkv_checkbox_show_plugin_admin_page' ) ) {
	/** Plugin admin page content */
	function mrkv_checkbox_show_plugin_admin_page() {
		?>
		<style>
			.table_input {
				width: 350px
			}

			.table_storagesList {
				width: 200px
			}
		</style>

		<div class="wrap">
			<h2><?php esc_html_e( 'Налаштування PPO Checkbox', 'checkbox' ); ?></h2>

			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'ppo-settings-group' ); ?>

				<table class="form-table">

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Логін', 'checkbox' ); ?></th>
						<td><input class="table_input" type="text" name="ppo_login" value="<?php echo esc_html( get_option( 'ppo_login' ) ); ?>" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Пароль', 'checkbox' ); ?></th>
						<td><input class="table_input" type="password" name="ppo_password" value="<?php echo esc_html( get_option( 'ppo_password' ) ); ?>" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Ім\'я касира', 'checkbox' ); ?></th>
						<td><input class="table_input" type="text" name="ppo_cashier_name" value="<?php echo esc_html( get_option( 'ppo_cashier_name' ) ); ?>" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Прізвище касира', 'checkbox' ); ?></th>
						<td><input class="table_input" type="text" name="ppo_cashier_surname" value="<?php echo esc_html( get_option( 'ppo_cashier_surname' ) ); ?>" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Ліцензійний ключ віртуального касового апарату', 'checkbox' ); ?></th>
						<td><input class="table_input" type="text" name="ppo_cashbox_key" value="<?php echo esc_html( get_option( 'ppo_cashbox_key' ) ); ?>" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Автоматичне відкриття зміни', 'checkbox' ); ?></th>
						<td><input class="table_input" type="checkbox" name="ppo_autoopen_shift" value="1" <?php checked( get_option( 'ppo_autoopen_shift' ), 1 ); ?> /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( "Автоматично створювати чеки при статусі 'Виконано'", 'checkbox' ); ?></th>
						<td><input class="table_input" type="checkbox" name="ppo_auto_create" value="1" <?php checked( get_option( 'ppo_auto_create' ), 1 ); ?> /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Налаштування статусу платіжної системи (CASH або CASHLESS)', 'checkbox' ); ?></th>
						<td>
							<?php
							$gateways         = WC()->payment_gateways->get_available_payment_gateways();
							$ppo_payment_type = get_option( 'ppo_payment_type' );

							foreach ( $gateways as $gateway ) :
								if ( 'yes' === $gateway->enabled ) :
									?>
									<div>
										<table>
											<td style="width: 60%;">
												<?php echo esc_html( $gateway->title ); ?>
											</td>
											<td>
												<input type="radio" name="ppo_payment_type[<?php echo esc_html( $gateway->id ); ?>]"
																									  <?php
																										if ( isset( $ppo_payment_type[ $gateway->id ] ) ) {
																											checked( $ppo_payment_type[ $gateway->id ], 'cash' ); }
																										?>
												 value="cash"><label>CASH</label>
											</td>
											<td>
												<input type="radio" name="ppo_payment_type[<?php echo esc_html( $gateway->id ); ?>]"
																									  <?php
																										if ( isset( $ppo_payment_type[ $gateway->id ] ) ) {
																											checked( $ppo_payment_type[ $gateway->id ], 'cashless' ); }
																										?>
												 value="cashless"><label>CASHLESS</label>
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
				<input type="hidden" name="page_options" value="ppo_login, ppo_password, ppo_cashbox_key, ppo_auto_create" />

				<?php echo submit_button( esc_html_e( 'Зберегти', 'checkbox' ) ); ?>

			</form>
		</div>
		<?php
	}
}

// -----------------------------------------------------------------------//
// ------------------ACTIVATION AND DEACTIVATION HOOKS--------------------//
// -----------------------------------------------------------------------//

register_activation_hook( __FILE__, 'mrkv_checkbox_activation_cb' );
function mrkv_checkbox_activation_cb() {

	if ( ! wp_next_scheduled( 'end_work_day' ) ) {
		wp_schedule_event( strtotime( '23:57:00 Europe/Kiev' ), 'daily', 'end_work_day' );
	}
}

register_deactivation_hook( __FILE__, 'mrkv_checkbox_deactivation_cb' );
function mrkv_checkbox_deactivation_cb() {

	if ( get_option( 'ppo_connected' ) ) {
		mrkv_checkbox_disconnect();
	}

	if ( wp_next_scheduled( 'end_work_day' ) ) {
		wp_clear_scheduled_hook( 'end_work_day' );
	}

}


