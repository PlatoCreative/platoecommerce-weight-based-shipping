<?php

/*
*	WeightBasedShippingPriceRange_Extension extends ShopConfig
*/

class WeightBasedShippingPriceRange_Extension extends DataExtension {
	private static $has_many = array(
		'WeightBasedShippingPriceRanges' => 'WeightBasedShippingPriceRange'
	);
}
