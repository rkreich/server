<?php
/**
 * @package plugins.reach
 * @subpackage Admin
 */
class PartnerCatalogItemListAction extends KalturaApplicationPlugin
{
	const ADMIN_CONSOLE_PARTNER = "-2";

	public function __construct()
	{
		$this->action = 'PartnerCatalogItemListAction';
		$this->label = "Partner Catalog Items";
		$this->rootLabel = "Reach";
	}

	/**
	 * @return string - absolute file path of the phtml template
	 */
	public function getTemplatePath()
	{
		return realpath(dirname(__FILE__));
	}

	public function doAction(Zend_Controller_Action $action)
	{
		$request = $action->getRequest();
		$page = $this->_getParam('page', 1);
		$pageSize = $this->_getParam('pageSize', 10);
		$partnerId = $this->_getParam('filter_input') ? $this->_getParam('filter_input') : $request->getParam('partnerId');
		$ServiceFeature = $this->_getParam('templateServiceFeature') != "" ? $this->_getParam('templateServiceFeature') : null;
		$ServiceType = $this->_getParam('templateServiceType') != "" ? $this->_getParam('templateServiceType') : null;
		$turnAround = $this->_getParam('templateTurnAround') != "" ? $this->_getParam('templateTurnAround') : null;

		$action->view->allowed = $this->isAllowedForPartner($partnerId);

		// init filter
		$catalogItemProfileFilter = new Kaltura_Client_Reach_Type_VendorCatalogItemFilter();
		$catalogItemProfileFilter->orderBy = "-createdAt";
		$catalogItemProfileFilter->serviceFeatureEqual = $ServiceFeature;
		$catalogItemProfileFilter->serviceTypeEqual = $ServiceType;
		$catalogItemProfileFilter->turnAroundTimeEqual = $turnAround;
		$catalogItemProfileFilter->partnerIdEqual = $partnerId;

		$client = Infra_ClientHelper::getClient();
		$reachPluginClient = Kaltura_Client_Reach_Plugin::get($client);

		// get results and paginate
		$paginatorAdapter = new Infra_FilterPaginator($reachPluginClient->vendorCatalogItem, "list", $partnerId, $catalogItemProfileFilter);
		$paginator = new Infra_Paginator($paginatorAdapter, $request);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($pageSize);

		// set view
		$catalogItemProfileFilterForm = new Form_PartnerCatalogItemFilter();
		$catalogItemProfileFilterForm->populate($request->getParams());
		$catalogItemProfileFilterFormAction = $action->view->url(array('controller' => $request->getParam('controller'), 'action' => $request->getParam('action')), null, true);
		$catalogItemProfileFilterForm->setAction($catalogItemProfileFilterFormAction);

		$action->view->filterForm = $catalogItemProfileFilterForm;
		$action->view->paginator = $paginator;

		$createProfileForm = new Form_PartnerCreateCatalogItem();
		$actionUrl = $action->view->url(array('controller' => 'plugin', 'action' => 'PartnerCatalogItemConfigure'), null, true);
		$createProfileForm->setAction($actionUrl);

		$action->view->newPartnerCatalogItemFolderForm = $createProfileForm;
	}

	public function getInstance($interface)
	{
		if ($this instanceof $interface)
			return $this;

		return null;
	}

	public function isAllowedForPartner($partnerId)
	{
		$client = Infra_ClientHelper::getClient();
		$client->setPartnerId($partnerId);
		$filter = new Kaltura_Client_Type_PermissionFilter();
		$filter->nameEqual = Kaltura_Client_Enum_PermissionName::REACH_PLUGIN_PERMISSION;
		$filter->partnerIdEqual = $partnerId;
		try
		{
			$result = $client->permission->listAction($filter, null);
		} catch (Exception $e)
		{
			return false;
		}
		$client->setPartnerId(self::ADMIN_CONSOLE_PARTNER);

		$isAllowed = ($result->totalCount > 0) && ($result->objects[0]->status == Kaltura_Client_Enum_PermissionStatus::ACTIVE);
		return $isAllowed;
	}
}