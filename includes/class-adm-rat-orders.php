<?php
/**
 * For handling processing the request tailor meta through the order
 */
Class ADM_RAT_Orders{

    /**
     * Construcdur :)
     */
    public function __construct(){
		
		add_filter( 'adm_pk_order_item_meta_list', array( $this, 'add_order_item_meta_list' ), 10, 3 );
    }
    
	/**
	 * Adds order item to the meta list
	 *
	 * @param WC_Order_Item_Product $item
	 * @param array                $list_array
	 * @param int                  $item_id
	 * @param mixed                $item
	 * @hook filter adm_pk_order_item_meta_list
	 * @return array
 	 */
	public function add_order_item_meta_list( $list_array, $item_id, $item ) {
		$request_val = wc_get_order_item_meta( $item_id, '_request_tailor', true );
	
		// Overide list array since we dont need the old data
		$list_array = array(
			'Request Tailor' => $request_val
		);
		return $list_array;
	}

}
new ADM_RAT_Orders();