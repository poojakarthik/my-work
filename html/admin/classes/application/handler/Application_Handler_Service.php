<?php

class Application_Handler_Service extends Application_Handler
{
	// View all unbilled charges and charges to the service
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
			BreadCrumb()->SetCurrentPage("Unbilled Charges");
			
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
			$aDetailsToRender['ServiceId']				= $oService->Id;
			
			$this->LoadPage('service_unbilled', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message']		= "An error occured";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
	
	// View the details for a CDR from the service
	public function CDR($subPath)
	{
		$aDetailsToRender	= array();
		
		try
		{
			if (!isset($subPath[0]))
			{
				throw new Exception('Invalid parameters supplied to this page.');
			}
			
			$iServiceId	= $subPath[0];
			$iCDRId		= $subPath[1];
			
			// Get service orm object
			$oService	= Service::getForId($subPath[0]);
			
			// Setup breadcrumbs
			BreadCrumb()->EmployeeConsole();
			BreadCrumb()->AccountOverview($oService->Account, true);
			BreadCrumb()->ViewService($oService->Id, true);
			BreadCrumb()->ViewUnbilledCharges($oService->Id);
			BreadCrumb()->SetCurrentPage("Record Id: {$iCDRId}");
			
			// Update context menu
			AppTemplateAccount::BuildContextMenu($oService->Account);
			AppTemplateService::BuildContextMenu($oService->Account, $oService->Id, $oService->ServiceType);
			
			$aCDR	= CDR::getCDRDetails($iCDRId);

			$aDetailsToRender['FNN'] 				= $oService->FNN;
			$aDetailsToRender['Id'] 				= $iCDRId;
			$aDetailsToRender['Status'] 			= $GLOBALS['*arrConstant']['CDR'][$aCDR['Status']]['Description'];
			
			$aDetailsToRender['FileName'] 			= $aCDR['FileName'];
			$aDetailsToRender['Carrier'] 			= $aCDR['CarrierName'];
			$aDetailsToRender['CarrierRef'] 		= $aCDR['CarrierRef'];
			$aDetailsToRender['Source'] 			= $aCDR['Source'];
			$aDetailsToRender['Destination'] 		= $aCDR['Destination'];
			$aDetailsToRender['StartDatetime'] 		= $aCDR['StartDatetime'];
			$aDetailsToRender['EndDatetime'] 		= $aCDR['EndDatetime'];
			$aDetailsToRender['Cost'] 				= $aCDR['Cost'];
			$aDetailsToRender['Description'] 		= $aCDR['Description'];
			$aDetailsToRender['DestinationCode'] 	= $aCDR['DestinationCodeDescription'];
			$aDetailsToRender['RecordType'] 		= $aCDR['RecordType'];
			$aDetailsToRender['Charge'] 			= $aCDR['Charge'];
			$aDetailsToRender['Rate'] 				= $aCDR['RateName'];
			$aDetailsToRender['RateId'] 			= $aCDR['RateId'];
			$aDetailsToRender['NormalisedOn'] 		= $aCDR['NormalisedOn'];
			$aDetailsToRender['RatedOn'] 			= $aCDR['RatedOn'];
			$aDetailsToRender['SequenceNo'] 		= $aCDR['SequenceNo'];
			$aDetailsToRender['Credit'] 			= $aCDR['Credit'];
			$aDetailsToRender['RawCDR'] 			= $aCDR['RawCDR'];
			$aDetailsToRender['Units'] 				= $aCDR['Units'];
			$aDetailsToRender['DisplayType'] 		= $aCDR['DisplayType'];
			
			$this->LoadPage('service_cdr_details', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
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