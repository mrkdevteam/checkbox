<?php
/**
 * Plugin Name: WooCommerce Checkbox Integration
 * Plugin URI: https://morkva.co.ua/shop-2/checkbox-woocommerce?utm_source=checkbox-plugin
 * Description: Інтеграція WooCommerce з пРРО Checkbox
 * Version: 0.5.2
 * Tested up to: 5.8.1
 * Requires at least: 5.0
 * Requires PHP: 7.1
 * Author: MORKVA
 * Author URI: https://morkva.co.ua
 * Text Domain: checkbox
 * Domain Path: /languages
 * WC requires at least: 3.9.0
 * WC tested up to: 5.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Admin\Overrides\Order;

if ( ! function_exists( 'mrkv_checkbox_fs' ) ) {
	/**
	 * Create a helper function for easy SDK access.
	 */
	function mrkv_checkbox_fs() {
		global $mrkv_checkbox_fs;

		if ( ! isset( $mrkv_checkbox_fs ) ) {
			/**
			 *  Include Freemius SDK.
			 */
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
						'slug'       => 'checkbox_settings',
						'first-path' => 'admin.php?page=checkbox_settings',
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
require_once 'api/class-mrkv-checkboxapi.php';

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
		register_setting( 'ppo-settings-group', 'ppo_autocreate' );
		register_setting( 'ppo-settings-group', 'ppo_autocreate_receipt_order_statuses' );
		register_setting( 'ppo-settings-group', 'ppo_payment_type' );
		register_setting( 'ppo-settings-group', 'ppo_connected' );
		register_setting( 'ppo-settings-group', 'ppo_autoopen_shift' );
		register_setting( 'ppo-settings-group', 'ppo_sign_method' );
		register_setting( 'ppo-settings-group', 'ppo_skip_receipt_creation' );
		register_setting( 'ppo-settings-group', 'ppo_is_dev_mode' );
	}
}

add_action( 'admin_menu', 'mrkv_checkbox_register_plugin_page_in_name' );
if ( ! function_exists( 'mrkv_checkbox_register_plugin_page_in_name' ) ) {

	/**
	 * Register plugin page in admin menu
	 */
	function mrkv_checkbox_register_plugin_page_in_name() {
		add_menu_page( __( 'Налаштування пРРО Checkbox', 'checkbox' ), __( 'Checkbox', 'checkbox' ), 'manage_woocommerce', 'checkbox_settings', 'mrkv_checkbox_show_plugin_admin_page', plugin_dir_url( __FILE__ ) . 'assets/img/logo.svg' );
	}
}

/** Add action for plugin cron event 'checkbox_close_shift' */
add_action( 'checkbox_close_shift', 'mrkv_checkbox_disconnect' );

// -----------------------------------------------------------------------//
// -----------------------WOOCOMMERCE CUSTOMISATION-----------------------//
// -----------------------------------------------------------------------//

add_action( 'woocommerce_order_actions', 'mrkv_checkbox_wc_add_order_meta_box_action' );
if ( ! function_exists( 'mrkv_checkbox_wc_add_order_meta_box_action' ) ) {

	/**
	 * Add order metabox action
	 *
	 * @param array $actions all actions registered in order meta box
	 * @return array $actions all actions registered in order meta box
	 */
	function mrkv_checkbox_wc_add_order_meta_box_action( $actions ) {
		$actions['create_bill_action'] = __( 'Створити чек', 'checkbox' );
		return $actions;
	}
}

add_action( 'woocommerce_order_action_create_bill_action', 'mrkv_checkbox_wc_process_order_meta_box_action' );
if ( ! function_exists( 'mrkv_checkbox_wc_process_order_meta_box_action' ) ) {

	/**
	 * Process order metabox action
	 *
	 * @param WC_Order $order Order Info
	 */
	function mrkv_checkbox_wc_process_order_meta_box_action( $order ) {
		$login       = get_option( 'ppo_login' );
		$password    = get_option( 'ppo_password' );
		$cashbox_key = get_option( 'ppo_cashbox_key' );

		if ( ! $login ) {
			$order->add_order_note( __( 'Вкажіть логін в налаштуваннях плагіна Checkbox', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
			return;
		}
		if ( ! $password ) {
			$order->add_order_note( __( 'Вкажіть пароль в налаштуваннях плагіна Checkbox', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
			return;
		}
		if ( ! $cashbox_key ) {
			$order->add_order_note( __( 'Вкажіть ліцензійний ключ віртуального касового апарату в налаштуваннях плагіна Checkbox', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
			return;
		}

		$is_dev = boolval( get_option( 'ppo_is_dev_mode' ) );
		$api = new Mrkv_CheckboxApi( $login, $password, $cashbox_key, $is_dev );

		/** Check if receipt is already created */
		if ( ! empty( get_post_meta( $order->get_id(), 'receipt_id', true ) ) ) {
			$order->add_order_note( __( 'Чек вже створено', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
			return;
		}

		/** Check current shift status */
		$current_shift = $api->getCurrentCashierShift();
		if ( ! isset( $current_shift['status'] ) && ( 'OPENED' !== $current_shift['status'] ) ) {
			/** Check if Autoopen shift feature is activated */
			if ( 1 === (int) get_option( 'ppo_autoopen_shift' ) ) {
				mrkv_checkbox_connect();
				sleep( 8 ); // wait for 8 sec while shift is opening
			} else {
				$order->add_order_note( __( 'Зміна не відкрита', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
				return;	
			}
		}

		$result = mrkv_checkbox_create_receipt( $api, $order );

		if ( $result['success'] ) {
			$order->add_order_note( __( 'Чек створено', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
		} else {
			$order->add_order_note( __( 'Виникла помилка під час створення чека', 'checkbox' ) . '.' . __( 'Повідомлення:', 'checkbox' ) . ' ' . $result['message'], $is_customer_note = 0, $added_by_user = false );
		}
	}
}

add_filter( 'manage_edit-shop_order_columns', 'mrkv_checkbox_wc_new_order_column' );
if ( ! function_exists( 'mrkv_checkbox_wc_new_order_column' ) ) {

	/**
	 * Add order admin column
	 *
	 * @param array $columns Columns from edit shop page
	 * @return array $columns Updated columns from edit shop page
	 **/
	function mrkv_checkbox_wc_new_order_column( $columns ) {
		$columns['receipt_column'] = __( 'ID Чека', 'checkbox' );
		return $columns;
	}
}

add_action( 'manage_shop_order_posts_custom_column', 'mrkv_checkbox_wc_cogs_add_order_receipt_column_content' );
if ( ! function_exists( 'mrkv_checkbox_wc_cogs_add_order_receipt_column_content' ) ) {

	/**
	 * Fill ID Receipt column
	 *
	 * @param string $column column name
	 */
	function mrkv_checkbox_wc_cogs_add_order_receipt_column_content( $column ) {
		global $the_order;

		if ( 'receipt_column' === $column ) {
			$receipt_id = get_post_meta( $the_order->get_id(), 'receipt_id', true );
			echo esc_html( $receipt_id );
		}
	}
}

// -----------------------------------------------------------------------//
// -----------------------------CREATE RECEIPT----------------------------//
// -----------------------------------------------------------------------//

add_action( 'woocommerce_order_status_changed', 'mrkv_checkbox_auto_create_receipt', 99, 3 );
if ( ! function_exists( 'mrkv_checkbox_auto_create_receipt' ) ) {
	/**
	 * Automatic receipt creation
	 *
	 * @param string $order_id Order ID
	 * @param string $old_status old order status
	 * @param string $new_status new order status
	 */
	function mrkv_checkbox_auto_create_receipt( $order_id, $old_status, $new_status ) {

		if ( 1 !== (int) get_option( 'ppo_autocreate' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		$order_statuses = (array) get_option('ppo_autocreate_receipt_order_statuses');

		if ( in_array( $new_status, $order_statuses ) ) {

			/** Check whether to create receipt or not */
			$ppo_skip_receipt_creation = get_option( 'ppo_skip_receipt_creation' );
			if ( 'yes' === $ppo_skip_receipt_creation[ $order->get_payment_method() ] ) {
				$order->add_order_note( __( 'Чек не створено згідно правила', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
				return;
			}

			/** Check if receipt is already created */
			if ( ! empty( get_post_meta( $order_id, 'receipt_id', true ) ) ) {
				$order->add_order_note( __( 'Чек вже створено', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
				return;
			}

			/** Check if Autoopen shift feature is activated */
			if ( 1 === (int) get_option( 'ppo_autoopen_shift' ) && 0 === (int) get_option( 'ppo_connected' ) ) {
				mrkv_checkbox_connect();
				sleep( 8 ); // wait for 8 sec while shift is opening
			}

			$login       = get_option( 'ppo_login' );
			$password    = get_option( 'ppo_password' );
			$cashbox_key = get_option( 'ppo_cashbox_key' );

			if ( $login && $password && $cashbox_key ) {

				$is_dev = boolval( get_option( 'ppo_is_dev_mode' ) );
				$api = new Mrkv_CheckboxApi( $login, $password, $cashbox_key, $is_dev );

				$shift = $api->getCurrentCashierShift();

				if ( isset( $shift['status'] ) && ( 'OPENED' === $shift['status'] ) ) {
					$result = mrkv_checkbox_create_receipt( $api, $order );

					if ( $result ) {
						$order->add_order_note( __( 'Чек створено', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
					} else {
						$order->add_order_note( __( 'Помилка створення чеку', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
					}
				} else {
					$order->add_order_note( __( 'Зміна не відкрита', 'checkbox' ), $is_customer_note = 0, $added_by_user = false );
				}
			}
		}

	}
}

if ( ! function_exists( 'mrkv_checkbox_create_receipt' ) ) {
	/**
	 * Receipt creation
	 *
	 * @param Mrkv_CheckboxApi $api Checkbox API
	 * @param WC_Order         $order Order
	 */
	function mrkv_checkbox_create_receipt( $api, $order ) {

		$payment_settings = get_option( 'ppo_payment_type' );
		$user             = wp_get_current_user();

		$cashier_name = get_option( 'ppo_cashier_name' ) . ' ' . get_option( 'ppo_cashier_surname' );
		$departament  = 'store';

		$params         = array();
		$order_data     = $order->get_data();
		$goods_items    = $order->get_items();
		$payment_method = $order->get_payment_method();

		$email = isset( $order_data['billing']['email'] ) ? $order_data['billing']['email'] : $user->user_email;

		$payment_type = isset( $payment_settings[ $payment_method ] ) ? mb_strtoupper( $payment_settings[ $payment_method ] ) : 'CASHLESS';

		$goods       = array();
		$total_price = 0;
		/**
		 * @let WC_Order_Item_Product $item
		*/
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
	/**
	 * Register plugin dashboard widget only for admin role
	 */
	function mrkv_checkbox_ppo_status_dashboard_widget() {
		if ( current_user_can( 'activate_plugins' ) ) {
			wp_add_dashboard_widget( 'status_widget', 'Checkbox', 'mrkv_checkbox_status_widget_form' );
		}
	}
}

if ( ! function_exists( 'mrkv_checkbox_status_widget_form' ) ) {
	/**
	 * Plugin dashboard widget functionality
	 */
	function mrkv_checkbox_status_widget_form() {
		$shift        = '';
		$shift_id     = '';
		$is_connected = false;
		$status       = __( 'Закрито', 'checkbox' );

		$login       = get_option( 'ppo_login' );
		$password    = get_option( 'ppo_password' );
		$cashbox_key = get_option( 'ppo_cashbox_key' );

		if ( $login && $password && $cashbox_key ) {
			$is_dev = boolval( get_option( 'ppo_is_dev_mode' ) );
			$api   = new Mrkv_CheckboxApi( $login, $password, $cashbox_key, $is_dev );
			$shift = $api->getCurrentCashierShift();

			if ( isset( $shift['status'] ) ) {
				$shift_id = $shift['id'] ?? '';

				if ( 'OPENED' === $shift['status'] ) {
					$is_connected = true;
					$status       = __( 'Відкрито', 'checkbox' );

					if( (int) get_option('ppo_connected') !== 1 ) {
						update_option('ppo_connected', 1);
					}
				} else {
					if( (int) get_option('ppo_connected') !== 0 ) {
						update_option('ppo_connected', 0);
					}
				}
			}
		}

		?>
		<form>
			<p><?php esc_html_e( 'Статус', 'checkbox' ); ?>: <span id="ppo_status" class="status" style="font-weight: 500; text-transform: uppercase;"><?php echo esc_html( $status ); ?></span></p>
			<div class="ppo_connect-group" style="<?php echo esc_html( ( $is_connected ) ? 'display: none;' : 'display: inline-flex;' ); ?> align-items: center;¨" >
				<button type="button" id="ppo_button_connect" class="start button button-secondary"><?php esc_html_e( 'Відкрити зміну', 'checkbox' ); ?></button>
				<img id="ppo_process" style="display: none; margin-left: 10px;" src="<?php echo esc_url( plugins_url( 'assets/img/ajax-loader.gif', __FILE__ ) ); ?>" width="20px" height="20px" alt="proccess imgage" />
			</div>
			<div class="ppo_disconnect-group" style="<?php echo esc_html( ( ! $is_connected ) ? 'display: none;' : 'display: inline-flex;' ); ?> align-items: center;">
				<button type="button" id="ppo_button_disconnect" class="end button button-secondary"><?php esc_html_e( 'Закрити зміну', 'checkbox' ); ?></button>
				<img id="ppo_process" style="display: none; margin-left: 10px;" src="<?php echo esc_url( plugins_url( 'assets/img/ajax-loader.gif', __FILE__ ) ); ?>" width="20px" height="20px" alt="proccess imgage" />
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
	/**
	 * Check connection
	 */
	function mrkv_checkbox_check_connection() {

		check_ajax_referer( 'ppo_checkconnect' );

		$res = array();

		$login       = get_option( 'ppo_login' );
		$password    = get_option( 'ppo_password' );
		$cashbox_key = get_option( 'ppo_cashbox_key' );

		if ( $login && $password && $cashbox_key ) {

			$shift_id = isset( $_POST['shift_id'] ) ? sanitize_text_field( wp_unslash( $_POST['shift_id'] ) ) : '';

			if ( $shift_id ) {
				$is_dev 		= boolval( get_option( 'ppo_is_dev_mode' ) );
				$api            = new Mrkv_CheckboxApi( $login, $password, $cashbox_key, $is_dev );
				$response       = $api->checkConnection( $shift_id );
				$status         = $response['status'] ?? '';
				$res['status']  = $status;
				$res['message'] = '';

				wp_send_json_success( $res );

				return true;
			} else {
				wp_send_json_error(
					array(
						'message' => __( 'Відсутній ID зміни', 'checkbox' ),
					)
				);
			}
		} else {

			wp_send_json_error(
				array(
					'message' => __( "Будь ласка, заповніть обов'язкові поля в налаштуваннях плагіна Checkbox", 'checkbox' ),
				)
			);

			return false;
		}
	}
}

add_action( 'wp_ajax_mrkv_checkbox_connect', 'mrkv_checkbox_connect' );
if ( ! function_exists( 'mrkv_checkbox_connect' ) ) {
	/**
	 * Shift opening
	 *
	 * !this function is used directly and by WP AJAX
	 */
	function mrkv_checkbox_connect() {
		if ( wp_doing_ajax() ) {
			check_ajax_referer( 'ppo_connect' );
		}

		$res = array();

		$login       = get_option( 'ppo_login' );
		$password    = get_option( 'ppo_password' );
		$cashbox_key = get_option( 'ppo_cashbox_key' );

		if ( $login && $password && $cashbox_key ) {

			$is_dev = boolval( get_option( 'ppo_is_dev_mode' ) );
			$api 	= new Mrkv_CheckboxApi( $login, $password, $cashbox_key, $is_dev );

			$shift = $api->connect();

			if ( isset( $shift['id'] ) ) {

				$res['shift_id'] = $shift['id'];
				$res['status']   = ( 'CREATED' === $shift['status'] ) ? __( 'Відкрито', 'checkbox' ) : $shift['status'];
				$res['message']  = '';

				update_option( 'ppo_connected', 1 );

				if ( wp_doing_ajax() ) {
					wp_send_json_success( $res );
				}
			} else {

				$res['shift_id'] = '';

				if ( 'Not authenticated' === $shift['message'] ) {
					$res['message'] = __( 'Невірний логін або пароль. Будь ласка, перевірте дані доступу до особистого кабінету касира на сервісі Checkbox.', 'checkbox' );
				} else {
					$res['message'] = $shift['message'];
				}

				if ( wp_doing_ajax() ) {
					wp_send_json_error( $res );
				}
			}
		} else {

			if ( wp_doing_ajax() ) {
				wp_send_json_error(
					array(
						'message' => __( "Будь ласка, заповніть обов'язкові поля в налаштуваннях плагіна PPO Checkbox", 'checkbox' ),
					)
				);
			}
		}
	}
}

add_action( 'wp_ajax_mrkv_checkbox_disconnect', 'mrkv_checkbox_disconnect' );
if ( ! function_exists( 'mrkv_checkbox_disconnect' ) ) {
	/**
	 * Shift closing
	 *
	 * !this function is used by CRON and WP AJAX
	 * */
	function mrkv_checkbox_disconnect() {

		if ( wp_doing_ajax() ) {
			check_ajax_referer( 'ppo_disconnect' );
		}

		$res = array();

		$login       = get_option( 'ppo_login' );
		$password    = get_option( 'ppo_password' );
		$cashbox_key = get_option( 'ppo_cashbox_key' );

		if ( $login && $password && $cashbox_key ) {

			$is_dev = boolval( get_option( 'ppo_is_dev_mode' ) );
			$api 	= new Mrkv_CheckboxApi( $login, $password, $cashbox_key, $is_dev );

			$shift = $api->disconnect();

			if ( isset( $shift['id'] ) ) {

				$res['shift_id'] = $shift['id'];
				$res['status']   = ( 'CLOSING' === $shift['status'] ) ? __( 'Закрито', 'checkbox' ) : $shift['status'];
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
						'message' => __( "Будь ласка, заповніть обов'язкові поля в налаштуваннях плагіна PPO Checkbox", 'checkbox' ),
					)
				);
			}
		}
	}
}

add_action( 'admin_print_scripts', 'mrkv_checkbox_ppo_connect_script', 999 );
if ( ! function_exists( 'mrkv_checkbox_ppo_connect_script' ) ) {
	/**
	 * Script for plugin dashboard widget
	 */
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

							alert( _e('Виникла помилка. Спробуйте ще раз.', 'checkbox') );
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

			.gateway-settings__wrapper {
				width: 100%;
				height: 100%;
			}

			table.gateway-settings {
				width: 100%;
				border-collapse: collapse;
			}

			table.gateway-settings thead {
				white-space: nowrap;
			}

			table.gateway-settings th {
				background-color: #bbb;
			}

			table.gateway-settings th,
			table.gateway-settings td {
				border: 1px solid #555;
				text-align: center;
			}

			table.gateway-settings .gateway-title {
				max-width: 250px;
			}

			table.gateway-settings .gateway-title p {
				text-align: left;
				text-overflow: ellipsis;
				white-space: nowrap;
				  overflow: hidden;
				width: 100%;
			}

			table.gateway-settings tbody td label:not(:last-child) {
				margin-right: 7px;
			}

			@media (max-width: 782px) {
				table.gateway-settings th, table.gateway-settings td {
					display: inline-flex;
					justify-content: center;
					align-items: center;
    				width: 33.33%;
					min-width: 200px;
					height: 50px;
					white-space: normal;
				}
				table.gateway-settings tbody tr {
					white-space: nowrap;
				}
				div.gateway-settings__wrapper {
					overflow-x: auto;
				}
			}

			@media (max-width: 700px) {
				div.gateway-settings__wrapper {
					overflow-x: auto;
					width: 100vw;
				}
			}
		</style>

		<div class="wrap">
			<h2><?php echo get_admin_page_title(); ?></h2>

			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'ppo-settings-group' ); ?>

				<table class="form-table">

					<tr valign="top">
						<th class="label" scope="row"><?php esc_html_e( 'Логін', 'checkbox' ); ?></th>
						<td><input class="table_input" type="text" name="ppo_login" value="<?php echo esc_html( get_option( 'ppo_login' ) ); ?>" /></td>
					</tr>

					<tr valign="top">
						<th class="label" scope="row"><?php esc_html_e( 'Пароль', 'checkbox' ); ?></th>
						<td><input class="table_input" type="password" name="ppo_password" value="<?php echo esc_html( get_option( 'ppo_password' ) ); ?>" /></td>
					</tr>

					<tr valign="top">
						<th class="label" scope="row"><?php esc_html_e( 'Ім\'я касира', 'checkbox' ); ?></th>
						<td><input class="table_input" type="text" name="ppo_cashier_name" value="<?php echo esc_html( get_option( 'ppo_cashier_name' ) ); ?>" /></td>
					</tr>

					<tr valign="top">
						<th class="label" scope="row"><?php esc_html_e( 'Прізвище касира', 'checkbox' ); ?></th>
						<td><input class="table_input" type="text" name="ppo_cashier_surname" value="<?php echo esc_html( get_option( 'ppo_cashier_surname' ) ); ?>" /></td>
					</tr>

					<tr valign="top">
						<th class="label" scope="row"><?php esc_html_e( 'Ліцензійний ключ віртуального касового апарату', 'checkbox' ); ?></th>
						<td><input class="table_input" type="text" name="ppo_cashbox_key" value="<?php echo esc_html( get_option( 'ppo_cashbox_key' ) ); ?>" /></td>
					</tr>

					<tr valign="top">
						<th class="label" scope="row"><?php esc_html_e( 'Спосіб підпису', 'checkbox' ); ?> <span class="tooltip" aria-label="<?php echo esc_html( 'Доступні два механізми підпису чеків: Checkbox Підпис — утиліта, що встановлюється на будь-якому комп’ютері з доступом до Інтернету, і HSM, або Checkbox Cloud, — сертифікований хмарний сервіс для генерації та зберігання ключів DepositSign, у разі вибору якого необхідність встановлення будь-якого ПЗ для роботи з ЕЦП відсутня.', 'checkbox' ); ?>" data-microtip-position="right" role="tooltip"></sp></th>
						<td>
							<?php
								$ppo_sign_method = get_option( 'ppo_sign_method' );
							?>
							<input class="table_input" type="radio" name="ppo_sign_method" id="ppo_sign_method_cloud" value="cloud"
							<?php
							if ( isset( $ppo_sign_method ) ) {
								checked( $ppo_sign_method, 'cloud' ); }
							?>
							 />
							<label for="ppo_sign_method_cloud"><?php esc_html_e( 'Checkbox Cloud', 'checkbox' ); ?></label>

							<input class="table_input" type="radio" name="ppo_sign_method" id="ppo_sign_method_soft" value="soft"
							<?php
							if ( isset( $ppo_sign_method ) ) {
								checked( $ppo_sign_method, 'soft' ); }
							?>
							 />
							<label for="ppo_sign_method_soft"><?php esc_html_e( 'Checkbox Підпис', 'checkbox' ); ?></label>
						</td>
					</tr>

					<tr valign="top">
						<th class="label" scope="row"><?php esc_html_e( 'Автоматичне відкриття зміни', 'checkbox' ); ?> <span class="tooltip" aria-label="<?php echo esc_html( 'Зміна автоматично відкриватиметься при першому створенні чека.', 'checkbox' ); ?>" data-microtip-position="right" role="tooltip"></sp></th>
						<td><input class="table_input" type="checkbox" name="ppo_autoopen_shift" value="1" <?php checked( get_option( 'ppo_autoopen_shift' ), 1 ); ?> /></td>
					</tr>

					<tr valign="top">
						<th class="label" scope="row"><?php esc_html_e( "Автоматично створювати чеки при зміні статуса замовлення", 'checkbox' ); ?> <span class="tooltip" aria-label="<?php echo esc_html( 'Чек створюватиметься автоматично при зміні статусу замовлення. При ввімкненому стані в табличці "Налаштування способів оплати" з\'явиться колонка "Пропускати чек?", в якій ви можете для кожного способу оплати дозволити або заборонити автоматичне створення чеку при зміні статусу.', 'checkbox' ); ?>" data-microtip-position="right" role="tooltip"></span></th>
						<td>
							<input class="table_input" type="checkbox" name="ppo_autocreate" value="1" <?php checked( get_option( 'ppo_autocreate' ), 1 ); ?> />
						</td>
					</tr>

					<tr class="order-statuses" valign="top" style="<?= ( 1 == get_option( 'ppo_autocreate' ) ) ? '' : 'display: none;'; ?>">
						<th class="label" scope="row"><?php esc_html_e( "Статуси замовлення", 'checkbox' ); ?> <span class="tooltip" aria-label="<?php echo esc_html( 'Виберіть статуси замовлення, при зміні на які буде створюватися чек.', 'checkbox' ); ?>" data-microtip-position="right" role="tooltip"></th>
						<td>
							<? 
								$all_order_statuses = wc_get_order_statuses(); 
								$autocreate_receipt_statuses = (array) get_option( 'ppo_autocreate_receipt_order_statuses' );
							?>
							<select class="chosen order-statuses" name="ppo_autocreate_receipt_order_statuses[]" data-placeholder="<? _e('Виберіть статуси замовлення', 'checkbox') ?>" multiple>
								<? 
								if ( ! empty( $all_order_statuses ) ) : 
									foreach ( $all_order_statuses as $k => $v ) : $k = str_replace( 'wc-', '', $k );
								?>
									<option value="<?= $k; ?>" <?= ( in_array( $k, $autocreate_receipt_statuses ) ) ? 'selected' : ''; ?>><?= $v; ?></option>
								<?
									endforeach;
								else:
									printf('<option value="">%s</option>', __('None'));
								endif;
								?>
							</select>	
						</td>
					</tr>

					<tr valign="top">
						<th class="label" scope="row"><?php esc_html_e( 'Налаштування статусу платіжної системи (CASH або CASHLESS)', 'checkbox' ); ?> <span class="tooltip" aria-label="<?php echo esc_html( 'Визначення типу для кожного способу оплати необхідне для створення чека.', 'checkbox' ); ?>" data-microtip-position="right" role="tooltip"></sp></th>
						<td>
							<?php
							$gateways = WC()->payment_gateways->get_available_payment_gateways();

							$ppo_payment_type          = get_option( 'ppo_payment_type' );
							$ppo_skip_receipt_creation = get_option( 'ppo_skip_receipt_creation' );
							?>
							<div class="gateway-settings__wrapper">
								<table class="gateway-settings">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Спосіб оплати', 'checkbox' ); ?></th>
											<th><?php esc_html_e( 'Тип', 'checkbox' ); ?></th>
											<th class="skip-receipt-creation" style="<?= ( 1 !== (int) get_option( 'ppo_autocreate' ) ) ? 'display:none;' : '' ; ?>"><?php esc_html_e( 'Пропускати створення чека?', 'checkbox' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ( $gateways as $gateway ) :
											if ( 'yes' === $gateway->enabled ) :
												?>
										<tr>
											<td class="gateway-title" title="<?php echo esc_html( $gateway->title ); ?>">
												<p><?php echo esc_html( $gateway->title ); ?></p>
											</td>
											<td>
												<input type="radio" name="ppo_payment_type[<?php echo esc_html( $gateway->id ); ?>]" id="ppo_payment_type_cash[<?php echo esc_html( $gateway->id ); ?>]"
																									<?php
																									if ( isset( $ppo_payment_type[ $gateway->id ] ) ) {
																										checked( $ppo_payment_type[ $gateway->id ], 'cash' ); }
																									?>
												value="cash">
												<label for="ppo_payment_type_cash[<?php echo esc_html( $gateway->id ); ?>]">CASH</label>

												<input type="radio" name="ppo_payment_type[<?php echo esc_html( $gateway->id ); ?>]" id="ppo_payment_type_cashless[<?php echo esc_html( $gateway->id ); ?>]"
																									<?php
																									if ( isset( $ppo_payment_type[ $gateway->id ] ) ) {
																										checked( $ppo_payment_type[ $gateway->id ], 'cashless' ); }
																									?>
												value="cashless">
												<label for="ppo_payment_type_cashless[<?php echo esc_html( $gateway->id ); ?>]">CASHLESS</label>
											</td>
											<td class="skip-receipt-creation" style="<?= ( 1 !== (int) get_option( 'ppo_autocreate' ) ) ? 'display:none;' : '' ; ?>">
												<input type="radio" name="ppo_skip_receipt_creation[<?php echo esc_html( $gateway->id ); ?>]" id="ppo_skip_receipt_creation_yes[<?php echo esc_html( $gateway->id ); ?>]" value="yes"
																											<?php
																												if ( isset( $ppo_skip_receipt_creation[ $gateway->id ] ) ) {
																													checked( $ppo_skip_receipt_creation[ $gateway->id ], 'yes' ); }
																												?>
																										>
												<label for="ppo_skip_receipt_creation_yes[<?php echo esc_html( $gateway->id ); ?>]"><?php esc_html_e( 'Так', 'checkbox' ); ?></label>

												<input type="radio" name="ppo_skip_receipt_creation[<?php echo esc_html( $gateway->id ); ?>]" id="ppo_skip_receipt_creation_no[<?php echo esc_html( $gateway->id ); ?>]" value="no"
																											<?php
																												if ( isset( $ppo_skip_receipt_creation[ $gateway->id ] ) ) {
																													checked( $ppo_skip_receipt_creation[ $gateway->id ], 'no' ); }
																												?>
																										>
												<label for="ppo_skip_receipt_creation_no[<?php echo esc_html( $gateway->id ); ?>]"><?php esc_html_e( 'Ні', 'checkbox' ); ?></label>
											</td>
										</tr>
												<?php
											endif;
										endforeach;
										?>
									</tbody>
								</table>
							</div>
						</td>
					</tr>

					
					<tr valign="top">
						<th class="label" scope="row"><?php esc_html_e( "Тестовий режим", 'checkbox' ); ?> <span class="tooltip" aria-label="<?php echo esc_html( 'При ввімкненому тестовому режимі, всі запити будуть спрямованими до тестового сервера Checkbox — dev-api.checkbox.in.ua. Для підключення ви повинні ввести "Логін", "Пароль" і "Ліцензійний ключ ВКА" від тестового акаунта. Тестовий акаунт надається за проханням адміністрацією Checkbox.', 'checkbox' ); ?>" data-microtip-position="right" role="tooltip"></sp></th>
						<td><input class="table_input" type="checkbox" name="ppo_is_dev_mode" value="1" <?php checked( get_option( 'ppo_is_dev_mode' ), 1 ); ?> /></td>
					</tr>
				</table>

				<?php echo submit_button( __( 'Зберегти', 'checkbox' ) ); ?>

			</form>
		</div>
		<?php
	}
}

// -----------------------------------------------------------------------//
// ----------------------------ADDITIONAL---------------------------------//
// -----------------------------------------------------------------------//

/**
 * Custom plugin style and script
 */
add_action( 'admin_head', 'mrkv_checkbox_custom_style' );
if ( ! function_exists( 'mrkv_checkbox_custom_style' ) ) {

	function mrkv_checkbox_custom_style() {
	?>
		<style>
			#toplevel_page_ppo_settings .wp-menu-image img {
				padding: 7px 0 0 0;
			}
			<?php
				$screen = get_current_screen();
				if( 'toplevel_page_checkbox_settings' === $screen->base ):
			?>
			table tr th.label {
				display: flex;
				align-items: center;
			}
			span.tooltip {
				display: inline-block;
				width: 15px;
				height: 15px;
				min-width: 15px;
				background: url(http://checkbox.morkva.co.ua/wp-content/plugins/checkbox/assets/img/tooltip-icon.svg) no-repeat center / cover;
				margin: 0 10px;
				cursor: pointer;
			}
			span.tooltip::after {
				width: 300px;
				white-space: normal !important;
				height: auto;
				-webkit-font-smoothing: subpixel-antialiased;
			}
			<?php
				endif;
			?>

		</style>
	<?}

}

add_action( 'admin_footer', 'mrkv_checkbox_custom_script' );
if( ! function_exists( 'mrkv_checkbox_custom_script' ) ) {

	function mrkv_checkbox_custom_script() {
		$screen = get_current_screen();
		if( 'toplevel_page_checkbox_settings' === $screen->base ):
		?>
			<script>
				jQuery( function ($) {
					$('input[name=ppo_autocreate]').on('change', function (e) {
						if( $(this).is(":checked") ) {
							$('.skip-receipt-creation').show()
							$('tr.order-statuses').show()
							$('tr.order-statuses select.chosen').chosen({
								width: '300px',
							})
						} else {
							$('tr.order-statuses').hide()
							$('tr.order-statuses select.chosen').chosen('destroy')
							$('tr.order-statuses select.chosen').val('')

							$('.skip-receipt-creation').hide()
							$('td.skip-receipt-creation input[type=radio]').val('')
						}
					})
					if($('input[name=ppo_autocreate]').is(':checked')) {
						$('tr.order-statuses select.chosen').chosen({
							width: '300px',
						})
					}
				})
			</script>
	<?	endif;
	}

}

add_action( 'admin_enqueue_scripts', 'mrkv_checkbox_styles_and_scripts' );
if ( ! function_exists( 'mrkv_checkbox_styles_and_scripts' ) ) {

	function mrkv_checkbox_styles_and_scripts ( $hook ) {
		if ( 'toplevel_page_checkbox_settings' != $hook ) {
			return;
		}
		// microtip
		wp_enqueue_style( 'checkbox-microtip', plugin_dir_url( __FILE__ ) . 'assets/css/microtip.min.css' );
		// chosen
		wp_enqueue_style( 'checkbox-chosen', plugin_dir_url( __FILE__ ) . 'assets/css/chosen.min.css', array(), '1.8.7' );
		wp_enqueue_script( 'checkbox-chosen', plugin_dir_url( __FILE__ ) . 'assets/js/chosen.jquery.min.js', array('jquery'), '1.8.7', true );
	}

}


// -----------------------------------------------------------------------//
// ------------------ACTIVATION AND DEACTIVATION HOOKS--------------------//
// -----------------------------------------------------------------------//

register_activation_hook( __FILE__, 'mrkv_checkbox_activation_cb' );
function mrkv_checkbox_activation_cb() {

	if ( ! wp_next_scheduled( 'checkbox_close_shift' ) ) {
		wp_schedule_event( strtotime( '23:57:00 Europe/Kiev' ), 'daily', 'checkbox_close_shift' );
	}

}


register_deactivation_hook( __FILE__, 'mrkv_checkbox_deactivation_cb' );
function mrkv_checkbox_deactivation_cb() {

	if ( get_option( 'ppo_connected' ) ) {
		mrkv_checkbox_disconnect();
	}

	if ( wp_next_scheduled( 'checkbox_close_shift' ) ) {
		wp_clear_scheduled_hook( 'checkbox_close_shift' );
	}

}

// TEST

function register_awaiting_shipment_order_status() {
    register_post_status( 'wc-awaiting-shipment', array(
        'label'                     => 'Awaiting shipment',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Awaiting shipment (%s)', 'Awaiting shipment (%s)' )
    ) );
    register_post_status( 'wc-form-waybill', array(
        'label'                     => 'Forming Waybill',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Forming Waybill (%s)', 'Forming Waybill (%s)' )
    ) );
}
add_action( 'init', 'register_awaiting_shipment_order_status' );

// Add to list of WC Order statuses
function add_awaiting_shipment_to_order_statuses( $order_statuses ) {
 
    $new_order_statuses = array();
 
    // add new order status after processing
    foreach ( $order_statuses as $key => $status ) {
 
        $new_order_statuses[ $key ] = $status;
 
        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-awaiting-shipment'] = 'Awaiting shipment';
            $new_order_statuses['wc-form-waybill'] = 'Forming Waybill';
        }
    }
 
    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_awaiting_shipment_to_order_statuses' );
