<?php
/**
 * For handling processing the request tailor meta through the checkout
 */
Class ADM_RAT_Checkout{

    /**
     * Construcdur :)
     */
    public function __construct(){
		
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_request_tailor_data_to_order_items' ), 10, 4 );
    }
    
	/**
	 * Adds request tailor data to the order items
	 *
	 * @param WC_Order_Item_Product $item
	 * @param string                $cart_item_key
	 * @param array                 $values
	 * @param WC_Order              $order
 	 */
	public function add_request_tailor_data_to_order_items( $item, $cart_item_key, $values, $order ) {
		
	}
}
new ADM_RAT_Checkout();