<?php
class WeightBasedShippingProvider extends DataObject {

	private static $db = array(
		'Sort' => 'Int',
		'Name' => 'Text'
	);

	private static $has_one = array(
		'ShopConfig' => 'ShopConfig',
		'Country' => 'Country'
	);

	private static $summary_fields = array(
		'Name' => 'Name',
		'Country.Title' => 'Country'
	);

	static $default_sort = "Sort ASC";

    public function providePermissions(){
        return array(
            'EDIT_WEIGHTBASEDSHIPPINGPROVIDER' => 'Edit Shipping Providers',
        );
    }

    public function canEdit($member = null){
        return Permission::check('EDIT_WEIGHTBASEDSHIPPINGPROVIDER');
    }

    public function canView($member = null){
        return true;
    }

    public function canDelete($member = null){
        return Permission::check('EDIT_WEIGHTBASEDSHIPPINGPROVIDER');
    }

    public function canCreate($member = null){
        return Permission::check('EDIT_WEIGHTBASEDSHIPPINGPROVIDER');
    }


	public function getCMSFields() {
		return new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('Shipping Providers',
					TextField::create('Name', 'Name'),
					DropdownField::create('CountryID', _t('Country', 'Country'), Country_Shipping::get()->map()->toArray())
				)
			)
		);
	}

}
