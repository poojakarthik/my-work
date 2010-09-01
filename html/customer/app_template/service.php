<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// service
//----------------------------------------------------------------------------//
/**
 * service
 *
 * contains all ApplicationTemplate extended classes relating to service functionality
 *
 * contains all ApplicationTemplate extended classes relating to service functionality
 *
 * @file		service.php
 * @language	PHP
 * @package		framework
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateService
//----------------------------------------------------------------------------//
/**
 * AppTemplateService
 *
 * The AppTemplateAccount class
 *
 * The AppTemplateAccount class.  This incorporates all logic for all pages
 * relating to services
 *
 *
 * @package	web_app
 * @class	AppTemplateService
 * @extends	ApplicationTemplate
 */
class AppTemplateService extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// ViewUnbilledCharges
	//------------------------------------------------------------------------//
	/**
	 * ViewUnbilledCharges()
	 *
	 * Performs the logic for the service_view_unbilled_charges.php webpage
	 *
	 * Performs the logic for the service_view_unbilled_charges.php webpage
	 *
	 * @return		void
	 * @method		ViewUnbilledCharges
	 *
	 */
	function ViewUnbilledCharges()
	{
		// Check user authorization
		AuthenticatedUser()->CheckClientAuth();
				
		// Load the service
		if (!DBO()->Service->Load())
		{
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "The service with service id: ". DBO()->Service->Id->value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// Check that the user can view this service
		$bolUserCanViewService = FALSE;
		if (AuthenticatedUser()->_arrUser['CustomerContact'])
		{
			// The user can only view the account, if it belongs to the account group that they belong to
			if (AuthenticatedUser()->_arrUser['AccountGroup'] == DBO()->Service->AccountGroup->Value)
			{
				$bolUserCanViewService = TRUE;
			}
		}
		elseif (AuthenticatedUser()->_arrUser['Account'] == DBO()->Service->Account->Value)
		{
			// The user can only view the account, if it is their primary account
			$bolUserCanViewService = TRUE;
		}
		
		if (!$bolUserCanViewService)
		{
			// The user does not have permission to view the requested Service
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "ERROR: The user does not have permission to view service# ". DBO()->Service->Id->Value ." as it does not belong to any of their available accounts";
			$this->LoadPage('Error');
			return FALSE;
		}
		
		// if no filter is specified then retrieve all CDRs
		if (!DBO()->Filter->Id->Value)
		{
			DBO()->Filter->Id = 0;
			$strFilter = "";
		}
		else
		{
			// set up the phrase in the Where clause to facilitate filtering
			$strFilter = " AND RecordType = ". DBO()->Filter->Id->Value;
		}
		
		// Get related Services
		$aFNNInstances		= Service::getFNNInstances(DBO()->Service->FNN->Value, DBO()->Service->Account->Value, false);
		$aRelatedServiceIds	= array_keys($aFNNInstances);
		
		// build the where clause and array for retrieving the relevant CDRs
		$strCDRWhereClause = "Service IN (".implode(', ', $aRelatedServiceIds).") AND (Status = <CDRRated> OR Status = <CDRTempInvoice>)$strFilter AND Credit != 1";
		$arrCDRWhereClause = Array("Service"=> DBO()->Service->Id->Value, "CDRRated"=> CDR_RATED, "CDRTempInvoice"=> CDR_TEMP_INVOICE);
		
		// Find out how many records we are dealing with in the CDR table
		$selCDRCount = new StatementSelect("CDR", "COUNT(Id) AS NumOfCDRs", $strCDRWhereClause);
		$selCDRCount->Execute($arrCDRWhereClause);
		$arrCDRCount = $selCDRCount->Fetch();
		
		$intNumOfCDRs = $arrCDRCount['NumOfCDRs'];
		$intMaxPossiblePage = (int)ceil($intNumOfCDRs / MAX_RECORDS_PER_PAGE);
		if ($intNumOfCDRs == 0)
		{
			// No records were retrieved
			$intMaxPossiblePage = 1;
		}
		
		// Work out what page of the Call Information table has been requested
		if (DBO()->Page->PageToLoad->Value)
		{
			// A request has been made to load a particular page.
			$intRequestedPage = DBO()->Page->PageToLoad->Value;
			
			// Check if it is within range
			if ($intRequestedPage < 1 || $intRequestedPage > $intMaxPossiblePage)
			{
				// The page is not within the allowable range so display the first page
				$intRequestedPage = 1;
			}
		}
		else
		{
			// A page to load has not been requested so display the first page
			$intRequestedPage = 1;
		}
		
		// calculate the Start Record value for the limit clause of the sql query to pull the CDRs from the database
		$intStartRecord = ($intRequestedPage - 1) * MAX_RECORDS_PER_PAGE;
		
		// Retrieve all unbilled charges for the service, but only if the first page is being displayed
		if ($intRequestedPage == 1)
		{
			$strWhere  = "(Account = ". DBO()->Service->Account->Value .")";
			$strWhere .= " AND (Service IN (".implode(', ', $aRelatedServiceIds)."))";
			$strWhere .= " AND (Status = ". CHARGE_APPROVED .")";
			DBL()->Charge->Where->SetString($strWhere);
			DBL()->Charge->OrderBy("CreatedOn DESC, Id DESC");
			DBL()->Charge->Load();
		}
		
		// Retrieve the desired unbilled CDRs for the service
		DBL()->CDR->Where->Set($strCDRWhereClause, $arrCDRWhereClause);
		DBL()->CDR->OrderBy("StartDatetime DESC, Id DESC");
		DBL()->CDR->SetLimit(MAX_RECORDS_PER_PAGE, $intStartRecord);
		DBL()->CDR->Load();
		
		// Define details required of the pagination controls
		DBO()->Page->CurrentPage = $intRequestedPage;
		DBO()->Page->FirstPage = 1;  //probably not required
		DBO()->Page->LastPage = $intMaxPossiblePage;

		// Breadcrumb menu
		BreadCrumb()->LoadAccountInConsole(DBO()->Service->Account->Value);
		BreadCrumb()->ViewUnbilledChargesForAccount(DBO()->Service->Account->Value, TRUE);
		BreadCrumb()->SetCurrentPage("Service Charges");


		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('service_view_unbilled_charges');
		
		return TRUE;
	}
	
	//----- DO NOT REMOVE -----//
	
}
