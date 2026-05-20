<?php
/**
 * Plugin Name: morkva Checkbox Integration
 * Plugin URI: https://morkva.co.ua/shop/woocommerce-checkbox-pro?utm_source=checkbox-plugin
 * Description: Інтеграція WooCommerce з пРРО Checkbox
 * Version: 3.0.3
 * Requires at least: 5.2
 * Requires PHP: 7.1
 * Tested up to: 6.9
 * Requires Plugins: woocommerce
 * Author: morkva
 * Author URI: https://morkva.co.ua
 * Text Domain: checkbox
 * Domain Path: /languages
 * WC requires at least: 5.0
 * WC tested up to: 9.8
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Exit if accessed directly to prevent security vulnerabilities.
 */
if (! defined('ABSPATH')) 
{
    exit;
}

/**
 * Declare compatibility with WooCommerce Custom Order Tables (HPOS).
 * This must be hooked into 'before_woocommerce_init'.
 */
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

/**
 * Load plugin constants.
 * We include this early so constants are available for all hooks.
 */
require_once 'constants-mrkv-checkbox.php';

/**
 * Initialize the plugin after all other plugins have loaded.
 * Ensures WooCommerce is active before instantiating the main controller.
 */
function mrkv_checkbox_init_core() {
	// Check if WooCommerce is active and the main runner class exists.
	if ( class_exists( 'WooCommerce' ) ) {
		$runner_path = plugin_dir_path( __FILE__ ) . 'classes/mrkv-checkbox-run.php';

		if ( file_exists( $runner_path ) ) {
			require_once $runner_path;

			if ( class_exists( 'MRKV_CHECKBOX_RUN' ) ) {
				new MRKV_CHECKBOX_RUN();
			}
		}
	}
}
add_action( 'plugins_loaded', 'mrkv_checkbox_init_core' );