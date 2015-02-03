jQuery.noConflict();
jQuery(document).ready(function($) {
	
	$.entwine('#OrderForm_OrderForm', function($){
		function showNoShippingError(){
			$('section.payment-details, div.Actions').hide();
			$('.order-details').append('<div id="shippingNotice" class="message bad"><h3>Shipping Error</h3><p>There are currently no shipping methods available for the region you have selected.</p></div>');	
		}
		
		if($('#OrderForm_OrderForm_Modifiers-WeightBasedShippingModification').length < 1){
			showNoShippingError();
		}
		
		$('#OrderForm_OrderForm_Modifiers-WeightBasedShippingModification').entwine({
			onmatch : function() {
				if($('#shippingNotice').length > 0){
					$('#shippingNotice').hide().remove();
					$('section.payment-details, div.Actions').show();
				}
			},
			onunmatch : function() {
				showNoShippingError();
			}
		});
	});
});