<?php 

use Automattic\WooCommerce\Utilities\OrderUtil;

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
	        if ((is_array($order_statuses) && in_array($new_status, $order_statuses)) || (is_array($payment_order_statuses) && isset($payment_order_statuses[$order_payment_id]) && in_array($new_status, $payment_order_statuses[$order_payment_id]))) 
	        {
	        	if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
        		{
        			# Check if receipt is already created 
		            if (! empty($order->get_meta('receipt_id'))) 
		            {
		            	# Add message to order
		                $order->add_order_note(__(self::RECEIPT_ALREADY_CREATED, 'checkbox'), $is_customer_note = 0, $added_by_user = false);

		                # Stop action
		                return;
		            }
        		}
        		else
        		{
        			# Check if receipt is already created 
		            if (! empty(get_post_meta($order_id, 'receipt_id', true))) 
		            {
		            	# Add message to order
		                $order->add_order_note(__(self::RECEIPT_ALREADY_CREATED, 'checkbox'), $is_customer_note = 0, $added_by_user = false);

		                # Stop action
		                return;
		            }
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
	     * Generate UUID for create receipt
	     * @return string UUID 
	     * */
	    public function mrkv_checkbox_generate_uuid($api)
	    {
	    	$has_uuid = true;
	    	$uuid = '';

	    	while ($has_uuid) 
	    	{
    			# Generate UUID
		    	$uuid = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			        mt_rand( 0, 0xffff ),
			        mt_rand( 0, 0x0fff ) | 0x4000,
			        mt_rand( 0, 0x3fff ) | 0x8000,
			        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
			    );

			    $result_answer = $api->getReceipt($uuid);

		    	if(isset($result_answer['message']))
		    	{
	            	$has_uuid = false;
	            }
	    	}

	    	return $uuid;
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

	        # Get payment type
	        $payment_new_settings = get_option('ppo_payment_type_checkbox');
	        $payment_type_checkbox_label = isset($payment_new_settings[ $payment_method ]['label']) ? $payment_new_settings[ $payment_method ]['label'] : '';

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

	            if ( wc_get_price_decimals() == 1 && $item->get_quantity() == 1) {
				    $price = round( $price, 1 );
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
            	$total_price += round( $item->get_total() * 100 );

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

	        $payments_data = array(
	        	'type'  => $payment_type,
	            'value' => ceil($total_price),
	        );

	        if($payment_method == 'morkva-monopay')
	        {
	        	$payments_data['code']   = 1;
	        	$payments_data['label'] = 'Платіж plata by mono';

	        	if($order->get_meta('mrkv_mopay_payment_method') && $order->get_meta('mrkv_mopay_payment_method') == 'morkva-monopay-checkout')
	        	{
	        		$payments_data['label'] = 'Платіж mono checkout';
	        	}
	        }
	        elseif($payment_method == 'morkva-liqpay')
	        {
	        	$payments_data['code']   = 1;
	        	$payments_data['label'] = 'Платіж LiqPay';
	        }
	        elseif($payment_method == 'morkva-monopay-prepay' || $payment_method == 'morkva-liqpay-prepay')
	        {
	        	$payments_data['code']   = 1;
	        	$payments_data['label'] = 'Післяплата';
	        }
	        elseif(isset($payment_type_checkbox_label) && $payment_type_checkbox_label == 'Післяплата (з контролем оплати)')
	        {
	        	$payments_data['code']   = 1;
	        	$payments_data['label']  = 'Платіж NovaPay';
	        }

	        if($payment_type_checkbox_label)
	        {
	        	$payments_data['code']   = CHECKBOX_PAYMENT_LABELS[$payment_type_checkbox_label]['code'];
    			$payments_data['label']   = $payment_type_checkbox_label;

    			if(isset($ppo_payment_type_label[ $payment_method ]) && $ppo_payment_type_label[ $payment_method ] != '')
    			{
    				$payments_data['label'] = $ppo_payment_type_label[ $payment_method ];
    			}
	        }
	        else
	        {
	        	if(!isset($payments_data['label']))
	        	{
	        		if($payment_method == 'cod')
	        		{
	        			$payments_data['code']   = '0';
	        			$payments_data['label']   = 'Готівка';
	        		}
	        		else
	        		{
	        			$payments_data['code']   = '1';
	        			$payments_data['label']   = 'Електронний платіжний засіб';
	        		}
	        	}
	        }

	        /* LIQPAY */
 
	        if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    		{
    			$order_acq_id = $order->get_meta('_mrkv_liqpay_acq_id');
    		}
    		else
    		{
    			$order_acq_id = get_post_meta( $order->get_id(), '_mrkv_liqpay_acq_id', true );
    		}

	        if(isset($order_acq_id) && $order_acq_id != ''){
	        	$payments_data['acquirer_and_seller']   = $order_acq_id;
	        }

	        if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    		{
    			$order_agent_commission = $order->get_meta('_mrkv_liqpay_agent_commission');
    		}
    		else
    		{
    			$order_agent_commission = get_post_meta( $order->get_id(), '_mrkv_liqpay_agent_commission', true );
    		}

	        if(isset($order_agent_commission) && $order_agent_commission != ''){
	        	$payments_data['commission']   = $order_agent_commission;
	        }

	        if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    		{
    			$order_sender_card_mask = $order->get_meta('_mrkv_liqpay_sender_card_mask2');
    		}
    		else
    		{
    			$order_sender_card_mask = get_post_meta( $order->get_id(), '_mrkv_liqpay_sender_card_mask2', true );
    		}


	        if(isset($order_sender_card_mask) && $order_sender_card_mask != ''){
	        	$payments_data['card_mask']   = $order_sender_card_mask;
	        }

	        if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    		{
    			$order_liqpay_order_id = $order->get_meta('_mrkv_liqpay_liqpay_order_id');
    		}
    		else
    		{
    			$order_liqpay_order_id = get_post_meta( $order->get_id(), '_mrkv_liqpay_liqpay_order_id', true );
    		}

	        if(isset($order_liqpay_order_id) && $order_liqpay_order_id != ''){
	        	$payments_data['rrn']   = $order_liqpay_order_id;
	        }

	        if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    		{
    			$payment_system = $order->get_meta('_mrkv_liqpay_sender_card_type');
    		}
    		else
    		{
    			$payment_system = get_post_meta( $order->get_id(), '_mrkv_liqpay_sender_card_type', true );
    		}

	        if(isset($order_liqpay_order_id) && $order_liqpay_order_id != ''){
	        	$payments_data['payment_system']   = $payment_system;
	        }

	        /* MONO Plata */

	        if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    		{
    			$order_acq_id = $order->get_meta('mrkv_mopay_accuiring_tran_id');
    		}
    		else
    		{
    			$order_acq_id = get_post_meta( $order->get_id(), 'mrkv_mopay_accuiring_tran_id', true );
    		}

	        if(isset($order_acq_id) && $order_acq_id != ''){
	        	$payments_data['acquirer_and_seller']   = $order_acq_id;
	        }

	         if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    		{
    			$order_agent_commission = $order->get_meta('mrkv_mopay_accuiring_fee');
    		}
    		else
    		{
    			$order_agent_commission = get_post_meta( $order->get_id(), 'mrkv_mopay_accuiring_fee', true );
    		}

	        if(isset($order_agent_commission) && $order_agent_commission != ''){
	        	$payments_data['commission']   = $order_agent_commission;
	        }

	        if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    		{
    			$order_sender_card_mask = $order->get_meta('mrkv_mopay_accuiring_masked_pan');
    		}
    		else
    		{
    			$order_sender_card_mask = get_post_meta( $order->get_id(), 'mrkv_mopay_accuiring_masked_pan', true );
    		}


	        if(isset($order_sender_card_mask) && $order_sender_card_mask != ''){
	        	$payments_data['card_mask']   = $order_sender_card_mask;
	        }

	        if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    		{
    			$order_liqpay_order_id = $order->get_meta('mrkv_mopay_accuiring_rrn');
    		}
    		else
    		{
    			$order_liqpay_order_id = get_post_meta( $order->get_id(), 'mrkv_mopay_accuiring_rrn', true );
    		}

	        if(isset($order_liqpay_order_id) && $order_liqpay_order_id != ''){
	        	$payments_data['rrn']   = $order_liqpay_order_id;
	        }

	        if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    		{
    			$payment_system = $order->get_meta('mrkv_mopay_accuiring_payment_system');
    		}
    		else
    		{
    			$payment_system = get_post_meta( $order->get_id(), 'mrkv_mopay_accuiring_payment_system', true );
    		}

	        if(isset($order_liqpay_order_id) && $order_liqpay_order_id != ''){
	        	$payments_data['payment_system']   = $payment_system;
	        }

	        $params['payments'][] = $payments_data;

	        # Set footer
	        $footer = get_option('ppo_receipt_footer');

	        # Check footer data
	        if ($footer) 
	        {
	        	# Set footer data
	            $params['footer'] = Checkbox\Support::processReceiptFooter($order, $footer);
	        }

	       $ppo_logger = get_option('ppo_logger');

	        if($ppo_logger){
	        	# Add message to order
                $order->add_order_note('Query: ' . print_r($params, 1), $is_customer_note = 0, $added_by_user = false);
	        }

	        $uuid = $this->mrkv_checkbox_generate_uuid($api);

	        if($uuid)
	        {
	        	$params['id'] = $uuid;
	        }

	        # Create result array
	        $result  = array();
	        # Create receipt
	        $receipt = $api->createReceipt($params);

	        if($ppo_logger){
	        	# Add message to order
                $order->add_order_note('Answer: ' . print_r($receipt, 1), $is_customer_note = 0, $added_by_user = false);
	        }

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
	        	if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
        		{
        			# Save receipt ID in meta 
        			$order->update_meta_data( 'receipt_id', sanitize_text_field($receipt['id']) );
		            $order->update_meta_data( 'receipt_url', sprintf(self::URL_CHECKBOX . '%s', $receipt['id']) );

		            $order->save();
        		}
        		else
        		{
        			# Save receipt ID in meta 
		            update_post_meta($order->get_id(), 'receipt_id', sanitize_text_field($receipt['id']));
		            update_post_meta($order->get_id(), 'receipt_url', sprintf(self::URL_CHECKBOX . '%s', $receipt['id']));
        		}

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