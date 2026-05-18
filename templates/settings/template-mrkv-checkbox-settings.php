<?php 
	if ( ! defined( 'ABSPATH' ) ) exit;
	$mrkv_checkbox_current_page = '/wp-admin/admin.php?page=mrkv_checkbox_settings';
?>
<div class="admin_mrkv_ua_shipping_page">
	<div class="admin_mrkv_ua_shipping_page__header">
		<div class="admin_mrkv_ua_shipping__header mrkv_block_rounded">
			<div class="admin_mrkv_ua_shipping__header__content">
				<a class="admin_mrkv_ua_shipping__header_img" href="<?php echo esc_url($mrkv_checkbox_current_page); ?>">
					<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/morkva-minilogo.svg'); ?>" alt="Checkbox" title="Checkbox">
				</a>
				<a class="active" href="<?php echo esc_url($mrkv_checkbox_current_page); ?>"><?php echo esc_html__('Settings', 'checkbox'); ?></a>
				<a class="admin_mrkv_ua_shipping_morkva-logo" href="https://morkva.co.ua/" target="blanc">
					<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/morkva-logo.svg'); ?>" alt="morkva" title="morkva">
				</a>
			</div>
		</div>
	</div>
	<div class="admin_mrkv_ua_shipping_page__inner">
		<div class="admin_mrkv_ua_shipping__block col-mrkv-10">
			<div class="admin_mrkv_ua_shipping__info">
				<?php settings_errors(); ?>
			</div>
		</div>
		<div class="admin_mrkv_ua_shipping__block col-mrkv-10">
			<div class="admin_mrkv_ua_shipping__tabs">
				<div class="admin_mrkv_ua_shipping__tabs_main mrkv_block_rounded">
					<h2>
						<?php echo esc_html__('Settings', 'checkbox'); ?>
						<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/checkbox-logo.svg'); ?>" alt="Checkbox" title="Checkbox">
					</h2>
					<div class="admin_mrkv_ua_shipping__tabs_main__inner">
						<?php
							$mrkv_checkbox_counter = 0;
							foreach($mrkv_checkbox_tabs as $mrkv_checkbox_id => $mrkv_checkbox_name)
							{
								?>
									<a href="#<?php echo esc_html($mrkv_checkbox_id); ?>-mrkv" data-tab="<?php echo esc_html($mrkv_checkbox_id); ?>" class="mrkv_up_ship_tab_btn <?php if($mrkv_checkbox_counter == 0){echo esc_html('active'); } ?>"><?php echo esc_html($mrkv_checkbox_name); ?></a>
								<?php

								++$mrkv_checkbox_counter;
							}
						?>
					</div>
				</div>
			</div>
		</div>
		<div class="admin_mrkv_ua_shipping__block col-mrkv-7">
			<div class="admin_mrkv_ua_shipping__settings">
				<form method="post" action="options.php">
					<?php settings_fields('mrkv-checkbox-settings-group'); ?>
					<div class="mrkv_block_rounded">
						<section id="cashiers_settings" class="mrkv_up_ship_shipping_tab_block active">
							<h2><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/settings-icon.svg'); ?>" alt="Cashier Settings" title="Cashier Settings"><?php echo esc_html__('Cashier Settings', 'checkbox'); ?></h2>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'cashiers_settings', 'row_1'); ?>
							<h3><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/connect.svg'); ?>" alt="Status" title="Status"><?php esc_html_e('Shift status', 'checkbox'); ?></h3>
							<p><?php esc_html_e('Check the status of the shifts and close or open them as needed ', 'checkbox'); ?></p>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'cashiers_settings', 'row_2'); ?>
							<div class="admin_ua_ship_morkva_settings_row mrkv-row-shifts">
								<div class="col-mrkv-5">
									<?php
										if(!empty($mrkv_checkbox_cashier_list))
										{
											?>
												<div class="admin_ua_ship_morkva_settings_line">
													<div class="mrkv_checkbox__shift__list">
														<?php
														$mrkv_checkbox_cashier_counter = 1;
															foreach($mrkv_checkbox_cashier_list as $mrkv_checkbox_cashier_id => $mrkv_checkbox_cashier)
															{
																?>
																	<div class="mrkv_checkbox__shift__line">
																		<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/pin.svg'); ?>" alt="Cashier Settings" title="Cashier Settings">
																		<div class="mrkv_checkbox__shift__number"><?php echo esc_attr((int) $mrkv_checkbox_cashier_counter); ?></div>
																		<div class="mrkv_checkbox__shift__name"><?php echo esc_attr($mrkv_checkbox_cashier['register_name'] ?? ''); ?></div>
																		<div class="mrkv_checkbox__shift__status <?php echo esc_attr($mrkv_checkbox_cashier['shift_status']); ?>" data-contraryopen="<?php echo esc_html__('Opened', 'checkbox'); ?>" data-contraryclose="<?php echo esc_html__('Closed', 'checkbox'); ?>">
																			<?php
																				echo esc_attr($mrkv_checkbox_cashier['shift_status'] == 'closed' ? esc_html__('Closed', 'checkbox') : esc_html__('Opened', 'checkbox'));
																			?>
																		</div>
																		<div class="mrkv_checkbox__shift__action">
																			<div class="mrkv_checkbox__change_shift_status">
																				<?php 
																					if($mrkv_checkbox_cashier['shift_status'] == 'closed')
																					{
																						?>
																						<div class="mrkv_checkbox__change_shift_status" data-status="open" data-cashbox="<?php echo esc_attr($mrkv_checkbox_cashier_id); ?>" data-contraryclose="<?php echo esc_html__('Close Shift', 'checkbox'); ?>" data-contraryopen="<?php echo esc_html__('Open Shift', 'checkbox'); ?>"><?php echo esc_html__('Open Shift', 'checkbox'); ?>
																							<div class="mrkv_ua_ship_create_invoice__loader"></div>
																						</div>
																						<?php
																					}
																					else
																					{
																						?>
																						<div class="mrkv_checkbox__change_shift_status" data-status="close" data-cashbox="<?php echo esc_attr($mrkv_checkbox_cashier_id); ?>" data-contraryopen="<?php echo esc_html__('Open Shift', 'checkbox'); ?>" data-contraryclose="<?php echo esc_html__('Close Shift', 'checkbox'); ?>"><?php echo esc_html__('Close Shift', 'checkbox'); ?>
																							<div class="mrkv_ua_ship_create_invoice__loader"></div>
																						</div>
																						<?php
																					}
																				?>
																			</div>
																		</div>
																	</div>
																<?php
																++$mrkv_checkbox_cashier_counter;
															}
														?>
													</div>
												</div>
											<?php
										}
										else
										{
											?>
											<p class="mrkv-ua-ship-description"><?php echo esc_html__('There are no cash registers. Add new cash register and cashier details to track shift statuses. ', 'checkbox'); ?></p>
											<?php
										}
									?>	
								</div>
								<div class="col-mrkv-5">
									
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'cashiers_settings', 'row_3'); ?>
							<h3><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/user-icon.svg'); ?>" alt="Status" title="Status"><?php esc_html_e('Cash register Settings', 'checkbox'); ?></h3>
							<p><?php esc_html_e('Add cash registers based on whose data the order receipts will be generated', 'checkbox'); ?></p>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'cashiers_settings', 'row_4'); ?>
							<div class="mrkv_checkbox__cashiers">
								<div class="mrkv_checkbox__cashier__block">
									<?php
										$mrkv_checkbox_data = isset($mrkv_checkbox_cashier_list['default']['shift_status']) ? $mrkv_checkbox_cashier_list['default']['shift_status'] : 'closed';
											echo wp_kses( $field_generator->get_input_hidden($mrkv_checkbox_settings_name . '[cashiers][default][shift_status]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_cashiers_default_shift_status'), $allowed_tags);

											$mrkv_checkbox_data = isset($mrkv_checkbox_cashier_list['default']['signin']) ? $mrkv_checkbox_cashier_list['default']['signin'] : '';
											echo wp_kses( $field_generator->get_input_hidden($mrkv_checkbox_settings_name . '[cashiers][default][signin]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_cashiers_default_signin'), $allowed_tags);
										?>
									<div class="mrkv_checkbox__cashier__block__number">
										<h3><span>1</span> <?php esc_html_e('Cash register', 'checkbox'); ?></h3>
									</div>
									<div class="mrkv_checkbox__cashier__block__data">
										<div class="admin_ua_ship_morkva_settings_row">
											<div class="col-mrkv-5"><h3><?php echo esc_html__('Cashier data', 'checkbox'); ?></h3><hr></div>
											<div class="col-mrkv-5"><h3><?php echo esc_html__('Cash register data', 'checkbox'); ?></h3><hr></div>
										</div>
										<div class="admin_ua_ship_morkva_settings_row">
											<div class="col-mrkv-5">
												<div class="admin_ua_ship_morkva_settings_line">
													<?php 
														$mrkv_checkbox_data = isset($mrkv_checkbox_cashier_list['default']['cashier_login']) ? $mrkv_checkbox_cashier_list['default']['cashier_login'] : '';
														$mrkv_checkbox_label = __('Cashier login', 'checkbox');
														$mrkv_checkbox_description = '';
														echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[cashiers][default][cashier_login]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_cashiers_default_cashier_login' , '', __('Enter the login...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
													?>
												</div>
											</div>
											<div class="col-mrkv-5">
												<div class="admin_ua_ship_morkva_settings_line">
													<?php 
														$mrkv_checkbox_data = isset($mrkv_checkbox_cashier_list['default']['register_name']) ? $mrkv_checkbox_cashier_list['default']['register_name'] : '';
														$mrkv_checkbox_label = __('Cash Register name (optional)', 'checkbox');
														$mrkv_checkbox_description = '';
														echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[cashiers][default][register_name]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_cashiers_default_register_name' , '', __('Enter the name...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
													?>
												</div>
											</div>
										</div>
										<div class="admin_ua_ship_morkva_settings_row">
											<div class="col-mrkv-5">
												<div class="admin_ua_ship_morkva_settings_line">
													<?php 
														$mrkv_checkbox_data = isset($mrkv_checkbox_cashier_list['default']['cashier_password']) ? $mrkv_checkbox_cashier_list['default']['cashier_password'] : '';
														$mrkv_checkbox_label = __('Cashier password', 'checkbox');
														$mrkv_checkbox_description = '';
														echo wp_kses($field_generator->get_input_password($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[cashiers][default][cashier_password]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_cashiers_default_cashier_password' , '', __('Enter the password...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
													?>
												</div>
											</div>
											<div class="col-mrkv-5">
												<div class="admin_ua_ship_morkva_settings_line">
													<?php 
														$mrkv_checkbox_data = isset($mrkv_checkbox_cashier_list['default']['register_key']) ? $mrkv_checkbox_cashier_list['default']['register_key'] : '';
														$mrkv_checkbox_label = __('Cash Register key', 'checkbox');
														$mrkv_checkbox_description = '';
														echo wp_kses($field_generator->get_input_password($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[cashiers][default][register_key]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_cashiers_default_register_key' , '', __('Enter the key...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
														?>
												</div>
											</div>
										</div>
										<div class="admin_ua_ship_morkva_settings_row">
											<div class="col-mrkv-5">
												<div class="admin_ua_ship_morkva_settings_line">
													<?php 
														$mrkv_checkbox_data = isset($mrkv_checkbox_cashier_list['default']['cashier_name']) ? $mrkv_checkbox_cashier_list['default']['cashier_name'] : '';
														$mrkv_checkbox_label = __('Cashier name', 'checkbox');
														$mrkv_checkbox_description = '';
														echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[cashiers][default][cashier_name]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_cashiers_default_cashier_name' , '', __('Enter the name...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
													?>
												</div>
											</div>
											<div class="col-mrkv-5">
												<div class="admin_ua_ship_morkva_settings_line">
													<?php 
														$mrkv_checkbox_data = isset($mrkv_checkbox_cashier_list['default']['register_edrpou']) ? $mrkv_checkbox_cashier_list['default']['register_edrpou'] : '';
														$mrkv_checkbox_label = __('EPRPOU', 'checkbox');
														$mrkv_checkbox_description = '';
														echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[cashiers][default][register_edrpou]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_cashiers_default_register_edrpou' , '', __('Enter the edrpou...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
														?>
												</div>
											</div>
										</div>
										<div class="admin_ua_ship_morkva_settings_row">
											<div class="col-mrkv-5">
												<div class="admin_ua_ship_morkva_settings_line">
													<?php 
														$mrkv_checkbox_data = isset($mrkv_checkbox_cashier_list['default']['cashier_lastname']) ? $mrkv_checkbox_cashier_list['default']['cashier_lastname'] : '';
														$mrkv_checkbox_label = __('Cashier lastname', 'checkbox');
														$mrkv_checkbox_description = '';
														echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[cashiers][default][cashier_lastname]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_cashiers_default_cashier_lastname' , '', __('Enter the lastname...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
													?>
												</div>
											</div>
											<div class="col-mrkv-5">
												<div class="admin_ua_ship_morkva_settings_line">
													<?php 
														$mrkv_checkbox_data = isset($mrkv_checkbox_cashier_list['default']['register_tax_code']) ? $mrkv_checkbox_cashier_list['default']['register_tax_code'] : '';
														$mrkv_checkbox_label = __('Tax code', 'checkbox');
														$mrkv_checkbox_description = __('You can find the tax code (letter or number) on the Tax Rates page in your Checkbox account here: https://my.checkbox.ua/dashboard/taxrates', 'checkbox');
														echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[cashiers][default][register_tax_code]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_cashiers_default_register_tax_code' , '', __('Enter the code...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
														?>
												</div>
											</div>
										</div>
										<?php do_action('mrkv_checkbox_settings_page_row', 'cashiers_settings', 'row_4_default'); ?>
									</div>
								</div>
								<p class="mrkv-ua-ship-only-pro"><?php echo esc_html__('Only in the Pro version', 'checkbox'); ?></p>
								<div class="mrkv_checkbox__add_new_cashier mrkv-checkbox-disabled">
									<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/circle-plus-icon.svg'); ?>" alt="">
										<?php echo esc_html__('Add new cash register', 'checkbox'); ?>
									<div class="mrkv_ua_ship_create_invoice__loader"></div>
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'cashiers_settings', 'row_5'); ?>
						</section>
						<section id="automation_settings" class="mrkv_up_ship_shipping_tab_block">
							<h2><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/automation-icon.svg'); ?>" alt="Automation Settings" title="Automation Settings"><?php echo esc_html__('Automation Settings', 'checkbox'); ?></h2>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'automation_settings', 'row_1'); ?>
							<div class="admin_ua_ship_morkva_settings_row">
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line">
										<?php
											$mrkv_checkbox_ppo_autoopen_shift = get_option('ppo_autoopen_shift');
											$mrkv_checkbox_data = isset($mrkv_checkbox_settings['automation']['open_shift']) ? $mrkv_checkbox_settings['automation']['open_shift'] : '';

											if(!$mrkv_checkbox_data && !is_array($mrkv_checkbox_settings) && isset($mrkv_checkbox_ppo_autoopen_shift))
											{
												$mrkv_checkbox_data = $mrkv_checkbox_ppo_autoopen_shift;
											}											

											echo wp_kses($field_generator->get_input_checkbox(__('Open a shift when creating a receipt', 'checkbox'), $mrkv_checkbox_settings_name . '[automation][open_shift]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_automation_open_shift'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-description"><?php echo esc_html__('The change will be opened automatically when the receipt is strated create.', 'checkbox'); ?></p>
									</div>
								</div>
								<div class="col-mrkv-5">
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'automation_settings', 'row_2'); ?>
							<h3><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/wallet.svg'); ?>" alt="Status" title="Status"><?php esc_html_e('Payment Settings', 'checkbox'); ?></h3>
							<p><?php esc_html_e('Configure the settings for each payment method separately ', 'checkbox'); ?></p>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'automation_settings', 'row_3'); ?>
							<?php
								$mrkv_checkbox_automation_payments = isset($mrkv_checkbox_settings['automation']['payments']) ? $mrkv_checkbox_settings['automation']['payments'] : '';
								$mrkv_checkbox_ppo_rules_active = get_option('ppo_rules_active');
								$mrkv_checkbox_ppo_payment_type_checkbox = get_option('ppo_payment_type_checkbox');
								$mrkv_checkbox_ppo_payment_type_label = get_option('ppo_payment_type_label');
								$mrkv_checkbox_ppo_payment_type_cash = get_option('ppo_payment_type_cash');
								$mrkv_checkbox_ppo_payment_type = get_option('ppo_payment_type');
								$mrkv_checkbox_ppo_autocreate_payment_order_statuses = get_option('ppo_autocreate_payment_order_statuses');
								foreach ($enabled_gateways as $mrkv_checkbox_gateway_id => $mrkv_checkbox_gateway_data)
								{
									$mrkv_checkbox_payment_label_active = 'yes';
									$mrkv_checkbox_labels_time = $mrkv_checkbox_labels;
									?>
									<div class="admin_ua_ship_morkva_settings_line">
										<?php 
											$mrkv_checkbox_data = isset($mrkv_checkbox_automation_payments[$mrkv_checkbox_gateway_id]['enabled']) ? $mrkv_checkbox_automation_payments[$mrkv_checkbox_gateway_id]['enabled'] : '';

											if(!$mrkv_checkbox_data && !is_array($mrkv_checkbox_settings) && isset($mrkv_checkbox_ppo_rules_active[$mrkv_checkbox_gateway_id]))
											{
												$mrkv_checkbox_data = $mrkv_checkbox_ppo_rules_active[$mrkv_checkbox_gateway_id];
											}

											echo wp_kses($field_generator->get_input_checkbox($mrkv_checkbox_gateway_data->get_title(), $mrkv_checkbox_settings_name . '[automation][payments][' . $mrkv_checkbox_gateway_id . '][enabled]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_automation_payments' . $mrkv_checkbox_gateway_id . 'enabled', ), $allowed_tags);
											?>
										<div class="admin_ua_ship_morkva_settings_line__inner">
											<div class="admin_ua_ship_morkva_settings_row">
												<div class="col-mrkv-5">
													<div class="admin_ua_ship_morkva_settings_line">
														<?php 
															$mrkv_checkbox_data = isset($mrkv_checkbox_automation_payments[$mrkv_checkbox_gateway_id]['label']) ? $mrkv_checkbox_automation_payments[$mrkv_checkbox_gateway_id]['label'] : '';
															$mrkv_checkbox_placeholders = __('Choose a label', 'checkbox');
															if(!$mrkv_checkbox_data && !is_array($mrkv_checkbox_settings) && isset($mrkv_checkbox_ppo_payment_type_checkbox[$mrkv_checkbox_gateway_id]['label']))
															{
																$mrkv_checkbox_data = $mrkv_checkbox_ppo_payment_type_checkbox[$mrkv_checkbox_gateway_id]['label'];
															}

															if(!$mrkv_checkbox_data)
															{
																if($mrkv_checkbox_gateway_id == 'cod')
	                                                			{
	                                                				$mrkv_checkbox_data = 'Готівка';
	                                                			}
	                                                			else
	                                                			{
                                                					$mrkv_checkbox_data = 'Електронний платіжний засіб';
	                                                			}
															}

															$mrkv_checkbox_special_labels = [
																'morkva-monopay'        => [__('Payment plata by mono', 'checkbox') => __('morkva Plata by Mono Acquiring', 'checkbox') . ' ' . __('(recommended)', 'checkbox')],
																'morkva-liqpay'         => [__('Payment LiqPay', 'checkbox') => __('morkva Liqpay Acquiring', 'checkbox') . ' ' . __('(recommended)', 'checkbox')],
																'morkva-monopay-prepay' => [__('Postpaid', 'checkbox') => __('morkva Plata by Mono Postpaid', 'checkbox') . ' ' . __('(recommended)', 'checkbox')],
																'morkva-liqpay-prepay'  => [__('Postpaid', 'checkbox') => __('morkva Liqpay Postpaid', 'checkbox') . ' ' . __('(recommended)', 'checkbox')],
															];

															if (isset($mrkv_checkbox_special_labels[$mrkv_checkbox_gateway_id])) {
																$new_element_key = array_key_first($mrkv_checkbox_special_labels[$mrkv_checkbox_gateway_id]);
																$new_element_val = array_first($mrkv_checkbox_special_labels[$mrkv_checkbox_gateway_id]);
																$mrkv_checkbox_labels_time = [$new_element_key => $new_element_val] + $mrkv_checkbox_labels_time;
															}

															$mrkv_checkbox_description = '';
															echo wp_kses($field_generator->get_select_simple(__('Checkbox Payment method', 'checkbox'), $mrkv_checkbox_settings_name . '[automation][payments][' . $mrkv_checkbox_gateway_id . '][label]', $mrkv_checkbox_labels_time, $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_automation_payments_' . $mrkv_checkbox_gateway_id . '_label' , $mrkv_checkbox_placeholders, $mrkv_checkbox_description), $allowed_tags);
														?>
													</div>
												</div>
												<div class="col-mrkv-5">
													<div class="admin_ua_ship_morkva_settings_line">
													<?php 
														$mrkv_checkbox_data = isset($mrkv_checkbox_automation_payments[$mrkv_checkbox_gateway_id]['custom_label']) ? $mrkv_checkbox_automation_payments[$mrkv_checkbox_gateway_id]['custom_label'] : '';

														if(!$mrkv_checkbox_data && !is_array($mrkv_checkbox_settings) && isset($mrkv_checkbox_ppo_payment_type_label[$mrkv_checkbox_gateway_id]))
														{
															$mrkv_checkbox_data = $mrkv_checkbox_ppo_payment_type_label[$mrkv_checkbox_gateway_id];
														}



														$mrkv_checkbox_label = __('Payment Label (Title)', 'checkbox');
														$mrkv_checkbox_description = '';
														echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[automation][payments][' . $mrkv_checkbox_gateway_id . '][custom_label]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_automation_payments_' . $mrkv_checkbox_gateway_id . '_label' , '', __('Enter the text...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
														?>
												</div>
												</div>
											</div>
											<div class="admin_ua_ship_morkva_settings_row">
												<div class="col-mrkv-3">
													<div class="admin_ua_ship_morkva_settings_line">
														<h4 style="margin-top: 0;"><?php esc_html_e('Payment Form', 'checkbox'); ?></h4>
														<div class="admin_ua_ship_morkva_settings_row">
															<?php
																$mrkv_checkbox_data = isset($mrkv_checkbox_automation_payments[$mrkv_checkbox_gateway_id]['form']) ? $mrkv_checkbox_automation_payments[$mrkv_checkbox_gateway_id]['form'] : '';

																if(!$mrkv_checkbox_data && !is_array($mrkv_checkbox_settings) && isset($mrkv_checkbox_ppo_payment_type_cash[$mrkv_checkbox_gateway_id]))
																{
																	$mrkv_checkbox_data = $mrkv_checkbox_ppo_payment_type_cash[$mrkv_checkbox_gateway_id];
																}

																echo wp_kses($field_generator->get_input_radio(__('CASH', 'checkbox'), $mrkv_checkbox_settings_name . '[automation][payments][' . $mrkv_checkbox_gateway_id . '][form]', 'CASH', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_automation_payments_' . $mrkv_checkbox_gateway_id . '_form_cash', 'CASH'), $allowed_tags);
																echo wp_kses($field_generator->get_input_radio(__('CASHLESS', 'checkbox'), $mrkv_checkbox_settings_name . '[automation][payments][' . $mrkv_checkbox_gateway_id . '][form]', 'CASHLESS', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_automation_payments_' . $mrkv_checkbox_gateway_id . '_form_cashless', 'CASH'), $allowed_tags);
															?>
														</div>
													</div>
												</div>
												<div class="col-mrkv-3">
													<div class="admin_ua_ship_morkva_settings_line">
														<?php 
															$mrkv_checkbox_data = isset($mrkv_checkbox_automation_payments[$mrkv_checkbox_gateway_id]['statuses']) ? $mrkv_checkbox_automation_payments[$mrkv_checkbox_gateway_id]['statuses'] : '';
															;
															
															if(!$mrkv_checkbox_data && !is_array($mrkv_checkbox_settings) && isset($mrkv_checkbox_ppo_autocreate_payment_order_statuses[$mrkv_checkbox_gateway_id]))
															{
																$mrkv_checkbox_data = $mrkv_checkbox_ppo_autocreate_payment_order_statuses[$mrkv_checkbox_gateway_id];
															}

															echo wp_kses($field_generator->get_select_multiple(__('Autocreate statuses', 'checkbox'), $mrkv_checkbox_settings_name . '[automation][payments][' . $mrkv_checkbox_gateway_id . '][statuses][]', $mrkv_checkbox_order_statuses, $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_automation_payments_' . $mrkv_checkbox_gateway_id . '_statuses' , '', __('When selected, only orders with related order statuses will be processed', 'checkbox'),  'multiple'), $allowed_tags);
														?>
													</div>
												</div>
											</div>
											<?php do_action('mrkv_checkbox_settings_page_row', 'automation_settings', 'row_3_payment_' . $mrkv_checkbox_gateway_id); ?>
										</div>
									</div>
									<?php
								}
							?>
							<?php do_action('mrkv_checkbox_settings_page_row', 'automation_settings', 'row_4'); ?>
							<h3><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/routing-icon.svg'); ?>" alt="Status" title="Status"><?php esc_html_e('Auto-creation type Settings', 'checkbox'); ?></h3>
							<p><?php esc_html_e('Set the auto-create type that works best for you', 'checkbox'); ?></p>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'automation_settings', 'row_5'); ?>
							<div class="admin_ua_ship_morkva_settings_row">
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php
											$mrkv_checkbox_data = '';
										

											echo wp_kses($field_generator->get_input_checkbox(__('Turn on auto-create queue', 'checkbox'), $mrkv_checkbox_settings_name . '[automation][cron][enabled]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_automation_cron_enabled', '', 'disabled'), $allowed_tags);
										?>
										<span class="mrkv-ua-ship-only-pro"><?php echo esc_html__('Only in the Pro version', 'checkbox'); ?></span>
										<p class="mrkv-ua-ship-description"><?php echo esc_html__('All receipts that need to be generated during auto-creation will be stored in a table and processed one by one using a cron job.', 'checkbox'); ?></p>
									</div>
								</div>
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled mrkv-checkbox-hidden-cron-block">
										<h4 style="margin-top: 0;"><?php esc_html_e('Cron type', 'checkbox'); ?></h4>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
										<div class="admin_ua_ship_morkva_settings_row">
											<?php
												$mrkv_checkbox_data = '';

												echo wp_kses($field_generator->get_input_radio(__('WP Cron', 'checkbox'), $mrkv_checkbox_settings_name . '[automation][cron][type]', 'wp', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_automation_cron_type_wp_cron', 'wp', 'disabled'), $allowed_tags);
												echo wp_kses($field_generator->get_input_radio(__('Server Cron', 'checkbox'), $mrkv_checkbox_settings_name . '[automation][cron][type]', 'server', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_automation_cron_type_server_cron', 'wp', 'disabled'), $allowed_tags);
											?>
										</div>
										<p>
											<?php esc_html_e('Select the type of crown that you will use to create receipt auto-create queue', 'checkbox'); ?>
										</p>
									</div>
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'automation_settings', 'row_6'); ?>
							<div class="admin_ua_ship_morkva_settings_row mrkv-checkbox-hidden-cron-block">
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php 
											$mrkv_checkbox_data = '';

											echo wp_kses($field_generator->get_input_number(__('Number of checks per iteration', 'checkbox'), $mrkv_checkbox_settings_name . '[automation][cron][quantity]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_automation_cron_quantity' , '', '', '', 'readonly'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
										<p>
											<?php esc_html_e('Recommended 3. If you have a powerful hosting plan and a high volume of orders, you can try increasing the number', 'checkbox'); ?>
										</p>
									</div>
								</div>
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php 
											$mrkv_checkbox_data = '';

											$mrkv_checkbox_description = __('Configure the interval for automatic receipt generation in WP CRON', 'checkbox');

											echo wp_kses($field_generator->get_select_simple(__('WP CRON interval', 'checkbox'), $mrkv_checkbox_settings_name . '[automation][cron][interval]', $custom_interval_autocreate, $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_automation_cron_interval' , __('Choose an interval', 'checkbox'), $mrkv_checkbox_description, '', 'readonly'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
									</div>
								</div>
							</div>
							<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled mrkv-checkbox-hidden-cron-block">
								<label for="nova-poshta_m_ua_settings_api_key"><?php esc_html_e('Server Cron URL', 'checkbox'); ?></label>
								<input style="width: 100%; max-width: 100%;" type="text" value="<?php echo esc_url(rest_url('mrkv-checkbox/v1/autocreate-receipt')); ?>" readonly="">
								<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
								<p><?php esc_html_e('Create a cron on the server to this link and set it to execute at your discretion. Recommended for every 2 minutes', 'checkbox'); ?></p>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'automation_settings', 'row_7'); ?>
						</section>
						<section id="advanced_settings" class="mrkv_up_ship_shipping_tab_block">
							<h2><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/settings-icon.svg'); ?>" alt="Advanced Settings" title="Advanced Settings"><?php echo esc_html__('Advanced Settings', 'checkbox'); ?></h2>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_1'); ?>
							<h3><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/routing-icon.svg'); ?>" alt="Status" title="Status"><?php esc_html_e('Delivery Settings', 'checkbox'); ?></h3>
							<p><?php esc_html_e('Select your delivery settings when creating a receipt', 'checkbox'); ?></p>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_2'); ?>
							<div class="admin_ua_ship_morkva_settings_row">
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php
											$mrkv_checkbox_data = '';
											
											echo wp_kses($field_generator->get_input_checkbox(__('Enable delivery in receipt', 'checkbox'), $mrkv_checkbox_settings_name . '[delivery][enabled]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_delivery_enabled', '', 'disabled'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
										<p class="mrkv-ua-ship-description"><?php esc_html__('Added delivery when creating a receipt', 'checkbox'); ?></p>
									</div>
								</div>
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php 
											$mrkv_checkbox_data = '';

											$mrkv_checkbox_description = __('Select how you want to add shipping to the receipt', 'checkbox');

											echo wp_kses($field_generator->get_select_simple(__('Type of delivery added', 'checkbox'), $mrkv_checkbox_settings_name . '[delivery][type]', $mrkv_checkbox_delivery_types, $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_delivery_type' , __('Choose a type', 'checkbox'), $mrkv_checkbox_description, '', 'readonly'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
									</div>
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_3'); ?>
							<div class="admin_ua_ship_morkva_settings_row">
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php 
											$mrkv_checkbox_data = '';

											$mrkv_checkbox_label = __('Shipping name on the receipt', 'checkbox');
											$mrkv_checkbox_description = __('Enter the delivery name to be included on the receipt. Default: Shipping', 'checkbox');
											echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[delivery][title]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_delivery_title' , '', __('Enter the text...', 'checkbox'), $mrkv_checkbox_description, 'readonly'), $allowed_tags);
											?>
											<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
									</div>
								</div>
								<div class="col-mrkv-5">
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_4'); ?>
							<h3><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/mention-square-icon.svg'); ?>" alt="Status" title="Status"><?php esc_html_e('Email Settings', 'checkbox'); ?></h3>
							<p><?php esc_html_e('Select your email settings when creating a receipt', 'checkbox'); ?></p>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_5'); ?>
							<div class="admin_ua_ship_morkva_settings_row">
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php
											$mrkv_checkbox_data = '';

											echo wp_kses($field_generator->get_input_checkbox(__('Email notification failure to generate a receipt', 'checkbox'), $mrkv_checkbox_settings_name . '[email][cancelled][enabled]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_email_cancelled_enabled', '', 'readonly'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
										<p class="mrkv-ua-ship-description"><?php echo esc_html__('If you enable this, you will receive an email notification that the receipt could not be generated', 'checkbox'); ?></p>
									</div>
								</div>
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php 
											$mrkv_checkbox_data = '';

											$mrkv_checkbox_label = __('Email for notifications', 'checkbox');
											$mrkv_checkbox_description = __('Enter the email address where you\'ll receive a notification if the receipt cannot be generated', 'checkbox');
											echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[email][cancelled][client]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_email_cancelled_client' , '', __('Enter the email...', 'checkbox'), $mrkv_checkbox_description, 'readonly'), $allowed_tags);
											?>
											<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
									</div>
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_6'); ?>
							<div class="admin_ua_ship_morkva_settings_row">
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php
											$mrkv_checkbox_data = '';

											echo wp_kses($field_generator->get_input_checkbox(__('Enable email in receipt', 'checkbox'), $mrkv_checkbox_settings_name . '[email][receipt_added]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_email_receipt_added', '', 'readonly'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
										<p class="mrkv-ua-ship-description"><?php echo esc_html__('Added an email when creating a receipt', 'checkbox'); ?></p>
									</div>
								</div>
								<div class="col-mrkv-5"></div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_7'); ?>
							<h3><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/scan.svg'); ?>" alt="Status" title="Status"><?php esc_html_e('HS Code', 'checkbox'); ?></h3>
							<p><?php esc_html_e('Configure the HS Code you want to add to the receipt', 'checkbox'); ?></p>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_8'); ?>
							<div class="admin_ua_ship_morkva_settings_row">
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php
											$mrkv_checkbox_data = '';

											echo wp_kses($field_generator->get_input_checkbox(__('Enable HS Code in receipt', 'checkbox'), $mrkv_checkbox_settings_name . '[hs_code][enabled]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_hs_code_enabled', '', 'readonly'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
										<p class="mrkv-ua-ship-description"><?php echo esc_html__('Added HS Code when creating a receipt', 'checkbox'); ?></p>
									</div>
								</div>
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php 
											$mrkv_checkbox_data = '';

											$mrkv_checkbox_description = __('Select the global product attribute that will add the HS code when creating a receipt.', 'checkbox');

											echo wp_kses($field_generator->get_select_simple(__('Global product attributes', 'checkbox'), $mrkv_checkbox_settings_name . '[hs_code][type]', $mrkv_checkbox_attributes, $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_hs_code_type' , __('Choose an attribute', 'checkbox'), $mrkv_checkbox_description, '', 'readonly'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
									</div>
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_9'); ?>
							<h3><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/box-icon.svg'); ?>" alt="Status" title="Status"><?php esc_html_e('Product title', 'checkbox'); ?></h3>
							<p><?php esc_html_e('Configure product title you want to add to the receipt', 'checkbox'); ?></p>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_10'); ?>
							<div class="admin_ua_ship_morkva_settings_row">
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php
											$mrkv_checkbox_data = '';

											echo wp_kses($field_generator->get_input_checkbox(__('Change product title in receipt', 'checkbox'), $mrkv_checkbox_settings_name . '[product_title][enabled]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_product_title_enabled', '', 'readonly'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
										<p class="mrkv-ua-ship-description"><?php echo esc_html__('Check this box to change the source of the product name on the receipt', 'checkbox'); ?></p>
									</div>
								</div>
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php 
											$mrkv_checkbox_data = '';

											$mrkv_checkbox_description = __('Select the global product attribute that will change product title when creating a receipt.', 'checkbox');

											echo wp_kses($field_generator->get_select_simple(__('Global product attributes', 'checkbox'), $mrkv_checkbox_settings_name . '[product_title][type]', $mrkv_checkbox_attributes, $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_product_title_type' , __('Choose an attribute', 'checkbox'), $mrkv_checkbox_description, '', 'readonly'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
									</div>
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_11'); ?>
							<h3><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/tuning-icon.svg'); ?>" alt="Status" title="Status"><?php esc_html_e('Added Fields', 'checkbox'); ?></h3>
							<p><?php esc_html_e('Configure the additional fields you want to add to the receipt', 'checkbox'); ?></p>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_12'); ?>
							<div class="admin_ua_ship_morkva_settings_row">
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line">
										<?php
											$mrkv_checkbox_data = isset($mrkv_checkbox_settings['added_fields']['barcode']) ? $mrkv_checkbox_settings['added_fields']['barcode'] : '';

											if(!$mrkv_checkbox_data && !is_array($mrkv_checkbox_settings) && get_option('ppo_barcode'))
											{
												$mrkv_checkbox_data = get_option('ppo_barcode');
											}

											echo wp_kses($field_generator->get_input_checkbox(__('Enable product barcode', 'checkbox'), $mrkv_checkbox_settings_name . '[added_fields][barcode]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_added_fields_barcode'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-description"><?php echo esc_html__('Added a product barcode when creating a receipt', 'checkbox'); ?></p>
									</div>
								</div>
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php
											$mrkv_checkbox_data = '';

											echo wp_kses($field_generator->get_input_checkbox(__('Enable phone', 'checkbox'), $mrkv_checkbox_settings_name . '[added_fields][phone]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_added_fields_phone', '', 'readonly'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
										<p class="mrkv-ua-ship-description"><?php echo esc_html__('Added a phone when creating a receipt', 'checkbox'); ?></p>
									</div>
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_13'); ?>
							<div class="admin_ua_ship_morkva_settings_row">
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line">
										<?php
											$mrkv_checkbox_data = isset($mrkv_checkbox_settings['added_fields']['zero_price']) ? $mrkv_checkbox_settings['added_fields']['zero_price'] : '';

											if(!$mrkv_checkbox_data && !is_array($mrkv_checkbox_settings) && get_option('ppo_zero_product_exclude'))
											{
												$mrkv_checkbox_data = get_option('ppo_zero_product_exclude');
											}

											echo wp_kses($field_generator->get_input_checkbox(__('Exclude items with a zero price', 'checkbox'), $mrkv_checkbox_settings_name . '[added_fields][zero_price]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_added_fields_zero_price'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-description"><?php echo esc_html__('Check this box to prevent items priced at 0 UAH from being added to the receipt', 'checkbox'); ?></p>
									</div>
								</div>
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php 
											$mrkv_checkbox_data = '';

											$mrkv_checkbox_description = __('Select the global product attribute that will change product title when creating a receipt.', 'checkbox');

											echo wp_kses($field_generator->get_select_simple(__('Source product code', 'checkbox'), $mrkv_checkbox_settings_name . '[added_fields][code_type]', $mrkv_checkbox_product_codes_type, $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_added_fields_code_type' , __('Choose a source', 'checkbox'), $mrkv_checkbox_description, '', 'readonly'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
									</div>
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_14'); ?>
							<div class="admin_ua_ship_morkva_settings_line">
								<?php
									$mrkv_checkbox_data = isset($mrkv_checkbox_settings['added_fields']['description']) ? $mrkv_checkbox_settings['added_fields']['description'] : '';

									if(!$mrkv_checkbox_data && !is_array($mrkv_checkbox_settings) && get_option('ppo_receipt_footer'))
									{
										$mrkv_checkbox_data = get_option('ppo_receipt_footer');
									}

									$mrkv_checkbox_description = esc_html__('Plain text, no html.', 'checkbox');

									echo wp_kses($field_generator->get_textarea(__('Official information', 'checkbox'), $mrkv_checkbox_settings_name . '[added_fields][description]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_added_fields_description' , '', __('Enter the text...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
								?>
								<p><?php echo esc_html__('The value of this field will appear at the bottom of the electronic receipt. Available shortcodes: [website_title], [order_id], [order_created_date], [order_paid_date], [novapost_pro_ttn].', 'checkbox'); ?></p>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_15'); ?>
							<h3><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/sale.svg'); ?>" alt="Status" title="Status"><?php esc_html_e('Discounts', 'checkbox'); ?></h3>
							<p><?php esc_html_e('Configure discounts data you want to add to the receipt', 'checkbox'); ?></p>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_16'); ?>
							<div class="admin_ua_ship_morkva_settings_row">
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line">
										<?php 
											$mrkv_checkbox_data = isset($mrkv_checkbox_settings['discount']['label']) ? $mrkv_checkbox_settings['discount']['label'] : '';

											if(!$mrkv_checkbox_data && !is_array($mrkv_checkbox_settings) && get_option('ppo_receipt_coupon_text'))
											{
												$mrkv_checkbox_data = get_option('ppo_receipt_coupon_text');
											}

											$mrkv_checkbox_label = __('Coupon name on the receipt', 'checkbox');
											$mrkv_checkbox_description = __('Enter the name that will appear on all coupons in the receipt. Default: Coupon', 'checkbox');
											echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[discount][label]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_discount_label' , '', __('Enter the name...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
											?>
									</div>
								</div>
								<div class="col-mrkv-5">	
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_17'); ?>
							<h3><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/document-icon.svg'); ?>" alt="Status" title="Status"><?php esc_html_e('PDF Settings', 'checkbox'); ?></h3>
							<p><?php esc_html_e('Set up the functionality for working with PDF receipts', 'checkbox'); ?></p>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_18'); ?>
							<div class="admin_ua_ship_morkva_settings_row">
								<div class="col-mrkv-5">
									<div class="admin_ua_ship_morkva_settings_line mrkv-field-disabled">
										<?php
											$mrkv_checkbox_data = '';

											echo wp_kses($field_generator->get_input_checkbox(__('Safe PDF receipts', 'checkbox'), $mrkv_checkbox_settings_name . '[pdf][enabled]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_pdf_enabled', '', 'readonly'), $allowed_tags);
										?>
										<p class="mrkv-ua-ship-only-pro"><?php esc_html_e('Only in the Pro version', 'checkbox'); ?></p>
										<p class="mrkv-ua-ship-description"><?php echo esc_html__('Check this box if you need to download the receipt in PDF format. The generated receipts will be saved in the /wp-content/plugins/checkbox-pro/receipts-pdf folder.', 'checkbox'); ?></p>
									</div>
								</div>
								<div class="col-mrkv-5">
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'advanced_settings', 'row_19'); ?>
						</section>
						<section id="log" class="mrkv_up_ship_shipping_tab_block">
							<h2><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/settings-icon.svg'); ?>" alt="Debug Log" title="Debug Log"><?php echo esc_html__('Test/Debug', 'checkbox'); ?></h2>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'log', 'row_1'); ?>
							<h3><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/tuning-icon.svg'); ?>" alt="Status" title="Status"><?php esc_html_e('Test mode', 'checkbox'); ?></h3>
							<p><?php esc_html_e('Configure the test data and enable test mode when you need to verify the receipt creation request', 'checkbox'); ?></p>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'log', 'row_2'); ?>
							<div class="admin_ua_ship_morkva_settings_line">
								<?php 
									$mrkv_checkbox_data = isset($mrkv_checkbox_settings['test_mode']['shift_status']) ? $mrkv_checkbox_settings['test_mode']['shift_status'] : 'closed';
									echo wp_kses( $field_generator->get_input_hidden($mrkv_checkbox_settings_name . '[test_mode][shift_status]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_test_mode_shift_status'), $allowed_tags);

									$mrkv_checkbox_data = isset($mrkv_checkbox_settings['test_mode']['signin']) ? $mrkv_checkbox_settings['test_mode']['signin'] : '';
									echo wp_kses( $field_generator->get_input_hidden($mrkv_checkbox_settings_name . '[test_mode[signin]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_test_mode_signin'), $allowed_tags);

									$mrkv_checkbox_data = isset($mrkv_checkbox_settings['test_mode']['enabled']) ? $mrkv_checkbox_settings['test_mode']['enabled'] : '';

									if(!$mrkv_checkbox_data && !is_array($mrkv_checkbox_settings) && get_option('ppo_is_dev_mode'))
									{
										$mrkv_checkbox_data = get_option('ppo_is_dev_mode');
									}

									echo wp_kses($field_generator->get_input_checkbox(__('Test mode', 'checkbox'), $mrkv_checkbox_settings_name . '[test_mode][enabled]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_test_mode_enabled', ), $allowed_tags);
									?>
								<div class="admin_ua_ship_morkva_settings_line__inner">
									<div class="admin_ua_ship_morkva_settings_row">
										<div class="col-mrkv-5"><h3><?php echo esc_html__('Test cashier data', 'checkbox'); ?></h3><hr></div>
										<div class="col-mrkv-5"><h3><?php echo esc_html__('Test cash register data', 'checkbox'); ?></h3><hr></div>
									</div>
									<div class="admin_ua_ship_morkva_settings_row">
										<div class="col-mrkv-5">
											<div class="admin_ua_ship_morkva_settings_line">
												<?php 
													$mrkv_checkbox_data = isset($mrkv_checkbox_settings['test_mode']['cashier_login']) ? $mrkv_checkbox_settings['test_mode']['cashier_login'] : '';
													$mrkv_checkbox_label = __('Cashier login', 'checkbox');
													$mrkv_checkbox_description = '';
													echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[test_mode][cashier_login]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_test_mode_cashier_login' , '', __('Enter the login...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
												?>
											</div>
										</div>
										<div class="col-mrkv-5">
											<div class="admin_ua_ship_morkva_settings_line">
												<?php 
													$mrkv_checkbox_data = isset($mrkv_checkbox_settings['test_mode']['register_name']) ? $mrkv_checkbox_settings['test_mode']['register_name'] : '';
													$mrkv_checkbox_label = __('Cash Register name (optional)', 'checkbox');
													$mrkv_checkbox_description = '';
													echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[test_mode][register_name]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_test_mode_register_name' , '', __('Enter the name...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
												?>
											</div>
										</div>
									</div>
									<div class="admin_ua_ship_morkva_settings_row">
										<div class="col-mrkv-5">
											<div class="admin_ua_ship_morkva_settings_line">
												<?php 
													$mrkv_checkbox_data = isset($mrkv_checkbox_settings['test_mode']['cashier_password']) ? $mrkv_checkbox_settings['test_mode']['cashier_password'] : '';
													$mrkv_checkbox_label = __('Cashier password', 'checkbox');
													$mrkv_checkbox_description = '';
													echo wp_kses($field_generator->get_input_password($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[test_mode][cashier_password]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_test_mode_cashier_password' , '', __('Enter the password...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
												?>
											</div>
										</div>
										<div class="col-mrkv-5">
											<div class="admin_ua_ship_morkva_settings_line">
												<?php 
													$mrkv_checkbox_data = isset($mrkv_checkbox_settings['test_mode']['register_key']) ? $mrkv_checkbox_settings['test_mode']['register_key'] : '';
													$mrkv_checkbox_label = __('Cash Register key', 'checkbox');
													$mrkv_checkbox_description = '';
													echo wp_kses($field_generator->get_input_password($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[test_mode][register_key]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_test_mode_register_key' , '', __('Enter the key...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
													?>
											</div>
										</div>
									</div>
									<div class="admin_ua_ship_morkva_settings_row">
										<div class="col-mrkv-5">
											<div class="admin_ua_ship_morkva_settings_line">
												<?php 
													$mrkv_checkbox_data = isset($mrkv_checkbox_settings['test_mode']['cashier_name']) ? $mrkv_checkbox_settings['test_mode']['cashier_name'] : '';
													$mrkv_checkbox_label = __('Cashier name', 'checkbox');
													$mrkv_checkbox_description = '';
													echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[test_mode][cashier_name]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_test_mode_cashier_name' , '', __('Enter the name...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
												?>
											</div>
										</div>
										<div class="col-mrkv-5">
											<div class="admin_ua_ship_morkva_settings_line">
												<?php 
													$mrkv_checkbox_data = isset($mrkv_checkbox_settings['test_mode']['register_edrpou']) ? $mrkv_checkbox_settings['test_mode']['register_edrpou'] : '';
													$mrkv_checkbox_label = __('EPRPOU', 'checkbox');
													$mrkv_checkbox_description = '';
													echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[test_mode][register_edrpou]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_test_mode_register_edrpou' , '', __('Enter the edrpou...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
													?>
											</div>
										</div>
									</div>
									<div class="admin_ua_ship_morkva_settings_row">
										<div class="col-mrkv-5">
											<div class="admin_ua_ship_morkva_settings_line">
												<?php 
													$mrkv_checkbox_data = isset($mrkv_checkbox_settings['test_mode']['cashier_lastname']) ? $mrkv_checkbox_settings['test_mode']['cashier_lastname'] : '';
													$mrkv_checkbox_label = __('Cashier lastname', 'checkbox');
													$mrkv_checkbox_description = '';
													echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[test_mode][cashier_lastname]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_test_mode_cashier_lastname' , '', __('Enter the lastname...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
												?>
											</div>
										</div>
										<div class="col-mrkv-5">
											<div class="admin_ua_ship_morkva_settings_line">
												<?php 
													$mrkv_checkbox_data = isset($mrkv_checkbox_settings['test_mode']['register_tax_code']) ? $mrkv_checkbox_settings['test_mode']['register_tax_code'] : '';
													$mrkv_checkbox_label = __('Tax code', 'checkbox');
													$mrkv_checkbox_description = __('You can find the tax code (letter or number) on the Tax Rates page in your Checkbox account here: https://my.checkbox.ua/dashboard/taxrates', 'checkbox');
													echo wp_kses($field_generator->get_input_text($mrkv_checkbox_label, $mrkv_checkbox_settings_name . '[test_mode][register_tax_code]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name. '_test_mode_register_tax_code' , '', __('Enter the code...', 'checkbox'), $mrkv_checkbox_description), $allowed_tags);
													?>
											</div>
										</div>
									</div>
									<?php do_action('mrkv_checkbox_settings_page_row', 'log', 'row_3'); ?>
								</div>
							</div>
							<?php do_action('mrkv_checkbox_settings_page_row', 'log', 'row_4'); ?>
							<h3><img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/tuning-icon.svg'); ?>" alt="Status" title="Status"><?php esc_html_e('Debug settings', 'checkbox'); ?></h3>
							<p><?php esc_html_e('Configure the plugin\'s log to view query results and error causes', 'checkbox'); ?></p>
							<hr class="mrkv-ua-ship__hr">
							<?php do_action('mrkv_checkbox_settings_page_row', 'log', 'row_5'); ?>
								<div class="admin_ua_ship_morkva_settings_row">
									<div class="col-mrkv-5">
										<div class="admin_ua_ship_morkva_settings_line">
											<?php
												$mrkv_checkbox_data = isset($mrkv_checkbox_settings['debug']['log']) ? $mrkv_checkbox_settings['debug']['log'] : '';
												echo wp_kses($field_generator->get_input_checkbox(__('Enable debug log', 'checkbox'), $mrkv_checkbox_settings_name . '[debug][log]', $mrkv_checkbox_data, $mrkv_checkbox_settings_name . '_debug_log', ), $allowed_tags);
											?>
											<p class="mrkv-ua-ship-description"><?php echo esc_html__('Enable to receive request error logs', 'checkbox'); ?></p>
										</div>
									</div>
									<div class="col-mrkv-5">
									</div>
								</div>
								<div class="admin_ua_ship_morkva_settings_line">
									<a href="<?php echo esc_url(admin_url( 'admin.php?page=wc-status&tab=logs' )); ?>"><?php echo esc_html__('Show Log files', 'checkbox'); ?></a>
								</div>
						</section>
						<?php echo esc_html(submit_button(esc_html__('Save', 'checkbox'))); ?>
					</div>
				</form>
			</div>
		</div>
		<div class="admin_mrkv_ua_shipping__block col-mrkv-3">
			<div class="admin_mrkv_ua_shipping__plugin-info mrkv_block_rounded">
				<div class="admin_mrkv_ua_shipping__plugin__support">
					<h2><?php echo esc_html__('Like this plugin?', 'checkbox'); ?></h2>
					<p>
						<?php echo esc_html__( 'Support our efforts with a', 'checkbox' ) . ' '; ?>
						<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/star.svg'); ?>" alt="Star" alt="Star">
						<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/star.svg'); ?>" alt="Star" alt="Star">
						<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/star.svg'); ?>" alt="Star" alt="Star">
						<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/star.svg'); ?>" alt="Star" alt="Star">
						<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/star.svg'); ?>" alt="Star" alt="Star">
						<?php echo esc_html__( 'review at', 'checkbox' ); ?>
						<a href="https://wordpress.org/plugins/checkbox/" target="blanc">WordPress.org</a>
					</p>
					<a class="button button-primary mrkv-btn-sidebar-main" href="https://wordpress.org/plugins/checkbox/" target="blanc">
						<?php echo esc_html__( 'Leave', 'checkbox' ) . ' '; ?>
						<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/star.svg'); ?>" alt="Star" alt="Star">
						<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/star.svg'); ?>" alt="Star" alt="Star">
						<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/star.svg'); ?>" alt="Star" alt="Star">
						<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/star.svg'); ?>" alt="Star" alt="Star">
						<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/star.svg'); ?>" alt="Star" alt="Star">
					</a>
					<p>
						<?php echo esc_html__( 'Isn’t good enough for a 5', 'checkbox' ) . ' '; ?>
						<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/star.svg'); ?>" alt="Star" alt="Star">? 
						<?php echo esc_html__( 'Contact us via the widget on our website, or check out', 'checkbox' ) . ' '; ?>
						<a href="https://docs.morkva.co.ua/uk?utm_source=plugin&utm_medium=sidebar&utm_campaign=checkbox_free" target="blanc"><?php echo esc_html__( 'documantation', 'checkbox' ); ?></a>
					</p>
					<div class="mrkv-btns-line-sidebar" style="display: flex;gap: 4px;">
						<a class="button mrkv-btn-sidebar-black" href="https://morkva.co.ua/?utm_source=plugin&utm_medium=sidebar&utm_campaign=checkbox_free" target="blanc">
							<?php echo esc_html__( 'Go to the website', 'checkbox' ); ?>
						</a>
						<a class="button mrkv-btn-sidebar-black" href="https://docs.morkva.co.ua/uk?utm_source=plugin&utm_medium=sidebar&utm_campaign=checkbox_free" target="blanc">
							<?php echo esc_html__( 'Documantation', 'checkbox' ); ?>
						</a>
					</div>
				</div>
			</div>
			<div class="admin_mrkv_ua_shipping__plugin-info mrkv_block_rounded">
				<div class="admin_mrkv_ua_shipping__plugin__support">
					<h2><?php echo esc_html__('Check out pro-version', 'checkbox'); ?></h2>
					<ul>
						<li>
							<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/check.svg'); ?>" alt="Check" alt="Check">
							<?php echo esc_html__( 'Using multiple cash registers', 'checkbox' ); ?>
						</li>
						<li>
							<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/check.svg'); ?>" alt="Check" alt="Check">
							<?php echo esc_html__( 'Use of HS codes', 'checkbox' ); ?>
						</li>
						<li>
							<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/check.svg'); ?>" alt="Check" alt="Check">
							<?php echo esc_html__( 'Using the product code', 'checkbox' ); ?>
						</li>
						<li>
							<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/check.svg'); ?>" alt="Check" alt="Check">
							<?php echo esc_html__( 'Bulk printing of receipts', 'checkbox' ); ?>
						</li>
						<li>
							<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/check.svg'); ?>" alt="Check" alt="Check">
							<?php echo esc_html__( 'Bulk deletion of receipts', 'checkbox' ); ?>
						</li>
						<li>
							<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/check.svg'); ?>" alt="Check" alt="Check">
							<?php echo esc_html__( 'Shortcodes for service information', 'checkbox' ); ?>
						</li>
						<li>
							<img src="<?php echo esc_url(MRKV_CHECKBOX_IMG_URL . '/global/check.svg'); ?>" alt="Check" alt="Check">
							<?php echo esc_html__( 'Using the name from the attribute for multilingual websites', 'checkbox' ); ?>
						</li>
						<li><?php echo esc_html__( 'and more', 'checkbox' ); ?></li>
					</ul>
					<a class="button button-primary mrkv-btn-sidebar-main" href="https://morkva.co.ua/shop/woocommerce-checkbox-pro/?utm_source=plugin&utm_medium=sidebar&utm_campaign=checkbox_free" target="blanc">
						<?php echo esc_html__( 'Buy Pro-version', 'checkbox' ); ?>
					</a>
				</div>
			</div>
			<div class="admin_mrkv_ua_shipping__plugin-info mrkv_block_rounded">
				<div class="admin_mrkv_ua_shipping__plugin__support">
					<h2><?php echo esc_html__('Other free plugins', 'checkbox'); ?></h2>
					<?php
						$mrkv_checkbox_response = wp_remote_get( 'https://morkva.co.ua/wp-json/pluginManagement/v2', array(
							'headers' => array(
							),
							'timeout' => 30,
							'redirection' => 5,
							'httpversion' => '1.1',
							'sslverify' => true
						));

						$mrkv_checkbox_response_data = $mrkv_checkbox_response['body'] ? json_decode( $mrkv_checkbox_response['body'], true ) : null;
						$mrkv_checkbox_plugins = $mrkv_checkbox_response_data['plugins'] ?? [];

						if(!empty($mrkv_checkbox_plugins))
						{
							?>
								<ul style="list-style: disc;padding-left: 17px;">
									<?php
										foreach($mrkv_checkbox_plugins as $mrkv_checkbox_plugin_slug => $mrkv_checkbox_plugin_data)
										{
											if($mrkv_checkbox_plugin_slug == 'checkbox'){ continue; }
											?>
												<li><a style="display:block; margin-bottom:5px;" href="<?php echo esc_attr($mrkv_checkbox_plugin_data['url'] ?? ''); ?>" target="blanc" class="plugin_line"><?php echo esc_attr($mrkv_checkbox_plugin_data['label'] ?? ''); ?></a></li>
											<?php
										}
									?>
								</ul>
							<?php
						}
					?>
				</div>
			</div>
		</div>
	</div>
</div>