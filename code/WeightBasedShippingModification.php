<?php
class WeightBasedShippingModification extends Modification {

	private static $has_one = array(
		'WeightBasedShippingRate' => 'WeightBasedShippingRate'
	);

	private static $defaults = array(
		'SubTotalModifier' => true,
		'SortOrder' => 50
	);

	private static $default_sort = 'SortOrder ASC';

	public function add($order, $value = null) {

		$this->OrderID = $order->ID;

		$country = Country_Shipping::get()
				->filter("Code",$order->ShippingCountryCode)
				->first();

		$rates = $this->getWeightShippingRates($country);
		if ($rates && $rates->exists()) {

			//Pick the rate
			$rate = $rates->find('ID', $value);

			if (!$rate || !$rate->exists()) {
				$rate = $rates->first();
			}

			//Generate the Modification now that we have picked the correct rate
			$mod = new WeightBasedShippingModification();

			$mod->Price = $rate->Amount()->getAmount();

			$mod->Description = $rate->Description;
			$mod->OrderID = $order->ID;
			$mod->Value = $rate->ID;
			$mod->WeightBasedShippingRateID = $rate->ID;
			$mod->write();
		}
	}

	public function getWeightShippingRates(Country_Shipping $country) {
		//Get valid rates for this country
		$countryID = ($country && $country->exists()) ? $country->ID : null;
		$rates = WeightBasedShippingRate::get()->filter("CountryID", $countryID);
		$this->extend("updateWeightShippingRates", $rates, $country);
		return $rates;
	}

	public function getFormFields() {

		$fields = new FieldList();
		$rate = $this->WeightBasedShippingRate();
		$rates = $this->getWeightShippingRates($rate->Country());

		if ($rates && $rates->exists()) {

			if ($rates->count() > 1) {
				$field = WeightBasedShippingModifierField_Multiple::create(
					$this,
					_t('WeightBasedShippingModification.FIELD_LABEL', 'Shipping'),
					$rates->map('ID', 'Label')->toArray()
				)->setValue($rate->ID);
			}
			else {
				$newRate = $rates->first();
				$field = WeightBasedShippingModifierField::create(
					$this,
					$newRate->Title,
					$newRate->ID
				)->setAmount($newRate->Price());
			}

			$fields->push($field);
		}

		if (!$fields->exists()) Requirements::javascript('swipestripe-weightbasedshipping/javascript/WeightBasedShippingModifierField.js');

		return $fields;
	}
}