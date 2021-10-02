<?php

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// delete plugin options
$options = array(
    'ppo_login',
    'ppo_password',
    'ppo_cashier_name',
    'ppo_cashier_surname',
    'ppo_cashbox_key',
    'ppo_autocreate',
    'ppo_payment_type',
    'ppo_connected',
    'ppo_autoopen_shift',
    'ppo_skip_receipt_creation',
    'ppo_is_dev_mode',
    'ppo_autocreate_receipt_order_statuses',
);

foreach ( $options as $option ) {
	delete_option( $option );
}
