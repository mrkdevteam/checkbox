<?php 
# Check if class exist
if (!class_exists('MRKV_DASHBOARD_WIDGET'))
{
	/**
	 * Class for dashboard widget
	 */
	class MRKV_DASHBOARD_WIDGET
	{
		/**
		 * @var string Path to plugin
		 * */
		private $filename;

		/**
	     * @var string closed text
	     * */
	    const CLOSED_TEXT = 'Закрито';

	    /**
	     * @var string opened text
	     * */
	    const OPENED_TEXT = 'Відкрито';

		/**
		 * Constructor for dashboard widget
		 * */
		function __construct($file_name)
		{
			# Save filename
			$this->file_name = $file_name;

			# Add function to dashboard
			add_action('wp_dashboard_setup', array($this, 'mrkv_checkbox_ppo_status_dashboard_widget'));

			# Script connect
			add_action('admin_print_scripts', array($this, 'mrkv_checkbox_ppo_connect_script'), 999);
		}

		/**
	     * Register plugin dashboard widget only for admin role
	     */
	    public function mrkv_checkbox_ppo_status_dashboard_widget()
	    {
	    	# Check active plugin
	        if (current_user_can('activate_plugins')) 
	        {
	        	# Add dashboard widget
	            wp_add_dashboard_widget('status_widget', 'Checkbox', array($this, 'mrkv_checkbox_status_widget_form'));
	        }
	    }

	    /**
	     * Plugin dashboard widget functionality
	     */
	    public function mrkv_checkbox_status_widget_form()
	    {
	    	# Create fields
	        $shift        = '';
	        $shift_id     = '';
	        $is_connected = false;
	        $status       = __(self::CLOSED_TEXT, 'checkbox');

	        # Get all data
	        $login       = get_option('ppo_login');
	        $password    = get_option('ppo_password');
	        $cashbox_key = get_option('ppo_cashbox_key');

	        # Check all data
	        if ($login && $password && $cashbox_key) 
	        {
	        	# Get dev mode
	            $is_dev = boolval(get_option('ppo_is_dev_mode'));
	            # Get api data
	            $api   = new Checkbox\API($login, $password, $cashbox_key, $is_dev);
	            # Check shift
	            $shift = $api->getCurrentCashierShift();

	            # Check shift status
	            if (isset($shift['status'])) 
	            {
	            	# Check shift id
	                $shift_id = $shift['id'] ?? '';

	                # Check shift status
	                if ('OPENED' === $shift['status']) 
	                {
	                	# Set connect
	                    $is_connected = true;

	                    # Set text 
	                    $status       = __(self::OPENED_TEXT, 'checkbox');

	                    # Check connect
	                    if ((int) get_option('ppo_connected') !== 1) 
	                    {
	                    	# Update option
	                        update_option('ppo_connected', 1);
	                    }
	                } 
	                else 
	                {
	                	# Check connect
	                    if ((int) get_option('ppo_connected') !== 0) 
	                    {
	                    	# Update option
	                        update_option('ppo_connected', 0);
	                    }
	                }
	            }
	        }

	        ?>
	        <form>
	            <p><?php esc_html_e('Статус', 'checkbox'); ?>: <span id="ppo_status" class="status" style="font-weight: 500; text-transform: uppercase;"><?php echo esc_html($status); ?></span></p>
	            <div class="ppo_connect-group" style="<?php echo esc_html(( $is_connected ) ? 'display: none;' : 'display: inline-flex;'); ?> align-items: center;¨" >
	                <button type="button" id="ppo_button_connect" class="start button button-secondary"><?php esc_html_e('Відкрити зміну', 'checkbox'); ?></button>
	                <img id="ppo_process" style="display: none; margin-left: 10px;" src="<?php echo esc_url(plugins_url('assets/img/ajax-loader.gif', $this->file_name)); ?>" width="20px" height="20px" alt="proccess imgage" />
	            </div>
	            <div class="ppo_disconnect-group" style="<?php echo esc_html(( ! $is_connected ) ? 'display: none;' : 'display: inline-flex;'); ?> align-items: center;">
	                <button type="button" id="ppo_button_disconnect" class="end button button-secondary"><?php esc_html_e('Закрити зміну', 'checkbox'); ?></button>
	                <img id="ppo_process" style="display: none; margin-left: 10px;" src="<?php echo esc_url(plugins_url('assets/img/ajax-loader.gif', $this->file_name)); ?>" width="20px" height="20px" alt="proccess imgage" />
	            </div>
	            <input type="hidden" id="ppo_shift_id" value="<?php echo esc_html($shift_id); ?>">
	        </form>

	        <?php
	    }
	    /**
	     * Script for plugin dashboard widget
	     */
	    public function mrkv_checkbox_ppo_connect_script()
	    {

	        if ('dashboard' !== get_current_screen()->base) {
	            return;
	        }
	        ?>

	        <script>
	            jQuery(document).ready(function($) {

	                $('#ppo_button_connect').on('click', function() {

	                    $.ajax({
	                        type: 'post',
	                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
	                        data: {
	                            action: 'mrkv_checkbox_connect_ajax',
	                            _wpnonce: '<?php echo wp_create_nonce('ppo_connect'); ?>'
	                        },
	                        dataType: 'json',
	                        beforeSend: function() {
	                            $('.ppo_connect-group img#ppo_process').show();
	                        },
	                        error: function(response) {
	                            $('.ppo_connect-group img#ppo_process').hide();

	                            alert( _e('Виникла помилка. Спробуйте ще раз.', 'checkbox') );
	                        },
	                        success: function(response) {
	                            $('.ppo_connect-group img#ppo_process').hide();

	                            if (response.success) {
	                                let shift_id = response.data.shift_id;
	                                let status = response.data.status;
	                                $('#ppo_status').html(status);
	                                $('#ppo_shift_id').val(shift_id);
	                                $('.ppo_connect-group').hide();
	                                $('.ppo_disconnect-group').css('display', 'inline-flex');
	                            } else {
	                                alert(response.data.message);
	                            }
	                        }
	                    });

	                });

	                $('#ppo_button_disconnect').on('click', function() {

	                    $.ajax({
	                        type: 'post',
	                        url: ajaxurl,
	                        data: {
	                            action: 'mrkv_checkbox_disconnect',
	                            _wpnonce: '<?php echo wp_create_nonce('ppo_disconnect'); ?>'
	                        },
	                        dataType: 'json',
	                        beforeSend: function() {
	                            $('.ppo_disconnect-group img#ppo_process').show();
	                        },
	                        error: function() {
	                            $('.ppo_disconnect-group img#ppo_process').hide();

	                            alert( esc_html_e('Виникла помилка. Спробуйте ще раз.', 'checkbox') );
	                        },
	                        success: function(response) {
	                            $('.ppo_disconnect-group img#ppo_process').hide();

	                            if (response.success) {
	                                let status = response.data.status;
	                                $('#ppo_status').html(status);
	                                $('.ppo_disconnect-group').hide();
	                                $('.ppo_connect-group').css('display', 'inline-flex');
	                            } else {
	                                alert(response.data.message);
	                            }
	                        }
	                    });

	                });

	                let timer = setInterval(() => checkStatus(), 5000);

	                function checkStatus() {
	                    let shift_id = $('#ppo_shift_id').val();
	                    if (shift_id) {
	                        let request = $.post(
	                            ajaxurl, {
	                                action: 'mrkv_checkbox_check_connection',
	                                shift_id: shift_id,
	                                _wpnonce: '<?php echo wp_create_nonce('ppo_checkconnect'); ?>'
	                            }
	                        );
	                    }
	                }
	            });
	        </script>

	        <?php
	    }
	}
}
?>