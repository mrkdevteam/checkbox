<?php
/**
 * Plugin Name: WooCommerce Checkbox Integration
 * Plugin URI: https://morkva.co.ua/shop/checkbox-woocommerce?utm_source=checkbox-plugin
 * Description: Інтеграція WooCommerce з пРРО Checkbox
 * Version: 2.1.1
 * Tested up to: 6.2
 * Requires at least: 5.2
 * Requires PHP: 7.1
 * Author: MORKVA
 * Author URI: https://morkva.co.ua
 * Text Domain: checkbox
 * Domain Path: /languages
 * WC tested up to: 7.6.0
 */

#  Stop access .php files through URL
if (! defined('ABSPATH')) 
{
    exit;
}

# Versions number
define('CHECKBOX_VERSION', '2.1.1');
define('CHECKBOX_LICENSE', 'free');

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

