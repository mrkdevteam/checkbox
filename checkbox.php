<?php
/**
 * Plugin Name: Morkva Checkbox Integration
 * Plugin URI: https://morkva.co.ua/shop/checkbox-woocommerce?utm_source=checkbox-plugin
 * Description: Інтеграція WooCommerce з пРРО Checkbox
 * Version: 2.8.6
 * Tested up to: 6.8
 * Requires at least: 5.2
 * Requires PHP: 7.1
 * Author: MORKVA
 * Author URI: https://morkva.co.ua
 * Text Domain: checkbox
 * Domain Path: /languages
 * WC tested up to: 9.8
 */

#  Stop access .php files through URL
if (! defined('ABSPATH')) 
{
    exit;
}

add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

# Versions number
define('CHECKBOX_VERSION', '2.8.6');
define('CHECKBOX_LICENSE', 'free');

define('CHECKBOX_PAYMENT_LABELS', array(
    'Готівка' => array(
        'code' => 0
    ),
    'Подарунковий сертифікат' => array(
        'code' => 1
    ),
    'Талон' => array(
        'code' => 1
    ),
    'Жетон' => array(
        'code' => 1
    ),
    'Картка' => array(
        'code' => 1
    ),
    'Платіж через інтегратора' => array(
        'code' => 1
    ),
    'Переказ через ННПП' => array(
        'code' => 1
    ),
    'Переказ через ПТКС ННПП' => array(
        'code' => 1
    ),
    'Інтернет еквайринг' => array(
        'code' => 1
    ),
    'Інтернет банкінг' => array(
        'code' => 1
    ),
    'З поточного рахунку' => array(
        'code' => 1
    ),
    'Переказ через ПТКС банку' => array(
        'code' => 1
    ),
    'Фішка' => array(
        'code' => 1
    ),
    'Електронний грошовий замінник' => array(
        'code' => 1
    ),
    'Ігровий замінник гривні' => array(
        'code' => 1
    ),
    'Електронні гроші' => array(
        'code' => 1
    ),
    'Цифрові гроші' => array(
        'code' => 1
    ),
    'Криптовалюта' => array(
        'code' => 1
    ),
    'Післяплата' => array(
        'code' => 1,
    ),
    'Переказ з картки' => array(
        'code' => 1,
    ),
    'Переказ з поточного рахунку' => array(
        'code' => 1,
    )
));

function my_custom_admin_notice() {
    // Check if the notice has been dismissed
    if (get_user_meta(get_current_user_id(), 'mrkv_checkbox_notice_dismissed', true)) {
        return;
    }

    ?>
    <div class="notice notice-error is-dismissible mrkv-checkbox-notice">
        <br>
        <p><?php _e('<b>Morkva Checkbox Integration</b> Увага! Перевірте <b><a href="' . esc_url(admin_url('admin.php?page=checkbox_settings')) . '">в налаштуваннях</a></b> чи для всіх способів оплати вказані label.', 'checkbox'); ?></p>
        <br>
    </div>
    <script>
        jQuery(document).ready(function($) {
            $(document).on('click', '.mrkv-checkbox-notice .notice-dismiss', function() {
                $.post(ajaxurl, {
                    action: 'mrkv_checkbox_dismiss_notice',
                    nonce: '<?php echo wp_create_nonce("mrkv_checkbox_notice_nonce"); ?>'
                });
            });
        });
    </script>
    <?php
}
add_action('admin_notices', 'my_custom_admin_notice');

// Handle AJAX request to dismiss notice
function mrkv_checkbox_dismiss_notice() {
    check_ajax_referer('mrkv_checkbox_notice_nonce', 'nonce');
    update_user_meta(get_current_user_id(), 'mrkv_checkbox_notice_dismissed', true);
    wp_die();
}
add_action('wp_ajax_mrkv_checkbox_dismiss_notice', 'mrkv_checkbox_dismiss_notice');


# Include autoload
require_once 'vendor/autoload.php';
# Include checkbox api library
require_once 'includes/class-api.php';
# Include support
require_once 'includes/class-support.php';
# Include klogger
require_once 'includes/class-klogger.php';

// -----------------------------------------------------------------------//
// ------------------ACTIVATION AND DEACTIVATION HOOKS--------------------//
// -----------------------------------------------------------------------//

# Include all needed classes for activation and deactivation plugin
require_once 'includes/class-activation-deactivation.php'; 

# Setup activation and deactivation plugin
new MRKV_ACTIVATION_DEACTIVATION();

add_action( 'init', function() {
    // -----------------------------------------------------------------------//
    // --------------------------------SETUP----------------------------------//
    // -----------------------------------------------------------------------//

    # Include all needed classes for setup
    require_once 'includes/class-setup.php'; 

    # Setup plugin
    new MRKV_CHECKBOX_SETUP(__FILE__);

    // -----------------------------------------------------------------------//
    // -----------------------WOOCOMMERCE CUSTOMISATION-----------------------//
    // -----------------------------------------------------------------------//

    # Include all needed classes for woocommerce
    require_once 'includes/class-woocommerce.php'; 

    # Setup woocommerce settings
    new MRKV_CHECKBOX_WOOCOMMERCE();

    // -----------------------------------------------------------------------//
    // -----------------------------CREATE RECEIPT----------------------------//
    // -----------------------------------------------------------------------//

    # Include all needed classes for create receipt
    require_once 'includes/class-create-receipt.php'; 

    # Setup create receipt
    new MRKV_CHECKBOX_RECEIPT();

    // -----------------------------------------------------------------------//
    // ---------------------------DASHBOARD WIDGET----------------------------//
    // -----------------------------------------------------------------------//

    # Include all needed classes for dashboard settings
    require_once 'includes/class-dashboard-widget.php'; 

    # Setup dashboard settings
    new MRKV_DASHBOARD_WIDGET(__FILE__);

    // -----------------------------------------------------------------------//
    // --------------------------CONNECT AND DISCONNECT-----------------------//
    // -----------------------------------------------------------------------//

    # Include all needed classes for conntect disconnect shift
    require_once 'includes/class-conntect-disconnect.php'; 

    # Setup conntect disconnect shift
    new MRKV_CONNTECT_DISCONNECT();
} );



