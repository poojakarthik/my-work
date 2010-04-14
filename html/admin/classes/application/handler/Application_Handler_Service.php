<?php

class Application_Handler_Service extends Application_Handler
{
	// View all unbilled adjustments and charges to the service
	public function Unbilled($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		
		$aDetailsToRender	= array();
		
		try
		{
			if (!isset($subPath[0]))
			{
				throw new Exception('Invalid parameters supplied to this page.');
			}
			
			// Get service orm object
			$oService	= Service::getForId($subPath[0]);
			
			// Setup breadcrumbs
			BreadCrumb()->EmployeeConsole();
			BreadCrumb()->AccountOverview($oService->Account, true);
			BreadCrumb()->ViewService($oService->Id, true);
			BreadCrumb()->SetCurrentPage("View Unbilled Charges");
			
			// Update context menu
			AppTemplateAccount::BuildContextMenu($oService->Account);
			AppTemplateService::BuildContextMenu($oService->Account, $oService->Id, $oService->ServiceType);
			
			// Setup data for the page
			$aDetailsToRender['Charges']		= $oService->getCharges();
			$aDetailsToRender['RecordTypes']	= Record_Type::getForServiceType($oService->ServiceType);
			$aDetailsToRender['ServiceType']	= $oService->ServiceType;
			
			// Filter information
			$aDetailsToRender['filter'] = array(
				'offset' => array_key_exists('offset', $_REQUEST) ? intval($_REQUEST['offset']) : 0,
				'limit' => 30,
				'recordType' => (array_key_exists('recordType', $_REQUEST) && $_REQUEST['recordType']) ? intval($_REQUEST['recordType']) : NULL,
				'recordCount' => 0,
			);
			
			// Get the cdr information
			$aCDRsResult	= 	$oService->getCDRs(
									null, 
									$aDetailsToRender['filter']['recordType'], 
									$aDetailsToRender['filter']['limit'], 
									$aDetailsToRender['filter']['offset']
								);
			
			$aDetailsToRender['CDRs']					= $aCDRsResult['CDRs'];
			$aDetailsToRender['filter']['recordCount']	= $aCDRsResult['recordCount'];
			
			$this->LoadPage('service_unbilled', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message']		= "An error occured";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
}

?>