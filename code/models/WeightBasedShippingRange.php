<?php
class WeightBasedShippingRange extends DataObject {


	private static $db = array(
		'RangeStart' => 'Decimal(8,4)',
		'RangeEnd' => 'Decimal(8,4)'
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
            'EDIT_WEIGHTBASEDSHIPPINGRANGE' => 'Edit Weight Ranges',
        );
    }

    public function canEdit($member = null){
        return Permission::check('EDIT_WEIGHTBASEDSHIPPINGRANGE');
    }

    public function canView($member = null){
        return true;
    }

    public function canDelete($member = null){
        return Permission::check('EDIT_WEIGHTBASEDSHIPPINGRANGE');
    }

    public function canCreate($member = null){
        return Permission::check('EDIT_WEIGHTBASEDSHIPPINGRANGE');
    }


	public function getCMSFields() {
		return new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('WeightRanges',
					TextField::create('RangeStart', 'Range Start (kgs)'),
					TextField::create('RangeEnd', 'Range End (kgs)')
				)
			)
		);
	}

	public function Label() {
		return $this->RangeStart . ' - ' . $this->RangeEnd;
	}
}
