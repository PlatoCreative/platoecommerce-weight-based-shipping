<?php

class ProductWeight_Extension extends DataExtension {
	private static $db = array(
		'Weight' => 'Decimal(8,4)'
	);

	public function updateProductCMSFields($fields) {
		$fields->addFieldToTab('Root.Main', TextField::create('Weight', 'Weight (kgs)'), 'Content');
	}
}
