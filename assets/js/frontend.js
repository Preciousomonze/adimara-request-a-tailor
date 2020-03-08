/* script */
/*globals*/
var $ = jQuery;
$(document).ready(function(){
	
	//event listeners
	$('a[href="#adm-rat-request"]').click(function(){
		var _value = $(this).attr('id');
		var res = _value.split("-");
		var product_id = res[1];
		var nonce = res[2];
		var deleteRequest = res[3]; // If we need to unrequest or request a tailor
		adm_pk_loadMeasurementPop( product_id, nonce, false, deleteRequest );
	
	});
	
	//For the cart button to affect all
	$('a[href="#adm-rat-request-all"]').click(function(){
		var _value = $(this).attr('id');
		var res = _value.split("-");
		//var product_id = res[1];
		var nonce = res[2];
		var deleteRequest = res[3]; // If we need to unrequest or request a tailor
		adm_pk_loadMeasurementPop( product_id, nonce, true, deleteRequest );
	
	});
	

});
