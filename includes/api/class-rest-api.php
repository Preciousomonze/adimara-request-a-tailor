<?php
/**
 * Ajax and Api stuff here
 */
class ADM_RAT_REST_Api extends WP_REST_Controller{

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'adm_rat/v1/';

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
    protected $rest_base = 'measurement';

    public function __construct(){
        /*api calling */
        add_action( 'rest_api_init', array($this,'register_routes'));
    }

    /**
     * Registers routes :)
     */
    public function register_routes() {
        register_rest_route( $this->namespace, 'request_tailor/', array(
        'methods' => $this->method,
        'callback' => array($this,'submit_request_tailor'),
        'permission_callback' => array($this,'get_permission')
        ) );
    }
    
    /**
     * Handles Submission of request tailor
     * 
     * @return void
     */
    public function submit_request_tailor(){
        $_apply_to_similar = (isset($_GET["_adm_apply_to_similar"]) ? $_GET["_adm_apply_to_similar"] : "" );
        //$cart_items = json_decode( adm_pk_serializer( sanitize_text_field( str_replace('\\"','"',$_GET['c_d']) ), false) , true);
        $cart_items = json_decode( str_replace('\\"','', adm_pk_serializer( sanitize_text_field( rawurldecode($_GET['c_d']) ), false) ) , true);
        $wc_cart_session = adm_pk_serializer(sanitize_text_field( rawurldecode($_GET['c_s']) ), false); //deserialise again
        $p_id = $_GET['adm_product_id'];
        
        //calling some useful hommies in the cart_features.php file
        add_filter('woocommerce_add_cart_item_data','adm_pk_add_measurement',10,8);
        //now check if the apply to similar wasnt checked
        $got_value == false;
        $_the_scripts = '';
        if($_apply_to_similar != "yes"){//only update this item
            foreach($cart_items as $cart_item){
                if($cart_item['product_id'] == $p_id){//what we're looking for
                   /* $cart_item_data,$product_id,$variation_id,$measure_vals,$unit,$apply_to,$cloth_type,$cart_session */
                 $value = apply_filters('woocommerce_add_cart_item_data',$cart_item,$p_id,$v_id,$input_vals,$unit,$_apply_to_similar,$cloth_type,$wc_cart_session);
                 $got_value = true;
                 //update the button color
                 $_the_scripts = "$('#_adm-".$p_id."-".$security."').removeClass('adm-not-measured-look');";
                 $_the_scripts .= "$('#_adm-".$p_id."-".$security."').addClass('adm-measured-look');";
                 break;//no longer need, break
                }
            }
    
        }
        else{//all
            foreach($cart_items as $cart_item){
                $c_p_id = $cart_item['product_id'];
                    /* $cart_item_data,$product_id,$variation_id,$measure_vals,$unit,$apply_to,$cloth_type */
                   apply_filters('woocommerce_add_cart_item_data',$cart_item,$c_p_id,$v_id,$input_vals,$unit,$_apply_to_similar,$cloth_type,$wc_cart_session);
                 /*$cart_item_data,$cart_item_session_data, $cart_item_key*/
                 $got_value = true;
                   //update the button color
                   $_the_scripts .= "$('#_adm-".$c_p_id."-".$security."').removeClass('adm-not-measured-look');";
                   $_the_scripts .= "$('#_adm-".$c_p_id."-".$security."').addClass('adm-measured-look');";
                 
            }
        }
        if($got_value){//hurray,kill script to prevent that weird null coming out
            //run script to update the btn colour
            echo '<script type="text/javascript">'.$_the_scripts.'</script>';
            die();
        }
        else{//error
            wp_die();
        }
        //end :)
    
        
        
        $msg = 'You are qualified to redeem voucher code'.($count_u > 1 ? 's' : '').' ';
        $msg .= ' from loystar.';
        return new WP_REST_Response($msg,200);
        //to be continued
    }

    /**
	 * Checks if the api request is valid 
	 * @return bool true if valid,false otherwise
	 */
    public function get_permission(){
        $result = true;
        $nonce = $_POST['nonce'];
        if(wp_verify_nonce( $nonce, 'wp_rest' ) == false)
            $result = false;
        return $result;
    }
}
new ADM_RAT_REST_Api();