<?php
/**
 * Cart
 */
class ADM_RAT_Cart{

    /**
     * Construcdur :)
     */
    public function __construct(){
    
	}

    /**
     * For extra custom validation
     * 
     * @param int         $user_id User ID being saved.
     * @param string      $load_address Type of address e.g. billing or shipping.
     * @hook filter adm_pk_add_item_data;
	 * @return array
     */
    public function add_item_data( $item_data, $cart_item ){
        // Create the wp_nonce.
        // Set Your Nonce
        global $adm_pk_ajax_nonce;// the ajax nonce variable
        $ajax_nonce = wp_create_nonce( $adm_pk_ajax_nonce );
        //check if the item has been measured already
        $measured_class = 'adm-not-measured-look';
        $measure_data = adm_pk_woo_get_item_data($cart_item['key']);
		$js_tailor_delete_request = false;
        if( isset($measure_data) && $measure_data['_adm_request_tailor'] === true ){
            $measured_class = 'adm-measured-look';
			$js_tailor_delete_request = true;
        }
        $item_data[] = array(
		    'key'     => __( 'adm_rat_btn', ADM_PK_TEXT_DOMAIN ),
		    'value'   => '<a id="_adm_rat-'.$product_id.'-'.$ajax_nonce.'-'.$js_tailor_delete_request.'" href="#adm-rat-request" class="btn btn-feel adm-measure-btn '.$measured_class.'" title="Request a tailor and skip entering measurement.">Request a Tailor</a>',
            'display' => '',
        );
		return $item_data;
    }
	
	
	 /**shows popup in cart */
	function item_data_script(){ 
		global $adm_pk_rest_measurement_link,$adm_pk_ajax_nonce;
		$wc_cart_data = WC()->cart->get_cart();
		$wc_cart_session = WC()->session;
		$s_wc_cart_data = adm_pk_serializer( json_encode($wc_cart_data) );
		$s_wc_cart_session = adm_pk_serializer($wc_cart_session);
		?>
		<script>
			//holds the ajax url
			var adm_rat_request_tailor_submit = <?php echo wp_json_encode(rest_url('adm_rat/v1/measurement/request_tailor/'));?>;
		/**
		 * for loading the measurements
		 * @param int product_id
		 * @param string nonce the wp stuff
		 * @param bool cartRequestTailor (optional) if request tailor is for all cart items, default is false
		 * @param bool deleteRequest (optiona) if true, a submitted tailor request will be reverted
		 */
		function adm_rat_submit_tailor_request( product_id, nonce, cartRequestTailor = false, deleteRequest = false){
			$('.adm-measure-pop').fadeIn('slow');
			$('.adm-measure-pop .inside').append('<div class="blanket center"><a href="#no-click" class="adm-nice-load-feel"><i class="fa fa-gear fa-spin fa-2x"></i></a> </div>');
			//for the nice load stuff
			$('.adm-nice-load-feel').click(function(e){
				e.preventDefault();
			});

			$.ajax( {
				url: adm_rat_request_tailor_submit,
						method: 'POST',
						dataType: 'html',
				<?php if(is_user_logged_in()){
				echo"
				beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', nonce );
				},";
				}
				?>
				data:{
					'adm_product_id': product_id,
					'security' : nonce,
					'c_d': '<?php echo $s_wc_cart_data; ?>',
					'c_s': '<?php echo $s_wc_cart_session; ?>'
					'adm_request_tailor_all': cartRequestTailor
				}
			} )
			.done(function(response) {
				$('#whole-to-load').html(response);
			} )
			.fail( function() {
				alert( "An error occured, please try again later or reload the page if the problem persists." );
			})
			.always(function(response) {
				$('.adm-measure-pop .blanket').remove();
			});

		}

	</script>
	<?php 
	}
 
}
new ADM_RAT_Cart();