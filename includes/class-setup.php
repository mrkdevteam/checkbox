<?php 
# Check if class exist
if (!class_exists('MRKV_CHECKBOX_SETUP'))
{
	/**
	 * Class for setup plugin settings
	 */
	class MRKV_CHECKBOX_SETUP
	{
		/**
		 * @var string Path to plugin
		 * */
		private $file_name;

		/**
		 * Constructor for check plugin
		 * @var string Directory plugin path
		 * */
		function __construct($file_name)
		{
			# Save filename
			$this->file_name = $file_name;
			
			# Register settings
			add_action('admin_init', array($this, 'mrkv_checkbox_register_mysettings'));

			# Register page settings
			add_action('admin_menu', array($this, 'mrkv_checkbox_register_plugin_page_in_name'));

			add_action( 'wp_ajax_checkbox_clean_log', array($this, 'checkbox_clean_log_func') );
			add_action( 'wp_ajax_nopriv_checkbox_clean_log', array($this, 'checkbox_clean_log_func') );

			add_action('admin_enqueue_scripts', array($this, 'mrkv_checkbox_styles_and_scripts'));
		}

		/**
		 * Register plugin options
		 * 
		 * */
	    public function mrkv_checkbox_register_mysettings()
	    {
	    	# List of plugin options
	        $options = array(
	            'ppo_login',
	            'ppo_password',
	            'ppo_cashier_name',
	            'ppo_cashier_surname',
	            'ppo_cashbox_key',
	            'ppo_tax_code',
	            'ppo_receipt_footer',
	            'ppo_autocreate',
	            'ppo_rules_active',
	            'ppo_autocreate_receipt_order_statuses',
	            'ppo_autocreate_payment_order_statuses',
	            'ppo_payment_type',
	            'ppo_connected',
	            'ppo_autoopen_shift',
	            'ppo_sign_method',
	            'ppo_skip_receipt_creation',
	            'ppo_is_dev_mode',
	            'ppo_logger',
	            'ppo_cashbox_edrpou',
	            'ppo_receipt_coupon_text',
	            'ppo_zero_product_exclude',
	            'ppo_payment_type_label',
	            'ppo_payment_type_checkbox'
	        );

	        # Loop of option
	        foreach ($options as $option) 
	        {
	        	# Register option
	            register_setting('ppo-settings-group', $option);
	        }
	    }

	    /**
	     * Register plugin page in admin menu
	     */
	    public function mrkv_checkbox_register_plugin_page_in_name()
	    {
	    	# Add menu to WP
	        add_menu_page(__('Налаштування пРРО Checkbox', 'checkbox'), __('Checkbox ПРРО', 'checkbox'), 'manage_woocommerce', 'checkbox_settings', array($this, 'mrkv_checkbox_show_plugin_admin_page'), plugin_dir_url(__FILE__) . '../assets/img/checkbox-icon-setting.svg');
	    }

	    /** Plugin admin page content */
	    public function mrkv_checkbox_show_plugin_admin_page()
	    {	
	    	$activation = new MRKV_ACTIVATION_DEACTIVATION(); 
	        $activation->mrkv_checkbox_send_request( 'updated' );
	        $activation->mrkv_checkbox_send_request_edrpou( 'updated' );
	        ?>
	        <style>
	            .table_input {
	                width: 100%
	            }

	            .table_textarea {
	                width: 100%;
	            }

	            span.tooltip {
	                background-image: url('<?php echo plugin_dir_url($this->file_name) ?>assets/img/tooltip-icon.svg');
	            }

	            .table_storagesList {
	                width: 200px
	            }

	            .gateway-settings__wrapper {
	                width: 100%;
	                height: 100%;
	                overflow-x: auto;
	            }

	            table.gateway-settings {
	                width: 100%;
	                border-collapse: collapse;
	            }

	            table.gateway-settings thead {
	                white-space: nowrap;
	            }

	            table.gateway-settings th {
	                background-color: #bbb;
	            }

	            table.gateway-settings th,
	            table.gateway-settings td {
	                border: 1px solid #555;
	                text-align: center;
	                padding-right: 30px;
	            }

	            table.gateway-settings .gateway-title {
	                max-width: 174px;
	            }

	            table.gateway-settings .gateway-title p {
	                text-align: left;
	                /*text-overflow: ellipsis;
	                white-space: nowrap;
	                overflow: hidden;*/
	                width: 100%;
	            }

	            table.gateway-settings tbody td label:not(:last-child) {
	                margin-right: 7px;
	            }
	            .wrap-checkbox-setting{
	                background: #fff;
	                padding: 20px 40px;
	                margin-top: 20px;
	                overflow: hidden;
	            }
	            .checkbox-setting-row{
	                display: flex;
	                align-items: flex-start;
	                flex-wrap: wrap;
	                justify-content: space-between;
	            }
	            .checkbox-setting-col{
	                width: calc(50% - 40px);
	            }
	            .wrap-checkbox-setting > h2{
	                display: flex;
	                align-items: center;
	            }
	            .wrap-checkbox-setting > h2 img{
	                width: 14px;
	                margin-left: 10px;
	                background: #000;
	                padding: 4px;
	                border-radius: 100%;
	            }
	            .checkbox-setting-data-title{
	                font-weight: 600;
	                display: flex;
	                align-items: center;
	            }
	            .checkbox-setting-data input[type="checkbox"]{
	                display: none;
	            }
	            .checkbox-setting-data-meta{
	                display: inline-block;
	                width: fit-content;
	                padding-right: 15px;
	            }
	            .checkbox-setting-data-meta input{
	                display: none;
	            }
	            .checkbox-setting-data-meta label{
	                position: relative;
	                display: flex;
	                align-items: center;
	            }
	            .checkbox-setting-data-meta label:before{
	                content: '';
	                position: relative;
	                display: inline-block;
	                vertical-align: middle;
	                height: 20px;
	                width: 20px;
	                border-color: #000;
	                border-style: solid;
	                border-width: 2px;
	                box-sizing: border-box;
	                border-radius: 50%;
	                margin-right: 10px;
	                -webkit-transition: all 0.2s;
	                transition: all 0.2s;
	                opacity: .6;
	            }
	            .checkbox-setting-data-meta input:checked + label:before{
	                opacity: 1;
	            }
	            .checkbox-setting-data-meta input:checked + label:after{
	                content: '';
	                width: 10px;
	                height: 10px;
	                background: #000;
	                border-radius: 50%;
	                position: absolute;
	                top: 5px;
	                bottom: 0;
	                left: 5px;
	                right: 0;
	                margin: 0;
	                opacity: 1;
	                -webkit-transition: all 0.2s;
	                transition: all 0.2s;
	            }
	            .checkbox-setting-data:has(input[type="radio"]){
	                margin-bottom: 18px;
	            }
	            .mrkv_table-payment__body__checkbox__input {
	                position: relative;
	                width: 50px;
	                height: 25px;
	                display: inline-block;
	                margin-right: 5px;
	                min-width: 28px;
	            }
	            .mrkv_checkbox_slider {
	                position: absolute;
	                cursor: pointer;
	                top: 0;
	                left: 0;
	                right: 0;
	                bottom: 0;
	                background-color: #ccc;
	                -webkit-transition: .4s;
	                transition: .4s;
	                border-radius: 34px;
	            }
	            .mrkv_checkbox_slider:before{
	                position: absolute;
	                content: "";
	                height: 21px;
	                width: 21px;
	                left: 2px;
	                bottom: 2px;
	                background-color: white;
	                -webkit-transition: .4s;
	                transition: .4s;
	                border-radius: 50%;
	            }
	            input[type="checkbox"]:checked + label .mrkv_checkbox_slider:before{
	                -webkit-transform: translateX(24px);
	                -ms-transform: translateX(24px);
	                transform: translateX(24px);
	            }
	            input[type="checkbox"]:checked + label .mrkv_checkbox_slider{
	                background-color: #8f257d;
	            }
	            .checkbox-setting-data input[type="checkbox"]{
	                display: none;
	            }
	            .checkbox-setting-data .chosen-container{
	                width: 100% !important;
	            }
	            .checkbox-setting-data .chosen-choices{
	                box-shadow: unset;
	                border-radius: 4px;
	                border: 1px solid #8c8f94 !important;
	                box-shadow: unset !important;
	                background-image: unset;
	            }
	            .checkbox-setting-col input[type="submit"]{
	                background-color: #8f257d;
	                border-radius: 12px;
	                height: 40px;
	                font-size: 14px;
	                line-height: 20px;
	                display: flex;
	                align-items: center;
	                justify-content: center;
	                min-width: 152px;
	                color: #fff;
	                border: unset;
	                font-weight: 500;
	            }
	            .checkbox-setting-col input[type="submit"]:hover{
	                background-color: #8f257dab;
	            }
	            .checkbox-setting-col .gateway-settings{
	                border: unset;
	            }
	            .checkbox-setting-col .gateway-settings th,
	            .checkbox-setting-col .gateway-settings td{
	                border: unset;
	                border-bottom: 1px solid #dcdcde;
	                text-align: left;
	            }
	            .checkbox-setting-col .gateway-settings th{
	                background-color: transparent;  
	                font-size: 13px;
	                margin-bottom: 0;
	                font-weight: 500;
	            }
	            .mt-40 {
	                margin-top: 40px;
	            }
	            .plugin-development span {
	                margin-bottom: 6px;
	                font-size: 16px;
	                display: block;
	                font-weight: 500;
	            }
	            .plugin-development img {
	                height: 27px;
	            }
	            /*.checkbox-setting-col-8{
	                width: calc(75% - 40px);
	            }
	            .checkbox-setting-col-4{
	                    width: calc(30% - 40px);
	            }*/
	            .checkbox-setting-col .gateway-settings .chosen-choices{
	                box-shadow: unset;
	                border-radius: 4px;
	                border: 1px solid #8c8f94 !important;
	                box-shadow: unset !important;
	                background-image: unset;
	            }
	            .checkbox-setting-col .gateway-settings input[type="radio"] + label:before{
	                content: '';
	                position: relative;
	                display: inline-block;
	                vertical-align: middle;
	                height: 20px;
	                width: 20px;
	                border-color: #000;
	                border-style: solid;
	                border-width: 2px;
	                box-sizing: border-box;
	                border-radius: 50%;
	                margin-right: 10px;
	                -webkit-transition: all 0.2s;
	                transition: all 0.2s;
	                opacity: .6;
	            }
	            .checkbox-setting-col .gateway-settings input[type="radio"]{
	                display: none;
	            }
	            .checkbox-setting-col .gateway-settings input[type="radio"] + label{
	                position: relative;
	                display: flex;
	                align-items: center;
	                margin-top: 5px;
	                margin-bottom: 5px;
	            }
	            .checkbox-setting-col .gateway-settings input[type="radio"]:checked + label:before{
	                opacity: 1;
	            }
	            .checkbox-setting-col .gateway-settings input[type="radio"]:checked + label:after{
	                content: '';
	                width: 10px;
	                height: 10px;
	                background: #000;
	                border-radius: 50%;
	                position: absolute;
	                top: 5px;
	                bottom: 0;
	                left: 5px;
	                right: 0;
	                margin: 0;
	                opacity: 1;
	                -webkit-transition: all 0.2s;
	                transition: all 0.2s;
	            }
	            .checkbox-log-pre{
	                margin-top: 20px;
	                border: 1px solid #dcdcde;
	                padding: 20px;
	                background: #fbfbfb;
	                height: 300px;
	                overflow-y: scroll;
	            }
	            .btn-call-clean-log{
	                cursor: pointer;
	                background-color: #00000029;
	                border-radius: 12px;
	                height: 40px;
	                font-size: 14px;
	                line-height: 20px;
	                display: flex;
	                align-items: center;
	                justify-content: center;
	                min-width: 152px;
	                color: #000;
	                border: unset;
	                font-weight: 500;
	                width: fit-content;
	            }
	            .btn-call-clean-log:hover{
	                opacity: .7;
	            }
	            .gateway-settings .gateway-checkbox-hide{
	            	display: none;
	            }
	            .gateway-settings > tbody > tr{
	            	opacity: .6;
	            }
	            .gateway-settings > tbody > tr:has(.gateway-checkbox-hide:checked){
	            	opacity: 1;
	            }
	            .gateway-settings .chosen-container{
	            	width: 150px !important;
	            }
	            .gateway-settings .mrkv_table-payment__body__checkbox__input{
            	    width: 25px;
    				height: 15px;
	            }
	            .gateway-settings .mrkv_checkbox_slider:before{
            	    height: 11px;
    				width: 11px;
	            }
	            .gateway-settings input[type="checkbox"]:checked + label .mrkv_checkbox_slider:before{
            	    -webkit-transform: translateX(13px);
				    -ms-transform: translateX(13px);
				    transform: translateX(13px);
	            }
	            table.gateway-settings td {
	            	padding-top: 15px;
	            	padding-bottom: 15px;
	            }
	            .checkbox-setting-col-12{
	            	width: 100%;
	            	margin-bottom: 30px;
	            }
	            @media(max-width: 1210px){
	            	.checkbox-setting-col{
            		    width: calc(50% - 15px);
	            	}
	            	.wrap-checkbox-setting{
	            		padding: 20px;
	            	}
	            }
	            @media(max-width:1130px){
	            	.checkbox-setting-col, .checkbox-setting-col-8, .checkbox-setting-col-4{
	                    width: 100%;
	                }
	            	.checkbox-setting-row{

	            	}
	            }
	            @media (max-width: 782px) {
	                /*table.gateway-settings th, table.gateway-settings td {
	                    display: block;
	                    justify-content: center;
	                    align-items: center;
	                    width: auto;
	                    height: 50px;
	                    white-space: normal;
	                }*/
	                table.gateway-settings tbody tr {
	                    white-space: nowrap;
	                }
	                div.gateway-settings__wrapper {
	                    overflow-x: auto;
	                }
	                .checkbox-setting-col, .checkbox-setting-col-8, .checkbox-setting-col-4{
	                    width: 100%;
	                }
	                .wrap-checkbox-setting{
	                    padding: 20px 15px; 
	                }
	                table.gateway-settings .gateway-title{
	                	max-width: 100%;
	                }
	            }

	            @media (max-width: 700px) {
	                div.gateway-settings__wrapper {
	                    overflow-x: auto;
	                    width: 100%;
	                }
	            }
	            @media(max-width:590px){
	            	.checkbox-setting-col .gateway-settings th{
	            		display: none;
	            	}
	            	table.gateway-settings td{
	            		display: flex;
	                    justify-content: flex-start;
	                    align-items: center;
	                    width: auto;
	                    white-space: normal;
	            	}
	            	table.gateway-settings select{
	            		width: 100%;
	            		max-width: 100%;
	            	}
	            	table.gateway-settings .select-order-statuses{
	            		width: 100%;
	            	}
	            	.gateway-settings .mrkv_table-payment__body__checkbox__input{
            		    width: 50px;
    					height: 25px;
	            	}
	            	.gateway-settings input[type="checkbox"]:checked + label .mrkv_checkbox_slider:before{
	            		-webkit-transform: translateX(24px);
					    -ms-transform: translateX(24px);
					    transform: translateX(24px);
	            	}
	            	.gateway-settings .mrkv_checkbox_slider:before{
            		    height: 21px;
    					width: 21px;				
	            	}
	            }
	        </style>
	        <div class="wrap wrap-checkbox-setting">
	            <h2>
	                <?php echo get_admin_page_title(); ?>
	                <img src="<?php echo plugin_dir_url($this->file_name) ?>assets/img/checkbox-icon-setting.jpg" alt="Checkbox">
	                </h2>
	                <hr>

	            <?php settings_errors(); ?>

	            <form method="post" action="options.php">
	                <?php settings_fields('ppo-settings-group'); ?>

	                <div class="checkbox-setting-row">
	                    <div class="checkbox-setting-col">
	                        <h2><?php esc_html_e('Основна інформація про касира', 'checkbox'); ?></h2>
	                        <hr>
	                        <div class="checkbox-setting-data">
	                            <p class="checkbox-setting-data-title"><?php esc_html_e('Логін касира', 'checkbox'); ?></p>
	                            <input class="table_input" type="text" name="ppo_login" value="<?php echo esc_html(get_option('ppo_login')); ?>" required />
	                        </div>
	                        <div class="checkbox-setting-data">
	                            <p class="checkbox-setting-data-title"><?php esc_html_e('Особистий пароль касира', 'checkbox'); ?></p>
	                            <input class="table_input" type="password" name="ppo_password" value="<?php echo esc_html(get_option('ppo_password')); ?>" required />
	                        </div>
	                        <div class="checkbox-setting-data">
	                            <p class="checkbox-setting-data-title"><?php esc_html_e('Ім\'я касира', 'checkbox'); ?></p>
	                            <input class="table_input" type="text" name="ppo_cashier_name" value="<?php echo esc_html(get_option('ppo_cashier_name')); ?>" required />
	                        </div>
	                        <div class="checkbox-setting-data">
	                            <p class="checkbox-setting-data-title"><?php esc_html_e('Прізвище касира', 'checkbox'); ?></p>
	                            <input class="table_input" type="text" name="ppo_cashier_surname" value="<?php echo esc_html(get_option('ppo_cashier_surname')); ?>" required />
	                        </div>
	                    </div>
	                    <div class="checkbox-setting-col">
	                        <h2><?php esc_html_e('Налаштування каси', 'checkbox'); ?></h2>
	                        <hr>
	                    <div class="checkbox-setting-data">
	                        <p class="checkbox-setting-data-title"><?php esc_html_e('Ключ каси', 'checkbox'); ?></p>
	                        <input class="table_input" type="text" name="ppo_cashbox_key" value="<?php echo esc_html(get_option('ppo_cashbox_key')); ?>" required />
	                    </div>
	                    <div class="checkbox-setting-data">
	                        <p class="checkbox-setting-data-title"><?php esc_html_e('ЄДРПОУ', 'checkbox'); ?></p>
	                        <input class="table_input" type="text" name="ppo_cashbox_edrpou" value="<?php echo esc_html(get_option('ppo_cashbox_edrpou')); ?>" required />
	                    </div>
	                        <div class="checkbox-setting-data">
	                            <p class="checkbox-setting-data-title code-tax-checkbox"><?php esc_html_e('Код податку', 'checkbox'); ?> <span class="tooltip" aria-label="<?php echo esc_html('Код податку (літерний або цифровий) знайдете на сторінці Податкові ставки в вашому кабінеті Чекбокс тут: https://my.checkbox.ua/dashboard/taxrates', 'checkbox'); ?>" data-microtip-position="right" role="tooltip"></span></p>
	                            <input class="table_input" type="text" name="ppo_tax_code" value="<?php echo esc_html(get_option('ppo_tax_code')); ?>" required />
	                        </div>
	                    <div class="checkbox-setting-data">
	                        <p class="checkbox-setting-data-title"><?php esc_html_e('Спосіб підпису', 'checkbox'); ?> <span class="tooltip" aria-label="<?php echo esc_html('Доступні два механізми підпису чеків: Checkbox Підпис — утиліта, що встановлюється на будь-якому комп’ютері з доступом до Інтернету, і HSM, або Checkbox Cloud, — сертифікований хмарний сервіс для генерації та зберігання ключів DepositSign, у разі вибору якого необхідність встановлення будь-якого ПЗ для роботи з ЕЦП відсутня.', 'checkbox'); ?>" data-microtip-position="right" role="tooltip"></span></p>
	                         <?php
	                            $ppo_sign_method = get_option('ppo_sign_method');
	                        ?>
	                        <div class="checkbox-setting-data-meta">
	                            <input class="table_input" type="radio" name="ppo_sign_method" id="ppo_sign_method_cloud" value="cloud" checked 
	                             />
	                            <label for="ppo_sign_method_cloud"><?php esc_html_e('Checkbox Cloud', 'checkbox'); ?></label>
	                        </div>
	                        <div class="checkbox-setting-data-meta checkbox-data-disabled">
	                            <input class="table_input" disabled type="radio" name="ppo_sign_method" id="ppo_sign_method_soft" value="soft"
	                             />
	                            <label for="ppo_sign_method_soft"><span><?php esc_html_e('Checkbox Підпис', 'checkbox'); ?></span><span class="tooltip" aria-label="<?php echo esc_html('з 1.01.24 плагін підтримує лише хмарний підпис', 'checkbox'); ?>" data-microtip-position="right" role="tooltip"></span></label>
	                        </div>
	                    </div>
	                    <div class="checkbox-setting-data">
	                        <p class="checkbox-setting-data-title"><?php esc_html_e('Відкривати зміну при створенні чека', 'checkbox'); ?> <span class="tooltip" aria-label="<?php echo esc_html('Зміна відкриватиметься автоматично при створенні чека.', 'checkbox'); ?>" data-microtip-position="right" role="tooltip"></span></p>
	                        <input class="table_input" type="checkbox" name="ppo_autoopen_shift" id="ppo_autoopen_shift" value="1" <?php checked(get_option('ppo_autoopen_shift'), 1); ?> />
	                        <label for="ppo_autoopen_shift">
	                            <div class="mrkv_table-payment__body__checkbox__input">
	                                <span class="mrkv_checkbox_slider"></span>
	                            </div>
	                        </label>
	                    </div>
	                </div>
	            </div>
	            <div class="checkbox-setting-row">
	            	<div class="checkbox-setting-col checkbox-setting-col-12">
	            		<h2><?php esc_html_e('Налаштування автоматичного створення чеків', 'checkbox'); ?></h2>
	                    <hr>
	                    <?php update_option( 'ppo_autoopen_shift', 1 ); ?>
	                    <div class="checkbox-setting-data" style="display: none;">
	                        <p class="checkbox-setting-data-title">
	                            <?php esc_html_e("Автоматично створювати чеки", 'checkbox'); ?> <span class="tooltip" aria-label="<?php echo esc_html('Чек створюватиметься автоматично при зміні статусу замовлення. При ввімкненому стані в табличці "Налаштування способів оплати" з\'явиться колонка "Ігнорувати чек?", в якій ви можете для кожного способу оплати дозволити або заборонити автоматичне створення чеку при зміні статусу.', 'checkbox'); ?>" data-microtip-position="right" role="tooltip"></span>
	                        </p>
	                        <input class="table_input" type="checkbox" name="ppo_autocreate" id="ppo_autocreate" value="1" <?php checked(get_option('ppo_autocreate'), 1); ?> />
	                        <label for="ppo_autocreate">
	                            <div class="mrkv_table-payment__body__checkbox__input">
	                                <span class="mrkv_checkbox_slider"></span>
	                            </div>
	                        </label>
	                    </div>
	                    <div class="order-statuses" style="<?php echo ( 1 == get_option('ppo_autocreate') ) ? '' : 'display: none;'; ?>">
	                        <div class="checkbox-setting-data">
	                        </div>
	                    </div>
	                    <div class="checkbox-setting-data">
                            <p class="checkbox-setting-data-title">
                                <?php esc_html_e('Правила автоматичного формування чеків', 'checkbox'); ?> <span class="tooltip" aria-label="<?php echo esc_html('Визначення типу для кожного способу оплати необхідне для створення чека.', 'checkbox'); ?>" data-microtip-position="right" role="tooltip"></span>
                            </p>
                        </div>
                        <?php
	                            $enabled_gateways = array_filter(WC()->payment_gateways->payment_gateways(), function ($gateway) {
	                                return 'yes' === $gateway->enabled;
	                            });

	                            update_option('ppo_autocreate', 1);

	                            $ppo_autocreate = get_option('ppo_autocreate');
	                            $ppo_autocreate_payment_order_statuses = get_option('ppo_autocreate_payment_order_statuses');
	                            $ppo_payment_type          = get_option('ppo_payment_type');
	                            $ppo_skip_receipt_creation = get_option('ppo_skip_receipt_creation');
	                            $ppo_rules_active = get_option('ppo_rules_active');
	                            $ppo_payment_type_label = get_option('ppo_payment_type_label');
	                            $ppo_payment_type_checkbox = get_option('ppo_payment_type_checkbox');

	                            ?>
	                            <div class="gateway-settings__wrapper">
	                                <table class="gateway-settings">
	                                    <thead>
	                                        <tr>
	                                            <th><?php esc_html_e('Спосіб оплати', 'checkbox'); ?></th>
	                                            <th><?php esc_html_e('Засіб оплати', 'checkbox'); ?></th>
	                                            <th><?php esc_html_e('Label(Назва)', 'checkbox'); ?></th>
	                                            <th><?php esc_html_e('Форма оплати', 'checkbox'); ?></th>
	                                            <th class="select-order-statuses" style="<?php echo (1 !== (int) $ppo_autocreate) ? 'display:none;' : ''; ?>"><?php esc_html_e('Статуси замовлення', 'checkbox'); ?></th>
	                                        </tr>
	                                    </thead>
	                                    <tbody>
	                                        <?php
	                                        foreach ($enabled_gateways as $id => $gateway) :
	                                            ?>
	                                            <tr>
	                                                <td class="gateway-title" title="<?php echo esc_html($gateway->get_title()); ?>">
	                                                    <p>
	                                                    	<?php 
	                                                    	if (isset($ppo_skip_receipt_creation[$id])) {
	                                                    		if($ppo_skip_receipt_creation[$id] == 'no'){
	                                                    			if($ppo_rules_active){
	                                                    				$ppo_rules_active[$id] = '1';
	                                                    			}
	                                                    		}
	                                                    		else{
	                                                    			if($ppo_rules_active){
	                                                    				unset($ppo_rules_active[$id]);	
	                                                    			}
	                                                    		}
	                                                    		unset($ppo_skip_receipt_creation[$id]);
	                                                    	}
	                                                    	?>
	                                                    	<span class="input-gateway-line">
	                                                    		<input class="table_input gateway-checkbox-hide" type="checkbox" name="ppo_rules_active[<?php echo esc_html($id); ?>]" id="ppo_rules_active[<?php echo esc_html($id); ?>]" value="1" <?php
			                                                    if(isset($ppo_rules_active[$id]) && $ppo_rules_active[$id] == '1'){
																	echo esc_html('checked');
																} 
			                                                    ?> />
										                        <label for="ppo_rules_active[<?php echo esc_html($id); ?>]">
										                            <span class="mrkv_table-payment__body__checkbox__input">
										                                <span class="mrkv_checkbox_slider"></span>
										                            </span>
										                        </label>
	                                                    	</span>		
	                                                    	<?php echo esc_html($gateway->get_title()); ?>
	                                                    </p>
	                                                </td>
	                                                 <td class="gateway-type-checkbox">
	                                                	<?php
	                                                		$current_ppo_payment_type_checkbox = '';
	                                                		if(isset($ppo_payment_type_checkbox[$id]['label']))
	                                                		{
	                                                			$current_ppo_payment_type_checkbox = $ppo_payment_type_checkbox[$id]['label'];
	                                                		}
	                                                		else{
	                                                			if($id == 'cod')
	                                                			{
	                                                				$current_ppo_payment_type_checkbox = 'Готівка';
	                                                			}
	                                                			else
	                                                			{
                                                					$current_ppo_payment_type_checkbox = 'Електронний платіжний засіб';
	                                                			}
	                                                		}
	                                                		$payment_label_active = 'yes';
	                                                	?>
	                                                	<select name="ppo_payment_type_checkbox[<?php echo esc_html($id); ?>][label]" class="ppo_payment_type_checkbox" id="ppo_payment_type_checkbox[<?php echo esc_html($id); ?>][label]">
	                                                		<?php 
	                                                			foreach(CHECKBOX_PAYMENT_LABELS as $key_payment_form => $val_payment_form)
	                                                			{
	                                                				if(esc_html($id) == 'morkva-monopay')
	                                                				{
	                                                					$payment_label_active = 'no';
	                                                					?>
		                                                					<option selected data-label="no" value="Платіж plata by mono">Morkva Plata by Mono еквайринг</option>
		                                                				<?php
		                                                				break;
	                                                				}
	                                                				if(esc_html($id) == 'morkva-liqpay')
	                                                				{
	                                                					$payment_label_active = 'no';
	                                                					?>
		                                                					<option selected data-label="no" value="Платіж LiqPay">Morkva Liqpay еквайринг</option>
		                                                				<?php
		                                                				break;
	                                                				}
	                                                				if(esc_html($id) == 'morkva-monopay-prepay')
	                                                				{
	                                                					$payment_label_active = 'no';
	                                                					?>
		                                                					<option selected data-label="no" value="Післяплата">Morkva Plata by Mono Післяплата</option>
		                                                				<?php
		                                                				break;
	                                                				}
	                                                				if(esc_html($id) == 'morkva-liqpay-prepay')
	                                                				{
	                                                					$payment_label_active = 'no';
	                                                					?>
		                                                					<option selected data-label="no" value="Післяплата">Morkva Liqpay Післяплата</option>
		                                                				<?php
		                                                				break;
	                                                				}
	                                                				?>
	                                                					<option <?php if($current_ppo_payment_type_checkbox == $key_payment_form){ echo 'selected'; } ?> data-label="<?php echo $key_payment_form; ?>" value="<?php echo $key_payment_form; ?>"><?php echo $key_payment_form; ?></option>
	                                                				<?php
	                                                			}
	                                                		?>
	                                                	</select>
	                                                </td>
	                                                <td class="gateway-label"> 
	                                                	<input type="text" class="gateway-label-read" name="ppo_payment_type_label[<?php echo esc_html($id); ?>]" value="<?php
	                                                	if(isset($ppo_payment_type_label[$id])){
	                                                		echo $ppo_payment_type_label[$id];
	                                                	}
	                                                	  ?>">
	                                                </td>
	                                                <td>
	                                                    <input type="radio" name="ppo_payment_type[<?php echo esc_html($id); ?>]" id="ppo_payment_type_cash[<?php echo esc_html($id); ?>]" <?php
	                                                    if (isset($ppo_payment_type[$id])) {
	                                                        checked($ppo_payment_type[$id], 'cash');
	                                                    }
	                                                    ?> value="cash">
	                                                    <label for="ppo_payment_type_cash[<?php echo esc_html($id); ?>]">CASH</label>

	                                                    <input type="radio" name="ppo_payment_type[<?php echo esc_html($id); ?>]" id="ppo_payment_type_cashless[<?php echo esc_html($id); ?>]" <?php
	                                                    if (isset($ppo_payment_type[$id])) {
	                                                        checked($ppo_payment_type[$id], 'cashless');
	                                                    }
	                                                    ?> value="cashless">
	                                                    <label for="ppo_payment_type_cashless[<?php echo esc_html($id); ?>]">CASHLESS</label>
	                                                </td>
	                                                <td class="select-order-statuses" style="<?php echo (1 !== (int) $ppo_autocreate) ? 'display:none;' : ''; ?>">
	                                                    <select class="chosen order-statuses" name="ppo_autocreate_payment_order_statuses[<?php echo esc_html($id); ?>][]" data-placeholder="<?php _e('Виберіть статуси замовлення', 'checkbox') ?>" multiple>
	                                                        <?php
	                                                        $all_order_statuses = wc_get_order_statuses();

	                                                        if (! empty($all_order_statuses)) :
	                                                            foreach ($all_order_statuses as $k => $v) :
	                                                                $k = str_replace('wc-', '', $k);
	                                                                ?>
	                                                            <option value="<?php echo $k; ?>" <?php echo ( isset($ppo_autocreate_payment_order_statuses[$id]) && in_array($k, $ppo_autocreate_payment_order_statuses[$id]) ) ? 'selected' : ''; ?>><?php echo $v; ?></option>
	                                                                <?php
	                                                            endforeach;
	                                                        else :
	                                                            printf('<option value="">%s</option>', __('None'));
	                                                        endif;
	                                                        ?>
	                                                    </select>
	                                                </td>
	                                            </tr>
	                                            <?php
	                                        endforeach;

	                                        update_option('ppo_skip_receipt_creation', $ppo_skip_receipt_creation);
	                                        update_option('ppo_rules_active', $ppo_rules_active);
	                                        ?>
	                                    </tbody>
	                                </table>
	                            </div>
            		</div>
	            </div>
	            <div class="checkbox-setting-row">
	                <div class="checkbox-setting-col checkbox-setting-col-4">
	                    <h2><?php esc_html_e('Додаткові налаштування', 'checkbox'); ?></h2>
	                    <hr>
	                    <div class="checkbox-setting-data">
	                        <p class="checkbox-setting-data-title">
	                            <?php esc_html_e("Службова інформація", 'checkbox'); ?> <span class="tooltip" aria-label="<?php echo esc_html('Значення данного поля буде відображатися в нижній частині електронного чеку.', 'checkbox'); ?>" data-microtip-position="right" role="tooltip"></span>
	                        </p>
	                        <textarea class="table_textarea" type="text" name="ppo_receipt_footer"/><?php echo get_option('ppo_receipt_footer'); ?></textarea>
	                        <br>
	                        <span style="color: grey;">Доступні шорткоди: [website_title], [order_id], [order_created_date], [order_paid_date].</span>
	                    </div>
	                    <div class="checkbox-setting-data">
	                        <p class="checkbox-setting-data-title">
	                            <?php esc_html_e("Тестовий режим", 'checkbox'); ?>
	                        </p>
	                        <div class="input-show-link">
	                        	<input class="table_input" type="checkbox" name="ppo_is_dev_mode" id="ppo_is_dev_mode" value="1" <?php checked(get_option('ppo_is_dev_mode'), 1); ?> />
		                        <label for="ppo_is_dev_mode">
		                            <div class="mrkv_table-payment__body__checkbox__input">
		                                <span class="mrkv_checkbox_slider"></span>
		                            </div>
		                        </label>
		                        <span class="test-text-checkbox" style="<?php echo ( 1 == get_option('ppo_is_dev_mode') ) ? '' : 'display: none;'; ?>"><?php echo __('В тестовому режимі, обовʼязково замініть ключ каси, логін та пароль касира на тестові дані (Не впевнені що це? ', 'checkbox'); ?> <a href="https://morkva.co.ua/testovyj-rezhym-v-plagini-chekboks/" target="blanc"><?php echo __('Читайте ось цю інструкцію', 'checkbox'); ?></a>). <?php echo __('Після вимкнення тестового режиму - поверніть реальні дані для ключ каси, логін та пароль касира.', 'checkbox'); ?></span>
	                        </div>
	                    </div>
	                    <div class="checkbox-setting-data">
	                        <p class="checkbox-setting-data-title">
	                            <?php esc_html_e("Ввімкнути логування", 'checkbox'); ?> <span class="tooltip" aria-label="<?php echo esc_html('Використовуйте лише для логування помилок.', 'checkbox'); ?>" data-microtip-position="right" role="tooltip"></span>
	                        </p>
	                        <input class="table_input" type="checkbox" name="ppo_logger" id="ppo_logger" value="1" <?php checked(get_option('ppo_logger'), 1); ?> />
	                        <label for="ppo_logger">
	                            <div class="mrkv_table-payment__body__checkbox__input">
	                                <span class="mrkv_checkbox_slider"></span>
	                            </div>
	                        </label>
	                    </div>
	                    <div class="checkbox-setting-data">
	                        <p class="checkbox-setting-data-title">
	                            <?php esc_html_e("Лог", 'checkbox'); ?>
	                        </p>
	                        <?php 
	                            $log_file = file_get_contents(plugin_dir_url($this->file_name) . "logs/checkbox.log");
	                        ?>
	                        <pre class="checkbox-log-pre"><?php echo $log_file; ?></pre>
	                        <div class="btn-call-clean-log"><?php echo __('Очистити лог', 'checkbox'); ?></div>
	                    </div>
	                    <?php echo submit_button(__('Зберегти', 'checkbox')); ?>
	                </div>
	                <div class="checkbox-setting-col checkbox-setting-col-8">
	                    <h2><?php esc_html_e('Просунуті налаштування', 'checkbox'); ?></h2>
                        <hr>
                        <div class="checkbox-setting-data">
                            <p class="checkbox-setting-data-title"><?php esc_html_e('Налаштування купонів (По замовчуванню: Купон)', 'checkbox'); ?></p>
                            <input class="table_input" type="text" name="ppo_receipt_coupon_text" value="<?php echo esc_html(get_option('ppo_receipt_coupon_text')); ?>" required />
                        </div>
                        <div class="checkbox-setting-data">
	                        <p class="checkbox-setting-data-title">
	                            <?php esc_html_e("Не додавати товари з нульовою ціною", 'checkbox'); ?> <span class="tooltip" aria-label="<?php echo esc_html('Використовуйте, щоб не додавати товари з ціною 0 грн до чеку', 'checkbox'); ?>" data-microtip-position="right" role="tooltip"></span>
	                        </p>
	                        <input class="table_input" type="checkbox" name="ppo_zero_product_exclude" id="ppo_zero_product_exclude" value="1" <?php checked(get_option('ppo_zero_product_exclude'), 1); ?> />
	                        <label for="ppo_zero_product_exclude">
	                            <div class="mrkv_table-payment__body__checkbox__input">
	                                <span class="mrkv_checkbox_slider"></span>
	                            </div>
	                        </label>
	                    </div>
	                </div>
	            </div>
	            <div class="plugin-development mt-40">
	                <span>Веб студія</span>
	                <a href="https://morkva.co.ua/" target="_blank"><img src="<?php echo plugin_dir_url($this->file_name) ?>assets/img/morkva-logo.svg" alt="Morkva" title="Morkva"></a>
	            </div>
	            </form>
	        </div>
	        <script>
	            jQuery(function($){
	                jQuery('.btn-call-clean-log').click(function(){
	                    jQuery.ajax({
	                        url: '<?php echo admin_url( "admin-ajax.php" ) ?>',
	                        type: 'POST',
	                        data: 'action=checkbox_clean_log', 
	                        success: function( data ) {
	                            jQuery('.checkbox-log-pre').text('');
	                        }
	                    });
	                });
	            });
	        </script>
	        <?php
	    }

	    public function checkbox_clean_log_func(){
		    file_put_contents( plugin_dir_path($this->file_name) . "logs/checkbox.log", '');

		    die; 
		}

		public function mrkv_checkbox_styles_and_scripts($hook)
	    {
	        if ('toplevel_page_checkbox_settings' != $hook) {
	            return;
	        }
	        // custom style and script
	        wp_enqueue_style('checkbox', plugin_dir_url($this->file_name) . 'assets/css/checkbox.css', array(), CHECKBOX_VERSION);
	        wp_enqueue_script('checkbox', plugin_dir_url($this->file_name) . 'assets/js/checkbox.js', array('jquery'), CHECKBOX_VERSION, true);
	        // microtip
	        wp_enqueue_style('checkbox-microtip', plugin_dir_url($this->file_name) . 'assets/css/microtip.min.css');
	        // chosen
	        wp_enqueue_style('checkbox-chosen', plugin_dir_url($this->file_name) . 'assets/css/chosen.min.css', array(), '1.8.7');
	        wp_enqueue_script('checkbox-chosen', plugin_dir_url($this->file_name) . 'assets/js/chosen.jquery.min.js', array('jquery'), '1.8.7', true);
	    }
	}
}
?>