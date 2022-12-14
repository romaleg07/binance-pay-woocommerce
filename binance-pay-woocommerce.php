<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https:///github.com/romaleg07
 * @since             1.0.0
 * @package           Binance_Pay_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Binance Pay Woocommrce
 * Plugin URI:        https://github.com/romaleg07/binance-pay-woocommerce
 * Description:       This plugin for fast integration Binance Pay with WooCommerce
 * Version:           1.0.0
 * Author:            Romaleg
 * Author URI:        https:///github.com/romaleg07
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       binance-pay-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BINANCE_PAY_WOOCOMMERCE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-binance-pay-woocommerce-activator.php
 */
function activate_binance_pay_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-binance-pay-woocommerce-activator.php';
	Binance_Pay_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-binance-pay-woocommerce-deactivator.php
 */
function deactivate_binance_pay_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-binance-pay-woocommerce-deactivator.php';
	Binance_Pay_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_binance_pay_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_binance_pay_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-binance-pay-woocommerce.php';


/**
 * Register new gateway class
 */
add_filter( 'woocommerce_payment_gateways', 'register_gateway_class' );
 
function register_gateway_class( $gateways ) {
	$gateways[] = 'WC_Gateway_BinancePay'; 
	return $gateways;
}
 
/*
 * Add new gateway class
 */
add_action( 'plugins_loaded', 'gateway_class' );
function gateway_class() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-binance-pay-woocommerce-create-gateway.php';

}



/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_binance_pay_woocommerce() {

	$plugin = new Binance_Pay_Woocommerce();
	$plugin->run();

}
run_binance_pay_woocommerce();
