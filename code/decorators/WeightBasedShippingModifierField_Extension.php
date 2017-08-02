<?php

class WeightBasedShippingModifierField_Extension extends Extension {
	public function updateFields($fields) {
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-entwine/dist/jquery.entwine-dist.js');
		Requirements::javascript('plato-ecommerce-weight-based-shipping/javascript/WeightBasedShippingModifierField.js');
	}
}
