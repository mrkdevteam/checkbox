<?php 

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use Automattic\WooCommerce\Utilities\OrderUtil;

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

			# Check HPOS
			if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled()){
				# Add new column
				add_filter('manage_woocommerce_page_wc-orders_columns', array($this, 'mrkv_checkbox_wc_new_order_column'), 20);
				# Add data to column
				add_action('manage_woocommerce_page_wc-orders_custom_column', array($this, 'mrkv_checkbox_wc_cogs_add_order_receipt_column_content_hpos'), 20, 2 );
			}
			else{
				# Add new column
				add_filter('manage_edit-shop_order_columns', array($this, 'mrkv_checkbox_wc_new_order_column'));
				# Add data to column
				add_action('manage_shop_order_posts_custom_column', array($this, 'mrkv_checkbox_wc_cogs_add_order_receipt_column_content'));
			}

			# Add metabox to order edit
			add_action( 'add_meta_boxes', array($this, 'mrkv_checkbox_wc_add_metabox'));

			add_action( 'wp_ajax_submit_morkva_checkbox', array($this, 'mrkv_checkbox_wc_do_metabox_action') );
			add_action( 'wp_ajax_nopriv_submit_morkva_checkbox', array($this, 'mrkv_checkbox_wc_do_metabox_action') );
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
	        $actions['create_bill_action'] = __('Створити чек з Checkbox', 'checkbox');

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

	        if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    		{
    			# Check if receipt is already created 
		        if (! empty($order->get_meta('receipt_id'))) 
		        {
		        	# Show message
		            $order->add_order_note(__('Чек вже створено', 'checkbox'), $is_customer_note = 0, $added_by_user = false);

		            # Add message to log
		            $logger->info(sprintf('Замовлення №%d. %s', $order->get_id(), __('Чек вже створено', 'checkbox')));

		            # Stop action
		            return;
		        }
    		}
    		else
    		{
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
	        	if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    			{
    				# Get Receipt ID
	            	$receipt_id = $the_order->get_meta('receipt_id');
    			}
    			else
    			{
    				# Get Receipt ID
	            	$receipt_id = get_post_meta($the_order->get_id(), 'receipt_id', true);
    			}

	            # Show receipt link
	            printf('<a href="%s" target="_blank">%s</a>', "https://check.checkbox.ua/{$receipt_id}", $receipt_id);
	        }
	    }

	    /**
	     * Fill ID Receipt column HPOS
	     *
	     * @param string $column column name
	     */
	    public function mrkv_checkbox_wc_cogs_add_order_receipt_column_content_hpos($column, $the_order)
	    {
	        # Check column slug
	        if ('receipt_column' === $column) 
	        {
	        	if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    			{
    				# Get Receipt ID
	            	$receipt_id = $the_order->get_meta('receipt_id');
    			}
    			else
    			{
    				# Get Receipt ID
	            	$receipt_id = get_post_meta($the_order->get_id(), 'receipt_id', true);
    			}

	            # Show receipt link
	            printf('<a href="%s" target="_blank">%s</a>', "https://check.checkbox.ua/{$receipt_id}", $receipt_id);
	        }
	    }

	    /**
	     * Add metabox
	     * 
	     * */
	    public function mrkv_checkbox_wc_add_metabox()
	    {
	    	# If HPOS support
	    	if(class_exists( CustomOrdersTableController::class )){
	    		# Check hpos
		    	$screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		        ? wc_get_page_screen_id( 'shop-order' )
		        : 'shop_order';
	    	}
	    	else{
	    		$screen = 'shop_order';
	    	}

	    	# Add metabox to admin page
	        add_meta_box( 'morkva_checkbox_metabox', __('Checkbox','woocommerce'), array($this, 'mrkv_checkbox_wc_add_metabox_content'), $screen, 'side', 'core' );
	    }

	    /**
	     * Add content to metabox
	     * 
	     * */
	    public function mrkv_checkbox_wc_add_metabox_content()
	    {
	    	# Check hpos
	    	if (isset($_GET["post"]) || isset($_GET["id"]))
        	{
        		# Set order ID
        		$order_id = '';
	            if(isset($_GET["post"])){
	                $order_id = $_GET["post"];    
	            }
	            else{
	                $order_id = $_GET["id"];
	            }

	            $order = wc_get_order( $order_id );

	            if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
    			{
    				# Get Receipt ID
	            	$receipt_id = $order->get_meta('receipt_id');
    			}
    			else
    			{
    				# Get Receipt ID
	            	$receipt_id = get_post_meta($order_id, 'receipt_id', true);
    			}

	            # Check receipt id
	            if($receipt_id)
	            {
	            	# Show receipt link
	            	printf('Чек: ' . '<a href="%s" target="_blank">%s</a>', "https://check.checkbox.ua/{$receipt_id}", $receipt_id);
	            }
	            else
	            {
	        	    $button_text = __( 'Створити чек', 'woocommerce' );

	            	echo '<div class="mrkv_checkbox_action_button">
	            	<div type="submit" class="button button-primary submit_morkva_checkbox_action" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">' . $button_text . '</div>
	            	<svg style="display: none;" version="1.1" id="L9" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="30px" height="30px" x="0px" y="0px"
					  viewBox="0 0 100 100" enable-background="new 0 0 0 0" xml:space="preserve">
					    <path fill="#000" d="M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50">
					      <animateTransform 
					         attributeName="transform" 
					         attributeType="XML" 
					         type="rotate"
					         dur="1s" 
					         from="0 50 50"
					         to="360 50 50" 
					         repeatCount="indefinite" />
					  </path>
					</svg>
	            	</div>';

	            	echo "<script>
	            		let CheckboxisProcessing = false;
	            		 jQuery('.submit_morkva_checkbox_action').click(function(){
	            		 	if (CheckboxisProcessing) return;
    						CheckboxisProcessing = true;
					        jQuery.ajax({
					            url: '" .  admin_url( "admin-ajax.php" ) . "',
					            type: 'POST',
					            data: 'action=submit_morkva_checkbox&order_id=" . $order_id . "', 
					            beforeSend: function( xhr ) {
					                jQuery('.mrkv_checkbox_action_button svg').show();
					            },
					            success: function( data ) {
					            	CheckboxisProcessing = false;
					                location.reload();
					            }
					        });
					    });
	            	</script>";
	            }
        	}
	    }

	    /**
	     * Create receipt by button checkbox
	     * 
	     * @var Order ID
	     * */
	    public function mrkv_checkbox_wc_do_metabox_action()
	    {
	    	# Check type
	    	if(isset($_POST[ 'order_id' ])){
	    		# Set order id
	    		$order_id = $_POST[ 'order_id' ];
		    	# Get order data
		    	$order = wc_get_order( $order_id );

		    	# Check order data
		    	if($order){
		    		# Create receipt
		    		$this->mrkv_checkbox_wc_process_order_meta_box_action($order);
		    	}
		    	else{
		    		# Get logger mode
        			$logger = new Checkbox\KLoggerDecorator(boolval(get_option('ppo_logger')));

        			# Add message to log
            		$logger->info(__('Помилка під час створення чека: Відсутній номер замовлення для обробки.', 'checkbox'));
		    	}
	    	}
	    }
	}
}
?>