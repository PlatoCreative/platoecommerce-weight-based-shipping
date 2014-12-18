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
			$filter = array();
			
            $region = Region_Shipping::get()->filter('Code', $regionCode)->first();
			if($region && $region->exists()){
				$filter[	'RegionID'] = $region->ID;
			}
			
            $ranges = WeightBasedShippingRange::get()->where($weight . " <= RangeEnd AND " . $weight . " >= RangeStart");
			if($ranges){
				$filter['RangeID'] = $ranges->column('ID');
			}
			
            $ratesList = WeightBasedShippingRate::get()->filter($filter);
        } else {
            $ratesList = WeightBasedShippingRate::get();
        }
		
		$rates = new ArrayList();		
		if($ratesList){
			foreach($ratesList as $rate){
				$rate->Label = $rate->Label();	
				$rates->push($rate);
			}
		}
		
		$this->extend("updateWeightShippingRates", $rates, $regionCode);
		
		return $rates;
	}

	public function getFormFields() {
		$fields = new FieldList();

		$rate = $this->WeightBasedShippingRate();

        // Get region code if possible
        $regionCode = Session::get('ShippingAddressID') ? DataObject::get_by_id('Address_Shipping', Session::get('ShippingAddressID'))->RegionCode : null;
		$rates = $this->getWeightShippingRates($regionCode);
		
		if ($rates && $rates->exists()) {
			$ratesArray = $rates->map('ID', 'Label');
			if (count($ratesArray) > 1) {
				$field = WeightBasedShippingModifierField_Multiple::create(
					$this,
					'Shipping',
					$ratesArray
				)->setValue($rate->ID);
			} else {
				$newRate = $rates->first();
				$field = WeightBasedShippingModifierField::create(
					$this,
					'Shipping - ' . $newRate->Description(),
					$newRate->ID
				)->setAmount($newRate->Price());
			}
			
			$fields->push($field);
		}
				
		$this->extend("updateWeightShippingRatesForm", $fields, $rate);

		if(!$fields->exists()){
			Requirements::javascript('swipestripe-weightbasedshipping/javascript/WeightBasedShippingModifierField.js');
		}

		return $fields;
	}
}