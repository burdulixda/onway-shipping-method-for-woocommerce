<?php
/**
 * Custom Shipping Methods for WooCommerce - Custom Shipping Class
 *
 * @version 1.6.2
 * @since   1.0.0
 * @author  Tyche Softwares
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Shipping_Onway_Custom' ) ) :

class WC_Shipping_Onway_Custom extends WC_Shipping_Method {

	/** @var string cost passed to [fee] shortcode */
	protected $fee_cost = '';

	/**
	 * Constructor.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 * @param   int $instance_id
	 * @todo    [feature] add free shipping **coupon** functionality
	 */
	function __construct( $instance_id = 0 ) {
		$this->id                    = 'onway_wc_shipping';
		$this->instance_id           = absint( $instance_id );
		$this->method_title          = get_option( 'onway_wc_custom_shipping_method_admin_title', __( 'Onway shipping', 'onway-shipping-method-for-woocommerce' ) );
		$this->method_description    = __( 'Custom shipping method.', 'onway-shipping-method-for-woocommerce' );
		$this->supports              = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		$this->max_weight 					 = get_option( 'onway_wc_custom_shipping_method_max_weight', '0' );
		$this->weight_steps					 = get_option( 'onway_wc_custom_shipping_method_weight_steps', '0' );

		$this->init();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * init user set variables.
	 *
	 * @version 1.6.2
	 * @since   1.0.0
	 * @todo    [feature] customizable admin title and description (i.e. per method instance)
	 */
	function init() {
		$this->instance_form_fields     = include( 'settings/settings-custom-shipping.php' );
		$this->title                    = $this->get_option( 'title' );
		$this->conditional_cost					= $this->get_option( 'conditional_cost' );
		$this->express_delivery_status	= $this->get_option( 'express_delivery_status' );
		$this->express_delivery_price		= $this->get_option( 'express_delivery_price' );

		for ( $i = $this->weight_steps; $i <= $this->max_weight; $i += $this->weight_steps ) {
			$this->{'weight_below_'."{$i}".'_kg'} = $this->get_option( 'weight_below_'.$i.'_kg', 0 );

			$this->weight_based_cost[$i] = $this->get_option( 'weight_below_'.$i.'_kg', 0 );
		}

		// for ( $i = 0; $i < 7; $i++ ) {
		// 	$day = jddayofweek($i, 1);
		// 	$day = lcfirst($day);

		// 	$this->{"{$day}" . '_delivery_logic'} = $this->get_option( $day . '_delivery_logic', 0 );

		// 	$this->conditional_delivery_date[$day] = $this->get_option( $day . '_delivery_logic', 0 );
		// }

		$this->monday_delivery_logic = $this->get_option( 'monday_delivery_logic', 0 );

	}

	/**
	 * get_package_products_data
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function get_package_products_data( $products, $type = 'product' ) {
		// Product IDs
		$product_ids = wp_list_pluck( $products, 'product_id' );
		if ( 'product' === $type ) {
			return $product_ids;
		}
		// Cats & Tags IDs
		$result = array();
		foreach ( $product_ids as $product_id ) {
			$product_terms = get_the_terms( $product_id, $type );
			if ( $product_terms && ! is_wp_error( $product_terms ) ) {
				$result = array_merge( $result, wp_list_pluck( $product_terms, 'term_id' ) );
			}
		}
		return $result;
	}

	/**
	 * is this method available?
	 *
	 * @version 1.6.2
	 * @since   1.0.0
	 * @param   array $package
	 * @return  bool
	 */
	// function is_available( $package ) {
	// 	$available = parent::is_available( $package );
	// 	if ( $available ) {
	// 		// Min/Max
	// 		$conditions = array( 'cost', 'weight', 'volume', 'qty', 'distance' );
	// 		foreach ( $conditions as $condition ) {
	// 			$min = 'min_' . $condition;
	// 			$max = 'max_' . $condition;
	// 			if ( 0 != $this->{$min} || 0 != $this->{$max} ) {
	// 				switch ( $condition ) {
	// 					case 'cost':
	// 						$total = $package['contents_cost'];
	// 						break;
	// 					case 'weight':
	// 						$total = onway_wc_custom_shipping_method()->core->get_package_item_weight( $package );
	// 						break;
	// 					case 'volume':
	// 						$total = onway_wc_custom_shipping_method()->core->get_package_item_volume( $package );
	// 						break;
	// 					case 'qty':
	// 						$total = $this->get_package_item_qty( $package );
	// 						break;
	// 					case 'distance':
	// 						add_shortcode( 'distance', array( onway_wc_custom_shipping_method()->core, 'distance' ) );
	// 						$total = do_shortcode( $this->distance_calculation );
	// 						break;
	// 					default:
	// 						$total = 0;
	// 				}
	// 				if ( ( 0 != $this->{$min} && $total < $this->{$min} ) || ( 0 != $this->{$max} && $total > $this->{$max} ) ) {
	// 					return false;
	// 				}
	// 			}
	// 		}
	// 		// Include/Exclude
	// 		$conditions = array( 'product', 'product_cat', 'product_tag' );
	// 		foreach ( $conditions as $condition ) {
	// 			$include = 'incl_' . $condition;
	// 			$exclude = 'excl_' . $condition;
	// 			$include = trim( $this->{$include} );
	// 			$exclude = trim( $this->{$exclude} );
	// 			if ( '' != $include || '' != $exclude ) {
	// 				$package_products = $this->get_package_products_data( $package['contents'], $condition );
	// 				if ( ! empty( $package_products ) ) {
	// 					$package_products   = array_unique( $package_products );
	// 					$_include           = array_unique( array_map( 'trim', explode( ',', $include ) ) );
	// 					$_exclude           = array_unique( array_map( 'trim', explode( ',', $exclude ) ) );
	// 					$_include_intersect = array_intersect( $_include, $package_products );
	// 					$_exclude_intersect = array_intersect( $_exclude, $package_products );
	// 					if (
	// 						( '' != $include && (
	// 							( 'one'       === $this->require_type && empty( $_include_intersect ) ) ||
	// 							( 'one_only'  === $this->require_type && count( $_include_intersect ) != count( $package_products ) ) ||
	// 							( 'all'       === $this->require_type && count( $_include_intersect ) != count( $_include ) ) ||
	// 							( 'all_only'  === $this->require_type &&
	// 								( count( $_include_intersect ) != count( $_include ) || count( $_include_intersect ) != count( $package_products ) )
	// 							)
	// 						) ) ||
	// 						( '' != $exclude && ! empty( $_exclude_intersect ) )
	// 					) {
	// 						return false;
	// 					}
	// 				}
	// 			}
	// 		}
	// 	}
	// 	return $available;
	// }

	/**
	 * work out fee (shortcode).
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @param   array $atts
	 * @return  string
	 */
	function fee( $atts ) {
		$atts = shortcode_atts( array(
			'percent' => '',
			'min_fee' => '',
			'max_fee' => '',
		), $atts, 'fee' );

		$calculated_fee = 0;

		if ( $atts['percent'] ) {
			$calculated_fee = $this->fee_cost * ( floatval( $atts['percent'] ) / 100 );
		}

		if ( $atts['min_fee'] && $calculated_fee < $atts['min_fee'] ) {
			$calculated_fee = $atts['min_fee'];
		}

		if ( $atts['max_fee'] && $calculated_fee > $atts['max_fee'] ) {
			$calculated_fee = $atts['max_fee'];
		}

		return $calculated_fee;
	}

	function get_conditional_shipping_price( $weight ) {
		foreach ( $this->weight_based_cost as $max_conditional_weight => $conditional_price ) {
			if ( $max_conditional_weight >= $weight ) {
				return $this->weight_based_cost[$max_conditional_weight];
			}
		}
	}

	function get_conditional_shipping_dates() {
		$conditional_dates = array();

		for ( $i = 0; $i < 7; $i++ ) {
			$day = jddayofweek( $i, 1 );
			
			$conditional_dates[$day] = $this->get_option( lcfirst($day) . '_delivery_logic', 0 );
		}

		return $conditional_dates;

	}

	/**
	 * calculate_shipping function.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 * @param   array $package (default: array())
	 * @todo    [feature] add "Free shipping calculation" option: "per class" (i.e. per package, as it is now), "per order" (i.e. total sum) and maybe "all"
	 */
	function calculate_shipping( $package = array() ) {
		$weight = 0;

		foreach ( $package['contents'] as $item_id => $values ) {
			$_product = $values['data'];
			$weight = $weight + $_product->get_weight() * $values['quantity'];
		}

		$weight = wc_get_weight( $weight, 'kg' );

		$rate = array(
			'id'      => $this->get_rate_id(),
			'label'   => $this->title,
			'cost'    => 0,
			'package' => $package,
		);

		// Calculate the costs
		$has_costs = false; // True when a cost is set. False if all costs are blank strings.
		$cost      = $this->get_option( 'cost' );

		if ( $this->conditional_cost === 'enabled' ) {
			$cost = $this->get_conditional_shipping_price( $weight );
		}

		$has_costs = true;
		$rate = array(
			'id'		=> $this->id,
			'label'	=> $this->title,
			'cost'	=> $cost,
			'meta_data' => $this->get_conditional_shipping_dates()
		);

		// Limits
		// if ( in_array( $this->limit_calc, array( 'order', 'all' ) ) ) {
		// 	$rate['cost'] = apply_filters( 'onway_wc_custom_shipping_method_min_max_limits', $rate['cost'], $this );
		// }

		// Add the rate
		if ( $has_costs ) {
			$this->add_rate( apply_filters( 'onway_wc_custom_shipping_method_add_rate', $rate, $package, $this ) );
		}

		/**
		 * Developers can add additional rates based on this one via this action
		 *
		 * This example shows how you can add an extra rate based on this rate via custom function:
		 *
		 * 		add_action( 'woocommerce_onway_wc_shipping_shipping_add_rate', 'add_another_custom_rate', 10, 2 );
		 *
		 * 		function add_another_custom_rate( $method, $rate ) {
		 * 			$new_rate          = $rate;
		 * 			$new_rate['id']    .= ':' . 'custom_rate_name'; // Append a custom ID.
		 * 			$new_rate['label'] = 'Rushed Shipping'; // Rename to 'Rushed Shipping'.
		 * 			$new_rate['cost']  += 2; // Add $2 to the cost.
		 *
		 * 			// Add it to WC.
		 * 			$method->add_rate( $new_rate );
		 * 		}.
		 */
		do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $rate );
	}

	/**
	 * get items in package.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @param   array $package
	 * @return  int
	 */
	function get_package_item_qty( $package ) {
		$total_quantity = 0;
		foreach ( $package['contents'] as $item_id => $values ) {
			if ( $values['quantity'] > 0 && $values['data']->needs_shipping() ) {
				$total_quantity += $values['quantity'];
			}
		}
		return $total_quantity;
	}

	/**
	 * finds and returns shipping classes and the products with said class.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @param   mixed $package
	 * @return  array
	 */
	function find_shipping_classes( $package ) {
		$found_shipping_classes = array();

		foreach ( $package['contents'] as $item_id => $values ) {
			if ( $values['data']->needs_shipping() ) {
				$found_class = $values['data']->get_shipping_class();

				if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
					$found_shipping_classes[ $found_class ] = array();
				}

				$found_shipping_classes[ $found_class ][ $item_id ] = $values;
			}
		}

		return $found_shipping_classes;
	}

}

endif;