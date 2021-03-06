<?php
/**
 * For handling processing the request tailor meta through the checkout
 */
Class ADM_RAT_Checkout{

	/**
	 * @var mixed
	 */
	private $request_value = null;

    /**
     * Construcdur :)
     */
    public function __construct(){
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_request_tailor_data_to_order_items' ), 10, 4 );

		add_filter( 'woocommerce_gateway_description', array( $this, 'add_note_to_cod_payment' ), 10, 2 );
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
		$cart_item_data = adm_pk_woo_get_item_data($cart_item_key);
		$request_value = isset( $cart_item_data['_adm_request_tailor'] ) ? trim( $cart_item_data['_adm_request_tailor'] ) : '';
		if ( empty( $request_value ) ) {
			return;
		}
		$this->request_value = $request_value;
		// Only add when its true.
		if( $request_value === 'true' ){
			//$cloth_type = '<span class="adm-cloth-type">'.$cart_item_data['_cloth_type'].'</span>';
			$value = '<span class="adm-unit">'.( $request_value === 'true' ? 'Yes' : 'No' ).'</span>';
			//done now add :)
			$item->add_meta_data( __( '_request_tailor', ADM_RAT_TEXT_DOMAIN ), $value );
		}
		
		// hook filter since request tailor has been in it
		/**
		 * A template like function which sets bool value on item for request tailor
		 * @param mixed 				$value
		 * @param WC_Order_Item_Product $item
		 * @param string                $cart_item_key
		 * @hook filter 				adm_pk_item_request_tailor
		 * @return 						bool
		 */
		add_filter( 'adm_pk_order_item_request_tailor', function( $value, $item, $cart_item_key ){
			$value = ( $this->request_value === 'true' ? true : false );
			return $value;
		}, 10, 3 );

	}
	
	/**
	 * Adds extra note to the Cash on Delivery method. 
	 *
	 * To display an extra note only for customers with request a tailor enabled
	 *
	 * @param WC_Order_Item_Product $item
	 * @param string                $description
	 * @param string                 $gateway_id
	 * @param WC_Order              $order
 	 */	
	public function add_note_to_cod_payment( $description, $gateway_id ) {
		if( $gateway_id !== 'cod' ){
			return $description;
		}
		
		// It's COD, continue
		foreach(WC()->cart->get_cart() as $cart_item ){
            $item = adm_pk_woo_get_item_data($cart_item['key']);
            $request_tailor = isset( $item['_adm_request_tailor'] ) ? $item['_adm_request_tailor'] : null;
			
			if ( $request_tailor === 'true' ){ // Found one, update description
				$description .= apply_filters( 'adm_rat_cod_note', ' <strong>Only available for those requesting Tailor Measurement and 20% down payment required after measurement.</strong>', $description, $gateway_id );
				break; // Break loop, no longer needed, saves time.
			}
		}
		return $description;
	}

}
new ADM_RAT_Checkout();