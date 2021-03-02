<?php
/**
 * Custom Shipping Methods for WooCommerce - Custom Shipping Settings
 *
 * @version 1.6.2
 * @since   1.0.0
 * @author  Tyche Softwares
 * @todo    [dev] (maybe) select dropdown for "Include/Exclude Cats/Tags"
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$globalShippingSettings = new Onway_WC_Custom_Shipping_Method_Settings_General();
$globalShippingSettingsArray = $globalShippingSettings->get_allowed_shipping_methods();
$globalShippingMaxWeight = $globalShippingSettingsArray['max_weight']['default'];

print_r($globalShippingMaxWeight);

$availability_extra_desc_zero  = ' ' . __( 'Ignored if set to zero.', 'onway-shipping-method-for-woocommerce' );
$availability_extra_desc_blank = ' ' . __( 'Ignored if empty.', 'onway-shipping-method-for-woocommerce' );
$availability_extra_desc_disabled = ' ' . __( 'Ignored if status is disabled.', 'onway-shipping-method-for-woocommerce' );
$pro_link                      = '<a target="_blank" href="https://wpfactory.com/item/custom-shipping-methods-for-woocommerce/">' . 'Custom Shipping Methods for WooCommerce Pro' . '</a>';
$pro_desc_short                = sprintf( '.<br><em>' . 'This shortcode is available in %s plugin only' . '</em>', $pro_link );
$pro_desc                      = sprintf( 'This option is available in %s plugin only.', $pro_link );

$available_shortcodes = apply_filters( 'onway_wc_custom_shipping_method_shortcodes_desc', array(
	'[qty]'                                           => __( 'number of items', 'onway-shipping-method-for-woocommerce' ),
	'[cost]'                                          => __( 'total cost of items', 'onway-shipping-method-for-woocommerce' ),
	'[weight]'                                        => __( 'total weight of items', 'onway-shipping-method-for-woocommerce' ),
	'[volume]'                                        => __( 'total volume of items', 'onway-shipping-method-for-woocommerce' ),
	'[fee percent="10" min_fee="20" max_fee=""]'      => __( 'percentage based fees', 'onway-shipping-method-for-woocommerce' ),
	'[round]'                                         => __( 'rounding', 'onway-shipping-method-for-woocommerce' ) .
		sprintf( ' (' . __( 'check examples %s', 'onway-shipping-method-for-woocommerce' ) . ')',
			'<a target="_blank" href="https://wpfactory.com/item/custom-shipping-methods-for-woocommerce/#round-shortcode">' .
				__( 'here', 'onway-shipping-method-for-woocommerce' ) . '</a>' ),
) );
$cost_desc = '';
$cost_desc .= '<ul>';
foreach ( $available_shortcodes as $shortcode => $shortcode_desc ) {
	$cost_desc .= "<li>* <code>{$shortcode}</code> - {$shortcode_desc}.</li>";
}
$cost_desc .= '</ul>';

// General settings
$settings = array(
	'title' => array(
		'title'             => __( 'Method title', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'text',
		'description'       => __( 'This controls the title which the user sees during checkout.', 'onway-shipping-method-for-woocommerce' ),
		'default'           => __( 'Onway shipping', 'onway-shipping-method-for-woocommerce' ),
		'desc_tip'          => true,
		'css'               => 'width:100%',
	),
);
$settings = array_merge( $settings, array(
	'tax_status' => array(
		'title'             => __( 'Tax status', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'select',
		'class'             => 'wc-enhanced-select',
		'default'           => 'taxable',
		'options'           => array(
			'taxable' => __( 'Taxable', 'onway-shipping-method-for-woocommerce' ),
			'none'    => _x( 'None', 'Tax status', 'onway-shipping-method-for-woocommerce' ),
		),
		'css'               => 'width:100%',
	),
	'cost' => array(
		'title'             => __( 'Cost', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'text',
		'placeholder'       => '',
		'description'       => $cost_desc,
		'default'           => '0',
		'desc_tip'          => __( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'onway-shipping-method-for-woocommerce' ),
		'css'               => 'width:100%',
	),
) );

// Shipping class settings
$shipping_classes = WC()->shipping->get_shipping_classes();
if ( ! empty( $shipping_classes ) ) {
	$settings['class_costs'] = array(
		'title'             => __( 'Shipping class costs', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'title',
		'default'           => '',
		'description'       => sprintf( __( 'These costs can optionally be added based on the <a href="%s">product shipping class</a>.', 'onway-shipping-method-for-woocommerce' ),
			admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) ),
	);
	foreach ( $shipping_classes as $shipping_class ) {
		if ( ! isset( $shipping_class->term_id ) ) {
			continue;
		}
		$settings[ 'class_cost_' . $shipping_class->term_id ] = array(
			/* translators: %s: shipping class name */
			'title'             => sprintf( __( '"%s" shipping class cost', 'onway-shipping-method-for-woocommerce' ), esc_html( $shipping_class->name ) ),
			'type'              => 'text',
			'placeholder'       => __( 'N/A', 'onway-shipping-method-for-woocommerce' ),
			'description'       => __( 'See "Cost" option description above for available options.', 'onway-shipping-method-for-woocommerce' ),
			'default'           => $this->get_option( 'class_cost_' . $shipping_class->slug ),
			'desc_tip'          => true,
			'css'               => 'width:100%',
		);
	}
	$settings['no_class_cost'] = array(
		'title'             => __( 'No shipping class cost', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'text',
		'placeholder'       => __( 'N/A', 'onway-shipping-method-for-woocommerce' ),
		'description'       => __( 'See "Cost" option description above for available options.', 'onway-shipping-method-for-woocommerce' ),
		'default'           => '',
		'desc_tip'          => true,
		'css'               => 'width:100%',
	);
	$settings['type'] = array(
		'title'             => __( 'Calculation type', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'select',
		'class'             => 'wc-enhanced-select',
		'default'           => 'class',
		'options'           => array(
			'class' => __( 'Per class: Charge shipping for each shipping class individually', 'onway-shipping-method-for-woocommerce' ),
			'order' => __( 'Per order: Charge shipping for the most expensive shipping class', 'onway-shipping-method-for-woocommerce' ),
		),
		'css'               => 'width:100%',
	);
	$settings['limit_calc'] = array(
		'title'             => __( 'Limits calculation', 'onway-shipping-method-for-woocommerce' ),
		'desc_tip'          => __( 'For "Min cost limit" and "Max cost limit" options.', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'select',
		'class'             => 'wc-enhanced-select',
		'default'           => 'class',
		'options'           => array(
			'class' => __( 'Per class: Check limits for each shipping class individually', 'onway-shipping-method-for-woocommerce' ),
			'order' => __( 'Per order: Check limits for final cost only', 'onway-shipping-method-for-woocommerce' ),
			'all'   => __( 'All: Check limits for each shipping class individually and then for final cost', 'onway-shipping-method-for-woocommerce' ),
		),
		'css'               => 'width:100%',
	);
}

// Express Delivery
$settings = array_merge( $settings, array(
	'express_delivery' => array(
		'title'             => __( 'Express delivery', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'title',
		'default'           => '',
		'description'       => __( 'Express delivery options.', 'onway-shipping-method-for-woocommerce' ),
	),
	'express_delivery_status' => array(
		'title'             => __( 'Express delivery status', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'select',
		'class'             => 'wc-enhanced-select',
		'default'           => 'disabled',
		'options'           => array(
			'enabled' => __( 'Enabled', 'onway-shipping-method-for-woocommerce' ),
			'disabled'=> __( 'Disabled', 'onway-shipping-method-for-woocommerce' ),
		),
		'css'               => 'width:100%',
	),
	'express_delivery_price' => array(
		'title'             => __( 'Express delivery price', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'text',
		'placeholder'       => '',
		'description'       => __( 'Same shortcodes apply as general cost.', 'onway-shipping-method-for-woocommerce' ),
		'default'           => '0',
		'desc_tip'          => __( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'onway-shipping-method-for-woocommerce' ) . $availability_extra_desc_disabled,
		'css'               => 'width:100%',
	),
) );

// Advanced settings
$settings = array_merge( $settings, array(
	'advanced' => array(
		'title'             => __( 'Advanced', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'title',
		'default'           => '',
		'description'       => __( 'Advanced settings.', 'onway-shipping-method-for-woocommerce' ),
	),
	'return_url' => array(
		'title'             => __( 'Custom return URL', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'text',
		'description'       => __( 'Will be used instead of the standard "Order received" page.', 'onway-shipping-method-for-woocommerce' ) . $availability_extra_desc_blank,
		'default'           => '',
		'desc_tip'          => true,
		'css'               => 'width:100%',
	),
) );

return $settings;
