<?php
/**
 * Ajax and Api stuff here
 */
class ADM_RAT_REST extends WP_REST_Controller{

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'adm_rat/v1';

    /**
     * METHOD
     *
     * @var string
     */
    public $method = 'POST';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = '/measurement/';

    public function __construct(){
        /*api calling */
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Registers routes :)
     */
    public function register_routes() {
        register_rest_route( $this->namespace, $this->rest_base.'request_tailor/', array(
        'methods' => $this->method,
        'callback' => array( $this, 'submit_request_tailor' ),
        'permission_callback' => array( $this,'get_permission' )
        ) );
	}
    
    /**
     * Handles Submission of request tailor
     * 
     * @return void
     */
    public function submit_request_tailor(){
		global $adm_pk_prod_cat;
        $_apply_to_all = (isset($_POST["adm_request_tailor_all"]) ? $_POST["adm_request_tailor_all"] : false );
        $delete_request = (isset($_POST["adm_remove_request"]) ? $_POST["adm_remove_request"] : false );
		$cart_items = json_decode( str_replace('\\"','', adm_pk_serializer( sanitize_text_field( rawurldecode($_POST['c_d']) ), false) ) , true);
        $wc_cart_session = adm_pk_serializer(sanitize_text_field( rawurldecode($_POST['c_s']) ), false); //deserialise again
        $p_id = $_POST['adm_product_id'];

		//add filter to set request tailor to respective value
		add_filter( 'adm_pk_item_request_tailor', function( $cart_item_data ){
			global $delete_request;
			//if its a delete request, set request tailor to false, else true
			return ( $delete_request === true ? 'false' : 'true' );
		}, 10, 1 );

        // Calling some useful hommies in the cart_features.php file in main adimara measurement plugin :)
        add_filter( 'adm_rat_woocommerce_add_cart_item_data', 'adm_pk_add_measurement', 10, 8 );
        
		// Now check if the apply to similar wasnt checked.
        $got_value == false;
        $_the_scripts = '';
        if($_apply_to_all !== true){//only update this item
            foreach($cart_items as $cart_item){
				
                if($cart_item['product_id'] == $p_id){//what we're looking for
					/* $cart_item_data,$product_id,$variation_id,$measure_vals,$unit,$apply_to,$cloth_type,$cart_session */
					apply_filters('adm_rat_woocommerce_add_cart_item_data',$cart_item,$p_id,$v_id,$input_vals,$unit,$_apply_to_similar,$cloth_type,$wc_cart_session);
					$got_value = true;
					// Update the button color for "enter measurement" and "request a tailor"
					$_the_scripts .= "$('#_adm_rat-".$c_p_id."-".$security."-".$delete_request."').removeClass('adm-not-measured-look');";
					$_the_scripts .= "$('#_adm_rat-".$c_p_id."-".$security."-".$delete_request."').addClass('adm-measured-look');";
					break;//no longer need, break
                }
            }
    
        }
        else{//all
            foreach($cart_items as $cart_item){
                $c_p_id = $cart_item['product_id'];
				
				// Now check if the product meta '_m_sub_cat' exists to know its a measurement ish product.
				$meta_val = trim( get_post_meta( $c_p_id, $adm_pk_prod_cat, true ) );
				
				if( !empty( $meta_val ) ){
					/* $cart_item_data,$product_id,$variation_id,$measure_vals,$unit,$apply_to,$cloth_type */
					apply_filters('woocommerce_add_cart_item_data',$cart_item,$c_p_id,$v_id,$input_vals,$unit,$_apply_to_similar,$cloth_type,$wc_cart_session);
					/*$cart_item_data,$cart_item_session_data, $cart_item_key*/
					$got_value = true;
					// Update the button color for "enter measurement" and "request a tailor"
					$_the_scripts .= "$('#_adm_rat-".$c_p_id."-".$security."-".$delete_request."').removeClass('adm-not-measured-look');";
					$_the_scripts .= "$('#_adm_rat-".$c_p_id."-".$security."-".$delete_request."').addClass('adm-measured-look');";
				}
				
            }
        }
        if($got_value){//hurray,kill script to prevent that weird null coming out
            // Run script to update the btn colour.
            echo '<script type="text/javascript">'.$_the_scripts.'</script>';
            die();
        }
        else{//error
            wp_die();
        }
        //end :)
    }

    /**
	 * Checks if the api request is valid 
	 * @return bool true if valid,false otherwise
	 */
	public function get_permission(){
		global $adm_pk_ajax_nonce;
		$result = true;
		$nonce = $_REQUEST['security'];//use this since we're using both get and post
		if( is_user_logged_in() ){
			if( wp_verify_nonce( $nonce, $adm_pk_ajax_nonce ) == false ){
				$result = false;
			}
		}
		return $result;
	}
}
new ADM_RAT_REST();