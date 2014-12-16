<?php
class WeightBasedShippingModification extends Modification {

	private static $has_one = array(
		'WeightBasedShippingRate' => 'WeightBasedShippingRate'
	);

	private static $defaults = array(
		'SubTotalModifier' => true,
		'SortOrder' => 100
	);

	private static $default_sort = 'SortOrder ASC';

	public function add($order, $value = null) {

        $rates = null;
		$this->OrderID = $order->ID;

        $weight = $this->getWeightOfOrder($order);

		$rates = $this->getWeightShippingRates($order->ShippingRegionCode, $weight);
		if ($rates && $rates->exists()) {

			//Pick the rate
			$rate = $rates->find('ID', $value);

			if (!$rate || !$rate->exists()) {
				$rate = $rates->first();
			}

			//Generate the Modification now that we have picked the correct rate
			$mod = new WeightBasedShippingModification();

			$mod->Price = $rate->Amount()->getAmount();

			$mod->Description = $rate->Description();
			$mod->OrderID = $order->ID;
			$mod->Value = $rate->ID;
			$mod->WeightBasedShippingRateID = $rate->ID;
			$mod->write();
		}
	}

    public function getWeightOfOrder($order){

        $items = $order->Items();
        $weight = 0;
        foreach($items as $item){
            $weight += $item->Product()->Weight;
        }

        return $weight;
    }

	public function getWeightShippingRates($regionCode = null, $weight = 0) {
		//Get valid rates for this region

        if($regionCode){
            $region = Region_Shipping::get()->filter('Code', $regionCode)->first();
            $ranges = WeightBasedShippingRange::get()->where($weight . " <= RangeEnd AND " . $weight . " >= RangeStart");

            $rates = WeightBasedShippingRate::get()->filter(array('RegionID' => $region->ID, 'RangeID' => $ranges->column('ID')));

        }else{
            $rates = WeightBasedShippingRate::get();
        }

		$this->extend("updateWeightShippingRates", $rates);
		return $rates;
	}

	public function getFormFields() {

		$fields = new FieldList();

		$rate = $this->WeightBasedShippingRate();

        //get region code if possible
        $regionCode = Region_Shipping::get()->filter('ID', $rate->RegionID)->first()->Code;
		$rates = $this->getWeightShippingRates($regionCode);

		if ($rates && $rates->exists()) {

			if ($rates->count() > 1) {
				$field = WeightBasedShippingModifierField_Multiple::create(
					$this,
					'Shipping',
					$rates->map('ID', 'Label')->toArray()
				)->setValue($rate->ID);
			}
			else {
				$newRate = $rates->first();
				$field = WeightBasedShippingModifierField::create(
					$this,
					'Shipping - ' . $newRate->Description(),
					$newRate->ID
				)->setAmount($newRate->Price());
			}

			$fields->push($field);
		}



        //if instore pickup is installed add a hard coded item
        if(class_exists('InstorePickup')){
            $instorePickup = InstorePickup::get()->filter(array('CountryID' => $rate->CountryID));

            if(count($instorePickup)){
                $instoreField = HiddenField::create('hasInstorePickup');
                $fields->push($instoreField);
            }
        }


		if (!$fields->exists()) Requirements::javascript('swipestripe-weightbasedshipping/javascript/WeightBasedShippingModifierField.js');

		return $fields;
	}
}