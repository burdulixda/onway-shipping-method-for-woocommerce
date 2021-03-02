<?php
/**
 * Custom Shipping Methods for WooCommerce - Core Class
 *
 * @version 1.6.1
 * @since   1.0.0
 * @author  Tyche Softwares
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Onway_WC_Custom_Shipping_Method_Core' ) ) :

class Onway_WC_Custom_Shipping_Method_Core {

	/**
	 * Constructor.
	 *
	 * @version 1.5.2
	 * @since   1.0.0
	 */
	function __construct() {
		if ( 'yes' === get_option( 'onway_wc_custom_shipping_method_plugin_enabled', 'yes' ) ) {

			// Init
			add_action( 'init', array( $this, 'init_custom_shipping' ) );

		}
	}

	/*
	 * init_custom_shipping.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function init_custom_shipping() {
		if ( class_exists( 'WC_Shipping_Method' ) ) {
			require_once( 'class-wc-shipping-onway-custom.php' );
			add_filter( 'woocommerce_shipping_methods', array( $this, 'add_custom_shipping' ) );
		}
	}

	/*
	 * add_custom_shipping.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_custom_shipping( $methods ) {
		$methods['onway_wc_shipping'] = 'WC_Shipping_Onway_Custom';
		return $methods;
	}

}

endif;

return new Onway_WC_Custom_Shipping_Method_Core();