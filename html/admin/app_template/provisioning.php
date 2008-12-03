<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// provisioning
//----------------------------------------------------------------------------//
/**
 * provisioning
 *
 * contains all ApplicationTemplate extended classes relating to provisioning functionality
 *
 * contains all ApplicationTemplate extended classes relating to provisioning functionality
 *
 * @file		provisioning.php
 * @language	PHP
 * @package		framework
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateProvisioning
//----------------------------------------------------------------------------//
/**
 * AppTemplateProvisioning
 *
 * The AppTemplateProvisioning class
 *
 * The AppTemplateProvisioning class.  This incorporates all logic for all pages
 * relating to Provisioning
 *
 *
 * @package	ui_app
 * @class	AppTemplateProvisioning
 * @extends	ApplicationTemplate
 */
class AppTemplateProvisioning extends ApplicationTemplate
{
	//------------------------------------------------------------------------//
	// BulkProvisioningRequest
	//------------------------------------------------------------------------//
	/**
	 * BulkProvisioningRequest()
	 *
	 * Manages the construction of the Bulk Provisioning Request page
	 * 
	 * Manages the construction of the Bulk Provisioning Request page
	 * It expects the following objects to be defined:
	 * 		DBO()->Service->Id		Id of the service to provision
	 * 		OR
	 * 		DBO()->Account->Id		Id of the account to provision services of 
	 * 
	 *
	 * @return		void
	 * @method		BulkProvisioningRequest
	 */
	function BulkProvisioningRequest()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		$arrSelectedServices = Array();
		// Setup all DBO and DBL objects required for the page
		if (DBO()->Service->Id->IsSet)
		{
			// Load this service to retrieve the Account it belongs to
			if (!DBO()->Service->Load())
			{
				DBO()->Error->Message = "The Service with id: ". DBO()->Service->Id->value .", you were attempting to provision could not be found";
				$this->LoadPage('error');
				return TRUE;
			}
			DBO()->Account->Id = DBO()->Service->Account->Value;
			$arrSelectedServices[] = DBO()->Service->Id->Value;
		}
		
		// Try loading the account
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "Account: ". DBO()->Account->Id->Value .", cannot be found";
			$this->LoadPage('error');
			return TRUE;
		}

		// Retrieve all the services belonging to the account and whether or not they have address details defined
		//$this->_LoadServiceDetails(DBO()->Account->Id->Value);
		DBO()->Account->Services = $this->GetServices(DBO()->Account->Id->Value);
		
		// Set up the BreadCrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->AccountOverview(DBO()->Account->Id->Value, TRUE);
		if (DBO()->Service->Id->IsSet)
		{
			BreadCrumb()->ViewService(DBO()->Service->Id->Value, TRUE);
		}
		BreadCrumb()->SetCurrentPage("Provisioning");
		
		// Set up the Context menu
		ContextMenu()->Account->Account_Overview(DBO()->Account->Id->Value);
		ContextMenu()->Account->Invoices_and_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Account->Services->List_Services(DBO()->Account->Id->Value);
		ContextMenu()->Account->Contacts->List_Contacts(DBO()->Account->Id->Value);
		ContextMenu()->Account->View_Cost_Centres(DBO()->Account->Id->Value);
		ContextMenu()->Account->Services->Add_Services(DBO()->Account->Id->Value);
		ContextMenu()->Account->Contacts->Add_Contact(DBO()->Account->Id->Value);
		ContextMenu()->Account->Payments->Make_Payment(DBO()->Account->Id->Value);
		ContextMenu()->Account->Payments->Change_Payment_Method(DBO()->Account->Id->Value);
		ContextMenu()->Account->Add_Associated_Account(DBO()->Account->Id->Value);
		ContextMenu()->Account->Notes->Add_Account_Note(DBO()->Account->Id->Value);
		ContextMenu()->Account->Notes->View_Account_Notes(DBO()->Account->Id->Value);
		if (count(Sale::listForAccountId(DBO()->Account->Id->Value, NULL, 1)))
		{
			// The account has sales associated with it
			ContextMenu()->Account->Sales->ViewSalesForAccount(DBO()->Account->Id->Value);
		}

		if (DBO()->Service->Id->Value)
		{
			ContextMenu()->Service->View_Service(DBO()->Service->Id->Value);
			ContextMenu()->Service->Plan->View_Service_Rate_Plan(DBO()->Service->Id->Value);
			ContextMenu()->Service->View_Unbilled_Charges(DBO()->Service->Id->Value);
			ContextMenu()->Service->Edit_Service(DBO()->Service->Id->Value);
			ContextMenu()->Service->Plan->Change_Plan(DBO()->Service->Id->Value);
			ContextMenu()->Service->Move_Service(DBO()->Service->Id->Value);
			ContextMenu()->Service->Adjustments->Add_Adjustment(DBO()->Account->Id->Value, DBO()->Service->Id->Value);
			ContextMenu()->Service->Adjustments->Add_Recurring_Adjustment(DBO()->Account->Id->Value, DBO()->Service->Id->Value);
			ContextMenu()->Service->Notes->Add_Service_Note(DBO()->Service->Id->Value);
			ContextMenu()->Service->Notes->View_Service_Notes(DBO()->Service->Id->Value);
		}
		
		// Retrieve the Provisioning History for the Account
		DBO()->History->CategoryFilter	= PROVISIONING_HISTORY_CATEGORY_BOTH;
		DBO()->History->TypeFilter		= PROVISIONING_HISTORY_FILTER_ALL;
		DBO()->History->MaxItems		= 10;

		// Retrieve the history
		$mixResult = $this->GetHistory(DBO()->History->CategoryFilter->Value, DBO()->History->TypeFilter->Value, DBO()->Account->Id->Value, NULL, DBO()->History->MaxItems->Value);
		// TODO check that $mixResult !== FALSE.  If it does, then an error has occured
		
		DBO()->History->Records	= $mixResult;

		// Retrieve all outbound provisioning request types
		DBL()->provisioning_type->SetColumns("id, name, outbound");
		DBL()->provisioning_type->outbound = 1;
		DBL()->provisioning_type->OrderBy("name");
		DBL()->provisioning_type->Load();
		
		// Retrieve all 'telco' carriers
		DBL()->Carrier->SetColumns("Id, Name, carrier_type");
		DBL()->Carrier->carrier_type = CARRIER_TYPE_TELECOM;
		DBL()->Carrier->OrderBy("Name");
		DBL()->Carrier->Load();
		
		// The service record is no longer needed
		DBO()->Service->Clean();
		
		// Store the list of currently selected services
		DBO()->Request->ServiceIds = $arrSelectedServices;
		
		// Define the page template to use
		$this->LoadPage('provisioning_request');
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// RenderServiceList
	//------------------------------------------------------------------------//
	/**
	 * RenderServiceList()
	 *
	 * Renders the Service List for the provisioning page using the HtmlTemplateProvisioningServiceList class
	 * 
	 * Renders the Service List for the provisioning page using the HtmlTemplateProvisioningServiceList class
	 * It expects the following objects to be defined:
	 * 		DBO()->Account->Id				Id of the account to provision services of
	 * 		DBO()->List->SelectedServices	Array of Service Ids for all the selected services
	 * 		DBO()->List->ContainerDivId		The Service List's container div id  
	 *
	 * @return		void
	 * @method		RenderServiceList
	 */
	function RenderServiceList()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// Retrieve all the services belonging to the account and whether or not they have address details defined
		//$this->_LoadServiceDetails(DBO()->Account->Id->Value);
		DBO()->Account->Services = $this->GetServices(DBO()->Account->Id->Value, DBO()->List->Filter->Value);
		
		// Store the list of currently selected services
		DBO()->Request->ServiceIds = DBO()->List->SelectedServices->Value;
		
		// Render the HtmlTemplate
		Ajax()->RenderHtmlTemplate("ProvisioningServiceList", HTML_CONTEXT_DEFAULT, DBO()->List->ContainerDivId->Value);

		return TRUE;
	}
	
	
	//------------------------------------------------------------------------//
	// SubmitRequest
	//------------------------------------------------------------------------//
	/**
	 * SubmitRequest()
	 *
	 * Processes a Provisioning Request
	 * 
	 * Processes a Provisioning Request
	 * It expects the following objects to be defined:
	 * 		DBO()->Account->Id			Id of the account to provision services of
	 * 		DBO()->Request->Type		type of provisioning request.  Must belong to the 
	 * 									"provisioning_type" constant group defined in database_constants.php
	 * 		DBO()->Request->ServiceIds	Array of Service Ids which the provisioning request
	 * 									will be applied to.  It is assumed that these all belong
	 * 									to the account specified by DBO()->Account->Id
	 * 		DBO()->Request->CarrierIds	Arrary of Carrier Ids which the provisioning request
	 * 									will be applied to
	 * 
	 * If the submittion is successful it will fire an EVENT_ON_PROVISIONING_REQUEST_SUBMITTED 
	 * event passing the following Event object data:
	 *		Account.Id	= id of the Account relating to the request
	 *		Service.Id	= id of the service that the request belongs to, if only 1 service was specified
	 *
	 * It will also fire the OnNewNote event if the provisioning request was "barring" related
	 *  
	 * @return		void
	 * @method		SubmitRequest
	 */
	function SubmitRequest()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		$arrServiceIds	= DBO()->Request->ServiceIds->Value;
		$intCarrier		= DBO()->Request->Carrier->Value;
		$intRequestType	= DBO()->Request->Type->Value;

		// Validate the AuthorisationDate if it has been specified
		if (DBO()->Request->AuthorisationDate->Value != "")
		{
			// A value has been supplied
			if (Validate("ShortDate", DBO()->Request->AuthorisationDate->Value) == FALSE)
			{
				// The date is invalid
				Ajax()->AddCommand("Alert", "ERROR: Authorisation Date is invalid. Please use the format DD/MM/YYYY or leave blank to use today's date.");
				return TRUE;
			}
			
			$strAuthDate = ConvertUserDateToMySqlDate(DBO()->Request->AuthorisationDate->Value);
			
			// Check that the date is within the last month
			$strEarliestDate	= date("Y-m-d", strtotime("-30 days"));
			$strLatestDate		= date("Y-m-d");
			
			if ($strAuthDate <= $strEarliestDate || $strAuthDate > $strLatestDate)
			{
				// The date is out of bounds
				Ajax()->AddCommand("Alert", "ERROR: Authorisation Date must be within the last 30 days.  Leave blank to use today's date.");
				return TRUE;
			}
		}
		else
		{
			// Use today's date
			$strAuthDate = date("Y-m-d");
		}

		DBO()->Account->Load();
		
		// Check that the Account's status allows for provisioning of services
		if (DBO()->Account->Archived->Value == ACCOUNT_STATUS_PENDING_ACTIVATION)
		{
			Ajax()->AddCommand("Alert", "The account is pending activation.  Provisioning cannot be performed.");
			return TRUE;
		}
		
		// Retrieve the Service records
		$strColumns = "Id, AccountGroup, Account, FNN, Status";
		$strWhere	= "Account = <AccountId> AND Id IN (". implode(", ", $arrServiceIds) .")";
		$selService = new StatementSelect("Service", $strColumns, $strWhere, "FNN");
		$intServicesFound = $selService->Execute(Array("AccountId" => DBO()->Account->Id->Value));
		if ($intServicesFound === FALSE || ($intServicesFound != count($arrServiceIds)))
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not find all the services requested.  Provisioning request aborted");
			return TRUE;
		}
		
		// Set a time for the request to be made on
		$strRequestedOn = GetCurrentDateAndTimeForMySQL();
		
		// Set up objects for record insertion
		$arrInsertValues = Array(	"AccountGroup"		=> DBO()->Account->AccountGroup->Value,
									"Account"			=> DBO()->Account->Id->Value,
									"Service"			=> NULL,
									"FNN"				=> NULL,
									"Employee"			=> AuthenticatedUser()->_arrUser['Id'],
									"Carrier"			=> NULL,
									"Type"				=> $intRequestType,
									"RequestedOn"		=> $strRequestedOn,
									"AuthorisationDate"	=> $strAuthDate,
									"Status"			=> REQUEST_STATUS_WAITING
								);
		$insRequest = new StatementInsert("ProvisioningRequest", $arrInsertValues);
		
		$arrServices = $selService->FetchAll();
		
		// Check that none of the services are pending activation
		foreach ($arrServices as $arrService)
		{
			if ($arrService['Status'] == SERVICE_PENDING)
			{
				Ajax()->AddCommand("Alert", "ERROR: Service {$arrService['FNN']} cannot be provisioned as it is pending activation.  Provisioning request aborted");
				return TRUE;
			}
		}
		
		// Start the database transaction
		TransactionStart();
		
		// Check if the requests are being made to a single Carrier, or for the 
		// carriers associated with the current plan of the account
		if ($intCarrier > 0)
		{
			// The request is being made to a single carrier
			$arrInsertValues['Carrier'] = $intCarrier;
			
			// Loop through each service that the request is being made with
			foreach ($arrServices as $arrService)
			{
				$arrInsertValues['Service'] = $arrService['Id'];
				$arrInsertValues['FNN']		= $arrService['FNN'];
				
				// Make the request
				if ($insRequest->Execute($arrInsertValues) === FALSE)
				{
					// Insertion failed
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Submitting the request failed, unexpectedly.  Provisioning request aborted<br />(Insertion of record into ProvisioningRequest table failed)");
					return TRUE;
				}
			}
		}
		else
		{
			// The request is being made to the carriers specific to the plan that the service is on
			//TODO! Implement this functionality
			// Is the request made to the CarrierFullService AND the CarrierPreselect?
			// If you were sending a Preselection request, would you send it to the CarrierFullService as well?
		}
		
		TransactionCommit();
		
		// If the request is barring related then create a system note
		// (if more than 1 service, then make the one note but don't specify a service)
		switch ($intRequestType)
		{
			case PROVISIONING_TYPE_BAR:
				$strBarAction = "Bar";
				break;
			case PROVISIONING_TYPE_UNBAR:
				$strBarAction = "Bar Reversal";
				break;
			default:
				break;
		}
		
		if (isset($strBarAction))
		{
			if (count($arrServiceIds) > 1)
			{
				// A request has been made on multiple services.  Don't associate a service with the System Note
				$strSystemNote	= "Provisioning Request: $strBarAction, has been made on multiple services";
				$intServiceId	= NULL;
			}
			else
			{
				// A request has been made on a single service
				$strSystemNote	= "Provisioning Request: $strBarAction";
				$intServiceId	= $arrServiceIds[0];
			}
			SaveSystemNote($strSystemNote, DBO()->Account->AccountGroup->Value, DBO()->Account->Id->Value, NULL, $intServiceId);
			Ajax()->FireOnNewNoteEvent(DBO()->Account->Id->Value, $intServiceId);
		}
		
		// Fire the OnProvisioningRequestSubmission Event
		$arrEvent['Account']['Id'] = DBO()->Account->Id->Value;
		$arrEvent['Service']['Id'] = $intServiceId;
		Ajax()->FireEvent(EVENT_ON_PROVISIONING_REQUEST_SUBMISSION, $arrEvent);
		
		// Notify the user of the outcome
		Ajax()->AddCommand("Alert", "Provisioning Request has been successfully submitted");
		
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// CancelRequest
	//------------------------------------------------------------------------//
	/**
	 * CancelRequest()
	 *
	 * Cancels a Provisioning Request, but only if it has not already been sent
	 * 
	 * Cancels a Provisioning Request, but only if it has not already been sent
	 * It expects the following objects to be defined:
	 * 		DBO()->ProvisioningRequest->Id	Id of the request to cancel
	 * 
	 * If the cancellation is successful it will fire an EVENT_ON_PROVISIONING_REQUEST_CANCELLATION 
	 * event passing the following Event object data:
	 *		Service.Id				= id of the service that the request belongs to
	 *		ProvisioningRequest.Id	= id of the request which was cancelled
	 *  
	 * @return		void
	 * @method		SubmitRequest
	 */
	function CancelRequest()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		if (!DBO()->ProvisioningRequest->Load())
		{
			// The record could not be retrieved
			Ajax()->AddCommand("Alert", "ERROR: The Request with Id ". DBO()->ProvisioningRequest->Id->Value ." could not be found");
			return TRUE;
		}
		
		// Only requests with Status == REQUEST_STATUS_WAITING can be cancelled
		if (DBO()->ProvisioningRequest->Status->Value != REQUEST_STATUS_WAITING)
		{
			Ajax()->AddCommand("Alert", "ERROR: The request cannot be cancelled as it has already been sent");
			return TRUE;
		}
		
		// Update the status of the request
		DBO()->ProvisioningRequest->Status = REQUEST_STATUS_CANCELLED;
		
		if (!DBO()->ProvisioningRequest->Save())
		{
			// Saving the changes failed
			Ajax()->AddCommand("Alert", "ERROR: Cancelling the request failed, unexpectedly");
			return TRUE;
		}
		
		// Fire the EVENT_ON_PROVISIONING_REQUEST_CANCELLATION event
		$arrEvent['ProvisioningRequest']['Id']	= DBO()->ProvisioningRequest->Id->Value;
		$arrEvent['Service']['Id']				= DBO()->ProvisioningRequest->Service->Value;
		
		Ajax()->FireEvent(EVENT_ON_PROVISIONING_REQUEST_CANCELLATION, $arrEvent);
		
		// Notify the user of the outcome
		Ajax()->AddCommand("Alert", "Provisioning Request has been successfully cancelled");
		
		return TRUE;
	}
	//------------------------------------------------------------------------//
	// ViewHistory
	//------------------------------------------------------------------------//
	/**
	 * ViewHistory()
	 *
	 * Performs the logic for the View Provisioning History popup window
	 * 
	 * Performs the logic for the View Provisioning History popup window
	 * Assumes
	 * 		DBO()->Service->Id 		When viewing a single service
	 * OR
	 * 		DBO()->Account->Id		When viewing for a whole account
	 *
	 * @return		void
	 * @method
	 *
	 */
	function ViewHistory()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		$intAccountId					= DBO()->Account->Id->Value;
		$intServiceId					= DBO()->Service->Id->Value;
		DBO()->History->CategoryFilter	= PROVISIONING_HISTORY_CATEGORY_BOTH;
		DBO()->History->TypeFilter		= PROVISIONING_HISTORY_FILTER_ALL;
		DBO()->History->MaxItems		= 50;


		if (DBO()->Service->Id->IsSet)
		{
			DBO()->Service->Load();
			DBO()->Account->Id = DBO()->Service->Account->Value;
		}
		DBO()->Account->Load();

		// Retrieve the history
		$mixResult = $this->GetHistory(DBO()->History->CategoryFilter->Value, DBO()->History->TypeFilter->Value, $intAccountId, $intServiceId, DBO()->History->MaxItems->Value);
		DBO()->History->Records	= $mixResult;
		
		// The account should already be set up as a DBObject
		if ($mixResult === FALSE)
		{
			Ajax()->AddCommand("Alert", "ERROR: An unforseen error occurred when trying to retrieve the provisioning history");
			return TRUE;
		}
		
		if (count($mixResult) == 0)
		{
			Ajax()->AddCommand("Alert", "There are no items to display in the provisioning history");
			return TRUE;
		}
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('provisioning_history_view');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// RenderHistoryList
	//------------------------------------------------------------------------//
	/**
	 * RenderHistoryList()
	 *
	 * Rerenders the HtmlTemplateProvisioningHistoryList object based on the filter conditions
	 * 
	 * Rerenders the HtmlTemplateProvisioningHistoryList object based on the filter conditions
	 * Assumes
	 * 		DBO()->Account->Id				is set
	 * 		DBO()->Service->Id 				is set, when viewing a single service
	 * 		DBO()->History->CategoryFilter	is set
	 * 		DBO()->History->TypeFilter		is set
	 * 		DBO()->History->MaxItems		is set
	 * 		DBO()->History->ContainerDivId	is set
	 * 		DBO()->History->UpdateCookies	is set
	 * 		DBO()->History->JsObjectName	is set
	 *
	 * @return		void
	 * @method
	 */
	function RenderHistoryList()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// Load the history
		$intAccountId		= DBO()->Account->Id->Value;
		$intServiceId		= DBO()->Service->Id->Value;
		$intCategory		= DBO()->History->CategoryFilter->Value;
		$intTypeFilter		= DBO()->History->TypeFilter->Value;
		$intMaxItems		= DBO()->History->MaxItems->Value;
		$bolUpdateCookies	= (DBO()->History->UpdateCookies->Value == TRUE) ? TRUE : FALSE;
		
		// Retrieve the history
		$mixResult = $this->GetHistory($intCategory, $intTypeFilter, $intAccountId, $intServiceId, $intMaxItems, $bolUpdateCookies);

		// The account should already be set up as a DBObject
		if ($mixResult === FALSE)
		{
			Ajax()->AddCommand("Alert", "ERROR: An unforseen error occurred when trying to retrieve the provisioning history");
			return TRUE;
		}
		
		DBO()->History->Records = $mixResult;
		
		// Load the HtmlTemplate
		Ajax()->RenderHtmlTemplate("ProvisioningHistoryList", HTML_CONTEXT_DEFAULT, DBO()->History->ContainerDivId->Value);
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// GetHistory
	//------------------------------------------------------------------------//
	/**
	 * GetHistory()
	 *
	 * Retrieves the Provisioning history for an account or single service
	 * 
	 * Retrieves the Provisioning history for an account or single service
	 *
	 * @param	int		$intCategoryFilter
	 * @param	int		$intTypeFilter
	 * @param	int		$intAccount			optional, Account Id.  Only bother setting if you want to retrieve
	 * 										the provisioning history of all services belonging to this account
	 * @param	int		$intService			optional, Service Id.  Only bother setting if you want to retrieve
	 * 										the provisioning history of a single service.  This one takes 
	 * 										precedence if $intAccount is also set
	 * @param	int		$intMaxItems		optional, the max number of records to retrieve.  Set to 0 to retrieve
	 * 										all items.  Defaults to retrieve all items
	 * @param	bool	$bolUpdateCookies	optional, set to TRUE to have the cookies update according to the filter
	 * 										variables.  (this is not currently implemented)
	 *
	 * @return	mixed		FALSE	: 	An unforseen error has occurred
	 * 						Array	: 	Array of records, where each record is an array with the following
	 * 									fields: Id, FNN, Service, TimeStamp, Outbound, Carrier, Type, 
	 * 									Employee, Status, LinkedRecord 
	 * @method
	 */
	function GetHistory($intCategoryFilter, $intTypeFilter=NULL, $intAccount=NULL, $intService=NULL, $intMaxItems=0, $bolUpdateCookies=FALSE)
	{
		//TODO! I think there are some ProvisioningResponse records which should never be shown, based on their Status
		// Account for this
			
		if ($intService)
		{
			// We are retrieving all records for a specific Service
			
			// Get all Service Ids relating to $intService
			$arrServiceIds = AppTemplateService::GetAllServiceRecordIds($intService);
			
			if (!is_array($arrServiceIds))
			{
				// A database error must have occurred
				return FALSE;
			}
			if (count($arrServiceIds) == 0)
			{
				// It couldn't even return $intService as an Id
				return FALSE; 
			}
			elseif (count($arrServiceIds) == 1)
			{
				$strWhereIdObject = "Service = $intService";
			}
			else
			{
				$strWhereIdObject = "Service IN (". implode(", ", $arrServiceIds) .")";
			}
		}
		else if ($intAccount)
		{
			// We are retrieving all records for a specific Account
			$strWhereIdObject = "Account = $intAccount";
		}
		else
		{
			// Neither and Account nor a Service were specified
			return FALSE;
		}
		
		// Build the limit clause
		switch ($intMaxItems)
		{
			case 0:
				// All available items will be returned
				$strLimitClause = "";
				break;
			default:
				$strLimitClause = "Limit $intMaxItems";
				break;
		}
		
		// Handle the Request/Response Type filter
		switch ($intTypeFilter)
		{
			case PROVISIONING_HISTORY_FILTER_BARRINGS_ONLY:
				// Only include records relating to barrings and barring reversals
				$arrBarringTypes = Array(PROVISIONING_TYPE_BAR, PROVISIONING_TYPE_UNBAR);
				$strTypeFilter = "Type IN (". implode(", ", $arrBarringTypes) .")";
				break;
			case 0;
				// No type filter is being used
				$strTypeFilter = NULL;
				break;
			default:
				// The filter is assumed to be a single Request/Response Type
				$strTypeFilter = "Type = $intTypeFilter";
				break;
		}
		
		// Build Query clauses
		/* There are 2 main queries that are executed
		 * The first finds out how many records to retrieve to represent $intMaxItems TimeStamps
		 * The second retrieves the actual records from the ProvisioningRequest and ProvisioningResponse tables 
		 */
		//$strRecCountRequestFromClause	= "SELECT RequestedOn AS 'TimeStamp', Employee, COUNT(Id) AS RecCount FROM ProvisioningRequest WHERE $strWhereIdObject ". (($strTypeFilter != NULL)? "AND $strTypeFilter" : "") ." GROUP BY TimeStamp, Employee";
		//$strRecCountResponseFromClause	= "SELECT EffectiveDate AS 'TimeStamp', NULL as Employee, COUNT(Id) AS RecCount FROM ProvisioningResponse WHERE $strWhereIdObject ". (($strTypeFilter != NULL)? "AND $strTypeFilter" : "") ." GROUP BY TimeStamp, Employee";
		
		$strRequestSelect	= "SELECT Id, RequestedOn AS 'TimeStamp', 1 AS 'Outbound', Service, FNN, Carrier, Type, Response AS 'LinkedRecord', Status, Description, Employee FROM ProvisioningRequest WHERE $strWhereIdObject ". (($strTypeFilter != NULL)? "AND $strTypeFilter" : "");
		$strResponseSelect	= "SELECT Id, EffectiveDate AS 'TimeStamp', 0 AS 'Outbound', Service, FNN, Carrier, Type, Request AS 'LinkedRecord', Status, Description, NULL AS Employee FROM ProvisioningResponse WHERE $strWhereIdObject AND Status = ". RESPONSE_STATUS_IMPORTED ." ". (($strTypeFilter != NULL)? "AND $strTypeFilter" : "");
		
		switch ($intCategoryFilter)
		{
			case PROVISIONING_HISTORY_CATEGORY_REQUESTS:
				// The user just wants to retrieve the requests
				//$strRecCountFromClause	= $strRecCountRequestFromClause;
				$strRecRetrievalQuery	= $strRequestSelect; 
				break;
			case PROVISIONING_HISTORY_CATEGORY_RESPONSES:
				// The user just wants to retrieve the responses
				//$strRecCountFromClause	= $strRecCountResponseFromClause;
				$strRecRetrievalQuery	= $strResponseSelect;
				break;
			case PROVISIONING_HISTORY_CATEGORY_BOTH:
				// The user just wants to retrieve both requests and responses
				//$strRecCountFromClause	= $strRecCountRequestFromClause ." UNION ". $strRecCountResponseFromClause;
				$strRecRetrievalQuery	= $strRequestSelect ." UNION ". $strResponseSelect;
				break;
			default:
				return FALSE;
		}
		
		$strRecRetrievalQuery = "$strRecRetrievalQuery ORDER BY TimeStamp DESC, Service, Id DESC $strLimitClause";
		
		// Because we are using a UNION to retrieve a record set built from 2 seperate 
		// queries, we must use a Query object instead of a StatementSelect object
		$qryHistory = new Query();
		$objRecordSet = $qryHistory->Execute($strRecRetrievalQuery);
		
		if ($objRecordSet === FALSE)
		{
			// An error occurred in retrieving the records
			return FALSE;
		}
		
		// Load each record retrieved into an array
		$arrHistory = Array();
		for ($arrRow = $objRecordSet->fetch_assoc(); $arrRow != NULL; $arrRow = $objRecordSet->fetch_assoc())
		{
			$arrHistory[] = $arrRow;
		}
		
		return $arrHistory;
	}

	//------------------------------------------------------------------------//
	// _LoadServiceDetails
	//------------------------------------------------------------------------//
	/**
	 * _LoadServiceDetails()
	 *
	 * Sets up the DBL()->Service object, and loads the data from the database
	 * 
	 * Sets up the DBL()->Service object, and loads the data from the database
	 * 
	 * @param		int		$intAccountId	Id of the Account
	 *
	 * @return		boolean					TRUE on success; FALSE on failure
	 * @method		_LoadServiceDetails
	 */
	private function _LoadServiceDetails($intAccountId)
	{
		// Retrieve all the services belonging to the account and whether or not they have address details defined
		$strTables	= "	Service AS S LEFT JOIN ServiceAddress AS SA ON S.Id = SA.Service 
						LEFT JOIN ServiceRatePlan AS SRP1 ON S.Id = SRP1.Service AND SRP1.Id = (SELECT SRP2.Id 
								FROM ServiceRatePlan AS SRP2 
								WHERE SRP2.Service = S.Id AND NOW() BETWEEN SRP2.StartDatetime AND SRP2.EndDatetime
								ORDER BY SRP2.CreatedOn DESC
								LIMIT 1
								)
						LEFT JOIN RatePlan AS RP1 ON SRP1.RatePlan = RP1.Id
						LEFT JOIN ServiceRatePlan AS SRP3 ON S.Id = SRP3.Service AND SRP3.Id = (SELECT SRP4.Id 
								FROM ServiceRatePlan AS SRP4 
								WHERE SRP4.Service = S.Id AND SRP4.StartDatetime BETWEEN NOW() AND SRP4.EndDatetime
								ORDER BY SRP4.CreatedOn DESC
								LIMIT 1
								)
						LEFT JOIN RatePlan AS RP2 ON SRP3.RatePlan = RP2.Id";
		$arrColumns	= Array("Id" 						=> "S.Id",
							"FNN"						=> "S.FNN", 
							"Status"		 			=> "S.Status",
							"LineStatus"				=> "S.LineStatus",
							"LineStatusDate"			=> "S.LineStatusDate",
							"CreatedOn"					=> "S.CreatedOn", 
							"ClosedOn"					=> "S.ClosedOn",
							"AddressId"					=> "SA.Id",
							"CurrentPlanId" 			=> "RP1.Id",
							"CurrentPlanName"			=> "RP1.Name",
							"FuturePlanId"				=> "RP2.Id",
							"FuturePlanName"			=> "RP2.Name",
							"FuturePlanStartDatetime"	=> "SRP3.StartDatetime");
		$strWhere	= "S.Account = <AccountId> AND S.ServiceType IN (". SERVICE_TYPE_LAND_LINE .")";
		$arrWhere	= Array("AccountId" => $intAccountId);
		DBL()->Service->SetTable($strTables);
		DBL()->Service->SetColumns($arrColumns);
		DBL()->Service->Where->Set($strWhere, $arrWhere);
		DBL()->Service->OrderBy("S.FNN ASC, S.Id DESC");
		return DBL()->Service->Load();
	}

	//------------------------------------------------------------------------//
	// GetServices
	//------------------------------------------------------------------------//
	/**
	 * GetServices()
	 *
	 * Builds an array structure defining every service belonging to the account, and a history of their status, and their plan details
	 * 
	 * Builds an array structure defining every service belonging to the account, and a history of their status, and their plan details
	 * The history details when the service was activated(or created) and Closed(disconnected or archived)
	 * It will always have at least one record
	 * On Success the returned array will be of the format:
	 * $arrServices[]	['Id']
	 * 					['FNN']
	 * 					['ServiceType']
	 * 					['AddressId']						Only store a reference to the most recent Service record's ServiceAddress record
	 * 					['CurrentPlan']	['Id']
	 * 									['Name']
	 * 					['FuturePlan']	['Id']
	 * 									['Name']
	 * 									['StartDatetime']
	 * 					['History'][]	['ServiceId']		These will be ordered from Latest to Earliest Service records modelling this Service for this account
	 * 									['CreatedOn']
	 * 									['ClosedOn']
	 * 									['CreatedBy']
	 * 									['ClosedBy']
	 * 									['Status']
	 * 									['LineStatus']
	 * 									['LineStatusDate']
	 * 
	 * @param	int		$intAccount		Id of the Account to retrieve the services of 
	 * @param	int		$intFilter		optional, Filter constant.  Defaults to SERVICE_ACTIVE
	 * 									0 						:	Retrieve all LandLine Services
	 * 									SERVICE_ACTIVE			:	Retrieve all LandLine Services with ClosedOn == NULL or ClosedOn >= NOW()
	 * 									SERVICE_DISCONNECTED	:	Retrieve all LandLine Services with Status == SERVICE_DISCONNECTED AND ClosedOn in the past 
	 *									SERVICE_ARCHIVED		:	Retrieve all LandLine Services with Status == SERVICE_ARCHIVED AND ClosedOn in the past
	 *
	 * @return	mixed					FALSE:	On database error
	 * 									Array:  $arrServices
	 * 	
	 * @method
	 */
	function GetServices($intAccount, $intFilter=0)
	{
		// Retrieve all the services belonging to the account and whether or not they have address details defined
		$strTables	= "	Service AS S LEFT JOIN ServiceAddress AS SA ON S.Id = SA.Service
						LEFT JOIN ServiceRatePlan AS SRP1 ON S.Id = SRP1.Service AND SRP1.Id = (SELECT SRP2.Id 
								FROM ServiceRatePlan AS SRP2 
								WHERE SRP2.Service = S.Id AND NOW() BETWEEN SRP2.StartDatetime AND SRP2.EndDatetime
								ORDER BY SRP2.CreatedOn DESC
								LIMIT 1
								)
						LEFT JOIN RatePlan AS RP1 ON SRP1.RatePlan = RP1.Id
						LEFT JOIN ServiceRatePlan AS SRP3 ON S.Id = SRP3.Service AND SRP3.Id = (SELECT SRP4.Id 
								FROM ServiceRatePlan AS SRP4 
								WHERE SRP4.Service = S.Id AND SRP4.StartDatetime BETWEEN NOW() AND SRP4.EndDatetime
								ORDER BY SRP4.CreatedOn DESC
								LIMIT 1
								)
						LEFT JOIN RatePlan AS RP2 ON SRP3.RatePlan = RP2.Id";
		$arrColumns	= Array("Id" 						=> "S.Id",
							"FNN"						=> "S.FNN",
							"ServiceType"				=> "S.ServiceType", 
							"Status"		 			=> "S.Status",
							"LineStatus"				=> "S.LineStatus",
							"LineStatusDate"			=> "S.LineStatusDate",
							"CreatedOn"					=> "S.CreatedOn", 
							"ClosedOn"					=> "S.ClosedOn",
							"CreatedBy"					=> "S.CreatedBy", 
							"ClosedBy"					=> "S.ClosedBy",
							"NatureOfCreation"			=> "S.NatureOfCreation",
							"NatureOfClosure"			=> "S.NatureOfClosure",
							"LastOwner"					=> "S.LastOwner",
							"NextOwner"					=> "S.NextOwner",
							"AddressId"					=> "SA.Id",
							"CurrentPlanId" 			=> "RP1.Id",
							"CurrentPlanName"			=> "RP1.Name",
							"FuturePlanId"				=> "RP2.Id",
							"FuturePlanName"			=> "RP2.Name",
							"FuturePlanStartDatetime"	=> "SRP3.StartDatetime");
		$strWhere	= "S.Account = <AccountId> AND S.ServiceType IN (". SERVICE_TYPE_LAND_LINE .") AND (S.ClosedOn IS NULL OR S.CreatedOn <= S.ClosedOn)";
		$arrWhere	= Array("AccountId" => $intAccount);
		$strOrderBy	= ("S.ServiceType ASC, S.FNN ASC, S.Id DESC");
		
		$selServices = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy);
		if ($selServices->Execute($arrWhere) === FALSE)
		{
			// An error occurred
			return FALSE;
		}
		
		$arrServices	= Array();
		$arrRecord		= $selServices->Fetch();
		while ($arrRecord !== FALSE)
		{
			// Create the Service Array
			$arrService = Array (
									"Id"	=> $arrRecord['Id'],
									"FNN"	=> $arrRecord['FNN'],
									"ServiceType"	=> $arrRecord['ServiceType'],
									"AddressId"		=> $arrRecord['AddressId']
								);

			// Add details about the Service's current plan, if it has one
			if ($arrRecord['CurrentPlanId'] != NULL)
			{
				$arrService['CurrentPlan'] = Array	(
														"Id"	=> $arrRecord['CurrentPlanId'],
														"Name"	=> $arrRecord['CurrentPlanName']
													);
			}
			else
			{
				$arrService['CurrentPlan'] = NULL;
			}
			
			// Add details about the Service's Future scheduled plan, if it has one
			if ($arrRecord['FuturePlanId'] != NULL)
			{
				$arrService['FuturePlan'] = Array	(
														"Id"	=> $arrRecord['FuturePlanId'],
														"Name"	=> $arrRecord['FuturePlanName'],
														"StartDatetime"	=> $arrRecord['FuturePlanStartDatetime']
													);
			}
			else
			{
				$arrService['FuturePlan'] = NULL;
			}
			
			// Add this record's details to the history array
			$arrService['History']		= Array();
			$arrService['History'][]	= Array	(
													"ServiceId"			=> $arrRecord['Id'],
													"CreatedOn"			=> $arrRecord['CreatedOn'],
													"ClosedOn"			=> $arrRecord['ClosedOn'],
													"CreatedBy"			=> $arrRecord['CreatedBy'],
													"ClosedBy"			=> $arrRecord['ClosedBy'],
													"NatureOfCreation"	=> $arrRecord['NatureOfCreation'],
													"NatureOfClosure"	=> $arrRecord['NatureOfClosure'],
													"LastOwner"			=> $arrRecord['LastOwner'],
													"NextOwner"			=> $arrRecord['NextOwner'],
													"Status"			=> $arrRecord['Status'],
													"LineStatus"		=> $arrRecord['LineStatus'],
													"LineStatusDate"	=> $arrRecord['LineStatusDate'],
												);
			 
			
			// If multiple Service records relate to the one actual service then they will be consecutive in the RecordSet
			// Find each one and add it to the Status history
			while (($arrRecord = $selServices->Fetch()) !== FALSE)
			{
				if ($arrRecord['FNN'] == $arrService['FNN'])
				{
					// This record relates to the same Service
					$arrService['History'][]	= Array	(
															"ServiceId"	=> $arrRecord['Id'],
															"CreatedOn"	=> $arrRecord['CreatedOn'],
															"ClosedOn"	=> $arrRecord['ClosedOn'],
															"CreatedBy"	=> $arrRecord['CreatedBy'],
															"ClosedBy"	=> $arrRecord['ClosedBy'],
															"NatureOfCreation"	=> $arrRecord['NatureOfCreation'],
															"NatureOfClosure"	=> $arrRecord['NatureOfClosure'],
															"LastOwner"			=> $arrRecord['LastOwner'],
															"NextOwner"			=> $arrRecord['NextOwner'],
															"Status"	=> $arrRecord['Status'],
															"LineStatus"		=> $arrService['LineStatus'],
															"LineStatusDate"	=> $arrService['LineStatusDate'],
														);
				}
				else
				{
					// We have moved on to the next Service
					break;
				}
			}
			
			// Add the Service to the array of Services
			$arrServices[] = $arrService;
		}
		
		// Apply the filter
		$strNow = GetCurrentISODateTime();
		if ($intFilter)
		{
			$arrTempServices	= $arrServices;
			$arrServices		= Array();
			
			foreach ($arrTempServices as $arrService)
			{
				switch ($intFilter)
				{
					case SERVICE_ACTIVE:
						// Only keep the Service if ClosedOn IS NULL OR NOW() OR in the future
						if ($arrService['History'][0]['ClosedOn'] == NULL || $arrService['History'][0]['ClosedOn'] >= $strNow)
						{
							// Keep it
							$arrServices[] = $arrService;
						}
						break;
					
					case SERVICE_DISCONNECTED:
						// Only keep the Service if Status == Disconnected AND ClosedOn < NOW()
						if ($arrService['History'][0]['Status'] == SERVICE_DISCONNECTED && $arrService['History'][0]['ClosedOn'] < $strNow)
						{
							// Keep it
							$arrServices[] = $arrService;
						}
						break;
					
					case SERVICE_ARCHIVED:
						// Only keep the Service if Status == Archived AND ClosedOn < NOW()
						if ($arrService['History'][0]['Status'] == SERVICE_ARCHIVED && $arrService['History'][0]['ClosedOn'] < $strNow)
						{
							// Keep it
							$arrServices[] = $arrService;
						}
						break;
				}
			}
		}
		
		return $arrServices;
	}
}
?>