<?php
/**
 * Plugin Constants
 *
 * @package Checkbox
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Core File and Path Definitions
// Using trailingslashit prevents double slashes and ensures consistency.
define( 'MRKV_CHECKBOX_PLUGIN_FILE', __FILE__ );
define( 'MRKV_CHECKBOX_PLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'MRKV_CHECKBOX_PLUGIN_PATH_TEMP', MRKV_CHECKBOX_PLUGIN_PATH . 'templates/' );

// 2. URL and Asset Definitions
define( 'MRKV_CHECKBOX_PLUGIN_DIR', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'MRKV_CHECKBOX_ASSETS_URL', MRKV_CHECKBOX_PLUGIN_DIR . 'assets/' );
define( 'MRKV_CHECKBOX_IMG_URL', MRKV_CHECKBOX_ASSETS_URL . 'images/' );

// 3. Plugin Metadata
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$mrkv_checkbox_plugin_info = get_plugin_data( MRKV_CHECKBOX_PLUGIN_FILE, false, false );

define( 'MRKV_CHECKBOX_NAME', $mrkv_checkbox_plugin_info['Name'] );
define( 'MRKV_CHECKBOX_PLUGIN_VERSION', $mrkv_checkbox_plugin_info['Version'] );
define( 'MRKV_CHECKBOX_PLUGIN_TEXT_DOMAIN', 'checkbox' );