<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https:///github.com/romaleg07
 * @since      1.0.0
 *
 * @package    Binance_Pay_Woocommerce
 * @subpackage Binance_Pay_Woocommerce/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Binance_Pay_Woocommerce
 * @subpackage Binance_Pay_Woocommerce/includes
 * @author     Romaleg <romaleg.sky@yandex.ru>
 */
class Binance_Pay_Woocommerce_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'binance-pay-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
