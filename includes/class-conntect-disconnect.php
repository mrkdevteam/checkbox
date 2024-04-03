<?php 
# Check if class exist
if (!class_exists('MRKV_CONNTECT_DISCONNECT'))
{
	/**
	 * Class for dashboard widget
	 */
	class MRKV_CONNTECT_DISCONNECT
	{
		/**
	     * @var string error message id
	     * */
	    const ERROR_ID_MESSAGE = 'Відсутній ID зміни';

	    /**
	     * @var string error message data
	     * */
	    const ERROR_DATA_MESSAGE = "Будь ласка, заповніть обов'язкові поля в налаштуваннях плагіна Checkbox";

	    /**
	     * @var string name opened shift
	     * */
	    const OPEN_SHIFT_NAME = 'Відкрито';

	    /**
	     * @var string name closed shift
	     * */
	    const CLOSE_SHIFT_NAME = 'Закрито';

	    /**
	     * @var string shift success opened
	     * */
	    const SHIFT_SUCCESS_OPENED = 'Зміна успішно відкрита';

	    /**
	     * @var string shift success closed
	     * */
	    const SHIFT_SUCCESS_CLOSED = 'Зміна успішно закрита';

	    /**
	     * @var string wrong login message
	     * */
	    const WRONG_SUCCESS_MESSAGE_LOGIN = 'Невірний логін або пароль. Будь ласка, перевірте дані доступу до особистого кабінету касира на сервісі Checkbox.';

	    /**
	     * @var string error open shift
	     * */
	    const ERROR_OPEN_SHIFT = 'Під час відкриття зміни виникла помилка. Повідомлення:';

	    /**
	     * @var string error close shift
	     * */
	    const ERROR_CLOSE_SHIFT = 'Під час закриття зміни виникла помилка. Повідомлення:';


	    /**
	     * @var string error field empty
	     * */
	    const ERROR_FIELD_EMPTY = "Будь ласка, заповніть обов'язкові поля в налаштуваннях плагіна PPO Checkbox";

		/**
		 * Constructor for connect and disconnect shift
		 * */
		function __construct()
		{
			# Add function check connect
			add_action('wp_ajax_mrkv_checkbox_check_connection', array($this, 'mrkv_checkbox_check_connection'));

			# Add function to connect
			add_action('wp_ajax_mrkv_checkbox_connect', array($this, 'mrkv_checkbox_connect'));
			# Add function to connect
			add_action('wp_ajax_mrkv_checkbox_connect_ajax', array($this, 'mrkv_checkbox_connect_ajax'));
			# Add function to disconnect
			add_action('wp_ajax_mrkv_checkbox_disconnect', array($this, 'mrkv_checkbox_disconnect'));

			
			/** Add action for plugin cron event 'checkbox_close_shift' */
			add_action('checkbox_close_shift', array($this, 'mrkv_checkbox_disconnect'));
			/** Add action for plugin cron event 'checkbox_open_shift' */
			add_action('checkbox_open_shift', array($this, 'mrkv_checkbox_connect'));
		}

		/**
	     * Check connection
	     * 
	     * @return boolean Result
	     */
	    public function mrkv_checkbox_check_connection()
	    {
	    	# Check ajax
	        check_ajax_referer('ppo_checkconnect');

	        $res = array();

	        # Get field
	        $login       = get_option('ppo_login');
	        $password    = get_option('ppo_password');
	        $cashbox_key = get_option('ppo_cashbox_key');

	        # Check Field
	        if ($login && $password && $cashbox_key) 
	        {
	        	# Get shift id
	            $shift_id = isset($_POST['shift_id']) ? sanitize_text_field(wp_unslash($_POST['shift_id'])) : '';

	            # Check shift id
	            if ($shift_id) 
	            {
	            	# Set all data
	                $is_dev         = boolval(get_option('ppo_is_dev_mode'));
	                $api            = new Checkbox\API($login, $password, $cashbox_key, $is_dev);
	                $response       = $api->checkConnection((int) $shift_id);
	                $status         = $response['status'] ?? '';
	                $res['status']  = $status;
	                $res['message'] = '';

	                # Send json success
	                wp_send_json_success($res);

	                # Return positive result
	                return true;

	            } 
	            else 
	            {
	            	# Send json error
	                wp_send_json_error(
	                    array(
	                        'message' => __(self::ERROR_ID_MESSAGE, 'checkbox'),
	                    )
	                );
	            }
	        } 
	        else 
	        {
	        	# Send json success
	            wp_send_json_error(
	                array(
	                    'message' => __(self::ERROR_DATA_MESSAGE, 'checkbox'),
	                )
	            );

	            # Return negative result
	            return false;
	        }
	    }

	    /**
	     * Shift opening
	     *
	     * !this function is used directly
	     */
	    public function mrkv_checkbox_connect($this_ajax = true)
	    {
	    	# Create result array
	        $res = array();

	        # Get all data
	        $login       = get_option('ppo_login');
	        $password    = get_option('ppo_password');
	        $cashbox_key = get_option('ppo_cashbox_key');

	        # Check data
	        if ($login && $password && $cashbox_key) 
	        {	
	        	# Get dev mode
	            $is_dev = boolval(get_option('ppo_is_dev_mode'));

	            # Get logger data
	            $logger = new Checkbox\KLoggerDecorator(boolval(get_option('ppo_logger')));

	            # Get api data
	            $api    = new Checkbox\API($login, $password, $cashbox_key, $is_dev);

	            # Connect shift
	            $shift = $api->connect();

	            # Check shift id
	            if (isset($shift['id'])) 
	            {	
	            	# Create shift id
	                $res['shift_id'] = $shift['id'];
	                # Set status 
	                $res['status']   = ( 'CREATED' === $shift['status'] ) ? __(self::OPEN_SHIFT_NAME, 'checkbox') : $shift['status'];
	                # Set message
	                $res['message']  = '';

	                # Update option
	                update_option('ppo_connected', 1);

	                # Show log message
	                $logger->info(__(self::SHIFT_SUCCESS_OPENED, 'checkbox'));
	            } 
	            else 
	            {	
	            	# Create shift id
	                $res['shift_id'] = '';

	                # Check message
	                if ('Not authenticated' === $shift['message']) 
	                {
	                	# Set message
	                    $res['message'] = __(self::WRONG_SUCCESS_MESSAGE_LOGIN, 'checkbox');

	                    # Set log error message
	                    $logger->error(__(self::WRONG_SUCCESS_MESSAGE_LOGIN, 'checkbox'));
	                } 
	                else 
	                {
	                	# Set shift message
	                    $res['message'] = $shift['message'];

	                    # Set log error message
	                    $logger->error(sprintf('%s %s', __(self::ERROR_OPEN_SHIFT, 'checkbox'), $shift['message']));
	                }
	            }
	        } 
	    }

	    /**
	     * Shift opening
	     *
	     * !this function is used directly and by WP AJAX
	     */
	    public function mrkv_checkbox_connect_ajax($this_ajax = true)
	    {
	    	# Check doing ajax
            if (wp_doing_ajax()) 
            {
            	# Check
            	check_ajax_referer('ppo_connect');
        	}

	        # Create result 
	        $res = array();

	        # Get all user data
	        $login       = get_option('ppo_login');
	        $password    = get_option('ppo_password');
	        $cashbox_key = get_option('ppo_cashbox_key');

	        # Check user data
	        if ($login && $password && $cashbox_key) 
	        {
	        	# Get dev mode
	            $is_dev = boolval(get_option('ppo_is_dev_mode'));

	            # Get logger data
	            $logger = new Checkbox\KLoggerDecorator(boolval(get_option('ppo_logger')));

	            # Get api data
	            $api    = new Checkbox\API($login, $password, $cashbox_key, $is_dev);

	            # Connect shift
	            $shift = $api->connect();

	            # Check shift id
	            if (isset($shift['id'])) 
	            {
	            	# Set shift id
	                $res['shift_id'] = $shift['id'];
	                # Set status 
	                $res['status']   = ( 'CREATED' === $shift['status'] ) ? __(self::OPEN_SHIFT_NAME, 'checkbox') : $shift['status'];
	                # Set message
	                $res['message']  = '';

	                # Update option
	                update_option('ppo_connected', 1);

	                # Show log message
	                $logger->info(__(self::SHIFT_SUCCESS_OPENED, 'checkbox'));

	                # Check ajax
	                if (wp_doing_ajax()) 
	                {
	                	# Send json success
	                    wp_send_json_success($res);
	                }
	            } 
	            else 
	            {
	            	# Create shift id
	                $res['shift_id'] = '';

	                # Check message
	                if ('Not authenticated' === $shift['message']) 
	                {
	                	# Set message
	                    $res['message'] = __(self::WRONG_SUCCESS_MESSAGE_LOGIN, 'checkbox');

	                    # Set log error message
	                    $logger->error(__(self::WRONG_SUCCESS_MESSAGE_LOGIN, 'checkbox'));
	                } 
	                else 
	                {
	                	if(isset($shift['error']['message']))
	                	{
	                		# Set shift message
	                    	$res['message'] = $shift['error']['message'];
	                	}
	                	else
	                	{
	                		# Set shift message
	                    	$res['message'] = $shift['message'];
	                	}

	                    # Set log error message
	                    $logger->error(sprintf('%s %s', __(self::ERROR_OPEN_SHIFT, 'checkbox'), $shift['message']));
	                }

	                # Check ajax
	                if (wp_doing_ajax()) 
	                {
	                	# Send json error
	                    wp_send_json_error($res);
	                }
	            }
	        } 
	        else 
	        {
	        	# Check ajax
	            if (wp_doing_ajax()) 
	            {
	            	# Send json error
	                wp_send_json_error(
	                    array(
	                        'message' => __(self::ERROR_FIELD_EMPTY, 'checkbox'),
	                    )
	                );
	            }
	        }
	    }

	    /**
	     * Shift closing
	     *
	     * !this function is used by CRON and WP AJAX
	     * */
	    public function mrkv_checkbox_disconnect()
	    {
	    	# Check ajax
	        if (wp_doing_ajax()) {
	        	# Check
	            check_ajax_referer('ppo_disconnect');
	        }

	        # Create result array
	        $res = array();

	        # Get all data
	        $login       = get_option('ppo_login');
	        $password    = get_option('ppo_password');
	        $cashbox_key = get_option('ppo_cashbox_key');

	        # Check data
	        if ($login && $password && $cashbox_key) 
	        {
	        	# Get dev mode
	            $is_dev = boolval(get_option('ppo_is_dev_mode'));

	            # Get logger data
	            $logger = new Checkbox\KLoggerDecorator(boolval(get_option('ppo_logger')));

	            # Get api data
	            $api    = new Checkbox\API($login, $password, $cashbox_key, $is_dev);

            	# Disconnect shift
	            $shift = $api->disconnect();

	            # Check shift id
	            if (isset($shift['id'])) 
	            {	
	            	# Create shift id
	                $res['shift_id'] = $shift['id'];
	                # Set status 
	                $res['status']   = ( 'CLOSING' === $shift['status'] ) ? __(self::CLOSE_SHIFT_NAME, 'checkbox') : $shift['status'];
	                # Set message
	                $res['message']  = '';

	                # Update option
	                update_option('ppo_connected', 0);

	                # Show log message
	                $logger->info(__(self::SHIFT_SUCCESS_CLOSED, 'checkbox'));

	                # Check ajax
	                if (wp_doing_ajax()) 
	                {
	                	# Send json success
	                    wp_send_json_success($res);
	                }
	            } 
	            else 
	            {
	            	# Create shift id
	                $res['shift_id'] = '';
	                # Set message
	                $res['message']  = $shift['message'];

	                # Set log error message
	                $logger->error(sprintf('%s %s', __(self::ERROR_CLOSE_SHIFT, 'checkbox'), $shift['message']));

	                # Check ajax
	                if (wp_doing_ajax()) 
	                {
	                	# Send json error
	                    wp_send_json_error($res);
	                }
	            }
	        } 
	        else 
	        {
	        	# Check ajax
	            if (wp_doing_ajax()) 
	            {
	            	# Send json error
	                wp_send_json_error(
	                    array(
	                        'message' => __(self::ERROR_FIELD_EMPTY, 'checkbox'),
	                    )
	                );
	            }
	        }
	    }
	}
}
?>