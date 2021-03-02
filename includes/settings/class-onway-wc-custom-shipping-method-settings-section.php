<?php
/**
 * Custom Shipping Methods for WooCommerce - Section Settings
 *
 * @version 1.1.0
 * @since   1.0.0
 * @author  Tyche Softwares
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Onway_WC_Custom_Shipping_Method_Settings_Section' ) ) :

class Onway_WC_Custom_Shipping_Method_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function __construct() {
		add_filter( 'woocommerce_get_sections_onway_wc_custom_shipping_method',              array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_onway_wc_custom_shipping_method_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
	}

	/**
	 * settings_section.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

}

endif;
