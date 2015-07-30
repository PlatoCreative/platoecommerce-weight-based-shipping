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


class WeightBasedShippingProvider_Extension extends DataExtension {

	/**
	 * Attach {@link WeightBasedShippingProvider}s to {@link SiteConfig}.
	 *
	 * @see DataObjectDecorator::extraStatics()
	 */
	private static $has_many = array(
		'WeightBasedShippingProviders' => 'WeightBasedShippingProvider'
	);

}


class WeightBasedShippingProvider_Admin extends ShopAdmin {

	private static $tree_class = 'ShopConfig';

	private static $allowed_actions = array(
		'WeightBasedShippingProviderSettings',
		'WeightBasedShippingProviderSettingsForm',
		'saveWeightBasedShippingProviderSettings'
	);

	private static $url_rule = 'ShopConfig/WeightBasedShippingProviders';
	protected static $url_priority = 110;
	private static $menu_title = 'Shop Shipping Providers';

	private static $url_handlers = array(
		'ShopConfig/WeightBasedShippingProviders/WeightBasedShippingProviderSettingsForm' => 'WeightBasedShippingProviderSettingsForm',
		'ShopConfig/WeightBasedShippingProviders' => 'WeightBasedShippingProviderSettings'
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
			'Title' => 'Shipping Providers',
			'Link' => $this->Link(Controller::join_links($this->sanitiseClassName($this->modelClass), 'WeightBasedShippingProviders'))
		)));

		return $items;
	}

	public function SettingsForm($request = null) {
		return $this->WeightBasedShippingProviderSettingsForm();
	}

	public function WeightBasedShippingProviderSettings($request) {
		if ($request->isAjax()) {
			$controller = $this;
			$responseNegotiator = new PjaxResponseNegotiator(
				array(
					'CurrentForm' => function() use(&$controller) {
						return $controller->WeightBasedShippingProviderSettingsForm()->forTemplate();
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

	public function WeightBasedShippingProviderSettingsForm() {
		$shopConfig = ShopConfig::get()->First();

		//GridFieldConfig_HasManyRelationEditor::create()
		$providersConf = GridFieldConfig_RelationEditor::create(20)->addComponent(new GridFieldSortableRows('Sort'));
		$fields = new FieldList(
			$rootTab = new TabSet('Root',
				$tabMain = new Tab('Shipping',
					GridField::create(
						'WeightBasedShippingProviders',
						'Shipping Providers',
						$shopConfig->WeightBasedShippingProviders(),
						$providersConf
					)
				)
			)
		);

		$actions = new FieldList();
		$actions->push(FormAction::create('saveWeightBasedShippingProviderSettings', _t('GridFieldDetailForm.Save', 'Save'))
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
		$form->setFormAction(Controller::join_links($this->Link($this->sanitiseClassName($this->modelClass)), 'WeightBasedShippingProviders/WeightBasedShippingProviderSettingsForm'));

		$form->loadDataFrom($shopConfig);

		return $form;
	}

	public function saveWeightBasedShippingProviderSettings($data, $form) {
		//Hack for LeftAndMain::getRecord()
		self::$tree_class = 'ShopConfig';

		$config = ShopConfig::get()->First();
		$form->saveInto($config);
		$config->write();
		$form->sessionMessage('Saved Shipping Providers', 'good');

		$controller = $this;
		$responseNegotiator = new PjaxResponseNegotiator(
			array(
				'CurrentForm' => function() use(&$controller) {
					//return $controller->renderWith('ShopAdminSettings_Content');
					return $controller->WeightBasedShippingProviderSettingsForm()->forTemplate();
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
			'Title' => 'Shipping Providers Management',
			'Help' => 'Create Shipping Providers',
			'Link' => Controller::join_links($this->Link('ShopConfig'), 'WeightBasedShippingProviders'),
			'LinkTitle' => 'Edit shipping providers'
		))->renderWith('ShopAdmin_Snippet');
	}

}
