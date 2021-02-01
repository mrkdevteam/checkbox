<?php


// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
die;
}

$options = ['ppo_login','ppo_password','ppo_cashbox_key'];


foreach ($options as $option) {
    delete_option($option_name);
}

