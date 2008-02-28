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
		
		// If the Service has a CostCentre then retrieve the name of it
		if (DBO()->Service->CostCentre->Value)
		{
			DBO()->CostCentre->Id = DBO()->Service->CostCentre->Value;
			DBO()->CostCentre->Load();
			DBO()->Service->CostCentre = DBO()->CostCentre->Name->Value;
		}
		
		
		// Get the details of the current plan for the service
		DBO()->CurrentRatePlan->Id = GetCurrentPlan(DBO()->Service->Id->Value);
		if (DBO()->CurrentRatePlan->Id->Value)
		{
			DBO()->CurrentRatePlan->SetTable("RatePlan");
			DBO()->CurrentRatePlan->Load();
		}
		
		DBO()->FutureRatePlan->Id = GetPlanScheduledForNextBillingPeriod(DBO()->Service->Id->Value);
		if (DBO()->FutureRatePlan->Id->Value)
		{
			DBO()->FutureRatePlan->SetTable("RatePlan");
			DBO()->FutureRatePlan->Load();
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
	// Add  This functionality is not actually used yet
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
			
			// Retrieve properties of the Service record that arent already set
			DBO()->Service->LoadMerge();
			
			DBO()->Service->FNN = trim(DBO()->Service->FNN->Value);
			DBO()->Service->FNNConfirm = trim(DBO()->Service->FNNConfirm->Value);
			
			// If these have been updated record the details
			if (DBO()->Service->Indial100->Value != DBO()->Service->CurrentIndial100->Value)
			{
				// You can't actually change this property.  It can only be declared when the service is created
				$strChangesNote .= "Indial100" . ((DBO()->Service->Indial100->Value == 1) ? " is set" : " is not set") . "\n";
			}
			if (DBO()->Service->ELB->Value != DBO()->Service->CurrentELB->Value)
			{
				$strChangesNote .= "ELB" . ((DBO()->Service->ELB->Value == 1) ? " is set" : " is not set") . "\n";
			}
			if (DBO()->Service->ForceInvoiceRender->Value != DBO()->Service->CurrentForceInvoiceRender->Value)
			{
				$strChangesNote .= "'Always shown on invoice' has been set to ".  ((DBO()->Service->ForceInvoiceRender->Value)? "'Yes'":"'No'") . "\n";
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
					$strChangesNote .= "FNN was changed from ". DBO()->Service->CurrentFNN->Value ." to " . DBO()->Service->FNN->Value . "\n";
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
						
						// Retrieve the name of the last CostCentre
						if (DBO()->Service->CurrentCostCentre->Value == NULL)
						{
							$strChangesNote .= "CostCentre has been set to '". DBO()->CostCentre->Name->Value ."'\n";
						}
						else
						{
							DBO()->CurrentCostCentre->SetTable("CostCentre");
							DBO()->CurrentCostCentre->Id = DBO()->Service->CurrentCostCentre->Value;
							DBO()->CurrentCostCentre->Load();
							$strChangesNote .= "CostCentre has changed from '". DBO()->CurrentCostCentre->Name->Value ."' to '". DBO()->CostCentre->Name->Value ."'\n";
						}
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
			if (!DBO()->Service->Save())
			{
				// The service did not save
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Updating the service details failed, unexpectedly.  All modifications to the service have been aborted");
				return TRUE;
			}

			// Handle mobile phone details			
			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
			{
				// Load the current ServiceMobileDetail record
				DBO()->CurrentServiceMobileDetail->Where->Service = DBO()->Service->Id->Value;
				DBO()->CurrentServiceMobileDetail->SetTable("ServiceMobileDetail");
				$bolRecordFound = DBO()->CurrentServiceMobileDetail->Load();
				
				if ($bolRecordFound)
				{
					// The service already has a ServiceMobileDetail record associated with it
					// Check if anything has changed
					if (DBO()->ServiceMobileDetail->SimPUK->Value != DBO()->CurrentServiceMobileDetail->SimPUK->Value)
					{
						$strChangesNote .= "SimPUK was changed from '". DBO()->CurrentServiceMobileDetail->SimPUK->Value ."' to '" . DBO()->ServiceMobileDetail->SimPUK->Value ."'\n";
					}
					if (DBO()->ServiceMobileDetail->SimESN->Value != DBO()->CurrentServiceMobileDetail->SimESN->Value)
					{
						$strChangesNote .= "SimESN was changed from '". DBO()->CurrentServiceMobileDetail->SimESN->Value ."' to '" . DBO()->ServiceMobileDetail->SimESN->Value ."'\n";				
					}
					if (DBO()->ServiceMobileDetail->SimState->Value != DBO()->CurrentServiceMobileDetail->SimState->Value)
					{
						$strChangesNote .= "SimState was changed from '". 
											GetConstantDescription(DBO()->CurrentServiceMobileDetail->SimState->Value, 'ServiceStateType') . 
											"' to '" . GetConstantDescription(DBO()->ServiceMobileDetail->SimState->Value, 'ServiceStateType') . "'\n";
					}
					if (DBO()->ServiceMobileDetail->Comments->Value != DBO()->CurrentServiceMobileDetail->Comments->Value)
					{
						$strChangesNote .= "Comments were changed from '". DBO()->CurrentServiceMobileDetail->Comments->Value . 
											"' to '" . DBO()->ServiceMobileDetail->Comments->Value ."'\n";
					}
				}
				else
				{
					// The service does not already have a ServiceMobileDetail record associated with it
					// Only add details to the note, if something has actually been defined
					if (DBO()->ServiceMobileDetail->SimPUK->Value)
					{
						$strChangesNote .= "SimPUK has been defined\n";
					}
					if (DBO()->ServiceMobileDetail->SimESN->Value)
					{
						$strChangesNote .= "SimESN has been defined\n";				
					}
					if (DBO()->ServiceMobileDetail->SimState->Value)
					{
						$strChangesNote .= "SimState has been defined\n";
					}
					if (DBO()->ServiceMobileDetail->Comments->Value)
					{
						$strChangesNote .= "Mobile Comments have been defined\n";
					}
				}
			
				// Validate entered Birth Date
				if (trim(DBO()->ServiceMobileDetail->DOB->Value) != "")
				{
					// A date of birth has been specified
					if (!Validate("ShortDate", DBO()->ServiceMobileDetail->DOB->Value))
					{
						TransactionRollback();
						DBO()->ServiceMobileDetail->DOB->SetToInvalid();
						Ajax()->AddCommand("Alert", "ERROR: This is not a valid Date, please use DD/MM/YYYY");
						Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
						return TRUE;
					}
					
					// Set DOB to MySql date format
					DBO()->ServiceMobileDetail->DOB = ConvertUserDateToMySqlDate(DBO()->ServiceMobileDetail->DOB->Value);
					if ($bolRecordFound)
					{
						if (DBO()->ServiceMobileDetail->DOB->Value != DBO()->CurrentServiceMobileDetail->DOB->Value)
						{
							$strChangesNote .= "DOB was changed from '". OutputMask()->ShortDate(DBO()->CurrentServiceMobileDetail->DOB->Value) ."' to '" . DBO()->ServiceMobileDetail->DOB->FormattedValue() ."'\n";			
						}
					}
					else
					{
						$strChangesNote .= "DOB was changed to '". DBO()->ServiceMobileDetail->DOB->FormattedValue() ."'\n";
					}
				}
				else
				{
					// A date of Birth has not been specified
					DBO()->ServiceMobileDetail->DOB = NULL;
				}

				// If Id is passed set the columns to Update
				if ($bolRecordFound)
				{
					DBO()->ServiceMobileDetail->Id = DBO()->CurrentServiceMobileDetail->Id->Value;
					
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
					Ajax()->AddCommand("Alert", "ERROR: Updating the mobile details failed, unexpectedly.  All modifications to the service have been aborted");
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
					Ajax()->AddCommand("Alert", "ERROR: Updating the inbound details failed, unexpectedly.  All modifications to the service have been aborted");
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
							$strNoteDetails = "activated";
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
						$strNoteDetails = strtolower(GetConstantDescription(DBO()->Service->Status->Value, "Service"));
						
						// Declare columns to update
						DBO()->Service->SetColumns("ClosedOn, ClosedBy, Status");
						// Save the service to the service table
						if (!DBO()->Service->Save())
						{
							// The service did not save
							TransactionRollback();
							Ajax()->AddCommand("Alert", "ERROR: Updating the service details failed, unexpectedly.  All modifications to the service have been aborted");
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
					$strNoteForOldServiceRecord = 	"This service has been activated which required the creation of a new service record.".
													"  Please refer to the new service record (id: $intServiceId) for future use of this service.";
					
					SaveSystemNote($strNoteForOldServiceRecord, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, $intOldServiceRecordId);
				}
				else
				{
					// A new service record was new created, so add the note to the current service record
					$intServiceId = DBO()->Service->Id->Value;
				}
			
				$strNote  = "Service has been $strNoteDetails";
				
				if ($strChangesNote)
				{
					// Append the other changes made to the service, to this note
					$strNote .= "\nThe following changes were also made:\n$strChangesNote";
				}
				
				// Save the note. (this will save it to the new service id, if one was created)
				SaveSystemNote($strNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, $intServiceId);
			}
			elseif ($strChangesNote != "")
			{
				$strSystemChangesNote  = "Service modified.\n$strChangesNote";
				SaveSystemNote($strSystemChangesNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, DBO()->Service->Id->Value);
			}

			// Commit the transaction
			TransactionCommit();
			
			// Close the popup
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			
			// Alert the user
			$strMsgNewService = "";
			if (DBO()->NewService->Id->Value)
			{
				$strMsgNewService = ".  A new service record had to be made.  Please refer to it from now on.  A note detailing this, has been created";
			}
			
			// Check that something was actually changed
			if ($strNoteDetails != "" || $strChangesNote != "")
			{
				Ajax()->AddCommand("Alert", "The service was successfully updated$strMsgNewService");

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
			}
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
		DBO()->Service->CurrentFNN							= DBO()->Service->FNN->Value;
		DBO()->Service->CurrentStatus 						= DBO()->Service->Status->Value;
		DBO()->Service->CurrentIndial100 					= DBO()->Service->Indial100->Value;
		DBO()->Service->CurrentELB 							= DBO()->Service->ELB->Value;
		DBO()->Service->CurrentCostCentre 					= DBO()->Service->CostCentre->Value;
		DBO()->Service->CurrentForceInvoiceRender			= DBO()->Service->ForceInvoiceRender->Value; 
		DBO()->ServiceInboundDetail->CurrentAnswerPoint		= DBO()->ServiceInboundDetail->AnswerPoint->Value;
		DBO()->ServiceInboundDetail->CurrentConfiguration 	= DBO()->ServiceInboundDetail->Configuration->Value;
		
		// Declare which page to use
		$this->LoadPage('service_edit');
		return TRUE;
	}	
	
	//------------------------------------------------------------------------//
	// ViewPlan
	//------------------------------------------------------------------------//
	/**
	 * ViewPlan()
	 *
	 * Performs the logic for the View Service RatePlan page
	 * 
	 * Performs the logic for the View Service RatePlan page
	 * It assumes:
	 * 		DBO()->Service->Id		is set to the Id of the service
	 *
	 * @return		void
	 * @method		ViewPlan
	 */
	function ViewPlan()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		$bolUserHasOperatorPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		// The service should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
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
		
		// Retrieve all RecordTypes applicable for this service
		DBL()->RecordType->ServiceType = DBO()->Service->ServiceType->Value;
		DBL()->RecordType->OrderBy("Name, Id");
		DBL()->RecordType->Load();
		
		$this->_LoadPlanDetails();
		
		$this->LoadPage('service_plan_view');
		return TRUE;
	}	
	
	//------------------------------------------------------------------------//
	// RenderServiceRateGroupList
	//------------------------------------------------------------------------//
	/**
	 * RenderServiceRateGroupList()
	 *
	 * Renders the ServiceRateGroupList Html Template
	 * 
	 * Renders the ServiceRateGroupList Html Template for viewing
	 * It expects	DBO()->Service->Id 		service Id 
	 *				DBO()->Container->Id	id of the container div in which to place the 
	 *										Rendered HtmlTemplate
	 *
	 * @return		void
	 * @method
	 */
	function RenderServiceRateGroupList()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);

		DBO()->Service->Load();
		
		// Retrieve all RecordTypes applicable for this service
		DBL()->RecordType->ServiceType = DBO()->Service->ServiceType->Value;
		DBL()->RecordType->OrderBy("Name, Id");
		DBL()->RecordType->Load();
		
		$this->_LoadPlanDetails();
		
		// Render the ServiceRateGroupList HtmlTemplate
		Ajax()->RenderHtmlTemplate("ServiceRateGroupList", HTML_CONTEXT_DEFAULT, DBO()->Container->Id->Value);

		return TRUE;
	}	

	//------------------------------------------------------------------------//
	// _LoadPlanDetails
	//------------------------------------------------------------------------//
	/**
	 * _LoadPlanDetails()
	 *
	 * Loads all details required of the ServiceRateGroupList Html Template
	 * 
	 * Loads all details required of the ServiceRateGroupList Html Template
	 * It expects	DBO()->Service->Id 		service Id
	 * 
	 * POST: Sets up the following objects
	 * DBO()->CurrentRatePlan				RatePlan record for the currently active plan (if there is one)
	 * DBO()->CurrentServiceRatePlan		ServiceRatePlan record for the currently active plan (if there is one)
	 * DBO()->FutureRatePlan				RatePlan record for the future scheduled plan (if there is one)
	 * DBO()->FutureServiceRatePlan			ServiceRatePlan record for the future plan (if there is one)
	 * DBL()->CurrentServiceRateGroup		All ServiceRateGroup records that end after the current (or future)
	 * 										RatePlan started, and were created at or after the current (or future)
	 * 										RatePlan was created
	 *
	 * @return		void
	 * @method
	 */
	private function _LoadPlanDetails()
	{
		// Load the current RatePlan
		DBO()->CurrentRatePlan->Id = GetCurrentPlan(DBO()->Service->Id->Value);
		$strEarliestAllowableEndDatetime = NULL;
		if (DBO()->CurrentRatePlan->Id->Value)
		{
			// The Service has a current RatePlan
			DBO()->CurrentRatePlan->SetTable("RatePlan");
			DBO()->CurrentRatePlan->Load();

			// Load all the RateGroups belonging to the current rate plan
			DBL()->CurrentPlanRateGroup->SetTable("RateGroup");
			DBL()->CurrentPlanRateGroup->SetColumns("Id");
			$strWhere = "Id IN (SELECT RateGroup FROM RatePlanRateGroup WHERE RatePlan = <RatePlan>)";
			DBL()->CurrentPlanRateGroup->Where->Set($strWhere, Array("RatePlan" => DBO()->CurrentRatePlan->Id->Value));
			DBL()->CurrentPlanRateGroup->OrderBy("RecordType");
			DBL()->CurrentPlanRateGroup->Load();

			// Load the current ServiceRatePlan record
			$strWhere = "Service = <Service> AND NOW() BETWEEN StartDatetime AND EndDatetime ORDER BY CreatedOn DESC";
			DBO()->CurrentServiceRatePlan->SetTable("ServiceRatePlan");
			DBO()->CurrentServiceRatePlan->Where->Set($strWhere, Array("Service" => DBO()->Service->Id->Value));
			DBO()->CurrentServiceRatePlan->Load();
			
			DBO()->CurrentRatePlan->StartDatetime	= DBO()->CurrentServiceRatePlan->StartDatetime->Value;
			DBO()->CurrentRatePlan->EndDatetime		= DBO()->CurrentServiceRatePlan->EndDatetime->Value;
			
			// This will be used to retrieve all ServiceRateGroup records that have an EndDatetime greater than this
			$strEarliestAllowableEndDatetime = DBO()->CurrentServiceRatePlan->StartDatetime->Value;
		}
		
		// If $strEarliestAllowableEndDatetime hasn't yet been defined then define it now
		if (!$strEarliestAllowableEndDatetime)
		{
			$strEarliestAllowableEndDatetime = GetCurrentDateAndTimeForMySQL();
		}
		
		// Load the future RatePlan if there is one scheduled to begin next billing period
		DBO()->FutureRatePlan->Id = GetPlanScheduledForNextBillingPeriod(DBO()->Service->Id->Value);
		if (DBO()->FutureRatePlan->Id->Value)
		{
			// The Service has a plan scheduled to begin at the start of the next billing period
			DBO()->FutureRatePlan->SetTable("RatePlan");
			DBO()->FutureRatePlan->Load();
			
			// Load the Future RatePlan record (this is only really needed to get the StartDatetime and EndDatetime)
			$strStartOfNextBillingPeriod = ConvertUnixTimeToMySQLDateTime(GetStartDateTimeForNextBillingPeriod());
			$strWhere = "Service = <Service> AND StartDatetime = <StartOfNextBillingPeriod> AND StartDatetime < EndDatetime ORDER BY CreatedOn DESC";
			DBO()->FutureServiceRatePlan->SetTable("ServiceRatePlan");
			DBO()->FutureServiceRatePlan->Where->Set($strWhere, Array("Service" => DBO()->Service->Id->Value, "StartOfNextBillingPeriod" => $strStartOfNextBillingPeriod));
			DBO()->FutureServiceRatePlan->Load();
			
			DBO()->FutureRatePlan->StartDatetime	= DBO()->FutureServiceRatePlan->StartDatetime->Value;
			DBO()->FutureRatePlan->EndDatetime		= DBO()->FutureServiceRatePlan->EndDatetime->Value;
		}
		
		if (DBO()->CurrentRatePlan->Id->Value)
		{
			// Do not show ServiceRateGroup records that were created before the current ServiceRatePlan record was created
			$strEarliestAllowableCreatedOn = DBO()->CurrentServiceRatePlan->CreatedOn->Value;
		}
		elseif (DBO()->FutureRatePlan->Id->Value)
		{
			// Do not show ServiceRateGroup records that were created before the future ServiceRatePlan record was created
			$strEarliestAllowableCreatedOn = DBO()->FutureServiceRatePlan->CreatedOn->Value;
		}
		else
		{
			// The Service has no plans, so just show all ServiceRateGroup records that are still active
			$strEarliestAllowableCreatedOn = "";
		}
		
		// Retrieve all ServiceRateGroup records (with accompanying RateGroup details) that have an EndDatetime > $strEarliestAllowableEndDatetime
		// and StartDatetime < EndDatetime AND (were created after the current plan was created or are fleet RateGroups)
		$arrColumns	= Array("Id" => "SRG.Id", "RateGroup" => "SRG.RateGroup", "CreatedOn" => "SRG.CreatedOn", "StartDatetime" => "SRG.StartDatetime", 
							"EndDatetime" => "SRG.EndDatetime", "Name" => "RG.Name", "Description" => "RG.Description", "Fleet" => "RG.Fleet",
							"RecordType" => "RG.RecordType", "RateGroupId" => "RG.Id");
		$strTable	= "ServiceRateGroup AS SRG INNER JOIN RateGroup AS RG ON SRG.RateGroup = RG.Id";
		
		// OLD WHERE CLAUSE Includes RateGroups that Start after the current plan finishes as well as all of those that finished after the CurrentPlan Started but before now
		//$strWhere	= "SRG.Service = <Service> AND SRG.EndDatetime > <EarliestAllowableEndDatetime> AND SRG.StartDatetime < SRG.EndDatetime";
		//$arrWhere	= Array("Service" => DBO()->Service->Id->Value, "EarliestAllowableEndDatetime" => $strEarliestAllowableEndDatetime);

		// Retrieve all ServiceRateGroup records that have EndDatetime > CurrentRatePlan.StartDatetime (<EarliestAllowableEndDatetime>)
		// AND StartDatetime < EndDatetime AND (RG.Fleet = 1 OR (CreatedOn + 5 seconds) > CurrentRatePlan.CreatedOn)
		// Which means retrieve all RateGroups that have an EndDatetime greater than the StartDatetime of the Current RatePlan, and (are fleet
		// RateGroups OR were created at or after the CurrentRatePlan was created
		$strWhere	= "SRG.Service = <Service> AND SRG.EndDatetime > <EarliestAllowableEndDatetime> AND SRG.StartDatetime < SRG.EndDatetime".
						" AND (RG.Fleet = 1 OR ADDTIME(SRG.CreatedOn, SEC_TO_TIME(5)) > '$strEarliestAllowableCreatedOn')";
		$arrWhere	= Array("Service" => DBO()->Service->Id->Value, "EarliestAllowableEndDatetime" => $strEarliestAllowableEndDatetime);
		
		$strOrderBy = "SRG.CreatedOn DESC";

		DBL()->CurrentServiceRateGroup->SetColumns($arrColumns);
		DBL()->CurrentServiceRateGroup->SetTable($strTable);
		DBL()->CurrentServiceRateGroup->Where->Set($strWhere, $arrWhere);
		DBL()->CurrentServiceRateGroup->OrderBy($strOrderBy);
		DBL()->CurrentServiceRateGroup->Load();
	}


	//------------------------------------------------------------------------//
	// RemoveServiceRateGroup
	//------------------------------------------------------------------------//
	/**
	 * RemoveServiceRateGroup()
	 *
	 * Removes a ServiceRateGroup record, if it can be safely removed
	 * 
	 * Removes a ServiceRateGroup record, if it can be safely removed
	 * A ServiceRateGroup record can only be removed if:
	 * 		It is a fleet RateGroup
	 * OR
	 * 		The entire length of time that the RateGroup is applicable to, is covered
	 * 		by other ServiceRateGroup records (of the same RecordType)
	 * It expects	DBO()->ServiceRateGroup->Id 		Record to be removed 
	 *
	 * @return		void
	 * @method
	 */
	function RemoveServiceRateGroup()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		// Removing RateGroups can not be done while billing is in progress
		if (IsInvoicing())
		{
			$strErrorMsg =  "Billing is in progress.  Plan details cannot be changed while this is happening.  ".
							"Please try again in a couple of hours.  If this problem persists, please ".
							"notify your system administrator";
			Ajax()->AddCommand("Alert", $strErrorMsg);
			return TRUE;
		}
 
 		$bolCanRemoveRateGroup = FALSE;
 		
		// Load the ServiceRateGroup record to remove, and its RateGroup details 
		DBO()->ServiceRateGroup->Load();
		DBO()->RateGroup->Id = DBO()->ServiceRateGroup->RateGroup->Value;
		DBO()->RateGroup->Load();
		
		if (DBO()->RateGroup->Fleet->Value == TRUE)
		{
			// Fleet RateGroups can always be removed
			$bolCanRemoveRateGroup = TRUE;
		}
		else
		{
			// Retrieve the current RatePlan Record if there is one
			$selCurrentRatePlan	= new StatementSelect("ServiceRatePlan", "*", "Service = <Service> AND StartDatetime < EndDatetime AND NOW() BETWEEN StartDatetime AND EndDatetime", "CreatedOn DESC", "1");
			$intRecordCount		= $selCurrentRatePlan->Execute(Array("Service"=>DBO()->ServiceRateGroup->Service->Value));
			if ($intRecordCount != 1)
			{
				// The Service Does not have a current RatePlan
				// Check if it has a future plan
				$selFutureRatePlan	= new StatementSelect("ServiceRatePlan", "*", "Service = <Service> AND StartDatetime < EndDatetime AND StartDatetime > NOW()", "CreatedOn DESC", "1");
				$intRecordCount		= $selFutureRatePlan->Execute(Array("Service"=>DBO()->ServiceRateGroup->Service->Value));
				if ($intRecordCount != 1)
				{
					// There is no current or future Plan for the service
					// Removing the RateGroup should be safe
					$bolCanRemoveRateGroup = TRUE;
				}
				else
				{
					// Found a future plan
					$arrServiceRatePlan = $selFutureRatePlan->Fetch();
				}
			}
			else
			{
				// Found a current plan
				$arrServiceRatePlan = $selCurrentRatePlan->Fetch();
			}
			
			if (isset($arrServiceRatePlan))
			{
				$strEarliestCreatedOnDate = $arrServiceRatePlan['CreatedOn'];
				
				// Find all ServiceRateGroup records that were created at or after $strEarliestCreatedOnDate
				$arrColumns	= Array("Id" => "SRG.Id",
									"CreatedOn" => "SRG.CreatedOn",
									"StartDatetime" => "SRG.StartDatetime",
									"EndDatetime" => "SRG.EndDatetime",
									"RateGroupId" => "RG.Id",
									"RecordType" => "RG.RecordType",
									"Fleet" => "RG.Fleet",
									"Archived" => "RG.Archived"	
									);
				$strWhere	= "SRG.Service=<Service> AND RG.RecordType=<RecordType> AND ADDTIME(SRG.CreatedOn, SEC_TO_TIME(5)) > <EarliestCreatedOn> AND SRG.StartDatetime < SRG.EndDatetime AND SRG.Id != <RateGroupToRemove> AND RG.Fleet = 0 AND RG.Archived = 0";
				$strTables	= "ServiceRateGroup AS SRG INNER JOIN RateGroup AS RG ON SRG.RateGroup = RG.Id";
				$strOrderBy	= "SRG.StartDatetime ASC";
				$selServiceRateGroups = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy);
				$intRecCount = $selServiceRateGroups->Execute(Array(	"Service"			=> DBO()->ServiceRateGroup->Service->Value,
																		"RecordType"		=> DBO()->RateGroup->RecordType->Value,
																		"EarliestCreatedOn"	=> $strEarliestCreatedOnDate,
																		"RateGroupToRemove"	=> DBO()->ServiceRateGroup->Id->Value));
				if ($intRecCount)
				{
					// Check that the ServiceRateGroup records retrieved cover the time range of the one being removed.
					// One of them has to start before the one to remove, and then there has to be no gaps
					// until after the one to remove ends
					$arrServiceRateGroups = $selServiceRateGroups->FetchAll();
					
					$bolFoundStart = FALSE;
					$strCoveredUntilEndDatetime = DBO()->ServiceRateGroup->StartDatetime->Value;
					
					// Find the first record that starts before and ends after the one to remove starts
					foreach ($arrServiceRateGroups as $arrServiceRateGroup)
					{
						if (!$bolFoundStart)
						{
							if (($arrServiceRateGroup['StartDatetime'] <= DBO()->ServiceRateGroup->StartDatetime->Value) &&
								($arrServiceRateGroup['EndDatetime'] > DBO()->ServiceRateGroup->StartDatetime->Value))
							{
								// Found the earliest record that encompases the start of the record to remove
								$bolFoundStart = TRUE;
								$strCoveredUntilEndDatetime = $arrServiceRateGroup['EndDatetime'];
							}
							else
							{
								// We still have not found the earliest record which covers the Start of the record to remove
								continue;
							}
						}
						else
						{
							if ((date("Y-m-d H:i:s", strtotime($arrServiceRateGroup['StartDatetime'])-1) <= $strCoveredUntilEndDatetime) &&
								($arrServiceRateGroup['EndDatetime'] > $strCoveredUntilEndDatetime))
							{
								$strCoveredUntilEndDatetime = $arrServiceRateGroup['EndDatetime']; 
							}
						}
						
						// Check if the ServiceRateGroup record to be removed, is already covered
						if ($strCoveredUntilEndDatetime >= DBO()->ServiceRateGroup->EndDatetime->Value)
						{
							// ServiceRateGroup records exist to cover the loss of the one being removed
							// The user is allowed to remove the ServiceRateGroup record
							$bolCanRemoveRateGroup = TRUE;
							break; 
						}
					}
				}
			}
		}
		
		if (!$bolCanRemoveRateGroup)
		{
			// The RateGroup cannot be removed
			Ajax()->AddCommand("Alert", "ERROR: The RateGroup cannot be removed, as it would leave a whole in the Service's Plan");
			return TRUE;
		}
		
		TransactionStart();
		
		// Remove the ServiceRateGroup record by setting its EndDatetime to 1 second before its StartDatetime
		$strNewEndDatetime = date("Y-m-d H:i:s", (strtotime(DBO()->ServiceRateGroup->StartDatetime->Value)-1));
		$strOldEndDatetime = DBO()->ServiceRateGroup->EndDatetime->Value; 
		DBO()->ServiceRateGroup->EndDatetime = $strNewEndDatetime;
		if (!DBO()->ServiceRateGroup->Save())
		{
			// Updating the record failed
			TransactionRollback();
			Ajax()->AddCommand("Alert", "ERROR: Removing the RateGroup failed, unexpectedly<br />(Error updating the record to remove)");
			return TRUE;
		}
		
		// Rerate all the Rated CDRs in the CDR table
		$arrUpdate = Array('Status' => CDR_NORMALISED);
		$updCDRs = new StatementUpdate("CDR", "Service = <Service> AND Status = <CDRRated> AND RecordType = <RecordType>", $arrUpdate);
		$arrWhere = Array("Service"=>DBO()->ServiceRateGroup->Service->Value, "CDRRated"=>CDR_RATED, "RecordType"=>DBO()->RateGroup->RecordType->Value);
		if ($updCDRs->Execute($arrUpdate, $arrWhere) === FALSE)
		{
			// Could not update records in CDR table. Exit gracefully
			TransactionRollback();
			Ajax()->AddCommand("Alert", "ERROR: Removing the RateGroup failed, unexpectedly<br />(Error updating records in the CDR table)");
			return TRUE;
		}
		
		TransactionCommit();
		
		// Load the RecordType record as we need the name for the SystemNote
		DBO()->RecordType->Id = DBO()->RateGroup->RecordType->Value;
		DBO()->RecordType->Load();
		
		// Add a system note
		$strNote = 	"RateGroup removed from service\n".
					"RecordType: ". DBO()->RecordType->Description->Value ."\n".
					"Name: ". DBO()->RateGroup->Name->Value ."\n".
					"Desc: ". DBO()->RateGroup->Description->Value ."\n".
					"Created: ". date("H:i:s d/m/Y", strtotime(DBO()->ServiceRateGroup->CreatedOn->Value)) ."\n".
					"Start: ". date("H:i:s d/m/Y", strtotime(DBO()->ServiceRateGroup->StartDatetime->Value)) ."\n".
					"Finish: ". (($strOldEndDatetime == END_OF_TIME)? "Indefinite" : date("H:i:s d/m/Y", strtotime($strOldEndDatetime))) ."\n".
					"Fleet: ". DBO()->RateGroup->Fleet->FormattedValue() ."\n".
					"ServiceRateGroupId: ". DBO()->ServiceRateGroup->Id->Value;
		DBO()->Service->Id = DBO()->ServiceRateGroup->Service->Value;
		DBO()->Service->Load();
		SaveSystemNote($strNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, DBO()->Service->Id->Value);
		
		// Don't bother firing an OnNewNote event because no notes are displayed on the page where this functionality is accessed
		
		// Fire the EVENT_ON_SERVICE_RATE_GROUPS_UPDATE Event
		$arrEvent['Service']['Id'] = DBO()->Service->Id->Value;
		$arrEvent['RecordType']['Id'] = DBO()->RateGroup->RecordType->Value;
		Ajax()->FireEvent(EVENT_ON_SERVICE_RATE_GROUPS_UPDATE, $arrEvent);

		Ajax()->AddCommand("Alert", "The RateGroup has been successfully removed");
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
	 *
	 *		When the ChangePlan form data is submitted, this function expects the
	 *		following properties to be defined
	 *		DBO()->Service->Id			Id of the service that the change is affecting
	 *		DBO()->NewPlan->Id			Id of the rate plan to change to
	 *		DBO()->NewPlan->StartTime	0 signifies that the new plan should be 
	 *										used for the current billing period
	 *									1 signifies that the new plan should 
	 *										come into affect, starting the next
	 *										billing period
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

		// Check if the billing/invoice process is being run
		if (IsInvoicing())
		{
			// There are currently records in the InvoiceTemp table, which means a bill run is taking place.
			// Plan Changes cannot be made when a bill run is taking place
			$strErrorMsg =  "Billing is in progress.  Plans cannot be changed while this is happening.  ".
							"Please try again in a couple of hours.  If this problem persists, please ".
							"notify your system administrator";
			Ajax()->AddCommand("Alert", $strErrorMsg);
			return TRUE;
		}
		
		if (SubmittedForm("ChangePlan","Change Plan"))
		{
			// Work out the StartDatetime for the new records of the ServiceRatePlan and ServiceRateGroup tables
			$strCurrentDateAndTime						= GetCurrentDateAndTimeForMySQL();
			$intStartDateTimeForCurrentBillingPeriod	= GetStartDateTimeForBillingPeriod($strCurrentDateAndTime);
			$intStartDateTimeForNextBillingPeriod		= GetStartDateTimeForNextBillingPeriod($strCurrentDateAndTime);
			if (DBO()->NewPlan->StartTime->Value == 1)
			{
				// Get the StartDatetime for the next billing period
				$intStartDatetime = $intStartDateTimeForNextBillingPeriod;
				
				// The records defining the new plan should have their "Active" property set to 0 (Inactive)
				$intActive = 0;
				
				// Declare the note part detailing when the Plan Change will come into effect
				$strNotePlanStart = "This plan change will come into effect as of the start of the next billing period. (". date("d/m/Y", $intStartDatetime) .")";
			}
			else
			{
				// Get the StartDatetime for the current billing period
				$intStartDatetime = $intStartDateTimeForCurrentBillingPeriod;
				
				// The records defining the new plan should have their "Active" property set to 1 (Active)
				$intActive = 1;
				
				// Declare the note part detailing when the Plan Change will come into effect
				$strNotePlanStart = "This plan change has come into effect as of the beginging of the current billing period. (". date("d/m/Y", $intStartDatetime) .")";
			}
			$strStartDatetime = ConvertUnixTimeToMySQLDateTime($intStartDatetime);
			
			// Work out the EndDatetime for the old records of the ServiceRatePlan and ServiceRateGroup tables, which have an EndDatetime
			// greater than $strStartDatetime
			// The EndDatetime will be set to 1 second before the StartDatetime of the records relating to the new plan
			$intOldPlanEndDatetime = $intStartDatetime - 1;
			$strOldPlanEndDatetime = ConvertUnixTimeToMySQLDateTime($intOldPlanEndDatetime);
			
			// Find the current plan (if there is one)
			DBO()->CurrentRatePlan->Id = GetCurrentPlan(DBO()->Service->Id->Value);
			if (DBO()->CurrentRatePlan->Id->Value)
			{
				DBO()->CurrentRatePlan->SetTable("RatePlan");
				DBO()->CurrentRatePlan->Load();
			}
			
			// Find the plan scheduled to start for the next billing run (if there is one)
			DBO()->FutureRatePlan->Id = GetPlanScheduledForNextBillingPeriod(DBO()->Service->Id->Value, $strCurrentDateAndTime);
			if (DBO()->FutureRatePlan->Id->Value)
			{
				DBO()->FutureRatePlan->SetTable("RatePlan");
				DBO()->FutureRatePlan->Load();
			}
			
			// Start the transaction
			TransactionStart();
			
			// Set the EndDatetime to $strOldPlanEndDatetime for all records in the ServiceRatePlan and ServiceRateGroup tables
			// which relate this service.  Do not alter the records' "Active" property regardless of what it is.
			
			// Update existing ServiceRateGroup records
			$arrUpdate = Array('EndDatetime' => $strOldPlanEndDatetime);
			$updServiceRateGroup = new StatementUpdate("ServiceRateGroup", "Service = <Service> AND EndDatetime >= <StartDatetime>", $arrUpdate);
			if ($updServiceRateGroup->Execute($arrUpdate, Array("Service"=>DBO()->Service->Id->Value, "StartDatetime"=>$strStartDatetime)) === FALSE)
			{
				// Could not update records in ServiceRateGroup table. Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br />(Error updating records in the ServiceRateGroup table)");
				return TRUE;
			}
			
			// Update existing ServiceRatePlan records
			$updServiceRatePlan = new StatementUpdate("ServiceRatePlan", "Service = <Service> AND EndDatetime >= <StartDatetime>", $arrUpdate);
			if ($updServiceRatePlan->Execute($arrUpdate, Array("Service"=>DBO()->Service->Id->Value, "StartDatetime"=>$strStartDatetime)) === FALSE)
			{
				// Could not update records in ServiceRatePlan table. Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br />(Error updating records in the ServiceRatePlan table)");
				return TRUE;
			}
			
			// Declare the new plan for the service
			// Insert a record into the ServiceRatePlan table
			DBO()->ServiceRatePlan->Service 		= DBO()->Service->Id->Value;
			DBO()->ServiceRatePlan->RatePlan 		= DBO()->NewPlan->Id->Value;
			DBO()->ServiceRatePlan->CreatedBy 		= AuthenticatedUser()->_arrUser['Id'];
			DBO()->ServiceRatePlan->CreatedOn 		= $strCurrentDateAndTime;
			DBO()->ServiceRatePlan->StartDatetime 	= $strStartDatetime;
			DBO()->ServiceRatePlan->EndDatetime 	= END_OF_TIME;
			DBO()->ServiceRatePlan->LastChargedOn	= NULL;
			DBO()->ServiceRatePlan->Active			= $intActive;
			
			if (!DBO()->ServiceRatePlan->Save())
			{
				// Could not save the record. Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br />(Error adding record to ServiceRatePlan table)");
				return TRUE;
			}
			
			// Declare the new RateGroups for the service
			$intServiceId	= DBO()->Service->Id->Value;
			$intUserId		= AuthenticatedUser()->_arrUser['Id'];
			$strInsertRateGroupsIntoServiceRateGroup  = "INSERT INTO ServiceRateGroup (Id, Service, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active) ";
			$strInsertRateGroupsIntoServiceRateGroup .= "SELECT NULL, $intServiceId, RateGroup, $intUserId, '$strCurrentDateAndTime', '$strStartDatetime', '". END_OF_TIME ."', $intActive ";
			$strInsertRateGroupsIntoServiceRateGroup .= "FROM RatePlanRateGroup WHERE RatePlan = ". DBO()->NewPlan->Id->Value ." ORDER BY RateGroup";
			$qryInsertServiceRateGroup = new Query();
			if ($qryInsertServiceRateGroup->Execute($strInsertRateGroupsIntoServiceRateGroup) === FALSE)
			{
				// Inserting the records into the ServiceRateGroup table failed.  Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br />(Error adding records to ServiceRateGroup table)");
				return TRUE;
			}
			
			// If the plan goes into affect at the begining of the current month, then you must rerate all the cdrs which are currently
			// rated but not billed
			if (DBO()->NewPlan->StartTime->Value == 0)
			{
				// The plan change is retroactive to the start of the current month
				// Set the status of all CDRs that are currently "rated" (CDR_RATED) to "ready for rating" (CDR_NORMALISED)
				$arrUpdate = Array('Status' => CDR_NORMALISED);
				$updCDRs = new StatementUpdate("CDR", "Service = <Service> AND Status = <CDRRated>", $arrUpdate);
				if ($updCDRs->Execute($arrUpdate, Array("Service"=>DBO()->Service->Id->Value, "CDRRated"=>CDR_RATED)) === FALSE)
				{
					// Could not update records in CDR table. Exit gracefully
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br />(Error updating records in the CDR table)");
					return TRUE;
				}
			}
			
			//TODO! Do automatic provisioning here
			
			// Add a system note describing the change of plan
			DBO()->Service->Load();
			if (!DBO()->CurrentRatePlan->Id->Value)
			{
				// The Service has not previously had a RatePlan
				DBO()->CurrentRatePlan->Name = "undefined";
			}
			DBO()->NewPlan->SetTable("RatePlan");
			DBO()->NewPlan->Load();
			$strNote  = "This service has had its plan changed from '". DBO()->CurrentRatePlan->Name->Value ."' to '". DBO()->NewPlan->Name->Value ."'.  $strNotePlanStart";
			
			SaveSystemNote($strNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, DBO()->Service->Id->Value);
			
			// All changes to the database, required to define the plan change, have been completed
			// Commit the transaction
			TransactionCommit();
			
			// Close the popup, alert the user
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("Alert", "The service's plan has been successfully changed");

			// Build event object
			// The contents of this object should be declared in the doc block of this method
			$arrEvent['Service']['Id'] = DBO()->Service->Id->Value;
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
		
		// Set the default for the scheduled start time of the new plan
		// Defaults to "Start billing at begining of next billing period"
		DBO()->NewPlan->StartTime = 1;
		
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
		DBO()->NewService->EarliestCDR	= NULL;
		DBO()->NewService->LatestCDR	= NULL;
		DBO()->NewService->LineStatus	= NULL;
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
		
		// Give the new service the same RatePlan as the old service (including future rate plans and overrides)
		// Copy all ServiceRatePlan records across from the old service where EndDatetime is in the future and StartDatetime < EndDatetime
		$intNewServiceId = DBO()->NewService->Id->Value;
		$strCopyServiceRatePlanRecordsToNewService =	"INSERT INTO ServiceRatePlan (Id, Service, RatePlan, CreatedBy, CreatedOn, StartDatetime, EndDatetime, LastChargedOn, Active) ".
														"SELECT NULL, $intNewServiceId, RatePlan, CreatedBy, CreatedOn, StartDatetime, EndDatetime, LastChargedOn, Active ".
														"FROM ServiceRatePlan WHERE Service = $intOldServiceId AND EndDatetime > NOW() AND StartDatetime < EndDatetime";
		$qryInsertServicePlanDetails = new Query();
		
		if ($qryInsertServicePlanDetails->Execute($strCopyServiceRatePlanRecordsToNewService) === FALSE)
		{
			// Inserting the records into the ServiceRatePlan table failed
			return "ERROR: Activating the service failed, unexpectedly.  Inserting records into the ServiceRatePlan table failed";
		}
		
		// Copy all ServiceRateGroup records across from the old service where EndDatetime is in the future and StartDatetime < EndDatetime
		$strCopyServiceRateGroupRecordsToNewService =	"INSERT INTO ServiceRateGroup (Id, Service, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active) ".
														"SELECT NULL, $intNewServiceId, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active ".
														"FROM ServiceRateGroup WHERE Service = $intOldServiceId AND EndDatetime > NOW() AND StartDatetime < EndDatetime";
														
		if ($qryInsertServicePlanDetails->Execute($strCopyServiceRateGroupRecordsToNewService) === FALSE)
		{
			// Inserting the records into the ServiceRateGroup table failed
			return "ERROR: Activating the service failed, unexpectedly.  Inserting records into the ServiceRatePlan table failed";
		}
		
		// Activating the account was successfull
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
?>