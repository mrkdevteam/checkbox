<?php
/**
 * Checkbox Payment Labels Constants
 *
 * @package Checkbox
 */

/**
 * Exit if accessed directly to prevent security vulnerabilities.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define payment labels and their corresponding fiscal codes.
 * * Logic Mapping:
 * 0 => CASH (Готівка)
 * 1 => CASHLESS (Безготівка / Картка / Електронні платежі)
 */
define( 'MRKV_CHECKBOX_PAYMENT_LABELS', [
	__( 'Готівка', 'checkbox' )                       => 0,
	__( 'Подарунковий сертифікат', 'checkbox' )       => 1,
	__( 'Талон', 'checkbox' )                         => 1,
	__( 'Жетон', 'checkbox' )                         => 1,
	__( 'Картка', 'checkbox' )                        => 1,
	__( 'Платіж через інтегратора', 'checkbox' )      => 1,
	__( 'Переказ через ННПП', 'checkbox' )            => 1,
	__( 'Переказ через ПТКС ННПП', 'checkbox' )       => 1,
	__( 'Інтернет еквайринг', 'checkbox' )            => 1,
	__( 'Інтернет банкінг', 'checkbox' )              => 1,
	__( 'З поточного рахунку', 'checkbox' )           => 1,
	__( 'Переказ через ПТКС банку', 'checkbox' )      => 1,
	__( 'Фішка', 'checkbox' )                         => 1,
	__( 'Електронний грошовий замінник', 'checkbox' ) => 1,
	__( 'Ігровий замінник гривні', 'checkbox' )       => 1,
	__( 'Електронні гроші', 'checkbox' )              => 1,
	__( 'Цифрові гроші', 'checkbox' )                 => 1,
	__( 'Криптовалюта', 'checkbox' )                  => 1,
	__( 'Післяплата', 'checkbox' )                    => 1,
	__( 'Переказ з картки', 'checkbox' )              => 1,
	__( 'Переказ з поточного рахунку', 'checkbox' )   => 1,
] );