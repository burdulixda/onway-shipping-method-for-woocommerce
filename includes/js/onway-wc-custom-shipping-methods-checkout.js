/**
 * alg-wc-custom-shipping-methods-checkout.js
 *
 * @version 1.4.1
 * @since   1.4.0
 * @todo    [dev] maybe limit to exact fields only, i.e. `billing_city`, `billing_address_1` etc. `shipping_city`, `shipping_address_1` etc.
 */

jQuery( 'body' ).on( 'change', 'input[name^="billing_"], input[name^="shipping_"]', function() {
	jQuery( 'body' ).trigger( 'update_checkout' );
} );
