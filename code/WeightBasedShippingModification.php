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

	public function add($order, $value = null){
		$data = $_POST ? $_POST : null;
        $rates = null;
		$this->OrderID = $order->ID;

		$rates = $this->getWeightShippingRates($order->ShippingRegionCode);
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

			$this->extend("updateWeightShippingAdd", $rate, $mod, $data);

			$mod->write();
		}
	}

    public function getWeightOfOrder($order){
        $items = $order->Items();
        $weight = 0;

        foreach($items as $item){
            $weight += $item->CalculateWeight();
		}

        return $weight;
    }

	public function getWeightShippingRates($regionCode = null, $weight = 0, $orderamount = 0) {
		$shopConfig = ShopConfig::current_shop_config();
		$siteconfig = SiteConfig::current_site_config();

		$orderID = Session::get('Cart.OrderID');
		$order = null;
		if($orderID) {
			$order = DataObject::get_by_id('Order', $orderID);
		}

		//Get valid rates for this region
		if($regionCode && $order){
			$filter = array();
			// Check the region
            $region = Region_Shipping::get()->filter('Code', $regionCode)->first();
			if($region && $region->exists()){
				$filter['RegionID'] = $region->ID;
			}

			// Get the weight ranges
			if($weight <= 0){
				$weight = $this->getWeightOfOrder($order);
			}

            $ranges = WeightBasedShippingRange::get()->where($weight . " <= RangeEnd AND " . $weight . " >= RangeStart");
			if($ranges){
				$filter['RangeID'] = $ranges->column('ID');
			}

			// Get the price ranges
			if($siteconfig->Config()->UsePriceRanges){
				$orderamount = $order->SubTotal();//Total();
	            $priceranges = WeightBasedShippingPriceRange::get()->where($orderamount . " <= RangeEnd AND " . $orderamount . " >= RangeStart");
				if($priceranges){
					$filter['PriceRangeID'] = $priceranges->column('ID');
				}
			}

            $ratesList = WeightBasedShippingRate::get()->filter($filter);
        } else {
            $ratesList = null;
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
		$customer = Customer::currentUser();

        // Get region code if possible
		$defaultAddress = $customer ? $customer->DefaultShippingAddress() : false;
        $regionCode = Session::get('ShippingAddressID') ? DataObject::get_by_id('Address_Shipping', Session::get('ShippingAddressID'))->RegionCode : ($defaultAddress ? $defaultAddress->RegionCode : null);
		$rates = $this->getWeightShippingRates($regionCode);

		if($rates && $rates->exists()) {
			$ratesArray = $rates->map('ID', 'Label');

			$field = WeightBasedShippingModifierField_Multiple::create(
				$this,
				'Shipping',
				$ratesArray
			)->setValue($rate->ID);

			$fields->push($field);
		}

		$this->extend("updateWeightShippingRatesForm", $fields, $rate);

		if(!$fields->exists()){
			Requirements::javascript('swipestripe-weightbasedshipping/javascript/WeightBasedShippingModifierField.js');
		}

		return $fields;
	}
}
