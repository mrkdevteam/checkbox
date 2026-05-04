<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Include settings
require_once 'settings/mrkv-checkbox-settings.php';
# Include controller
require_once 'controller/mrkv-checkbox-controller.php';
# Include woocommerce settings
require_once 'woocommerce/mrkv-checkbox-woocommerce.php';

# Check if class exist
if (!class_exists('MRKV_CHECKBOX_RUN'))
{
	/**
	 * Class for setup plugin 
	 */
	class MRKV_CHECKBOX_RUN
	{
		/**
		 * Constructor for plugin setup
		 * */
		function __construct()
		{
			# Setup woo plugin settings
			new MRKV_CHECKBOX_SETTINGS();
			# Setup woo plugin controller
			new MRKV_CHECKBOX_CONTROLLER();
			# Setup woo plugin woocommerce
			new MRKV_CHECKBOX_WOOCOMMERCE();
		}
	}
}