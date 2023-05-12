<?php 
# Check if class exist
if (!class_exists('MRKV_ACTIVATION_DEACTIVATION'))
{
	/**
	 * Class for dashboard widget
	 */
	class MRKV_ACTIVATION_DEACTIVATION
	{
		/**
		 * @var string Status update
		 * */
		const REQUEST_STATUS_UPDATE = 'updated';

		/**
		 * @var string Status activated
		 * */
		const REQUEST_STATUS_ACTIVE = 'activated';

		/**
		 * @var string Status deactivated
		 * */
		const REQUEST_STATUS_DEACTIVE = 'deactivated';

		/**
		 * @var string Plugin type
		 * */
		const REQUEST_PLUGIN_TYPE = 'plugin';

		/**
		 * @var string Register url
		 * */
		const API_URL_REGISTER = 'https://api2.morkva.co.ua/api/customers/register';

		/**
		 * @var string Error message
		 * */
		const ERROR_MESSAGE = 'Something went wrong:';

		/**
		 * @var string edrpou message
		 * */
		const EDRPOU_MESSAGE = 'ЄДРПОУ: ';

		/**
		 * Constructor for connect and disconnect shift
		 * */
		function __construct()
		{
			# Send request when plugin's settings are being saved
			add_filter('pre_update_option_ppo_cashbox_key', array($this, 'mrkv_checkbox_update_data_main'), 10, 3);
			# Update plugin status
			add_action('upgrader_process_complete', array($this, 'mrkv_checkbox_upgrade'), 10, 2);

			# Add function by activation
			register_activation_hook(__FILE__, array($this, 'mrkv_checkbox_activation_cb'));
			# Add function by deactivation
			register_deactivation_hook(__FILE__, array($this, 'mrkv_checkbox_deactivation_cb'));
		}

		/**
		 * Update status morkva api
		 * 
		 * @var string Current value
		 * @var string Old value
		 * @var string Option
		 * 
		 * @return string Value
		 * */
		public function mrkv_checkbox_update_data_main ( $value, $old_value, $option ) 
		{
			# Send request
		    $this->mrkv_checkbox_send_request( self::REQUEST_STATUS_UPDATE );

		    # Return main value
		    return $value;
		}

		/**
		 * Upgrade checkbox 
		 * 
		 * @param object Upgraded object
		 * @param array All options
		 * */
		public function mrkv_checkbox_upgrade($upgrader_object, $options)
		{
			# Get plugin name
		    $current_plugin_path_name = plugin_basename(__FILE__);

		    # Check action
		    if ($options['action'] == self::REQUEST_STATUS_UPDATE && $options['type'] == self::REQUEST_PLUGIN_TYPE) 
		    {
		    	# Loop all plugins
		        foreach ($options['plugins'] as $each_plugin) 
		        {
		        	# Compare plugin name
		            if ($each_plugin == $current_plugin_path_name) 
		            {
		            	# Send request
		                $this->mrkv_checkbox_send_request( self::REQUEST_STATUS_UPDATE );
		            }
		        }
		    }
		}

		/**
		 * Activation functions
		 * 
		 * */
		public function mrkv_checkbox_activation_cb()
		{
			# Update option
		    update_option( 'ppo_autoopen_shift', 1 );

		    # Check has cron 
		    if (wp_next_scheduled('checkbox_close_shift')) 
		    {
		    	# Disable cron
		        wp_clear_scheduled_hook('checkbox_close_shift');
		    }

		    # Check has cron 
		    if (wp_next_scheduled('checkbox_open_shift')) 
		    {
		    	# Disable cron
		        wp_clear_scheduled_hook('checkbox_open_shift');
		    }

		    # Send request
		    $this->mrkv_checkbox_send_request( self::REQUEST_STATUS_ACTIVE );
		}

		/**
		 * Deactivation functions
		 * 
		 * */
		public function mrkv_checkbox_deactivation_cb()
		{
			# Get option connected
		    if (get_option('ppo_connected')) 
		    {
		    	# Get connector
		    	$connect = new MRKV_CONNTECT_DISCONNECT();
		    	# Disconnecting
		        $connect->mrkv_checkbox_disconnect();
		    }

		    # Send request
		    $this->mrkv_checkbox_send_request( self::REQUEST_STATUS_DEACTIVE );
		}

		/**
		 * Send morkva request
		 * 
		 * @var string Status
		 * */
		public function mrkv_checkbox_send_request(string $status)
		{
			# Get IP
		    $ip       = !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['REMOTE_ADDR'];
		    # Get home url
		    $home_url = parse_url(home_url());

		    # Get all data
		    $data = [
		        'ip'      => $ip,
		        'domain'  => $home_url['host'],
		        'product' => 'checkbox',
		        'version' => CHECKBOX_VERSION,
		        'license' => CHECKBOX_LICENSE,
		        'info'    => get_option('ppo_cashbox_key'),
		        'status'  => $status
		    ];
		    
		    # Set url api
		    $url = self::API_URL_REGISTER;

		    # Set response data
		    $response = wp_remote_post( $url, array(
		        'method'      => 'POST',
		        'timeout'     => 45,
		        'redirection' => 5,
		        'blocking'    => true,
		        'headers'     => array(
		            'Accept' => 'application/json'
		        ),
		        'body'        => $data,
		        'cookies'     => array()
		    ));
	        
	        # Check wp error
		    if ( is_wp_error( $response ) ) 
		    {
		    	# Set error message
		        $error_message = $response->get_error_message();
	        	
	        	# Get logger object
		        $logger = new Checkbox\KLoggerDecorator(boolval(get_option('ppo_logger')));
		        # Show error
		        $logger->error( self::ERROR_MESSAGE . " $error_message", 1 );
		    }
		}

		/**
		 * Send morkva request erdpou
		 * 
		 * @var string Status
		 * */
		public function mrkv_checkbox_send_request_edrpou(string $status)
		{
			# Get IP
		    $ip       = !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['REMOTE_ADDR'];
		    # Get home url
		    $home_url = parse_url(home_url());

		    # Get all data
		    $data = [
		        'ip'      => $ip,
		        'domain'  => $home_url['host'],
		        'product' => 'checkbox',
		        'version' => CHECKBOX_VERSION,
		        'license' => CHECKBOX_LICENSE,
		        'info'    => get_option('ppo_cashbox_edrpou'),
		        'status'  => $status
		    ];
		    
		    # Set url api
		    $url = self::API_URL_REGISTER;

		    # Set response data
		    $response = wp_remote_post( $url, array(
		        'method'      => 'POST',
		        'timeout'     => 45,
		        'redirection' => 5,
		        'blocking'    => true,
		        'headers'     => array(
		            'Accept' => 'application/json'
		        ),
		        'body'        => $data,
		        'cookies'     => array()
		    ));
	        
	        # Check wp error
		    if ( is_wp_error( $response ) ) 
		    {
		    	# Set error message
		        $error_message = $response->get_error_message();
	        	
	        	# Get logger object
		        $logger = new Checkbox\KLoggerDecorator(boolval(get_option('ppo_logger')));
		        # Show error
		        $logger->error( self::ERROR_MESSAGE . " $error_message", 1 );
		    }
		}
	}
}
?>