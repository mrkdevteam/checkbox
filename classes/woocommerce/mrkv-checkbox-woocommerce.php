<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Include order
require_once 'mrkv-checkbox-woo-order.php';
# Include orders
require_once 'mrkv-checkbox-woo-orders.php';

# Check if class exist
if (!class_exists('MRKV_CHECKBOX_WOOCOMMERCE'))
{
    /**
     * Class for setup plugin woocommerce settings
     */
    class MRKV_CHECKBOX_WOOCOMMERCE
    {
        /**
         * Constructor for plugin setup
         * */
        function __construct()
        {
            # Setup woo plugin settings order
            new MRKV_CHECKBOX_WOO_ORDER();
            # Setup woo plugin settings orders
            new MRKV_CHECKBOX_WOO_ORDERS();
        }
    }
}