<?php
/**
 * Custom Shipping Methods for WooCommerce - General Section Settings
 *
 * @version 1.6.0
 * @since   1.0.0
 * @author  Tyche Softwares
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Onway_WC_Custom_Shipping_Method_Settings_General' ) ) :

class Onway_WC_Custom_Shipping_Method_Settings_General extends Onway_WC_Custom_Shipping_Method_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = '';
		$this->desc = __( 'General', 'onway-shipping-method-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 * @todo    [dev] Icons & Descriptions: (maybe) make it always visible (and disabled) in each method's settings
	 * @todo    [dev] maybe set `onway_wc_custom_shipping_method_do_trigger_checkout_update` to `yes` by default
	 * @todo    [dev] (maybe) make "Advanced" settings (i.e. "Custom return URL") optional
	 * @todo    [feature] admin `method_description`
	 */
	function get_settings() {

		$plugin_settings = array(
			array(
				'title'    => __( 'Custom Shipping Methods Options', 'onway-shipping-method-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'onway_wc_custom_shipping_method_plugin_options',
			),
			array(
				'title'    => __( 'Onway Shipping Method', 'onway-shipping-method-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable plugin', 'onway-shipping-method-for-woocommerce' ) . '</strong>',
				'desc_tip' => __( 'Add custom shipping methods to WooCommerce.', 'onway-shipping-method-for-woocommerce' ),
				'id'       => 'onway_wc_custom_shipping_method_plugin_enabled',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'onway_wc_custom_shipping_method_plugin_options',
			),
		);

		$admin_settings = array(
			array(
				'title'    => __( 'Admin Settings', 'onway-shipping-method-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'onway_wc_custom_shipping_method_admin_options',
				'desc'     => sprintf( __( 'Visit %s to set each method\'s options.', 'onway-shipping-method-for-woocommerce' ),
					'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping' ) . '">' .
						__( 'WooCommerce > Settings > Shipping', 'onway-shipping-method-for-woocommerce' ) . '</a>' ),
			),
			array(
				'title'    => __( 'Admin title', 'onway-shipping-method-for-woocommerce' ),
				'id'       => 'onway_wc_custom_shipping_method_admin_title',
				'default'  => __( 'Onway shipping', 'onway-shipping-method-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'onway_wc_custom_shipping_method_admin_options',
			),
		);

		$frontend_settings = array(
			array(
				'title'    => __( 'Frontend Settings', 'onway-shipping-method-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'onway_wc_custom_shipping_method_frontend_options',
			),
			array(
				'title'    => __( 'Trigger checkout update', 'onway-shipping-method-for-woocommerce' ),
				'desc'     => __( 'Enable', 'onway-shipping-method-for-woocommerce' ),
				'desc_tip' => __( 'Will trigger the checkout update on any input change. This is useful if you are using cost calculation by distance to the customer.', 'onway-shipping-method-for-woocommerce' ),
				'id'       => 'onway_wc_custom_shipping_method_do_trigger_checkout_update',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Add to zero cost', 'onway-shipping-method-for-woocommerce' ),
				'desc'     => __( 'Enable', 'onway-shipping-method-for-woocommerce' ),
				'desc_tip' => __( 'Will add text to custom shipping cost on frontend in case if it\'s zero (i.e. free).', 'onway-shipping-method-for-woocommerce' ),
				'id'       => 'onway_wc_custom_shipping_method_do_replace_zero_cost',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc_tip' => __( 'Text to add to zero cost.', 'onway-shipping-method-for-woocommerce' ) . ' ' .
					__( 'Ignored if "Add to zero cost" option above is disabled.', 'onway-shipping-method-for-woocommerce' ),
				'desc'     => '<p>' . sprintf( __( 'E.g.: %s', 'onway-shipping-method-for-woocommerce' ),
					'<code>' . esc_html( ': <span style="color:green;font-weight:bold;">Free!</span>' ) . '</code>' ) . '</p>',
				'id'       => 'onway_wc_custom_shipping_method_replace_zero_cost_text',
				'default'  => '',
				'type'     => 'text',
				'onway_wc_csm_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'onway_wc_custom_shipping_method_frontend_options',
			),
		);

		$icon_and_desc_settings = array(
			array(
				'title'    => __( 'Icons & Descriptions Settings', 'onway-shipping-method-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'onway_wc_custom_shipping_method_icon_and_desc_options',
				'desc'     => sprintf( __( 'Visit %s to set each method\'s icon and description.', 'onway-shipping-method-for-woocommerce' ),
					'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping' ) . '">' .
						__( 'WooCommerce > Settings > Shipping', 'onway-shipping-method-for-woocommerce' ) . '</a>' ),
			),
			array(
				'title'    => __( 'Icons & Descriptions', 'onway-shipping-method-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'onway-shipping-method-for-woocommerce' ) . '</strong>',
				'desc_tip' => apply_filters( 'onway_wc_custom_shipping_method_settings', sprintf( __( 'You will need %s plugin to set icons and descriptions.', 'onway-shipping-method-for-woocommerce' ),
					'<a target="_blank" href="https://wpfactory.com/item/custom-shipping-methods-for-woocommerce/">' .
						__( 'Custom Shipping Methods for WooCommerce Pro', 'onway-shipping-method-for-woocommerce' ) . '</a>' ) ),
				'id'       => 'onway_wc_custom_shipping_method_icon_desc_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'onway_wc_custom_shipping_method_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Icon template', 'onway-shipping-method-for-woocommerce' ),
				'desc'     => sprintf( __( 'Placeholders: %s', 'onway-shipping-method-for-woocommerce' ), '<code>%icon_url%</code>' ),
				'id'       => 'onway_wc_custom_shipping_method_icon_template',
				'default'  => '<img style="display:inline;" src="%icon_url%">',
				'type'     => 'textarea',
				'css'      => 'width:100%',
				'onway_wc_csm_raw' => true,
			),
			array(
				'title'    => __( 'Description template', 'onway-shipping-method-for-woocommerce' ),
				'desc'     => sprintf( __( 'Placeholders: %s', 'onway-shipping-method-for-woocommerce' ), '<code>%desc_text%</code>' ),
				'id'       => 'onway_wc_custom_shipping_method_desc_template',
				'default'  => '<p style="font-size:small;font-style:italic;">%desc_text%</p>',
				'type'     => 'textarea',
				'css'      => 'width:100%',
				'onway_wc_csm_raw' => true,
			),
			array(
				'title'    => __( 'Final template', 'onway-shipping-method-for-woocommerce' ),
				'desc'     => sprintf( __( 'Placeholders: %s', 'onway-shipping-method-for-woocommerce' ), '<code>%icon%</code>, <code>%desc%</code>, <code>%label%</code>' ),
				'id'       => 'onway_wc_custom_shipping_method_icon_desc_template',
				'default'  => '%icon%%label%%desc%',
				'type'     => 'textarea',
				'css'      => 'width:100%',
				'onway_wc_csm_raw' => true,
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'onway_wc_custom_shipping_method_icon_and_desc_options',
			),
		);

		return array_merge( $plugin_settings, $admin_settings, $frontend_settings, $icon_and_desc_settings );
	}

}

endif;

return new onway_WC_Custom_Shipping_Method_Settings_General();
