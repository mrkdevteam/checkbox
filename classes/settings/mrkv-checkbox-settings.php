<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Include ua shipping options
require_once 'global/mrkv-checkbox-options.php'; 
# Include ua shipping menu
require_once 'admin/mrkv-checkbox-menu.php'; 
# Include settings assets
require_once 'admin/mrkv-checkbox-admin-assets.php';
# Include debug log
require_once 'log/mrkv-checkbox-log.php'; 
# Include active/deactive 
require_once 'admin/mrkv-checkbox-activation-deactivation.php'; 

# Check if class exist
if (!class_exists('MRKV_CHECKBOX_SETTINGS'))
{
	/**
	 * Class for setup plugin settings
	 */
	class MRKV_CHECKBOX_SETTINGS
	{
		/**
		 * Constructor for plugin settings
		 * */
		function __construct()
		{
			# Setup woo plugin settings options
			new MRKV_CHECKBOX_OPTIONS();

			# Setup woo plugin settings menu
			new MRKV_CHECKBOX_MENU();

			# Setup woo plugin settings assets
			new MRKV_CHECKBOX_ADMIN_ASSETS();

			# Setup active/deactive 
			new MRKV_CHECKBOX_ACTIVATION_DEACTIVATION();
		}
	}
}