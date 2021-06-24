<?php
/**
 * Custom Shipping Methods for WooCommerce - Custom Shipping Settings
 *
 * @version 1.7
 * @since   1.0.0
 * @author  Tyche Softwares
 * @todo    [dev] (maybe) select dropdown for "Include/Exclude Cats/Tags"
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$availability_extra_desc_zero  = ' ' . __( 'Ignored if set to zero.', 'onway-shipping-method-for-woocommerce' );
$availability_extra_desc_blank = ' ' . __( 'Ignored if empty.', 'onway-shipping-method-for-woocommerce' );
$availability_extra_desc_disabled = ' ' . __( 'Ignored if status is disabled.', 'onway-shipping-method-for-woocommerce' );
$pro_link                      = '<a target="_blank" href="https://wpfactory.com/item/custom-shipping-methods-for-woocommerce/">' . 'Custom Shipping Methods for WooCommerce Pro' . '</a>';
$pro_desc_short                = sprintf( '.<br><em>' . 'This shortcode is available in %s plugin only' . '</em>', $pro_link );
$pro_desc                      = sprintf( 'This option is available in %s plugin only.', $pro_link );

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
		'description'       => __( 'Shipping method cost without conditional logic' ),
		'default'           => '0',
		'css'               => 'width:100%',
	),
	'conditional_cost' => array(
		'title'             => __( 'Conditional cost', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'select',
		'class'             => 'wc-enhanced-select',
		'default'           => 'disabled',
		'options'           => array(
			'enabled' => __( 'Enabled', 'onway-shipping-method-for-woocommerce' ),
			'disabled'=> __( 'Disabled', 'onway-shipping-method-for-woocommerce' ),
		),
		'css'               => 'width:100%',
	),
) );

// Conditional shipping price fields
for ( $i = $this->weight_steps; $i <= $this->max_weight; $i += $this->weight_steps ) {
	$conditional_shipping_prices['weight_below_'.$i.'_kg'] = array(
		'title'		=> __( "Price below $i kg", "onway-shipping-method-for-woocommerce" ),
		'type'		=> 'number',
		'default'	=> $i,
		'id'			=> 'onway_wc_custom_shipping_method_weight_below_'.$i.'_kg'
	);
}

$settings = array_merge( $settings, $conditional_shipping_prices );

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
	'express_title'	=> array(
		'title'							=> __( 'Express delivery label', 'onway-shipping-method-for-woocommerce' ),
		'type'							=> 'text',
		'default'						=> __( 'Express Delivery', 'onway-shipping-method-for-woocommerce' ),
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
		'description'       => __( 'Additional cost for Express delivery.', 'onway-shipping-method-for-woocommerce' ),
		'default'           => '0',
		'css'               => 'width:100%',
	),
) );

// Delivery Dates
$settings = array_merge( $settings, array(
	'delivery_dates' => array(
		'title'             => __( 'Delivery Dates', 'onway-shipping-method-for-woocommerce' ),
		'type'              => 'title',
		'default'           => '',
		'description'       => __( 'Specific delivery dates for shipping method.', 'onway-shipping-method-for-woocommerce' ),
	),
) );

for ( $i = 0; $i < 7; $i++ ) {
	$day = jddayofweek($i, 1);
	$lcDay = lcfirst($day);
	$conditional_shipping_days[$lcDay.'_delivery_logic'] = array(
		'title'		=> __( "$day logic", "onway-shipping-method-for-woocommerce" ),
		'type'		=> 'number',
		'default'	=> 1,
		'id'			=> 'onway_wc_custom_shipping_method_'.$lcDay.'_delivery_logic'
	);
}

$settings = array_merge( $settings, $conditional_shipping_days );

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

$this->form_fields = $settings;

return $settings;
