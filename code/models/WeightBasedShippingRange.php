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


class WeightBasedShippingRange_Extension extends DataExtension {
	private static $has_many = array(
		'WeightBasedShippingRanges' => 'WeightBasedShippingRange'
	);
}


class WeightBasedShippingRange_Admin extends ShopAdmin {

	private static $tree_class = 'ShopConfig';

	private static $allowed_actions = array(
		'WeightBasedRangeSettings',
		'WeightBasedRangeSettingsForm',
		'saveWeightBasedRangeSettings'
	);

	private static $url_rule = 'ShopConfig/WeightBasedRanges';
	protected static $url_priority = 110;
	private static $menu_title = 'Shop Weight Ranges';

	private static $url_handlers = array(
		'ShopConfig/WeightBasedRanges/WeightBasedRangeSettingsForm' => 'WeightBasedRangeSettingsForm',
		'ShopConfig/WeightBasedRanges' => 'WeightBasedRangeSettings'
	);

	public function init() {
		parent::init();
		$this->modelClass = 'ShopConfig';
	}

	public function Breadcrumbs($unlinked = false) {

		$request = $this->getRequest();
		$items = parent::Breadcrumbs($unlinked);

		if ($items->count() > 1) $items->remove($items->pop());

		$items->push(new ArrayData(array(
			'Title' => 'Weight Ranges',
			'Link' => $this->Link(Controller::join_links($this->sanitiseClassName($this->modelClass), 'WeightBasedRanges'))
		)));

		return $items;
	}

	public function SettingsForm($request = null) {
		return $this->WeightBasedRangeSettingsForm();
	}

	public function WeightBasedRangeSettings($request) {

		if ($request->isAjax()) {
			$controller = $this;
			$responseNegotiator = new PjaxResponseNegotiator(
				array(
					'CurrentForm' => function() use(&$controller) {
						return $controller->WeightBasedRangeSettingsForm()->forTemplate();
					},
					'Content' => function() use(&$controller) {
						return $controller->renderWith('ShopAdminSettings_Content');
					},
					'Breadcrumbs' => function() use (&$controller) {
						return $controller->renderWith('CMSBreadcrumbs');
					},
					'default' => function() use(&$controller) {
						return $controller->renderWith($controller->getViewer('show'));
					}
				),
				$this->response
			);
			return $responseNegotiator->respond($this->getRequest());
		}

		return $this->renderWith('ShopAdminSettings');
	}

	public function WeightBasedRangeSettingsForm() {

		$shopConfig = ShopConfig::get()->First();

		$fields = new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('Shipping',
					GridField::create(
						'WeightBasedShippingRanges',
						'Weight Ranges',
						$shopConfig->WeightBasedShippingRanges(),
						GridFieldConfig_HasManyRelationEditor::create()
					)
				)
			)
		);

		$actions = new FieldList();
		$actions->push(FormAction::create('saveWeightBasedShippingSettings', _t('GridFieldDetailForm.Save', 'Save'))
			->setUseButtonTag(true)
			->addExtraClass('ss-ui-action-constructive')
			->setAttribute('data-icon', 'add'));

		$form = new Form(
			$this,
			'EditForm',
			$fields,
			$actions
		);

		$form->setTemplate('ShopAdminSettings_EditForm');
		$form->setAttribute('data-pjax-fragment', 'CurrentForm');
		$form->addExtraClass('cms-content cms-edit-form center ss-tabset');
		if($form->Fields()->hasTabset()) $form->Fields()->findOrMakeTab('Root')->setTemplate('CMSTabSet');
		$form->setFormAction(Controller::join_links($this->Link($this->sanitiseClassName($this->modelClass)), 'WeightBasedRanges/WeightBasedRangeSettingsForm'));

		$form->loadDataFrom($shopConfig);

		return $form;
	}

	public function saveWeightBasedRangeSettings($data, $form) {

		//Hack for LeftAndMain::getRecord()
		self::$tree_class = 'ShopConfig';

		$config = ShopConfig::get()->First();
		$form->saveInto($config);
		$config->write();
		$form->sessionMessage('Saved Weight Ranges', 'good');

		$controller = $this;
		$responseNegotiator = new PjaxResponseNegotiator(
			array(
				'CurrentForm' => function() use(&$controller) {
					//return $controller->renderWith('ShopAdminSettings_Content');
					return $controller->WeightBasedRangeSettingsForm()->forTemplate();
				},
				'Content' => function() use(&$controller) {
					//return $controller->renderWith($controller->getTemplatesWithSuffix('_Content'));
				},
				'Breadcrumbs' => function() use (&$controller) {
					return $controller->renderWith('CMSBreadcrumbs');
				},
				'default' => function() use(&$controller) {
					return $controller->renderWith($controller->getViewer('show'));
				}
			),
			$this->response
		);
		return $responseNegotiator->respond($this->getRequest());
	}

	public function getSnippet() {

		if (!$member = Member::currentUser()) return false;
		if (!Permission::check('CMS_ACCESS_' . get_class($this), 'any', $member)) return false;

		return $this->customise(array(
			'Title' => 'Weight Ranges Management',
			'Help' => 'Create weight ranges',
			'Link' => Controller::join_links($this->Link('ShopConfig'), 'WeightBasedRanges'),
			'LinkTitle' => 'Edit weight ranges'
		))->renderWith('ShopAdmin_Snippet');
	}

}
