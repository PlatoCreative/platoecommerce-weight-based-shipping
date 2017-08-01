<?php

class WeightBasedShippingRate_Extension extends DataExtension {

	/**
	 * Attach {@link WeightBasedShippingRate}s to {@link SiteConfig}.
	 *
	 * @see DataObjectDecorator::extraStatics()
	 */
	private static $has_many = array(
		'WeightBasedShippingRates' => 'WeightBasedShippingRate'
	);
}
