<?php 
# Check if class exist
if (!class_exists('MRKV_CHECKBOX_RECEIPT'))
{
	/**
	 * Class for create receipt
	 */
	class MRKV_CHECKBOX_RECEIPT
	{
		/**
	     * @var string check already created
	     * */
	    const RECEIPT_ALREADY_CREATED = 'Чек вже створено';

	    /**
	     * @var string order text
	     * */
	    const ORDER_TEXT = 'Замовлення';

	    /**
	     * @var string error create receipt
	     * */
	    const ERROR_CREATE_RECEIPT = 'Помилка створення чеку';

	    /**
	     * @var string print text
	     * */
	    const PRINT_RECEIPT = 'Роздрукувати';

	    /**
	     * @var string url checkbox
	     * */
	    const URL_CHECKBOX = 'https://check.checkbox.ua/';

	    /**
	     * @var string check created
	     * */
	    const RECEIPT_CREATED = 'Чек створено';

	    /**
	     * @var string shift already opened
	     * */
	    const SHIFT_ALREADY_OPENED = 'Зміна вже відкрита';

	    /**
	     * @var string shift already not opened
	     * */
	    const SHIFT_ALREADY_NOT_OPENED = 'Зміна не відкрита. Початок відкриття зміни';

	    /**
	     * @var string shift opened start processing
	     * */
	    const SHIFT_OPENED_START_PROCESSING = 'Зміна відкрита. Початок створення чека';

	    /**
	     * @var string receipt error processing
	     * */
	    const RECEIPT_ERROR_PROCESSING = 'Виникла помилка під час створення чека';

	    /**
	     * @var string message
	     * */
	    const MESSAGE = 'Повідомлення';

	    /**
	     * @var string details
	     * */
	    const DETAILS = 'Деталі';

	    /**
	     * @var string error content
	     * */
	    const ERROR_CONTENT = 'Зміст помилки';

	    /**
	     * @var string shift closed receipt
	     * */
	    const SHIFT_CLOSED_RECEIPT = 'Зміна не відкрита для створення чека';

	    /**
	     * @var string shift not opened
	     * */
	    const SHIFT_NOT_OPENED = 'Зміна не відкрита';

	    /**
	     * @var string code error
	     * */
	    const CODE_ERROR = 'Код помилки';

		/**
		 * Constructor for create receipt
		 * */
		function __construct()
		{
			# Add function to order status change
			add_action('woocommerce_order_status_changed', array($this, 'mrkv_checkbox_auto_create_receipt'), 99, 3);
		}

		/**
	     * Automatic receipt creation
	     *
	     * @param string $order_id Order ID
	     * @param string $old_status old order status
	     * @param string $new_status new order status
	     */
	    public function mrkv_checkbox_auto_create_receipt($order_id, $old_status, $new_status)
	    {
	    	# Check autoopen create receipt
	        if (1 !== (int) get_option('ppo_autocreate')) 
	        {
	        	# Stop action
	            return;
	        }

	        # Get order
	        $order = wc_get_order($order_id);
	        # get payment ID
	        $order_payment_id = $order->get_payment_method();

	        # Get all statuses
	        $order_statuses = (array) get_option('ppo_autocreate_receipt_order_statuses');
	        # Get payment statuses
	        $payment_order_statuses = (array) get_option('ppo_autocreate_payment_order_statuses');
	        # Get payment statuses
	        $payment_order_statuses_active = (array) get_option('ppo_rules_active');

	        # If not settings
	        if (empty($order_statuses) && empty($payment_order_statuses)) 
	        {
	        	# Stop action
	            return;
	        }

	        # Check rule
	        if($payment_order_statuses_active && is_array($payment_order_statuses_active))
	        {
	        	if(!array_key_exists($order_payment_id, $payment_order_statuses_active)){
	        		# Stop action
	            	return;
	        	}
	        }
	        else
	        {
	        	# Stop action
	        	return;
	        }

	        # Check statuses
	        if (in_array($new_status, $order_statuses) || in_array($new_status, $payment_order_statuses[$order_payment_id])) 
	        {
	            # Check if receipt is already created 
	            if (! empty(get_post_meta($order_id, 'receipt_id', true))) 
	            {
	            	# Add message to order
	                $order->add_order_note(__(self::RECEIPT_ALREADY_CREATED, 'checkbox'), $is_customer_note = 0, $added_by_user = false);

	                # Stop action
	                return;
	            }

	            # Check if Autoopen shift feature is activated 
	            if(is_null(get_option('ppo_autoopen_shift')))
	            {
	            	# Update option
	                update_option( 'ppo_autoopen_shift', 1 );
	            }
	           
	            # Get data options
	            $login       = get_option('ppo_login');
	            $password    = get_option('ppo_password');
	            $cashbox_key = get_option('ppo_cashbox_key');

	            # Check all data
	            if ($login && $password && $cashbox_key) 
	            {
	            	# Get dev mode
	                $is_dev = boolval(get_option('ppo_is_dev_mode'));

	                # Get log data
	                $logger = new Checkbox\KLoggerDecorator(boolval(get_option('ppo_logger')));

	                # Get api function
	                $api = new Checkbox\API($login, $password, $cashbox_key, $is_dev);

	                # Get current shift
	                $shift = $api->getCurrentCashierShift();

	                # Check aotoopen shift data
	                if (1 === (int) get_option('ppo_autoopen_shift')) 
	                {
	                	# Check status
	                    if (isset($shift['status']) && ( 'OPENED' === $shift['status'] )) 
	                    {
	                    	# Save to log
	                        $logger->info(__(self::SHIFT_ALREADY_OPENED, 'checkbox'));
	                    }
	                    else
	                    {
	                    	# Save to log
	                        $logger->info(__(self::SHIFT_ALREADY_NOT_OPENED, 'checkbox'));

	                        $connect = new MRKV_CONNTECT_DISCONNECT();

	                        # Connect to checkbox
	                        $connect->mrkv_checkbox_connect(false);

	                        # Wait for 8 sec while shift is opening
	                        sleep(8);

	                        # Check connect
	                        if (0 === (int) get_option('ppo_connected')) 
	                        {
	                        	# Connect to checkbox
	                            $connect->mrkv_checkbox_connect(false);
	                        }

	                        # Get current shift
	                        $shift = $api->getCurrentCashierShift();
	                    }
	                }

	                # Check status
	                if (isset($shift['status']) && ( 'OPENED' === $shift['status'] )) 
	                {
	                	# Save to log
	                    $logger->info(__(self::SHIFT_OPENED_START_PROCESSING, 'checkbox'));
	                    # Create receipt
	                    $result = $this->mrkv_checkbox_create_receipt($api, $order);

	                    # Check result
	                    if ($result && isset($result['receipt_id'])) 
	                    {
	                    	# Add message to order
	                        $order->add_order_note(sprintf('%s. <a href="%s" target="_blank">%s</a>', __(self::RECEIPT_CREATED, 'checkbox'), self::URL_CHECKBOX . "{$result['receipt_id']}", __(self::PRINT_RECEIPT, 'checkbox')), $is_customer_note = 0, $added_by_user = false);

	                        # Save to log
	                        $logger->info(sprintf(self::ORDER_TEXT . ' №%d. %s.', $order->get_id(), __(self::RECEIPT_CREATED, 'checkbox')));
	                    } 
	                    elseif(isset($result['success'])){
	                    	# Add message to order
	                        $order->add_order_note(__(self::ERROR_CREATE_RECEIPT, 'checkbox'), $is_customer_note = 0, $added_by_user = false);

	                        # Save to log
		                    $logger->info($result['message']);
	                    }
	                    else 
	                    {
	                    	# Add message to order
	                        $order->add_order_note(__(self::ERROR_CREATE_RECEIPT, 'checkbox'), $is_customer_note = 0, $added_by_user = false);

	                        # Save to log
	                        $logger->error(sprintf(self::ORDER_TEXT . ' №%d. %s.', $order->get_id(), __(self::RECEIPT_ERROR_PROCESSING, 'checkbox') . '.'
	                        . __(self::MESSAGE . ':', 'checkbox') . ' ' . $result['message'] . '. '
	                        . __(self::DETAILS . ':', 'checkbox') . " loc:{$result['detail']['loc']}, msg:{$result['detail']['msg']}, type:{$result['detail']['type']}"));

	                        # Save to log
	                        $logger->debug(__(self::ERROR_CONTENT . ':', 'checkbox'), $result);
	                    }
	                } else 
	                {
	                	# Save to log
	                    $logger->info(__(self::SHIFT_CLOSED_RECEIPT, 'checkbox'));

	                    # Add message to order
	                    $order->add_order_note(__(self::SHIFT_NOT_OPENED, 'checkbox'), $is_customer_note = 0, $added_by_user = false);

	                    # Save to log
	                    $logger->error(sprintf(self::ORDER_TEXT . ' №%d. %s.', $order->get_id(), __(self::SHIFT_NOT_OPENED, 'checkbox'), $order->get_id()));
	                }
	            }
	        }
	    }

	    /**
	     * Receipt creation
	     *
	     * @param Checkbox\API $api Checkbox API
	     * @param WC_Order         $order Order
	     * 
	     * @return array Result
	     */
	    public function mrkv_checkbox_create_receipt($api, $order)
	    {
	    	# Get payment type
	        $payment_settings = get_option('ppo_payment_type');
	        # Get user
	        $user             = wp_get_current_user();

	        # Get cashier name
	        $cashier_name = get_option('ppo_cashier_name') . ' ' . get_option('ppo_cashier_surname');

	        # Create params for query
	        $params         = array();

	        # Get order data
	        $order_data     = $order->get_data();
	        $goods_items    = $order->get_items();
	        $payment_method = $order->get_payment_method();

	        # Get email
	        $email = isset($order_data['billing']['email']) ? $order_data['billing']['email'] : $user->user_email;

	        # Get payment type
	        $payment_type = isset($payment_settings[ $payment_method ]) ? mb_strtoupper($payment_settings[ $payment_method ]) : 'CASHLESS';

	        # Create good array
	        $goods       = array();

	        # Total price
	        $total_price = 0;

	        # Get tax code
	        $tax = get_option('ppo_tax_code');
	        
	        # Coupon name
	        $coupon_name = 'Купон';

	        # Check settings
	        if(get_option('ppo_receipt_coupon_text')){
	        	# Set coupon name
	        	$coupon_name = get_option('ppo_receipt_coupon_text');
	        }

	        $zero_product_exclude = get_option('ppo_zero_product_exclude');
	        
	        # Loop all items
	        foreach ($goods_items as $item) 
	        {

	        	# Get price 
	            $price = ($item->get_subtotal() / $item->get_quantity());

	            if($zero_product_exclude && $price == 0){
	            	continue;
	            }

	            # Set price
	            $price_checkbox = floatval($price) * 100;

	            # Set product to array
	            $good = array(
	                'code'  => $item->get_id() . '-' . $item->get_name(),
	                'name'  => $item->get_name(),
	                'price' => round($price_checkbox),
	            );

	            # Check tax
	            if (!empty($tax)) 
	            {
	            	$tax_array = explode(',', $tax);

	            	# Set tax
	                $good['tax'] = $tax_array;
	            }

	            $good_data = array(
	                'good'     => $good,
	                'quantity' => (int) ($item->get_quantity() * 1000),
	            );

	            if($item->get_total() != $item->get_subtotal()){
	            	$discount_item = $item->get_subtotal() - $item->get_total();

            		$good_data['discounts'][] = array(
		                'type' => 'DISCOUNT',
		                'mode' => 'VALUE',
		                'value' => round($discount_item * 100),
		                'name' => $coupon_name
		            );
	            }

	            # Create total price
            	$total_price += $item->get_total() * 100;

	            # Set product to goods array
	            $goods[] = $good_data;
	        }

	        # Set array
	        $params['goods']        = $goods;
	        # Set cashier name 
	        $params['cashier_name'] = $cashier_name;

	        # Check email
	        if (!empty($email)) 
	        {
	        	# Set email to delivery
	            $params['delivery'] = array( 'email' => $email );
	        }

	        $ppo_payment_type_label = get_option('ppo_payment_type_label');

	        # Check payment label
	        if(isset($ppo_payment_type_label[ $payment_method ]) && $ppo_payment_type_label[ $payment_method ] != ''){
	        	# Set payments
		        $params['payments'][]   = array(
		            'type'  => $payment_type,
		            'value' => ceil($total_price),
		            'label' => $ppo_payment_type_label[ $payment_method ]
		        );
	        }
	        else{
	        	# Set payments
		        $params['payments'][]   = array(
		            'type'  => $payment_type,
		            'value' => ceil($total_price),
		        );
	        }

	        $order->add_order_note(print_r($params, 1), $is_customer_note = 0, $added_by_user = false);

	        # Set footer
	        $footer = get_option('ppo_receipt_footer');

	        # Check footer data
	        if ($footer) 
	        {
	        	# Set footer data
	            $params['footer'] = Checkbox\Support::processReceiptFooter($order, $footer);
	        }

	        //$order->add_order_note(print_r($params, 1), $is_customer_note = 0, $added_by_user = false);

	        # Create result array
	        $result  = array();
	        # Create receipt
	        $receipt = $api->createReceipt($params);

	        # Check receipt error
	        if (isset($receipt['error'])) 
	        {
	        	# Set error message
	            $result['success'] = false;
	            $result['message'] = sprintf('%s: %d.%s: %s.', __(self::CODE_ERROR, 'checkbox'), $receipt['error']['code'], __(self::MESSAGE . ':', 'checkbox'), $receipt['error']['message']);

	            # Return result
	            return $result;
	        }

	        # Save order ID 
	        if (isset($receipt['id'])) 
	        {
	            # Save receipt ID in meta 
	            update_post_meta($order->get_id(), 'receipt_id', sanitize_text_field($receipt['id']));
	            update_post_meta($order->get_id(), 'receipt_url', sprintf(self::URL_CHECKBOX . '%s', $receipt['id']));

	            # Set complete message
	            $result['success'] = true;
	            $result['receipt_id'] = $receipt['id'];
	        } else 
	        {
	        	# Set error message
	            $result['success'] = false;
	            $result['message'] = $receipt['message'];
	        }

	        # Return result
	        return $result;
	    }
	}
}
?>