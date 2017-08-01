<?php

class WeightBasedShippingPriceRange extends DataObject {
	private static $db = array(
		'RangeStart' => 'Decimal(18,2)',
		'RangeEnd' => 'Decimal(18,2)'
	);

	private static $has_one = array(
		'ShopConfig' => 'ShopConfig',
	);

	private static $summary_fields = array(
		'RangeStart' => 'Range Start',
		'RangeEnd' => 'Range End'
	);

    public function providePermissions(){
        return array(
            'EDIT_WEIGHTBASEDSHIPPINGPRICERANGE' => 'Edit Price Ranges',
        );
    }

    public function canEdit($member = null){
        return Permission::check('EDIT_WEIGHTBASEDSHIPPINGPRICERANGE');
    }

    public function canView($member = null){
        return true;
    }

    public function canDelete($member = null){
        return Permission::check('EDIT_WEIGHTBASEDSHIPPINGPRICERANGE');
    }

    public function canCreate($member = null){
        return Permission::check('EDIT_WEIGHTBASEDSHIPPINGPRICERANGE');
    }


	public function getCMSFields() {
		return new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('PriceRanges',
					NumericField::create('RangeStart', 'Range Start (dollars)'),
					NumericField::create('RangeEnd', 'Range End (dollars)')
				)
			)
		);
	}

	public function Label() {
		$siteconfig = SiteConfig::current_site_config();
		$shopConfig = ShopConfig::current_shop_config();

		if($siteconfig->Config()->UsePriceRanges){
			$symbol = $shopConfig->BaseCurrencySymbol;
			return $symbol. $this->RangeStart . ' - ' . $symbol . $this->RangeEnd;
		}

		return '';
	}
}
