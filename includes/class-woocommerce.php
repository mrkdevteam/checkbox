<?php 
# Check if class exist
if (!class_exists('MRKV_CHECKBOX_WOOCOMMERCE'))
{
	/**
	 * Class for setup plugin woocommerce settings
	 */
	class MRKV_CHECKBOX_WOOCOMMERCE
	{
		/**
		 * Constructor for woocommerce settings
		 * */
		function __construct()
		{
			# Add order metabox action
			add_action('woocommerce_order_actions', array($this, 'mrkv_checkbox_wc_add_order_meta_box_action'));

			# Add function to action
			add_action('woocommerce_order_action_create_bill_action', array($this, 'mrkv_checkbox_wc_process_order_meta_box_action'));

			# Add new column
			add_filter('manage_edit-shop_order_columns', array($this, 'mrkv_checkbox_wc_new_order_column'));

			# Add data to column
			add_action('manage_shop_order_posts_custom_column', array($this, 'mrkv_checkbox_wc_cogs_add_order_receipt_column_content'));
		}

		/**
	     * Add order metabox action
	     *
	     * @param array $actions all actions registered in order meta box
	     * @return array $actions all actions registered in order meta box
	     */
	    public function mrkv_checkbox_wc_add_order_meta_box_action($actions)
	    {
	    	# Add new action
	        $actions['create_bill_action'] = __('Створити чек', 'checkbox');

	        # Return array of actions 
	        return $actions;
	    }

	    /**
	     * Process order metabox action
	     *
	     * @param WC_Order $order Order Info
	     */
	    public function mrkv_checkbox_wc_process_order_meta_box_action($order)
	    {
	    	# Get setting options
	        $login       = get_option('ppo_login');
	        $password    = get_option('ppo_password');
	        $cashbox_key = get_option('ppo_cashbox_key');

	        # Check login data
	        if (! $login) 
	        {
	        	# Show error
	            $order->add_order_note(__('Вкажіть логін в налаштуваннях плагіна Checkbox', 'checkbox'), $is_customer_note = 0, $added_by_user = false);

	            # Stop action
	            return;
	        }

	        # Check password
	        if (! $password) 
	        {
	        	# Show error
	            $order->add_order_note(__('Вкажіть пароль в налаштуваннях плагіна Checkbox', 'checkbox'), $is_customer_note = 0, $added_by_user = false);

	            # Stop action
	            return;
	        }

	        # Check cashbox key
	        if (! $cashbox_key) 
	        {
	        	# Show error
	            $order->add_order_note(__('Вкажіть ліцензійний ключ віртуального касового апарату в налаштуваннях плагіна Checkbox', 'checkbox'), $is_customer_note = 0, $added_by_user = false);

	            # Stop action
	            return;
	        }

	        # Check dev mode
	        $is_dev = boolval(get_option('ppo_is_dev_mode'));

	        # Get logger mode
	        $logger = new Checkbox\KLoggerDecorator(boolval(get_option('ppo_logger')));

	        # Get api class
	        $api = new Checkbox\API($login, $password, $cashbox_key, $is_dev);

	        # Check if receipt is already created 
	        if (! empty(get_post_meta($order->get_id(), 'receipt_id', true))) 
	        {
	        	# Show message
	            $order->add_order_note(__('Чек вже створено', 'checkbox'), $is_customer_note = 0, $added_by_user = false);

	            # Add message to log
	            $logger->info(sprintf('Замовлення №%d. %s', $order->get_id(), __('Чек вже створено', 'checkbox')));

	            # Stop action
	            return;
	        }

	        # Get current shift status 
	        $current_shift = $api->getCurrentCashierShift();

	        # Check current shift status 
	        if (! isset($current_shift['status']) && ( 'OPENED' !== $current_shift['status'] )) 
	        {
	            # Check if Autoopen shift feature is activated 
	            if(is_null(get_option('ppo_autoopen_shift')))
	            {
	            	# Update autoopen shift function
	                update_option( 'ppo_autoopen_shift', 1 );
	            }

	            # Check aotoopen shift data
	            if (1 === (int) get_option('ppo_autoopen_shift')) 
	            {
	            	$connector = new MRKV_CONNTECT_DISCONNECT();

	            	# Connect with checkbox
	                $connector->mrkv_checkbox_connect();

	                # Wait for 8 sec while shift is opening
	                sleep(8); 

	                # Check connect
	                if (0 === (int) get_option('ppo_connected')) 
	                {
	                	# Connect with checkbox
	                    $connector->mrkv_checkbox_connect();
	                }
	            } else 
	            {
	            	# Show error
	                $order->add_order_note(__('Зміна не відкрита', 'checkbox'), $is_customer_note = 0, $added_by_user = false);

	                # Add logger
	                $logger->info(sprintf('Замовлення №%d. %s', $order->get_id(), __('Зміна не відкрита', 'checkbox')));

	                # Stop action
	                return;
	            }
	        }
	        $creator = new MRKV_CHECKBOX_RECEIPT();

	        # Create receipt
	        $result = $creator->mrkv_checkbox_create_receipt($api, $order);

	        # Check status result
	        if ($result['success']) 
	        {
	        	# Add message to order
	            $order->add_order_note(sprintf('%s. <a href="%s" target="_blank">%s</a>', __('Чек створено', 'checkbox'), "https://check.checkbox.ua/{$result['receipt_id']}", __('Роздрукувати', 'checkbox')), $is_customer_note = 0, $added_by_user = false);

	            # Add data to log
	            $logger->info(sprintf('Замовлення №%d. %s.', $order->get_id(), __('Чек створено', 'checkbox')));
	        } else 
	        {
	        	# Add message to order
	            $order->add_order_note(__('Виникла помилка під час створення чека', 'checkbox') . '.' . __('Повідомлення:', 'checkbox') . ' ' . $result['message'], $is_customer_note = 0, $added_by_user = false);

	            # Add data to log
	            $logger->error(sprintf('Замовлення №%d. %s.', $order->get_id(), __('Виникла помилка під час створення чека', 'checkbox') . '.'
	            . __('Повідомлення:', 'checkbox') . ' ' . $result['message'] . '. '
	            . __('Деталі:', 'checkbox') . " loc:{$result['detail']['loc']}, msg:{$result['detail']['msg']}, type:{$result['detail']['type']}"));
	            # Add data to log
	            $logger->debug(__('Зміст помилки:', 'checkbox'), $result);
	        }
	    }

	    /**
	     * Add order admin column
	     *
	     * @param array $columns Columns from edit shop page
	     * @return array $columns Updated columns from edit shop page
	     **/
	    public function mrkv_checkbox_wc_new_order_column($columns)
	    {
	    	# Add column
	        $columns['receipt_column'] = __('ID Чека', 'checkbox');

	        # Return array of columns
	        return $columns;
	    }

	    /**
	     * Fill ID Receipt column
	     *
	     * @param string $column column name
	     */
	    public function mrkv_checkbox_wc_cogs_add_order_receipt_column_content($column)
	    {
	    	# Get order data
	        global $the_order;

	        # Check column slug
	        if ('receipt_column' === $column) 
	        {
	        	# Get Receipt ID
	            $receipt_id = get_post_meta($the_order->get_id(), 'receipt_id', true);

	            # Show receipt link
	            printf('<a href="%s" target="_blank">%s</a>', "https://check.checkbox.ua/{$receipt_id}", $receipt_id);
	        }
	    }
	}
}
?>