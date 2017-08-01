<?php

class WeightBasedShippingRange_Extension extends DataExtension {
	private static $has_many = array(
		'WeightBasedShippingRanges' => 'WeightBasedShippingRange'
	);
}
