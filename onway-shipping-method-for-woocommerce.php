<?php
/*
Plugin Name: Onway Shipping Method for WooCommerce
Plugin URI: https://github.com/burdulixda/onway-woo/
Description: Add Onway shipping method to WooCommerce.
Version: 2.2
Author: George Burduli
Author URI: https://github.com/burdulixda
Text Domain: onway-shipping-method-for-woocommerce
Domain Path: /langs
Copyright: � 2021 George Burduli
WC tested up to: 5.0
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Onway_WC_Custom_Shipping_Method' ) ) :

/**
 * Main Alg_WC_Custom_Shipping_Methods Class
 *
 * @class   Alg_WC_Custom_Shipping_Methods
 * @version 20.0
 * @since   1.0.0
 */
final class Onway_WC_Custom_Shipping_Method {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = '2.2';

	/**
	 * @var   Alg_WC_Custom_Shipping_Methods The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_Custom_Shipping_Methods Instance
	 *
	 * Ensures only one instance of Alg_WC_Custom_Shipping_Methods is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @static
	 * @return  Alg_WC_Custom_Shipping_Methods - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_Custom_Shipping_Methods Constructor.
	 *
	 * @version 1.5.2
	 * @since   1.0.0
	 * @access  public
	 */
	function __construct() {

		// Check for active plugins
		if ( 'onway-shipping-method-for-woocommerce.php' !== basename( __FILE__ ) ) {
			return;
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}

		add_action( 'conditional_shipping_hook', array( $this, 'display_conditional_delivery_dates' ) );
		// add_action( 'conditional_shipping_hook', array( $this, 'calculate_delivery_date' ) );
	}

	/**
	 * is_plugin_active.
	 *
	 * @version 1.5.2
	 * @since   1.5.2
	 */
	function is_plugin_active( $plugin ) {
		return ( function_exists( 'is_plugin_active' ) ? is_plugin_active( $plugin ) :
			(
				in_array( $plugin, apply_filters( 'active_plugins', ( array ) get_option( 'active_plugins', array() ) ) ) ||
				( is_multisite() && array_key_exists( $plugin, ( array ) get_site_option( 'active_sitewide_plugins', array() ) ) )
			)
		);
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function includes() {
		// Core
		$this->core = require_once( 'includes/class-onway-wc-custom-shipping-method-core.php' );
	}

	function isBetween($from, $till, $input) {
    $f = DateTime::createFromFormat('!H:i', $from);
    $t = DateTime::createFromFormat('!H:i', $till);
    $i = DateTime::createFromFormat('!H:i', $input);
    if ($f > $t) $t->modify('+1 day');
    return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
	}

	function display_conditional_delivery_dates() {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}
	
		function get_shipping_rates() {
			$packages = WC()->shipping->get_packages();
			$rates = $packages[0]['rates'];
			foreach ( $rates as $key => $value ) {
				$meta_data = $packages[0]['rates'][$key]->meta_data;
			}
			return $meta_data;
		}
	
		$tz = new DateTimeZone('Asia/Tbilisi');
		$currentDateTime = new DateTime('NOW', $tz);
		$currentTime = $currentDateTime->format('H:i');
	
		$dayStart = "00:00";
		$dayEnd = "15:00";
		$today = $currentDateTime->format('l');
	
		if ($today === 'Saturday') {
			$dayEnd = "12:00";
		}
	
		$total_value = 0.0;
	
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$dimensions = $cart_item['data']->get_dimensions(0);
			$product_value = round((array_product($dimensions) / 5000) * $cart_item['quantity'], 2);
			$weight = wc_get_weight( $cart_item['data']->get_weight(), 'kg' ) * $cart_item['quantity'];
	
			if ( $product_value > $weight ) {
				$weight = wc_get_weight( $product_value, 'kg' );
			}
	
			$total_value += $product_value;
		}
	
		if ( ! $this->isBetween($dayStart, $dayEnd, $currentTime) ) {
			$currentDateTime->add(new DateInterval('P1D'));
		}
	
		$today = $currentDateTime->format('l');
	
		foreach ( get_shipping_rates() as $day => $logic ) {
			if ( $today === $day && $logic !== 0 ) {
				$deliveryDateTime = clone $currentDateTime;
				$deliveryDateTime->add(new DateInterval('P' . $logic . 'D'));
				$deliveryDay = $deliveryDateTime->format('l');
				$deliveryDate = $deliveryDateTime->format('j F');
				$moreThanOne = WC()->cart->get_cart_contents_count() > 1 ? "ების" : "ის";
	
				$lang = 'ge';
	
				switch ($lang) {
					case 'en':
						setlocale(LC_TIME, 'en_CA.UTF-8');
						echo strftime("%B %e, %G");
						break;
					case 'ge':
						setlocale(LC_TIME, 'ka_GE.UTF-8');
						echo '<span class="conditional_date">პროდუქტ' . $moreThanOne . ' მიღების თარიღია: ' . strftime("%e %B", $deliveryDateTime->getTimestamp()) . '</span>';
						break;
				}
			}
		}
	}
	

	/**
	 * admin.
	 *
	 * @version 1.6.1
	 * @since   1.2.1
	 */
	function admin() {
		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
		// Version update
		if ( get_option( 'onway_wc_custom_shipping_method_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}
	}

	/**
	 * Add Custom Shipping Methods settings tab to WooCommerce settings.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once( 'includes/settings/class-onway-wc-settings-custom-shipping-method.php' );
		return $settings;
	}

	/**
	 * version_updated.
	 *
	 * @version 1.2.1
	 * @since   1.1.0
	 */
	function version_updated() {
		update_option( 'onway_wc_custom_shipping_method_version', $this->version );
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}

endif;

if ( ! function_exists( 'onway_wc_custom_shipping_method' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Custom_Shipping_Methods to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_WC_Custom_Shipping_Methods
	 * @todo    [dev] `plugins_loaded`
	 */
	function onway_wc_custom_shipping_method() {
		return Onway_WC_Custom_Shipping_Method::instance();
	}
}

onway_wc_custom_shipping_method();
