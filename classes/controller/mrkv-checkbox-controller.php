<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Include settings
require_once 'mrkv-checkbox-ajax.php';
# Include settings
require_once 'mrkv-checkbox-autocreate.php';

# Check if class exist
if (!class_exists('MRKV_CHECKBOX_CONTROLLER'))
{
	/**
	 * Class for setup plugin API controller
	 */
	class MRKV_CHECKBOX_CONTROLLER
	{
		/**
		 * Constructor for plugin API controller
		 * */
		function __construct()
		{
			# Setup woo plugin ajax
			new MRKV_CHECKBOX_AJAX();
			# Setup woo plugin ajax
			new MRKV_CHECKBOX_AUTOCREATE();
		}
	}
}