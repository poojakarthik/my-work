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
 * @author		Sean, Jared 'flame' Herbohn
 * @version		7.05
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
 * The AppTemplateService class
 *
 * The AppTemplateService class.  This incorporates all logic for all pages
 * relating to services
 *
 *
 * @package	ui_app
 * @class	AppTemplateService
 * @extends	ApplicationTemplate
 */
class AppTemplateService extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// View
	//------------------------------------------------------------------------//
	/**
	 * View()
	 *
	 * Performs the logic for viewing a service
	 * 
	 * Performs the logic for viewing a service
	 *
	 * @return		void
	 * @method		View
	 *
	 */
	function View()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		$bolUserHasOperatorPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		// Setup all DBO and DBL objects required for the page
		if (!DBO()->Service->Load())
		{
			DBO()->Error->Message = "The Service id: ". DBO()->Service->Id->value ." you were attempting to view could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		DBO()->Account->Id = DBO()->Service->Account->Value;
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "Can not find Account: ". DBO()->Service->Account->Value . " associated with this service";
			$this->LoadPage('error');
			return FALSE;
		}
		if (DBO()->Service->Indial100->Value)
		{
			DBL()->ServiceExtension->Service = DBO()->Service->Id->Value;
			DBL()->ServiceExtension->Archived = 0;
			DBL()->ServiceExtension->Load();
			DBO()->Service->ELB = (bool)DBL()->ServiceExtension->RecordCount();
		}
		
		// Get the details of the current plan for the service
		DBO()->RatePlan->Id = GetCurrentPlan(DBO()->Service->Id->Value);
		if (DBO()->RatePlan->Id->Value !== FALSE)
		{
			DBO()->RatePlan->Load();
		}
		
		// Calculate unbilled charges (this includes all unbilled Adjustments(charges) and CDRs for the service)
		$fltUnbilledAdjustments					= UnbilledServiceChargeTotal(DBO()->Service->Id->Value);
		$fltUnbilledCDRs						= UnbilledServiceCDRTotal(DBO()->Service->Id->Value);
		DBO()->Service->TotalUnbilledCharges 	= AddGST($fltUnbilledAdjustments + $fltUnbilledCDRs);
		
		// Load the service notes
		LoadNotes(NULL, DBO()->Service->Id->Value);
		
		// context menu
		ContextMenu()->Account_Menu->Service->View_Unbilled_Charges(DBO()->Service->Id->Value);	
		ContextMenu()->Account_Menu->Service->View_Service_Rate_Plan(DBO()->Service->Id->Value);	
		if ($bolUserHasOperatorPerm)
		{
			ContextMenu()->Account_Menu->Service->Edit_Service(DBO()->Service->Id->Value);
			ContextMenu()->Account_Menu->Service->Change_Plan(DBO()->Service->Id->Value);	
			ContextMenu()->Account_Menu->Service->Change_of_Lessee(DBO()->Service->Id->Value);	
			ContextMenu()->Account_Menu->Service->Add_Adjustment(DBO()->Account->Id->Value, DBO()->Service->Id->Value);
			ContextMenu()->Account_Menu->Service->Add_Recurring_Adjustment(DBO()->Account->Id->Value, DBO()->Service->Id->Value);
			// Only Landlines can have provisioning
			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
			{
				ContextMenu()->Account_Menu->Service->Provisioning(DBO()->Service->Id->Value);
			}
			ContextMenu()->Account_Menu->Service->Add_Service_Note(DBO()->Service->Id->Value);
		}
		ContextMenu()->Account_Menu->Service->View_Service_Notes(DBO()->Service->Id->Value);
		
		ContextMenu()->Account_Menu->Account->Account_Overview(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Invoices_and_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->List_Services(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->List_Contacts(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->View_Cost_Centres(DBO()->Account->Id->Value);
		if ($bolUserHasOperatorPerm)
		{
			ContextMenu()->Account_Menu->Account->Add_Services(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Contact(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Make_Payment(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Change_Payment_Method(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Associated_Account(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Account_Note(DBO()->Account->Id->Value);
		}
		ContextMenu()->Account_Menu->Account->View_Account_Notes(DBO()->Account->Id->Value);
		
		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->AccountOverview(DBO()->Service->Account->Value);
		BreadCrumb()->SetCurrentPage("Service");

		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('service_view');
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// Add
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Performs the logic for adding a service
	 * 
	 * Performs the logic for adding a service
	 *
	 * @return		void
	 * @method		Add
	 *
	 */
	function Add()
	{
		// Check user authorization
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		if (!DBO()->Account->Load())
		{
			// The Account could not be loaded
			BreadCrumb()->Employee_Console(DBO()->Account->Id->Value);
			BreadCrumb()->SetCurrentPage("Error");
		
			DBO()->Error->Message = "The account with account id: ". DBO()->Account->Id->value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}

		if (SubmittedForm("AddService","Save"))
		{
			if (DBO()->Service->ServiceType->Value != SERVICE_TYPE_MOBILE)
			{
				// sets the invalid to valid on the DBO within the servicemobiledetail field
				// i.e. if the user hasn't chosen mobile as the service type
				DBO()->ServiceMobileDetail->Clean();
			}
			
			// test initial validation of fields
			if (DBO()->Service->IsInvalid() || ((DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE) && (DBO()->ServiceMobileDetail->IsInvalid())))
			{
				// The form has not passed initial validation
				Ajax()->AddCommand("Alert", "Could not save the service.  Invalid fields are highlighted");
				Ajax()->RenderHtmlTemplate("ServiceAdd", HTML_CONTEXT_DEFAULT, "ServiceAddDiv");
				return TRUE;
			}
			
			// only validate the FNN if it has been supplied
			if (DBO()->Service->FNN->Value != "")
			{
				if (DBO()->Service->FNN->Value != DBO()->Service->FNNConfirm->Value)
				{
					// This is entered if the FNN is different from FNNConfirm 
					// i.e. a typo when entering on the form
					// -------------------------------------------------------				
				
					DBO()->Service->FNN->SetToInvalid();
					DBO()->Service->FNNConfirm->SetToInvalid();
					Ajax()->AddCommand("Alert", "ERROR: Could not save the service.  Service # and Confirm Service # must be the same");
					Ajax()->RenderHtmlTemplate("ServiceAdd", HTML_CONTEXT_DEFAULT, "ServiceAddDiv");
					return TRUE;
				}
				
				// Make sure the new FNN is valid for the service type
				$intServiceType = ServiceType(DBO()->Service->FNN->Value);
				if ($intServiceType != DBO()->Service->ServiceType->Value)
				{
					// The FNN is invalid for the services servicetype, output an appropriate message
					DBO()->Service->FNN->SetToInvalid();
					Ajax()->AddCommand("Alert", "The FNN is invalid for the service type");
					Ajax()->RenderHtmlTemplate("ServiceAdd", HTML_CONTEXT_DEFAULT, "ServiceAddDiv");
					return TRUE;
				}
				
				// Test that the FNN is currently not being used
				$strWhere = "FNN LIKE \"". DBO()->Service->FNN->Value . "\"";
				DBL()->Service->Where->SetString($strWhere);
				DBL()->Service->Load();
				if (DBL()->Service->RecordCount() > 0)
				{	
					DBO()->Service->FNN->SetToInvalid();
					DBO()->Service->FNNConfirm->SetToInvalid();
					Ajax()->AddCommand("Alert", "This Service Number already exists in the Database");
					Ajax()->RenderHtmlTemplate("ServiceAdd", HTML_CONTEXT_DEFAULT, "ServiceAddDiv");
					return TRUE;
				}	
			}
			
			// Test that the costcentre is null i.e. nothing selected set the database to NULL
			if (DBO()->Service->CostCentre->Value == 0)
			{
				DBO()->Service->CostCentre = NULL;
			}	
			// all properties are valid. now set remaining properties of the record service record
			if (DBO()->Service->ServiceType->Value != SERVICE_TYPE_LAND_LINE)
			{
				DBO()->Service->Indial100 = 0;
			}
			DBO()->Service->AccountGroup	= DBO()->Account->AccountGroup->Value;
			DBO()->Service->Account			= DBO()->Account->Id->Value;
			DBO()->Service->CreatedOn		= GetCurrentDateForMySQL();
			DBO()->Service->CreatedBy 		= AuthenticatedUser()->_arrUser['Id'];
			DBO()->Service->CappedCharge	= 0;
			DBO()->Service->UncappedCharge	= 0;
			
			DBO()->Service->SetColumns("Id, FNN, ServiceType, Indial100, AccountGroup, Account, CostCentre, CappedCharge, UncappedCharge, CreatedOn, CreatedBy");

			// Start the transaction
			TransactionStart();

			// Save the Service record
			if (!DBO()->Service->Save())
			{
				// inserting records into the database failed unexpectedly
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: saving this service failed, unexpectedly");
				return TRUE;
			}
			
			// The service record was successfully saved.  Now add the record specific to the type of service
			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
			{
				// Service is a mobile phone.  Add record to ServiceMobileDetail table
				DBO()->ServiceMobileDetail->Account			= DBO()->Account->Id->Value;
				DBO()->ServiceMobileDetail->AccountGroup	= DBO()->Account->AccountGroup->Value;
				DBO()->ServiceMobileDetail->Service			= DBO()->Service->Id->Value;
				DBO()->ServiceMobileDetail->DOB				= ConvertUserDateToMySqlDate(DBO()->ServiceMobileDetail->DOB->Value);
				DBO()->ServiceMobileDetail->SetColumns("Id, AccountGroup, Account, Service, SimPUK, SimESN, SimState, DOB, Comments");
				
				// Save the ServiceMobileDetail record
				if (!DBO()->ServiceMobileDetail->Save())
				{
					// inserting the record into the database failed unexpectedly
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: saving this service failed, unexpectedly");
					return TRUE;
				}
			}

			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
			{
				// Service is an inbound 1300/1800 number.  Add record to ServiceInboundDetail table
				DBO()->ServiceInboundDetail->Service		= DBO()->Service->Id->Value;
				DBO()->ServiceInboundDetail->Complex		= 0;
				DBO()->ServiceInboundDetail->SetColumns("Id, Service, AnswerPoint, Complex, Configuration");				
			
				// Save the ServiceInboundDetail record
				if (!DBO()->ServiceInboundDetail->Save())
				{
					// inserting the record into the database failed unexpectedly
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: saving this service failed, unexpectedly");
					return TRUE;
				}				
			}

			// All records defining the service have successfully been inserted into the database
			
			// commit the transaction
			TransactionCommit();

			Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "This service was successfully created", "Location" => Href()->InvoicesAndPayments(DBO()->Account->Id->Value)));
			return TRUE;
		}
		
		// Context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->Invoices_And_Payments(DBO()->Account->Id->Value);
		BreadCrumb()->SetCurrentPage("Add Service");
		
		$this->LoadPage('service_add');
	}
	
	
	//------------------------------------------------------------------------//
	// Edit
	//------------------------------------------------------------------------//
	/**
	 * Edit()
	 *
	 * Performs the logic for th "Edit Service" popup
	 * 
	 * Performs the logic for th "Edit Service" popup
	 * If the service is successfully updated then it will fire an EVENT_ON_SERVICE_UPDATE event
	 * passing the following object:
	 *		objObject.Service.Id	= id of the service which has been updated
	 *		objObject.NewService.Id	= id of the new service, if the service being updated, 
	 *		was activated, and a new service had to be made.  See KB article KB00005
	 *
	 * @return		void
	 * @method		Edit
	 *
	 */
	function Edit()
	{
		// Check user authorization
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		if (SubmittedForm("EditService","Apply Changes"))
		{
			if (DBO()->Service->IsInvalid())
			{
				// The form has not passed initial validation
				Ajax()->AddCommand("Alert", "ERROR: Could not save the service.  Invalid fields are highlighted");
				Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
				return TRUE;
			}
			
			DBO()->Service->FNN = trim(DBO()->Service->FNN->Value);
			DBO()->Service->FNNConfirm = trim(DBO()->Service->FNNConfirm->Value);
			
			// If these have been updated record the details
			if (DBO()->Service->Indial100->Value != DBO()->Service->CurrentIndial100->Value)
			{
				$strChangesNote .= "Indial100" . ((DBO()->Service->Indial100->Value == 1) ? " is set" : " is not set") . "\n";
			}
			if (DBO()->Service->ELB->Value != DBO()->Service->CurrentELB->Value)
			{
				$strChangesNote .= "ELB" . ((DBO()->Service->ELB->Value == 1) ? " is set" : " is not set") . "\n";
			}
			
			if (DBO()->Service->FNN->Value != DBO()->Service->CurrentFNN->Value)
			{		
				// The user wants to change the FNN
				if (DBO()->Service->FNN->Value != DBO()->Service->FNNConfirm->Value)
				{
					// The FNN was re-entered incorrectly
					DBO()->Service->FNN->SetToInvalid();
					DBO()->Service->FNNConfirm->SetToInvalid();
					Ajax()->AddCommand("Alert", "ERROR: Could not save the service.  Service # and Confirm Service # must be the same");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
					return TRUE;
				}
				
				// Check that the FFN is valid
				if (!isValidFNN(DBO()->Service->FNN->Value))
				{
					// The FNN is invalid
					DBO()->Service->FNN->SetToInvalid();
					Ajax()->AddCommand("Alert", "ERROR: The FNN is not a valid Australian Full National Number");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
					return TRUE;
				}
				
				// Make sure the new FNN is valid for the service type
				$intServiceType = ServiceType(DBO()->Service->FNN->Value);
				if ($intServiceType != DBO()->Service->ServiceType->Value)
				{
					// The FNN is invalid for the service's ServiceType.
					DBO()->Service->FNN->SetToInvalid();
					Ajax()->AddCommand("Alert", "ERROR: The FNN is invalid for the service type");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
					return TRUE;
				}
				
				// Check that the FNN is not currently being used
				// Retrieve all service records that are currently using the FNN
				$strWhere = "FNN=<FNN> AND (ClosedOn IS NULL OR ClosedOn >= NOW())";
				$arrWhere = Array("FNN" => DBO()->Service->FNN->Value);
				DBL()->Service->Where->Set($strWhere, $arrWhere);
				DBL()->Service->Load();
				if (DBL()->Service->RecordCount() > 0)
				{	
					// The FNN is currently being used
					DBO()->Service->FNN->SetToInvalid();
					Ajax()->AddCommand("Alert", "ERROR: This FNN is currently being used by another service");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
					return TRUE;
				}
				else
				{
					// Declare properties to update
					$arrUpdateProperties[] = "FNN";
					$strChangesNote .= "FNN was changed to: " . DBO()->Service->FNN->Value . "\n";
				}
			}
			
			// Check if the CostCentre property has been updated
			if (DBO()->Service->CostCentre->Value !== NULL)
			{
				// If CostCentre == 0 then set it to NULL
				if (DBO()->Service->CostCentre->Value == 0)
				{
					DBO()->Service->CostCentre = NULL;
				}
				
				// Check if the value of the Service's cost centre property has been changed
				if (DBO()->Service->CostCentre->Value != DBO()->Service->CurrentCostCentre->Value)
				{
					// Add it to the list of properties to update
					$arrUpdateProperties[] = "CostCentre";
					
					// Work out what to stick in the System Note
					if (DBO()->Service->CostCentre->Value == NULL)
					{
						// Now there is no CostCentre associated with the service
						$strChangesNote .= "Now there is no CostCentre associated with this service\n";
					}
					else
					{
						// Retrieve the name of the CostCentre
						DBO()->CostCentre->Id = DBO()->Service->CostCentre->Value;
						DBO()->CostCentre->Load();
						$strChangesNote .= "CostCentre has been set to '". DBO()->CostCentre->Name->Value ."'\n";
					}
				}
			}

			// Declare the transaction
			TransactionStart();

			// Check if the Extension Level Billing property has been updated
			if (DBO()->Service->ELB->Value !== NULL)
			{
				if (DBO()->Service->ELB->Value)
				{
					// Enable ELB
					if (!($this->Framework->EnableELB(DBO()->Service->Id->Value)))
					{
						// EnableELB failed
						TransactionRollback();
						Ajax()->AddCommand("Alert", "ERROR: Enabling ELB failed, unexpectedly.  All modifications to the service have been aborted");
						return TRUE;
					}
				}
				else
				{
					// Disable ELB
					if (!($this->Framework->DisableELB(DBO()->Service->Id->Value)))
					{
						// DisableELB failed
						TransactionRollback();
						Ajax()->AddCommand("Alert", "ERROR: Disabling ELB failed, unexpectedly.  All modifications to the service have been aborted");
						return TRUE;
					}
				}
			}
			
			// If the FNN is changing and the service has ELB, then you will have to update the the FNNs in the ServiceExtension table
			// relating to this service
			if ((DBO()->Service->FNN->Value != DBO()->Service->CurrentFNN->Value) && (DBO()->Service->ELB->Value !== NULL))
			{
				// Update the records in the ServiceExtension table that relate to this Service
				$strFirstFNN = substr(DBO()->Service->FNN->Value, 0, 8) . "00";
				$strQuery = "UPDATE ServiceExtension SET Name = LPAD('$strFirstFNN' + RangeStart, 10, '0') WHERE Service = ". DBO()->Service->Id->Value;

				$qryUpdateServiceExtension = new Query();
				if ($qryUpdateServiceExtension->Execute($strQuery) == FALSE)
				{
					// Updating the ServiceExtension table failed, unexpectedly
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Updating the FNNs in the ServiceExtension table failed, unexpectedly.  All modifications to the service have been aborted");
					return TRUE;
				}
			}

			// Save the changes to the Service Table, if any changes were made
			if (count($arrUpdateProperties) > 0)
			{
				// Declare columns to update
				DBO()->Service->SetColumns($arrUpdateProperties);
				
				// Save the service to the service table
				if (!DBO()->Service->Save())
				{
					// The service did not save
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Updating the service details failed, unexpectedly");
					return TRUE;
				}
			}

			// handle mobile phone details			
			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
			{
				// Has anything been changed in the ServiceMobileDetails if so append this information onto $strChangesNote
				if (DBO()->ServiceMobileDetail->SimPUK->Value != DBO()->ServiceMobileDetail->CurrentSimPUK->Value)
				{
					$strChangesNote .= "SimPUK was changed to: " . DBO()->ServiceMobileDetail->SimPUK->Value ."\n";
				}
				if (DBO()->ServiceMobileDetail->SimESN->Value != DBO()->ServiceMobileDetail->CurrentSimESN->Value)
				{
					$strChangesNote .= "SimESN was changed to: " . DBO()->ServiceMobileDetail->SimESN->Value ."\n";				
				}
				if (DBO()->ServiceMobileDetail->SimState->Value != DBO()->ServiceMobileDetail->CurrentSimState->Value)
				{
					$strChangesNote .= "SimState was changed to: " . GetConstantDescription(DBO()->ServiceMobileDetail->SimState->Value, 'ServiceStateType') . "\n";
				}
				if (DBO()->ServiceMobileDetail->Comments->Value != DBO()->ServiceMobileDetail->CurrentComments->Value)
				{
					$strChangesNote .= "Comments were changed to: " . DBO()->ServiceMobileDetail->Comments->Value ."\n";
				}
			
				// Validate entered Birth Date?
				if (trim(DBO()->ServiceMobileDetail->DOB->Value) != "")
				{
					// A date of birth has been specified
					if (!Validate("ShortDate", DBO()->ServiceMobileDetail->DOB->Value))
					{
						TransactionRollback();	
						Ajax()->AddCommand("Alert", "ERROR: This is not a valid Date, please use DD/MM/YYYY");
						Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
						return TRUE;
					}
					
					// set DOB to MySql date format
					DBO()->ServiceMobileDetail->DOB = ConvertUserDateToMySqlDate(DBO()->ServiceMobileDetail->DOB->Value);
					if (DBO()->ServiceMobileDetail->DOB->Value != DBO()->ServiceMobileDetail->CurrentDOB->Value)
					{
						$strChangesNote .= "DOB was changed to: " . DBO()->ServiceMobileDetail->DOB->FormattedValue() ."\n";			
					}
				}
				else
				{
					// A date of Birth has not been specified
					DBO()->ServiceMobileDetail->DOB = NULL;
				}

				// If Id is passed set the columns to Update
				if (DBO()->ServiceMobileDetail->Id->Value)
				{
					// Update the existing MobileServiceDetail Record
					DBO()->ServiceMobileDetail->SetColumns("SimPUK, SimESN, SimState, DOB, Comments");
				}
				else
				{
					// Create a new MobileServiceDetail Record
					DBO()->ServiceMobileDetail->SetColumns("Id, AccountGroup, Account, Service, SimPUK, SimESN, SimState, DOB, Comments");
					DBO()->ServiceMobileDetail->Id = 0;
					DBO()->ServiceMobileDetail->AccountGroup = DBO()->Service->AccountGroup->Value;
					DBO()->ServiceMobileDetail->Account = DBO()->Service->Account->Value;
					DBO()->ServiceMobileDetail->Service = DBO()->Service->Id->Value;					
				}

				if (DBO()->ServiceMobileDetail->SimPUK->Value == NULL)
				{
					DBO()->ServiceMobileDetail->SimPUK  = "";
				}
				if (DBO()->ServiceMobileDetail->SimESN->Value == NULL)
				{
					DBO()->ServiceMobileDetail->SimESN  = "";
				}
				if (DBO()->ServiceMobileDetail->SimState->Value == NULL)
				{
					DBO()->ServiceMobileDetail->SimState  = "";
				}
				if (DBO()->ServiceMobileDetail->DOB->Value == NULL)
				{
					DBO()->ServiceMobileDetail->DOB = "0000-00-00";
				}
				if (DBO()->ServiceMobileDetail->Comments->Value == NULL)
				{
					DBO()->ServiceMobileDetail->Comments  = "";
				}

				
				if (!DBO()->ServiceMobileDetail->Save())
				{
					// The ServiceMobileDetail did not save
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Updating the mobile details failed, unexpectedly");
					return TRUE;
				}
				// The mobile details saved successfully
			}
			
			// Handle inbound call details
			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
			{
				// Has anything been changed in the ServiceInboundDetails if so append this information onto $strChangesNote
				if (DBO()->ServiceInboundDetail->AnswerPoint->Value != DBO()->ServiceInboundDetail->CurrentAnswerPoint->Value)
				{
					$strChangesNote = "AnswerPoint was changed to: " . DBO()->ServiceInboundDetail->AnswerPoint->Value ."\n";
				}
				if (DBO()->ServiceInboundDetail->Configuration->Value != DBO()->ServiceInboundDetail->CurrentConfiguration->Value)
				{
					$strChangesNote = "Configuration was changed to: " . DBO()->ServiceInboundDetail->Configuration->Value ."\n";					
				}
			
				// If Id is passed set the columns to Update
				if (DBO()->ServiceInboundDetail->Id->Value)
				{
					// Update the existing ServiceInboundDetail Record
					DBO()->ServiceInboundDetail->SetColumns("AnswerPoint, Configuration");
				}
				else
				{
					// Create a new ServiceInboundDetail Record
					DBO()->ServiceInboundDetail->SetColumns("Id, Service, AnswerPoint, Complex, Configuration");
					DBO()->ServiceInboundDetail->Id = 0;
					DBO()->ServiceInboundDetail->Service = DBO()->Service->Id->Value;
				}

				if (DBO()->ServiceInboundDetail->AnswerPoint->Value == NULL)
				{
					DBO()->ServiceInboundDetail->AnswerPoint  = "";
				}
				if (DBO()->ServiceInboundDetail->Complex->Value == NULL)
				{
					DBO()->ServiceInboundDetail->Complex  = 0;
				}
				if (DBO()->ServiceInboundDetail->Configuration->Value == NULL)
				{
					DBO()->ServiceInboundDetail->Configuration  = "";
				}

				if (!DBO()->ServiceInboundDetail->Save())
				{
					// The InboundDetail did not save
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Updating the inbound details failed, unexpectedly");
					return TRUE;
				}
				// The inbound details saved successfully
			}
			
			// All details regarding the service have been successfully updated

			// Handle updating the Service Status
			// First check that the Service Status has actually changed
			if (DBO()->Service->NewStatus->Value != DBO()->Service->Status->Value)
			{
				switch (DBO()->Service->NewStatus->Value)
				{
					case SERVICE_ACTIVE:
						$mixResult = $this->_ActivateService(DBO()->Service->Id->Value, DBO()->Service->FNN->Value, DBO()->Service->ClosedOn->Value);
						if ($mixResult !== TRUE)
						{
							// Activating the service failed, and an error message has been returned
							TransactionRollback();
							Ajax()->AddCommand("Alert", $mixResult);
							return TRUE;
						}
						else
						{
							// Activating the service was successfull. Define system generated note
							$strDateTime = OutputMask()->LongDateAndTime(GetCurrentDateAndTimeForMySQL());
							$strUserName = GetEmployeeName(AuthenticatedUser()->_arrUser['Id']);
							$strNoteDetails = "Activated on $strDateTime by $strUserName";
						}
						break;
					case SERVICE_ARCHIVED:
						// Check that the user has permission to archive the service
						if (!AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN))
						{
							// The user does not have permission to Archive the service
							TransactionRollback();
							Ajax()->AddCommand("Alert", "ERROR: You do not have permission to archive services");
							return TRUE;
						}
						// Now perform the same logic to disconnect a service
					case SERVICE_DISCONNECTED:
						// Set ClosedOn date to today's date
						DBO()->Service->ClosedOn = GetCurrentDateForMySQL();
						// Set ClosedBy to authenticated user ID
						DBO()->Service->ClosedBy = AuthenticatedUser()->_arrUser['Id'];
						// Set the Status
						DBO()->Service->Status = DBO()->Service->NewStatus->Value;
						
						// Define system generated note
						$strDateTime	= OutputMask()->LongDateAndTime(GetCurrentDateAndTimeForMySQL());
						$strUserName	= GetEmployeeName(AuthenticatedUser()->_arrUser['Id']);
						$strAction		= GetConstantDescription(DBO()->Service->Status->Value, "Service");
						$strNoteDetails	= "$strAction on $strDateTime by $strUserName";
						
						// Declare columns to update
						DBO()->Service->SetColumns("ClosedOn, ClosedBy, Status");
						// Save the service to the service table
						if (!DBO()->Service->Save())
						{
							// The service did not save
							TransactionRollback();
							Ajax()->AddCommand("Alert", "ERROR: Updating the service details failed, unexpectedly");
							return TRUE;
						}
						break;
				}
			}

			// Add an automatic note if the service has been archived or unarchived
			if ($strNoteDetails)
			{
				if (DBO()->NewService->Id->Value)
				{
					// The service was activated, which required a new service to be created
					$intServiceId = DBO()->NewService->Id->Value;
					$intOldServiceRecordId = DBO()->Service->Id->Value;
					
					// Create a note for the old service Id detailing what has happened
					$strNoteForOldServiceRecord = "This service has been activated which required the creation of a new service record.";
					$strNoteForOldServiceRecord = "  Please refer to the new service record (id: $intOldServiceRecordId) for future use of this service.";
					
					SaveSystemNote($strNoteForOldServiceRecord, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, $intOldServiceRecordId);
				}
				else
				{
					// A new service record was new created, so add the note to the current service record
					$intServiceId = DBO()->Service->Id->Value;
				}
			
				$strNote  = "Service with Id: $intServiceId and FNN: ". DBO()->Service->FNN->Value;
				$strNote .= " has been ". $strNoteDetails;
				
				if ($strChangesNote)
				{
					// Append the other changes made to the service, to this note
					$strNote .= "\nThe following changes we also made:\n$strChangesNote";
				}
				
				// Save the note. (this will save it to the new service id, if one was created)
				SaveSystemNote($strNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, $intServiceId);
			}
			else
			{
				$strUserName = GetEmployeeName(AuthenticatedUser()->_arrUser['Id']);
				$strDateTime = OutputMask()->LongDateAndTime(GetCurrentDateAndTimeForMySQL());
			
				$strSystemChangesNote  = "Service edited by $strUserName on $strDateTime\nThe following changes were made:\n$strChangesNote";
				SaveSystemNote($strSystemChangesNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, DBO()->Service->Id->Value);
			}

			// Commit the transaction
			TransactionCommit();
			
			// Close the popup
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("Alert", "The service was successfully updated");

			// Build event object
			// The contents of this object should be declared in the doc block of this method
			$arrEvent['Service']['Id'] = DBO()->Service->Id->Value;
			if (DBO()->NewService->Id->Value)
			{
				$arrEvent['NewService']['Id'] = DBO()->NewService->Id->Value;
			}
			Ajax()->FireEvent(EVENT_ON_SERVICE_UPDATE, $arrEvent);
			
			// Fire the OnNewNote Event
			Ajax()->FireOnNewNoteEvent(DBO()->Service->Account->Value, DBO()->Service->Id->Value);
			
			return TRUE;
		}
		
		// Load the service record
		if (!DBO()->Service->Load())
		{
			Ajax()->AddCommand("Alert", "ERROR: The service with id: ". DBO()->Service->Id->Value ." you were attempting to view could not be found");
			return FALSE;
		}
	
		// Check that the user has permission to edit this service
		if ((DBO()->Service->Status->Value == SERVICE_ARCHIVED) && (!$bolUserHasAdminPerm))
		{
			// The service is archived and the user doesn't have Admin permissions so they can't edit it
			Ajax()->AddCommand("Alert", "ERROR: Due to the service's status, and your permissions, you cannot edit this service");
			return TRUE;
		}
		
		// load mobile detail if the service is a mobile
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
		{
			DBO()->ServiceMobileDetail->Where->Service = DBO()->Service->Id->Value;
			DBO()->ServiceMobileDetail->Load();
		}
		
		// load inbound detail if the service is an inbound 1300/1800
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
		{
			DBO()->ServiceInboundDetail->Where->Service = DBO()->Service->Id->Value;
			DBO()->ServiceInboundDetail->Load();
		}
		
		// Set up the ELB checkbox, if service is an indial100
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE && DBO()->Service->Indial100->Value)
		{
			// Check if ELB is currently Enabled or Disabled
			DBL()->ServiceExtension->Service = DBO()->Service->Id->Value;
			DBL()->ServiceExtension->Archived = 0;
			DBL()->ServiceExtension->Load();
			DBO()->Service->ELB = (bool)DBL()->ServiceExtension->RecordCount();
		}
		
		// Store the current FNN to check between states that the FNN textbox has been changed
		DBO()->Service->CurrentFNN = DBO()->Service->FNN->Value;
		DBO()->Service->CurrentStatus = DBO()->Service->Status->Value;
		DBO()->Service->CurrentIndial100 = DBO()->Service->Indial100->Value;
		DBO()->Service->CurrentELB = DBO()->Service->ELB->Value;
		DBO()->Service->CurrentCostCentre = DBO()->Service->CostCentre->Value;
		DBO()->ServiceInboundDetail->CurrentAnswerPoint = DBO()->ServiceInboundDetail->AnswerPoint->Value;
		DBO()->ServiceInboundDetail->CurrentConfiguration = DBO()->ServiceInboundDetail->Configuration->Value;
		DBO()->ServiceMobileDetail->CurrentSimPUK = DBO()->ServiceMobileDetail->SimPUK->Value;
		DBO()->ServiceMobileDetail->CurrentSimESN = DBO()->ServiceMobileDetail->SimESN->Value;
		DBO()->ServiceMobileDetail->CurrentSimState = DBO()->ServiceMobileDetail->SimState->Value;
		DBO()->ServiceMobileDetail->CurrentDOB = DBO()->ServiceMobileDetail->DOB->Value;
		DBO()->ServiceMobileDetail->CurrentComments = DBO()->ServiceMobileDetail->Comments->Value;
		
		// Declare which page to use
		$this->LoadPage('service_edit');
		return TRUE;
	}	
	
	function ViewPlan()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		$bolUserHasOperatorPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Service->Load())
		{
			DBO()->Error->Message = "The Service id: ". DBO()->Service->Id->Value ." you were attempting to view could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		DBO()->Account->Id = DBO()->Service->Account->Value;
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "Can not find Account: ". DBO()->Service->Account->Value . " associated with this service";
			$this->LoadPage('error');
			return FALSE;
		}

		// Added an amendment where a check is performed for EndDattime != NOW() as it appears the enddatetime is being set to now() 
		// which is in turn showing these records on a page refresh

		// Retrieve all rate groups currently used by this service
		// Retrieve the list of RateGroups belonging to the RatePlan that the service is currently using
		DBL()->RatePlanRateGroup->SetTable("RateGroup, RatePlanRateGroup");
		$arrRatePlanRateGroupColumns = Array("RateGroupId"=>"RateGroup.Id", "RateGroupName"=>"RateGroup.Name", "RateGroupDescription"=>"RateGroup.Description", "RateGroupRecordType"=>"RateGroup.RecordType");
		DBL()->RatePlanRateGroup->SetColumns($arrRatePlanRateGroupColumns);
		$strWhere = "RateGroup.Id=RatePlanRateGroup.RateGroup AND RatePlanRateGroup.RatePlan = (SELECT RatePlan FROM ServiceRatePlan WHERE NOW( ) BETWEEN StartDatetime AND EndDatetime != NOW() AND EndDatetime AND Service =<Service> ORDER BY CreatedOn DESC LIMIT 0, 1)";
		DBL()->RatePlanRateGroup->Where->Set($strWhere, Array('Service' => DBO()->Service->Id->Value));
		DBL()->RatePlanRateGroup->OrderBy("RateGroup.Id");
		DBL()->RatePlanRateGroup->Load();
		
		// Retrieve the list of RateGroups currently used by the Service
		DBL()->ServiceRateGroup->SetTable("RateGroup, ServiceRateGroup");
		$arrServiceRateGroupColumns = Array("Id"=>"RateGroup.Id", "Name"=>"RateGroup.Name", "Description"=>"RateGroup.Description", "RecordType"=>"RateGroup.RecordType", "Fleet"=>"RateGroup.Fleet");
		DBL()->ServiceRateGroup->SetColumns($arrServiceRateGroupColumns);
		$strWhere = "(NOW() BETWEEN StartDatetime AND EndDatetime) AND EndDatetime != NOW() AND RateGroup.Id = ServiceRateGroup.RateGroup AND ServiceRateGroup.Service=<Service>";
		DBL()->ServiceRateGroup->Where->Set($strWhere, Array('Service' => DBO()->Service->Id->Value));
		DBL()->ServiceRateGroup->OrderBy("RateGroup.RecordType");
		DBL()->ServiceRateGroup->Load();
		
		// Loop through each RateGroup belonging to the Service and find out which ones actually belong to the RatePlan and which ones are OverRiders
		foreach (DBL()->ServiceRateGroup as $dboServiceRateGroup)
		{
			// initialise the "IsPartOfRatePlan" flag to FALSE
			$dboServiceRateGroup->IsPartOfRatePlan = FALSE;
			
			// Try and find the ServiceRateGroup in the list of RateGroups belonging to the RatePlan
			foreach (DBL()->RatePlanRateGroup as $dboRatePlanRateGroup)
			{
				if ($dboServiceRateGroup->Id->Value == $dboRatePlanRateGroup->RateGroupId->Value)
				{
					// This RateGroup belongs to the RatePlan; flag it as such
					$dboServiceRateGroup->IsPartOfRatePlan = TRUE;
					break;
				}
			}
		}
		
		// context menu
		ContextMenu()->Account_Menu->Service->View_Service(DBO()->Service->Id->Value);		
		ContextMenu()->Account_Menu->Service->View_Unbilled_Charges(DBO()->Service->Id->Value);	
		if ($bolUserHasOperatorPerm)
		{
			ContextMenu()->Account_Menu->Service->Edit_Service(DBO()->Service->Id->Value);		
			ContextMenu()->Account_Menu->Service->Change_Plan(DBO()->Service->Id->Value);	
			ContextMenu()->Account_Menu->Service->Change_of_Lessee(DBO()->Service->Id->Value);	
			ContextMenu()->Account_Menu->Service->Add_Adjustment(DBO()->Account->Id->Value, DBO()->Service->Id->Value);
			ContextMenu()->Account_Menu->Service->Add_Recurring_Adjustment(DBO()->Account->Id->Value, DBO()->Service->Id->Value);
			// Only LandLines can have Provisioning
			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
			{
				ContextMenu()->Account_Menu->Service->Provisioning(DBO()->Service->Id->Value);
			}
			if ($bolUserHasAdminPerm)
			{
				// Only admin staff can override rate groups
				ContextMenu()->Account_Menu->Service->Override_Rate_Group(DBO()->Service->Id->Value);
			}
			
			ContextMenu()->Account_Menu->Service->Add_Service_Note(DBO()->Service->Id->Value);
		}
		ContextMenu()->Account_Menu->Service->View_Service_Notes(DBO()->Service->Id->Value);
		
		ContextMenu()->Account_Menu->Account->Account_Overview(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Invoices_And_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->List_Services(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->List_Contacts(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->View_Cost_Centres(DBO()->Account->Id->Value);
		if ($bolUserHasOperatorPerm)
		{
			ContextMenu()->Account_Menu->Account->Add_Services(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Contact(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Make_Payment(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Change_Payment_Method(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Associated_Account(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Account_Note(DBO()->Account->Id->Value);
		}
		ContextMenu()->Account_Menu->Account->View_Account_Notes(DBO()->Account->Id->Value);
		
		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->AccountOverview(DBO()->Account->Id->Value);
		BreadCrumb()->ViewService(DBO()->Service->Id->Value, DBO()->Service->FNN->Value);
		BreadCrumb()->SetCurrentPage("Plan");
		
		$this->LoadPage('service_plan_view');
		return TRUE;
	}	
	
	// Why is this here?  I think this belongs in AppTemplateRate or AppTemplateRatePlan
	function ViewRates()
	{
		// logic for loading the view groups on the drop down div
		// last line should be a loadpage that loads the logic into a html page
		// have to trap if the user has specified to search for nothing i.e. a blank search
		// as this will attempt to retrieve every record and cripples the page
		$strSearchString = DBO()->Rate->SearchString->Value;
		if (trim($strSearchString) != "")
		{
			$strWhere = "Name like '%$strSearchString%' AND Id IN (SELECT Rate FROM RateGroupRate WHERE RateGroup=<RateGroupId>)";
			DBL()->Rate->Where->Set($strWhere, Array('RateGroupId' => DBO()->RateGroup->Id->Value));
			DBL()->Rate->Load();
		}
		else
		{
			// An invalid search string was used (an empty string)
			Ajax()->AddCommand("Alert", "ERROR: Please specify a name or partial name to search");
			return TRUE;
		}
		
		$this->LoadPage('rate_search_results');
		return TRUE;		
	}
	
	//------------------------------------------------------------------------//
	// ChangePlan
	//------------------------------------------------------------------------//
	/**
	 * ChangePlan()
	 *
	 * Performs the logic for "Change Plan" popup
	 * 
	 * Performs the logic for "Change Plan" popup
	 * If the service successfully has its plan changed then it will fire an 
	 * EVENT_ON_SERVICE_UPDATE event passing the following Event object data:
	 *		Service.Id		= id of the service which has had its plan changed
	 *		OldRatePlan.Id	= id of the old RatePlan for the service
	 *		NewRatePlan.Id	= id of the new RatePlan for the service
	 *		NewRatePlan.Name	= name of the new RatePlan for the service
	 *
	 * @return		void
	 * @method		ChangePlan
	 *
	 */
	function ChangePlan()
	{
		// Check user authorization here
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		
		if (SubmittedForm("ChangePlan","Change Plan"))
		{
			// check if the selected plan is the same as the previous plan if it is don't commit to database
			// just refresh page i.e. go back a page
			if (DBO()->NewPlan->Id->Value == DBO()->RatePlan->Id->Value)
			{
				// The new plan is the same as the existing plan, exit gracefully
				Ajax()->AddCommand("Alert", "No update has been saved as the new plan is the same as the previous plan");
				return TRUE;
			}
			
			// Record the current time (the new plan starts at this time)
			$strNowTimeStamp = GetCurrentDateAndTimeForMySQL();
			
			// Record the end time for the old plan (1 second before now)
			$strOldPlanEndDatetime = date("Y-m-d H:i:s", strtotime($strNowTimeStamp) - 1);
			
			// Start the database transaction
			TransactionStart();

			// All current ServiceRateGroup records must have EndDatetime set to $strOldPlanEndDatetime
			$arrUpdate = Array('EndDatetime' => $strOldPlanEndDatetime);
			$updServiceRateGroup = new StatementUpdate("ServiceRateGroup", "Service = <Service> AND EndDatetime >= <NowTimeStamp>", $arrUpdate);
			if ($updServiceRateGroup->Execute($arrUpdate, Array("Service"=>DBO()->Service->Id->Value, "NowTimeStamp"=>$strNowTimeStamp)) === FALSE)
			{
				// Could not update records in ServiceRateGroup table. Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br>(Error updating the current plan's rate groups to end today)");
				return TRUE;
			}
			
			// All current ServiceRatePlan records must have EndDatetime set to $strNowTimeStamp
			$updServiceRatePlan = new StatementUpdate("ServiceRatePlan", "Service = <Service> AND EndDatetime >= <NowTimeStamp>", $arrUpdate);
			if ($updServiceRatePlan->Execute($arrUpdate, Array("Service"=>DBO()->Service->Id->Value, "NowTimeStamp"=>$strNowTimeStamp)) === FALSE)
			{
				// Could not update records in ServiceRatePlan table. Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br>(Error updating the current plan to end today)");
				return TRUE;
			}
			
			// Declare the new plan for the service
			// Insert a record into the ServiceRatePlan table
			DBO()->ServiceRatePlan->Service 		= DBO()->Service->Id->Value;
			DBO()->ServiceRatePlan->RatePlan 		= DBO()->NewPlan->Id->Value;
			DBO()->ServiceRatePlan->CreatedBy 		= AuthenticatedUser()->_arrUser['Id'];
			DBO()->ServiceRatePlan->CreatedOn 		= $strNowTimeStamp;
			DBO()->ServiceRatePlan->StartDatetime 	= $strNowTimeStamp;
			DBO()->ServiceRatePlan->EndDatetime 	= END_OF_TIME;
			
			if (!DBO()->ServiceRatePlan->Save())
			{
				// Could not save the record. Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br>(Error adding record to ServiceRatePlan table)");
				return TRUE;
			}
			
			// Retrieve the rate groups belonging to the rate plan
			DBL()->RatePlanRateGroup->RatePlan = DBO()->NewPlan->Id->Value;
			DBL()->RatePlanRateGroup->Load();
			
			// For each Rate Group, save a record to the ServiceRateGroup table
			// Define constant properties for these records
			DBO()->ServiceRateGroup->Service 		= DBO()->Service->Id->Value;
			DBO()->ServiceRateGroup->CreatedBy 		= AuthenticatedUser()->_arrUser['Id'];
			DBO()->ServiceRateGroup->CreatedOn 		= $strNowTimeStamp;
			DBO()->ServiceRateGroup->StartDatetime 	= $strNowTimeStamp;
			DBO()->ServiceRateGroup->EndDatetime 	= END_OF_TIME;
			
			
			foreach (DBL()->RatePlanRateGroup as $dboRatePlanRateGroup)
			{
				// Set the id of the record to 0, so that it is inserted as a new record when saved
				DBO()->ServiceRateGroup->Id = 0;
				DBO()->ServiceRateGroup->RateGroup = $dboRatePlanRateGroup->RateGroup->Value;
				
				// Save the record to the ServiceRateGroup table
				if (!DBO()->ServiceRateGroup->Save())
				{
					// Could not save the record. Exit gracefully
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br>(Error adding new records to ServiceRateGroup table)");
					return TRUE;
				}
			}
			
			//TODO! Do automatic provisioning here
			
			// Add a system note describing the change of plan
			DBO()->Service->Load();
			if (DBO()->RatePlan->Id->Value)
			{
				DBO()->RatePlan->Load();
			}
			else
			{
				// The Service has not previously had a RatePlan
				DBO()->RatePlan->Name = "undefined";
			}
			DBO()->NewPlan->SetTable("RatePlan");
			DBO()->NewPlan->Load();
			if (DBO()->Service->FNN->Value)
			{
				$strNote = "Service with Id: ". DBO()->Service->Id->Value ." and FNN: ". DBO()->Service->FNN->Value ." has had its plan changed from '";
			}
			else
			{
				$strNote = "Service with Id: ". DBO()->Service->Id->Value ." has had its plan changed from '";
			}
			$strNote .= DBO()->RatePlan->Name->Value ."' to '". DBO()->NewPlan->Name->Value ."'";
			SaveSystemNote($strNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, DBO()->Service->Id->Value);
			
			// All changes to the database, required to define the plan change, have been completed
			// Commit the transaction
			TransactionCommit();
			
			// Close the popup
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("Alert", "The service's plan has been successfully changed");

			// Build event object
			// The contents of this object should be declared in the doc block of this method
			$arrEvent['Service']['Id']			= DBO()->Service->Id->Value;
			$arrEvent['OldRatePlan']['Id']		= (DBO()->RatePlan->Id->Value) ? DBO()->RatePlan->Id->Value : 0;
			$arrEvent['NewRatePlan']['Id']		= DBO()->NewPlan->Id->Value;
			$arrEvent['NewRatePlan']['Name']	= DBO()->NewPlan->Name->Value;
			Ajax()->FireEvent(EVENT_ON_SERVICE_UPDATE, $arrEvent);

			// Since a system note has been added, fire the OnNewNote event
			Ajax()->FireOnNewNoteEvent(DBO()->Service->Account->Value, DBO()->Service->Id->Value);

			return TRUE;
		}		
		
		// Retrieve the service details
		if (!DBO()->Service->Load())
		{
			DBO()->Error->Message = "The Service id: ". DBO()->Service->Id->value ." you were attempting to view could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// Retrieve the Account Details
		DBO()->Account->Id = DBO()->Service->Account->Value;
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "Can not find Account: ". DBO()->Service->Account->Value . " associated with this service";
			$this->LoadPage('error');
			return FALSE;
		}
		
		$this->LoadPage('plan_change');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _ActivateService
	//------------------------------------------------------------------------//
	/**
	 * _ActivateService()
	 *
	 * Performs the database modifications required of activating the service
	 * 
	 * Performs the database modifications required of activating the service
	 * If a new Service record has to be created, then DBO()->NewService will store the
	 * contents of this new service.
	 *
	 * @precondition	This function should be encapsulated by a database transaction (TransactionStart),
	 *					which should be rolled back if the function returns anything other than TRUE
	 * 		
	 * @param			integer		$intService		Id of the service to activate
	 * @param			string		$strFNN			FNN of the service to activate
	 * @param			string		$strClosedOn	date on which the service was closed (YYYY-MM-DD)
	 *	 
	 * @return			mix			returns TRUE if the service can be activated, else it returns an error 
	 *								message (string) detailing why the service could not be activated
	 * @method
	 */
	// 
	private function _ActivateService($intService, $strFNN, $strClosedOn)
	{
		// Check if the FNN is currently in use
		$selFNN = new StatementSelect("Service", "Id", "FNN=<FNN> AND (ClosedOn IS NULL OR ClosedOn >= NOW()) AND Id != <Service>");
		if ($selFNN->Execute(Array('FNN' => $strFNN, "Service" => $intService)))
		{
			// At least one record was returned, which means the FNN is currently in use by an active service
			return 	"ERROR: Cannot activate this service as the FNN: $strFNN is currently being used by another service.  ". 
					"The other service must be disconnected or archived before this service can be activated";
		}
		
		// Check if the FNN has been used by another de-activated service since $intService was de-activated
		$selFNN = new StatementSelect("Service", "Id", "FNN = <FNN> AND Id != <Service> AND ClosedOn > <ClosedOn>");
		if ($selFNN->Execute(Array('FNN' => $strFNN, 'Service' => $intService, 'ClosedOn' => $strClosedOn)) == 0)
		{
			// The FNN has not been used since this service was de-activated.  Activate the service
			DBO()->ArchivedService->SetTable("Service");
			DBO()->ArchivedService->SetColumns("Id, ClosedOn, Status");
			DBO()->ArchivedService->Id 			= $intService;
			DBO()->ArchivedService->ClosedOn 	= NULL;
			DBO()->ArchivedService->Status 		= SERVICE_ACTIVE;
			if (!DBO()->ArchivedService->Save())
			{
				// There was an error while trying to activate the service
				return "ERROR: Activating the service failed, unexpectedly";
			}
			
			// Service was activated successfully
			return TRUE;
		}
		
		// The FNN has been used by another service since this service was last de-activated
		// Now both services are de-activated.  Create a new service
		$intOldServiceId = $intService;
		DBO()->NewService->SetTable("Service");
		DBO()->NewService->Id = $intOldServiceId;
		DBO()->NewService->Load();
		
		// By setting the Id to zero, a new record will be inserted when the Save method is executed
		DBO()->NewService->Id			= 0;
		DBO()->NewService->CreatedOn	= GetCurrentDateForMySQL();
		DBO()->NewService->CreatedBy	= AuthenticatedUser()->_arrUser['Id'];
		DBO()->NewService->ClosedOn		= NULL;
		DBO()->NewService->ClosedBy		= NULL;
		DBO()->NewService->Status		= SERVICE_ACTIVE;
		if (!DBO()->NewService->Save())
		{
			return "ERROR: Activating the service failed, unexpectedly";
		}
		
		// Save extra service details like mobile details, and inbound details and address details
		switch (DBO()->NewService->ServiceType->Value)
		{
			case SERVICE_TYPE_MOBILE:
				DBO()->NewServiceMobileDetail->Where->Service = $intOldServiceId;
				DBO()->NewServiceMobileDetail->SetTable("ServiceMobileDetail");
				if (DBO()->NewServiceMobileDetail->Load())
				{
					DBO()->NewServiceMobileDetail->Service = DBO()->NewService->Id->Value;
					DBO()->NewServiceMobileDetail->Id = 0;
					DBO()->NewServiceMobileDetail->Save();
				}
				break;
			case SERVICE_TYPE_INBOUND:
				DBO()->NewServiceInboundDetail->Where->Service = $intOldServiceId;
				DBO()->NewServiceInboundDetail->SetTable("ServiceInboundDetail");
				if (DBO()->NewServiceInboundDetail->Load())
				{
					DBO()->NewServiceInboundDetail->Service = DBO()->NewService->Id->Value;
					DBO()->NewServiceInboundDetail->Id = 0;
					DBO()->NewServiceInboundDetail->Save();
				}
				break;
			case SERVICE_TYPE_LAND_LINE:
				DBO()->NewServiceAddress->Where->Service = $intOldServiceId;
				DBO()->NewServiceAddress->SetTable("ServiceAddress");
				if (DBO()->NewServiceAddress->Load())
				{
					DBO()->NewServiceAddress->Service = DBO()->NewService->Id->Value;
					DBO()->NewServiceAddress->Id = 0;
					DBO()->NewServiceAddress->Save();
				}
				
				// Handle ELB if in use
				if (DBO()->NewService->Indial100->Value)
				{
					// This will perform an insert query for each new record added to the ServiceExtension table.  It could have been done
					// with just one query if StatementInsert could accomodate SELECT querys for the VALUES clause
					// You could use the ExecuteQuery class defined in vixen/framework/db_access to do this in just one query
					DBL()->NewServiceExtension->Service = $intOldServiceId;
					DBL()->NewServiceExtension->SetTable("ServiceExtension");
					DBL()->NewServiceExtension->Load();
					foreach (DBL()->NewServiceExtension as $dboServiceExtension)
					{
						$dboServiceExtension->Service = DBO()->NewService->Id->Value;
						$dboServiceExtension->Id = 0;
						$dboServiceExtension->Save();
					}
				}
				break;
			default:
				break;
		}
		
		// Give the new service the same RatePlan as the old service
		$strWhere = "Service = <OldService> AND StartDatetime = (SELECT MAX(StartDatetime) FROM ServiceRatePlan WHERE Service = <OldService> AND NOW() BETWEEN StartDatetime AND EndDatetime)";
		DBO()->ServiceRatePlan->Where->Set($strWhere, Array('OldService' => $intOldServiceId));
		if (DBO()->ServiceRatePlan->Load())
		{
			// Save the record for the new plan
			$strOldStartDatetime = DBO()->ServiceRatePlan->StartDatetime->Value;
			$strNow = GetCurrentDateAndTimeForMySQL();
			DBO()->ServiceRatePlan->Service = DBO()->NewService->Id->Value;
			
			DBO()->ServiceRatePlan->CreatedOn = $strNow;
			DBO()->ServiceRatePlan->StartDatetime = $strNow;
			DBO()->ServiceRatePlan->CreatedBy = AuthenticatedUser()->_arrUser['Id'];
			DBO()->ServiceRatePlan->Id = 0;
			DBO()->ServiceRatePlan->Save();

			// Give the new service all the RateGroups of the old service, where StartDatetime >= DBO()->ServiceRatePlan->StartDatetime->Value
			// When adding a plan in the old system, records were added to ServiceRateGroup before the record was added to ServiceRatePlan.
			// The discrepancy in the start times shouldn't be any more than a couple of seconds, but just to be on the safe side, I'm 
			// retrieving all records added to the ServiceRateGroup table that were added up to a day before the current plan was added 
			// to the ServiceRatePlan table
			$strWhere = "Service = <OldService> AND StartDatetime > SUBTIME(<OldPlanStartDatetime>, SEC_TO_TIME(60*60*24))";
			DBL()->ServiceRateGroup->Where->Set($strWhere, Array('OldService' => $intOldServiceId, 'OldPlanStartDatetime' => $strOldStartDatetime));
			DBL()->ServiceRateGroup->Load();
			foreach (DBL()->ServiceRateGroup as $dboServiceRateGroup)
			{
				$dboServiceRateGroup->Service = DBO()->NewService->Id->Value;
				$dboServiceRateGroup->CreatedOn = $strNow;
				$dboServiceRateGroup->StartDatetime = $strNow;
				$dboServiceRateGroup->CreatedBy = AuthenticatedUser()->_arrUser['Id'];
				$dboServiceRateGroup->Id = 0;
				$dboServiceRateGroup->Save();
			}
		}
		else
		{
			// The archived service does not have a plan that is still considered active
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// BulkSetPlanForUnplanned  This is not currently in use
	//------------------------------------------------------------------------//
	/**
	 * BulkSetPlanForUnplanned()
	 *
	 * Performs the logic for declaring plans for all services that have an FNN but no current plan
	 * 
	 * Performs the logic for declaring plans for all services that have an FNN but no current plan
	 *
	 * @return		void
	 * @method
	 *
	 */
	function BulkSetPlanForUnplanned()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		
		//check if the form was submitted
		if (SubmittedForm('SetPlans', 'Submit Changes'))
		{
			TransactionStart();
			
			$mixReturn = $this->_BulkSetPlans();
			if ($mixReturn === TRUE)
			{
				TransactionCommit();
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert"=>"Plans have been successfully set", "Location" => Href()->Admin_Console()));
				return TRUE;
			}
			elseif ($mixReturn === FALSE)
			{
				TransactionRollback();
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert"=>"ERROR: Commiting changes to the database failed, unexpectedly", "Location" => Href()->Admin_Console()));
				return TRUE;
			}
			else
			{
				TransactionRollback();
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert"=>"ERROR: Commiting changes to the database failed, unexpectedly", "Location" => Href()->Admin_Console()));
				return TRUE;
			}
		}
		
		// context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		// breadcrumb menu
		//TODO! define what goes in the breadcrumb menu (assuming this page uses one)
		//BreadCrumb()->Invoices_And_Payments(DBO()->Account->Id->Value);
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SetCurrentPage("Services Without Plans");
		
		
		// Retrieve the list of services that currently don't have an active plan
		// Shouldn't constants be used here instead of the actual numbers?
		$strWhere = "ServiceType >= 100 AND ServiceType <= 104 AND ClosedOn IS NULL AND Id NOT IN (SELECT Service FROM ServiceRatePlan WHERE NOW( ) BETWEEN StartDatetime AND EndDatetime)";
		DBL()->Service->Where->Set($strWhere);
		DBL()->Service->Load();
		
		// retrieve a list of all plans for each type of service
		DBL()->RatePlan->Archived = 0;
		DBL()->RatePlan->OrderBy("Name");
		DBL()->RatePlan->Load();
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('set_unplanned_services');

		return TRUE;
	}
	
	function _BulkSetPlans()
	{
		foreach (DBO()->Service as $strService=>$objNewPlan)
		{
			if ($objNewPlan->Value)
			{
				// A plan has been declared for the service
				$intServiceId = str_replace("NewPlan", "", $strService);
				if (!ChangePlan($intServiceId, $objNewPlan->Value))
				{
					// The plan couldn't declared for some reason
					return FALSE;
				}
			}
			return TRUE;
		}
	}
	
	
	//----- DO NOT REMOVE -----//
	
}
