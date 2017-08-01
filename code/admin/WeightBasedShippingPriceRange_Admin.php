<?php

class WeightBasedShippingPriceRange_Admin extends ShopAdmin {
	private static $tree_class = 'ShopConfig';

	private static $allowed_actions = array(
		'WeightBasedPriceRangeSettings',
		'WeightBasedPriceRangeSettingsForm',
		'saveWeightBasedPriceRangeSettings'
	);

	private static $url_rule = 'ShopConfig/WeightBasedPriceRanges';
	protected static $url_priority = 110;
	private static $menu_title = 'Shop Weight Ranges';

	private static $url_handlers = array(
		'ShopConfig/WeightBasedPriceRanges/WeightBasedPriceRangeSettingsForm' => 'WeightBasedPriceRangeSettingsForm',
		'ShopConfig/WeightBasedPriceRanges' => 'WeightBasedPriceRangeSettings'
	);

	public function init() {
		parent::init();
		$this->modelClass = 'ShopConfig';
	}

	public function Breadcrumbs($unlinked = false) {
		$siteconfig = SiteConfig::current_site_config();
		$shopConfig = ShopConfig::current_shop_config();
		$request = $this->getRequest();
		$items = parent::Breadcrumbs($unlinked);

		if ($items->count() > 1) $items->remove($items->pop());

		if($siteconfig->Config()->UsePriceRanges){
			$items->push(new ArrayData(array(
				'Title' => 'Price Ranges',
				'Link' => $this->Link(Controller::join_links($this->sanitiseClassName($this->modelClass), 'WeightBasedPriceRanges'))
			)));
		}

		return $items;
	}

	public function SettingsForm($request = null) {
		return $this->WeightBasedPriceRangeSettingsForm();
	}

	public function WeightBasedPriceRangeSettings($request) {
		if ($request->isAjax()) {
			$controller = $this;
			$responseNegotiator = new PjaxResponseNegotiator(
				array(
					'CurrentForm' => function() use(&$controller) {
						return $controller->WeightBasedPriceRangeSettingsForm()->forTemplate();
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

	public function WeightBasedPriceRangeSettingsForm() {
		$shopConfig = ShopConfig::get()->First();

		$fields = new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('Shipping',
					GridField::create(
						'WeightBasedShippingPriceRanges',
						'Price Ranges',
						$shopConfig->WeightBasedShippingPriceRanges(),
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
		$form->setFormAction(Controller::join_links($this->Link($this->sanitiseClassName($this->modelClass)), 'WeightBasedPriceRanges/WeightBasedPriceRangeSettingsForm'));

		$form->loadDataFrom($shopConfig);

		return $form;
	}

	public function saveWeightBasedPriceRangeSettings($data, $form) {
		//Hack for LeftAndMain::getRecord()
		self::$tree_class = 'ShopConfig';

		$config = ShopConfig::current_shop_config();
		$form->saveInto($config);
		$config->write();
		$form->sessionMessage('Saved Price Ranges', 'good');

		$controller = $this;
		$responseNegotiator = new PjaxResponseNegotiator(
			array(
				'CurrentForm' => function() use(&$controller) {
					//return $controller->renderWith('ShopAdminSettings_Content');
					return $controller->WeightBasedPriceRangeSettingsForm()->forTemplate();
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
		$shopConfig = ShopConfig::current_shop_config();
		$siteconfig = SiteConfig::current_site_config();

		if (!$member = Member::currentUser()){
			return false;
		}

		if (!Permission::check('CMS_ACCESS_' . get_class($this), 'any', $member)){
			return false;
		}

		if($siteconfig->Config()->UsePriceRanges){
			return $this->customise(array(
				'Title' => 'Price Ranges Management',
				'Help' => 'Create price ranges',
				'Link' => Controller::join_links($this->Link('ShopConfig'), 'WeightBasedPriceRanges'),
				'LinkTitle' => 'Edit price ranges'
			))->renderWith('ShopAdmin_Snippet');
		}
		return false;
	}

}
