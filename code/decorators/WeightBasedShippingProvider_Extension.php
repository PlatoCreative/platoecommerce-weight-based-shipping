<?php

class WeightBasedShippingProvider_Extension extends DataExtension {

	/**
	 * Attach {@link WeightBasedShippingProvider}s to {@link SiteConfig}.
	 *
	 * @see DataObjectDecorator::extraStatics()
	 */
	private static $has_many = array(
		'WeightBasedShippingProviders' => 'WeightBasedShippingProvider'
	);

}
