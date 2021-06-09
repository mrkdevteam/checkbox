<?php

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$options = array(
    'ppo_login',
    'ppo_password',
    'ppo_cashier_name',
    'ppo_cashier_surname',
    'ppo_cashbox_key',
    'ppo_auto_create',
    'ppo_payment_type',
    'ppo_connected',
    'ppo_autoopen_shift'
);

foreach ( $options as $option ) {
	delete_option( $option_name );
}
