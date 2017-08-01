<?php
class WeightBasedShippingRate extends DataObject {

	private static $db = array(
		'Price' => 'Decimal(19,4)'
	);

	private static $has_one = array(
		'ShopConfig' => 'ShopConfig',
		'Range' => 'WeightBasedShippingRange',
		'PriceRange' => 'WeightBasedShippingPriceRange',
		'Provider' => 'WeightBasedShippingProvider',
		'Region' => 'Region_Shipping'
	);

	private static $summary_fields = array(
		'Amount' => 'Price',
		'Range.Label' => 'Weight Range',
		'PriceRange.Label' => 'Price Range',
		'Provider.Name' => 'Provider',
		'Region.Title' => 'Region'
	);

    public function providePermissions(){
        return array(
            'EDIT_WEIGHTBASEDSHIPPING' => 'Edit Weight Based Shipping',
        );
    }

    public function canEdit($member = null){
        return Permission::check('EDIT_WEIGHTBASEDSHIPPING');
    }

    public function canView($member = null){
        return true;
    }

    public function canDelete($member = null){
        return Permission::check('EDIT_WEIGHTBASEDSHIPPING');
    }

    public function canCreate($member = null){
        return Permission::check('EDIT_WEIGHTBASEDSHIPPING');
    }

	public function getCMSFields() {
		$shopConfig = ShopConfig::current_shop_config();
		$siteconfig = SiteConfig::current_site_config();

		$fields = new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('ShippingRate',
					DropdownField::create('RangeID', 'Weight Range', WeightBasedShippingRange::get()->filter(array('ShopConfigID' => $shopConfig->ID))->map('ID', 'Label')->toArray()),
					DropdownField::create('ProviderID', 'Provider', WeightBasedShippingProvider::get()->filter(array('ShopConfigID' => $shopConfig->ID))->map()->toArray()),
					DropdownField::create('RegionID', 'Region', Region_Shipping::get()->filter(array('ShopConfigID' => $shopConfig->ID))->map()->toArray()),
					PriceField::create('Price')
				)
			)
		);

		if($siteconfig->Config()->UsePriceRanges){
			$fields->addFieldsToTab('Root.ShippingRate', array(
				DropdownField::create('PriceRangeID', 'Price Range', WeightBasedShippingPriceRange::get()->filter(array('ShopConfigID' => $shopConfig->ID))->map('ID', 'Label')->toArray())
			), 'ProviderID');
		}

		return $fields;
	}

	public function Label() {
        $providerName = $this->Provider()->Name;
		return $providerName . ' - ' . $this->Price()->Nice();
	}

    public function Description() {
        return $this->Provider()->Name;
    }

	/**
	 * Summary of the current tax rate
	 *
	 * @return String
	 */
	public function SummaryOfPrice() {
		return $this->Amount()->Nice();
	}

	public function Amount() {
		// TODO: Multi currency
		$shopConfig = ShopConfig::current_shop_config();

		$amount = new Price();
		$amount->setAmount($this->Price);
		$amount->setCurrency($shopConfig->BaseCurrency);
		$amount->setSymbol($shopConfig->BaseCurrencySymbol);
		return $amount;
	}

	/**
	 * Display price, can decorate for multiple currency etc.
	 *
	 * @return Price
	 */
	public function Price() {

		$amount = $this->Amount();
		$this->extend('updatePrice', $amount);
		return $amount;
	}

	public function onBeforeWrite(){
		parent::onBeforeWrite();
		$shopConfig = ShopConfig::current_shop_config();

		$this->ShopConfigID = $shopConfig->ID;
	}
}

class ProductWeight_Extension extends DataExtension {
	private static $db = array(
		'Weight' => 'Decimal(8,4)'
	);

	public function updateProductCMSFields($fields) {
		$fields->addFieldToTab('Root.Main', TextField::create('Weight', 'Weight (kgs)'), 'Content');
	}
}
