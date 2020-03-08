<?php
/**
 * Cart
 */
class ADM_RAT_CART{

    /**
     * Construcdur :)
     */
    public function __construct(){
        //inherits style and js from the checkout class :)
            add_action('woocommerce_after_save_address_validation', array($this,'account_page_validate'),10,2);
    }

    /**
     * For extra custom validation
     * 
     * @param int         $user_id User ID being saved.
     * @param string      $load_address Type of address e.g. billing or shipping.
     * @hook woocommerce_after_save_address_validation
     */
    public function account_page_validate($user_id,$load_address){
        global $wc_pv_woo_custom_field_meta;
        $phone_name = $wc_pv_woo_custom_field_meta['billing_hidden_phone_field'];
        $phone_err_name = $wc_pv_woo_custom_field_meta['billing_hidden_phone_err_field'];
        $phone_valid_field = strtolower( sanitize_text_field($_POST[$phone_name]) );
        $phone_valid_err_field = trim( sanitize_text_field( $_POST[$phone_err_name] ) );
		$bil_email = sanitize_email($_POST['billing_email']);
        $bil_phone = sanitize_text_field($_POST['billing_phone']);

        if( !empty($bil_email) && !empty($bil_phone) && (empty($phone_valid_field) || !is_numeric($phone_valid_field) ) ){//there was an error, this way we know its coming directly from normal woocommerce, so no conflict :)
         $ph = explode(':',$phone_valid_err_field);
         $ph[0] = '<strong>'.$ph[0].'</strong>';
         $phone_err_msg = implode(':',$ph);
         $out =  __($phone_err_msg, WC_PV_TEXT_DOMAIN );
         wc_add_notice( $out, 'error');
        }
    }
}
new WC_PV_Account();