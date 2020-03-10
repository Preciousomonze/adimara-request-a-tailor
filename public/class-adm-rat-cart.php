<?php
/**
 * Cart
 */
class ADM_RAT_Cart{
	
	/**
	 * Keep account of active request tailor items to know if to paint bulk button.
	 * @var int
	 */
	private $rat_active_count = 0;
	
	/**
	 * Keep account of items fit for "request tailor".
	 * @var int
	 */
	private $rat_count = 0;
	
    /**
     * Construcdur :)
     */
    public function __construct(){
		add_action( 'adm_pk_after_popup', array( $this, 'item_data_script' ), 10, 2 );
		add_filter( 'adm_pk_add_item_data', array( $this, 'add_item_data' ), 10, 2 );
		// Woocommerce hook
		add_action( 'woocommerce_cart_actions', array( $this, 'display_bulk_button_to_cart' ), 10 );
	}

    /**
     * For extra custom validation
     * 
     * @param int         $user_id User ID being saved.
     * @param string      $load_address Type of address e.g. billing or shipping.
     * @hook filter 	  adm_pk_add_item_data;
	 * @return array
     */
    public function add_item_data( $item_data, $cart_item ){
        // Set Your Nonce.
        global $adm_pk_ajax_nonce;// the ajax nonce variable
        $ajax_nonce = wp_create_nonce( $adm_pk_ajax_nonce );
		
		$product_id = $cart_item['product_id'];
		
		// heck if the item has been measured already.
        $measure_data = adm_pk_woo_get_item_data( $cart_item['key'] );
		
		// Some variables to affect html.
		$measured_class = 'adm-not-measured-look';
        $js_tailor_delete_request = 'false';
		$rat_title = 'Request a tailor and skip entering measurement.';
		$rat_btn_text = 'Request a Tailor';
		++$this->rat_count;
		
        if( isset( $measure_data ) && $measure_data['_adm_request_tailor'] === 'true' ){
            $measured_class = 'adm-measured-look';
			$js_tailor_delete_request = 'true';
			$rat_title = 'Delete your \"Request a tailor\" for this item.';
			$rat_btn_text = 'Unrequest a Tailor';
			// increment
			++$this->rat_active_count;
        }
		
        $item_data[] = array(
		    'key'     => __( 'adm_rat_btn', ADM_PK_TEXT_DOMAIN ),
		    'value'   => '<a id="_adm_rat-'.$product_id.'-'.$ajax_nonce.'-'.$js_tailor_delete_request.'" href="#adm-rat-request" class="btn btn-feel adm-measure-btn '.$measured_class.'" title="'.$rat_title.'">'.$rat_btn_text.'</a>',
            'display' => '',
        );
		
		return $item_data;
    }
	
	/**
	 * Displays the bulk button.
	 */
	public function display_bulk_button_to_cart(){
		// If rat products are in cart, then show
		if( $this->rat_count > 0 ){
			global $adm_pk_ajax_nonce;// the ajax nonce variable
			$ajax_nonce = wp_create_nonce( $adm_pk_ajax_nonce );

			$measured_class = 'adm-not-measured-look';
			$js_tailor_delete_request = 'false';
			$rat_title = 'Request a tailor for all respective items and skip entering measurement.';
			$rat_btn_text = 'Request a Tailor for all items';

			// If the active count is equal, time for bulk reverse.
			if( $this->rat_count === $this->rat_active_count ){
				$measured_class = 'adm-measured-look';
				$js_tailor_delete_request = 'true';
				$rat_title = 'Delete your \"Request a tailor\" for all respectibe items in your cart.';
				$rat_btn_text = 'Unrequest a Tailor for all items';
			}

			$btn = '<a id="_adm_rat-0-'.$ajax_nonce.'-'.$js_tailor_delete_request.'" href="#adm-rat-request-all" class="btn bulk btn-feel adm-measure-btn '.$measured_class.'" title="'.$rat_title.'">'.$rat_btn_text.'</a>';
			echo $btn;
		}
	}
	
	 /**
	  * Shows popup in cart 
	  *
	  * @param string $s_wc_cart_data
	  * @param string $s_wc_cart_session
	  *
	  * @hook action adm_pk_after_popup
	 */
	public function item_data_script( $s_wc_cart_data, $s_wc_cart_session ){ 
		global $adm_pk_ajax_nonce;
		?>
		<script>
			//holds the ajax url
			var adm_rat_request_tailor_submit = <?php echo wp_json_encode( rest_url( 'adm_rat/v1/measurement/request_tailor/' ) );?>;
		/**
		 * For submitting request tailor.
		 *
		 * @param int product_id
		 * @param string nonce the wp stuff
		 * @param bool cartRequestTailor (optional) if request tailor is for all cart items, default is false
		 * @param string deleteRequest (optiona) if true, a submitted tailor request will be reverted
		 */
		function adm_rat_submit_tailor_request( product_id, nonce, cartRequestTailor = false, deleteRequest = "false"){
			$cart_element = $( '.cart.woocommerce-cart-form__contents' );
			$cart_element.addClass('adm-make-relative');
			$cart_element.append('<div class="adm-blanket blanket center"><a href="#no-click" class="adm-nice-load-feel"><i class="fa fa-gear fa-spin fa-2x"></i></a> </div>');
			
			// Append only if child element doesnt exist.
			if( $cart_element.find( '.adm-tailor-request-load' ).length < 1 ){
				$cart_element.append('<div class="adm-tailor-request-load"></div>');
			}
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
					'c_s': '<?php echo $s_wc_cart_session; ?>',
					'adm_request_tailor_all': cartRequestTailor,
					'adm_remove_request': deleteRequest
				}
			} )
			.done( function( response ) {
				$( '.adm-tailor-request-load' ).html( response );
				window.location.replace( "<?php echo wc_get_cart_url(); ?>" );
			} )
			.fail( function( response ) {
				alert( "An error occured, please try again later or reload the page if the problem persists." );
			})
			.always( function( response ) {
				$( '.adm-blanket' ).remove();
				$cart_element.removeClass( 'adm-make-relative' );
			});

		}

	</script>
	<?php 
	}
 
}
new ADM_RAT_Cart();