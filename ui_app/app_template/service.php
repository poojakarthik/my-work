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
// AppTemplateservice
//----------------------------------------------------------------------------//
/**
 * AppTemplateservice
 *
 * The AppTemplateservice class
 *
 * The AppTemplateservice class.  This incorporates all logic for all pages
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
		$pagePerms = PERMISSION_ADMIN;
		
		// Should probably check user authorization here
		AuthenticatedUser()->CheckAuth();
		
		AuthenticatedUser()->PermissionOrDie($pagePerms);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			// Add extra functionality for super-users
		}

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
		
		// Context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		// Breadcrumb menu
		BreadCrumb()->ViewAccount(DBO()->Service->Account->Value);
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
		$pagePerms = PERMISSION_ADMIN;
		
		// Should probably check user authorization here
		AuthenticatedUser()->CheckAuth();
		
		AuthenticatedUser()->PermissionOrDie($pagePerms);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			// Add extra functionality for super-users
		}

		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ." could not be found";
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

			Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "This service was successfully created", "Location" => Href()->ViewAccount(DBO()->Account->Id->Value)));
			return TRUE;

		}
		
		// Context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		// Breadcrumb menu
		BreadCrumb()->ViewAccount(DBO()->Account->Id->Value);
		BreadCrumb()->SetCurrentPage("Add Service");
		
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
		{
			$this->LoadPage('service_edit');
		}
		else
		{
			$this->LoadPage('service_add');
		}
	}
	
	
	//------------------------------------------------------------------------//
	// Edit
	//------------------------------------------------------------------------//
	/**
	 * Edit()
	 *
	 * Performs the logic for editting a service
	 * 
	 * Performs the logic for editting a service
	 *
	 * @return		void
	 * @method		Edit
	 *
	 */
	function Edit()
	{
		$pagePerms = PERMISSION_ADMIN;
		
		// Should probably check user authorization here
		AuthenticatedUser()->CheckAuth();
		
		AuthenticatedUser()->PermissionOrDie($pagePerms);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			// Add extra functionality for super-users
		}

		if (SubmittedForm("EditService","Apply Changes"))
		{
			if (DBO()->Service->IsInvalid())
			{
				// The form has not passed initial validation
				Ajax()->AddCommand("Alert", "Could not save the service.  Invalid fields are highlighted");
				Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, "ServiceEditDiv");
				return TRUE;
			}
			
			DBO()->Service->FNN = trim(DBO()->Service->FNN->Value);
			DBO()->Service->FNNConfirm = trim(DBO()->Service->FNNConfirm->Value);
			
			if (DBO()->Service->FNN->Value != DBO()->Service->CurrentFNN->Value)
			{		
				// The user wants to change the FNN
				if (DBO()->Service->FNN->Value != DBO()->Service->FNNConfirm->Value)
				{
					// The FNN wasn't re-entered correctly
					DBO()->Service->FNN->SetToInvalid();
					DBO()->Service->FNNConfirm->SetToInvalid();
					Ajax()->AddCommand("Alert", "Could not save the service.  Service # and Confirm Service # must be the same");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, "ServiceEditDiv");
					return TRUE;
				}
				
				// Check that the FFN is valid
				if (!isValidFNN(DBO()->Service->FNN->Value))
				{
					// The FNN is invalid
					DBO()->Service->FNN->SetToInvalid();
					Ajax()->AddCommand("Alert", "The FNN is not a valid Australian Full National Number");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, "ServiceEditDiv");
					return TRUE;
				}
				
				// Make sure the new FNN is valid for the service type
				$intServiceType = ServiceType(DBO()->Service->FNN->Value);
				if ($intServiceType != DBO()->Service->ServiceType->Value)
				{
					// The FNN is invalid for the services ServiceType.
					DBO()->Service->FNN->SetToInvalid();
					Ajax()->AddCommand("Alert", "The FNN is invalid for the service type");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, "ServiceEditDiv");
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
					DBO()->Service->FNNConfirm->SetToInvalid();
					Ajax()->AddCommand("Alert", "This Service FNN is currently being used by another service");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, "ServiceEditDiv");
					return TRUE;
				}
				
				// Declare properties to update
				$arrUpdateProperties[] = "FNN";
			}
			
			// test archive action
			if (DBO()->Service->ArchiveService->Value)
			{
				// we want to archive the service
				$bolArchiveService = TRUE;
				// set closedon date to todays date
				DBO()->Service->ClosedOn = GetCurrentDateForMySQL();
				// set closedby to authenticated user ID
				DBO()->Service->ClosedBy = AuthenticatedUser()->_arrUser['Id'];
				
				//TODO! probably need to run DisableELB
				
				// Declare properties to update
				$arrUpdateProperties[] = "ClosedOn";
				$arrUpdateProperties[] = "ClosedBy";
				
				// Define system generated note
				$strDateTime = OutputMask()->LongDateAndTime(GetCurrentDateAndTimeForMySQL());
				$strUserName = GetEmployeeName(AuthenticatedUser()->_arrUser['Id']);
				$strNote = "Service archived on $strDateTime by $strUserName";
			}
			if (DBO()->Service->ActivateService->Value)
			{
				// we want to activate this service
				$bolActivateService = TRUE;
				
				// Check if the FNN has been used by any other service since this was last active
				
				// set ClosedOn date to null
				DBO()->Service->ClosedOn = NULL;
				
				//TODO! probably need to run EnableELB
				
				// Declare properties to update
				$arrUpdateProperties[] = "ClosedOn";
				
				// Define system generated note
				$strDateTime = OutputMask()->LongDateAndTime(GetCurrentDateAndTimeForMySQL());
				$strUserName = GetEmployeeName(AuthenticatedUser()->_arrUser['Id']);
				$strNote = "Service unarchived on $strDateTime by $strUserName";
			}
			if (DBO()->Service->CostCentre->Value !== NULL)
			{
				if (DBO()->Service->CostCentre->Value == 0)
				{
					DBO()->Service->CostCentre = NULL;
				}
				$arrUpdateProperties[] = "CostCentre";
			}
			
			// Save the changes to the Service Table, if count($arrUpdateProperties) > 0

			// Declare the transaction
			TransactionStart();
			
			if (count($arrUpdateProperties) > 0)
			{
				// Declare columns to update
				DBO()->Service->SetColumns($arrUpdateProperties);			
				// Save the service to the service table of the vixen database
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
				if (DBO()->ServiceMobileDetail->IsInvalid())
				{
					// The form has not passed initial validation
					TransactionRollback();
					Ajax()->AddCommand("Alert", "Could not save the service.  Invalid fields are highlighted");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, "ServiceEditDiv");
					return TRUE;
				}
				// set DOB to MySql date format
				DBO()->ServiceMobileDetail->DOB = ConvertUserDateToMySqlDate(DBO()->ServiceMobileDetail->DOB->Value);
				// set columns to update
				
				// not saving correctly 'Service' represented in database as 0
				
				DBO()->ServiceMobileDetail->SetColumns("SimPUK, SimESN, SimState, DOB, Comments");
				if (!DBO()->ServiceMobileDetail->Save())
				{
					// The ServiceMobileDetail did not save
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Updating the mobile details failed, unexpectedly");
					return TRUE;
				}
				// the mobile details saved successfully
			}
			
			// handle inbound call details
			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
			{
				if (DBO()->ServiceInboundDetail->IsInvalid())
				{
					// The form has not passed initial validation
					TransactionRollback();
					Ajax()->AddCommand("Alert", "Could not save the service.  Invalid fields are highlighted");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, "ServiceEditDiv");
					return TRUE;
				}
				// set columns to update
				DBO()->ServiceInboundDetail->SetColumns("AnswerPoint, Configuration");
				if (!DBO()->ServiceInboundDetail->Save())
				{
					// The InboundDetail did not save
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Updating the inbound details failed, unexpectedly");
					return TRUE;
				}
				// the inbound details saved successfully
			}
			
			// all details regarding the service have been successfully updated

			// Add an automatic note if the service has been archived or unarchived
			if ($strNote)
			{
				SaveSystemNote($strNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, DBO()->Service->Id->Value);
			}

			// Commit the transaction
			TransactionCommit();
			Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "The service details were successfully updated", "Location" => Href()->ViewService(DBO()->Service->Id->Value)));
			return TRUE;
		}
		
		// Load the service record
		if (!DBO()->Service->Load())
		{
			DBO()->Error->Message = "The Service id: ". DBO()->Service->Id->Value ." you were attempting to view could not be found";
			$this->LoadPage('error');
			return FALSE;
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
		
		// Store the current FNN to check between states that the FNN textbox has been changed
		DBO()->Service->CurrentFNN = DBO()->Service->FNN->Value;

		// Load context menu items specific to the View Service page
		// Context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();

		// Bread Crumb Menu
		BreadCrumb()->ViewAccount(DBO()->Service->Account->Value);
		BreadCrumb()->View_Service(DBO()->Service->Id->Value, DBO()->Service->FNN->Value);
		BreadCrumb()->SetCurrentPage("Edit Service");

		// Declare which page to use
		$this->LoadPage('service_edit');
		return TRUE;
	}	
	
	function ViewPlan()
	{
		// Should probably check user authorization here
		//TODO!include user authorisation
		AuthenticatedUser()->CheckAuth();

		//DBO()->RatePlan->Id = GetCurrentPlan(DBO()->Service->Id->Value);
		//if (DBO()->RatePlan->Id->Value !== FALSE)
		//{
		
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Service->Load())
		{
			DBO()->Error->Message = "The Service id: ". DBO()->Service->Id->value ."you were attempting to view could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		DBO()->Account->Id = DBO()->Service->Account->Value;
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "Can not find Account: ". DBO()->Service->Account->Value . "associated with this service";
			$this->LoadPage('error');
			return FALSE;
		}

		// Retrieve all rate groups currently used by this service
		// TODO! This currently doesn't work properly if any extra rate groups have been added to this service
		// Rich is working on the proper query to use, to find this information
		$strServiceId = DBO()->Service->Id->Value;
		$strWhere = "Service = $strServiceId AND StartDatetime = (SELECT MAX(StartDatetime) FROM ServiceRatePlan WHERE Service = $strServiceId)";
		DBL()->ServiceRateGroup->Where->SetString($strWhere);
		DBL()->ServiceRateGroup->Load();
		
		// Load context menu items specific to the View Service page
		// Context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		// breadcrumb menu
		//TODO! define what goes in the breadcrumb menu (assuming this page uses one)
		//BreadCrumb()->Invoices_And_Payments(DBO()->Account->Id->Value);
		
		
		// Setup all DBO and DBL objects required for the page
		//TODO!
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		/*if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}*/
		
		// the DBList storing the invoices should be ordered so that the most recent is first
		// same with the payments list
		//DBL()->RatePlan->Load();
	
		$this->LoadPage('view_rate_group');

		return TRUE;
	}	
	
	//------------------------------------------------------------------------//
	// ChangePlan
	//------------------------------------------------------------------------//
	/**
	 * ChangePlan()
	 *
	 * Performs the logic for changing a service's plan
	 * 
	 * Performs the logic for changing a service's plan
	 *
	 * @return		void
	 * @method		ChangePlan
	 *
	 */
	function ChangePlan()
	{		
		$pagePerms = PERMISSION_ADMIN;
		
		// Should probably check user authorization here
		AuthenticatedUser()->CheckAuth();
		
		AuthenticatedUser()->PermissionOrDie($pagePerms);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			// Add extra functionality for super-users
		}
		
		if (SubmittedForm("ChangePlan","Change Plan"))
		{
			// check if the selected plan is the same as the previous plan if it is don't commit to database
			// just refresh page i.e. go back a page
			if (DBO()->NewPlan->Id->Value == DBO()->RatePlan->Id->Value)
			{
				// The new plan is the same as the existing plan, exit gracefully
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "No update has been saved as the new plan is the same as the previous plan", "Location" => Href()->ViewService(DBO()->Service->Id->Value)));
				return TRUE;
			}
			
			// Change the service's plan
			// Start the database transaction
			TransactionStart();

			// All current ServiceRateGroup and ServiceRatePlan records must have EndDatetime set to NOW()
			$arrUpdate = Array('EndDatetime' => new MySQLFunction("NOW()"));
			$updServiceRateGroup = new StatementUpdate("ServiceRateGroup", "Service = <Service> AND EndDatetime > NOW()", $arrUpdate);
			if (!$updServiceRateGroup->Execute($arrUpdate, Array("Service"=>DBO()->Service->Id->Value)))
			{
				// Could not update records in ServiceRateGroup table. Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "ERROR: Saving the plan change to the database failed, unexpectedly<br>(Error updating the current plan's rate groups to end today)", "Location" => Href()->ViewService(DBO()->Service->Id->Value)));
				return TRUE;
			}
			
			$updServiceRatePlan = new StatementUpdate("ServiceRatePlan", "Service = <Service> AND EndDatetime > NOW()", $arrUpdate);
			if (!$updServiceRatePlan->Execute($arrUpdate, Array("Service"=>DBO()->Service->Id->Value)))
			{
				// Could not update records in ServiceRatePlan table. Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "ERROR: Saving the plan change to the database failed, unexpectedly<br>(Error updating the current plan to end today)", "Location" => Href()->ViewService(DBO()->Service->Id->Value)));
				return TRUE;
			}
			
			// Declare the new plan for the service
			// Retrieve the rate groups belonging to the rate plan
			DBL()->RatePlanRateGroup->RatePlan = DBO()->NewPlan->Id->Value;
			DBL()->RatePlanRateGroup->Load();
			
			// For each Rate Group, save a record to the ServiceRateGroup table
			// Define constant properties for these records
			DBO()->ServiceRateGroup->Service 		= DBO()->Service->Id->Value;
			DBO()->ServiceRateGroup->CreatedBy 		= AuthenticatedUser()->_arrUser['Id'];
			DBO()->ServiceRateGroup->CreatedOn 		= GetCurrentDateAndTimeForMySQL();
			DBO()->ServiceRateGroup->StartDatetime 	= GetCurrentDateAndTimeForMySQL();
			DBO()->ServiceRateGroup->EndDatetime 	= END_OF_TIME;
			
			
			foreach (DBL()->RatePlanRateGroup as $dboRatePlanRateGroup)
			{
				// Set the id of the record to null, so that it is inserted as a new record when saved
				DBO()->ServiceRateGroup->Id = 0;
				DBO()->ServiceRateGroup->RateGroup = $dboRatePlanRateGroup->RateGroup->Value;
				
				// Save the record to the ServiceRateGroup table
				if (!DBO()->ServiceRateGroup->Save())
				{
					// Could not save the record. Exit gracefully
					TransactionRollback();
					Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "ERROR: Saving the plan change to the database failed, unexpectedly<br>(Error adding to ServiceRateGroup table)", "Location" => Href()->ViewService(DBO()->Service->Id->Value)));
					return TRUE;
				}
			}
			
			// Insert a record into the ServiceRatePlan table
			DBO()->ServiceRatePlan->Service 		= DBO()->Service->Id->Value;
			DBO()->ServiceRatePlan->RatePlan 		= DBO()->NewPlan->Id->Value;
			DBO()->ServiceRatePlan->CreatedBy 		= AuthenticatedUser()->_arrUser['Id'];
			DBO()->ServiceRatePlan->CreatedOn 		= GetCurrentDateAndTimeForMySQL();
			DBO()->ServiceRatePlan->StartDatetime 	= GetCurrentDateAndTimeForMySQL();
			DBO()->ServiceRatePlan->EndDatetime 	= END_OF_TIME;
			
			if (!DBO()->ServiceRatePlan->Save())
			{
				// Could not save the record. Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "ERROR: Saving the plan change to the database failed, unexpectedly<br>(Error adding to ServiceRatePlan table)", "Location" => Href()->ViewService(DBO()->Service->Id->Value)));
				return TRUE;
			}
			
			//TODO! Do automatic provisioning here
			
			// Add a system note describing the change of plan
			DBO()->Service->Load();
			DBO()->RatePlan->Load();
			DBO()->NewPlan->SetTable("RatePlan");
			DBO()->NewPlan->Load();
			if (DBO()->Service->FNN->Value)
			{
				$strFNN = "Service with FNN# ". DBO()->Service->FNN->Value ." has had its plan changed from '";
			}
			else
			{
				$strFNN = "The service's plan has been changed from '";
			}
			$strNote = $strFNN . DBO()->RatePlan->Name->Value ."' to '". DBO()->NewPlan->Name->Value ."'";
			SaveSystemNote($strNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, DBO()->Service->Id->Value);
			
			// All changes to the database, required to define the plan change, have been completed
			// Commit the transaction
			TransactionCommit();
			Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "The service's plan has been successfully changed", "Location" => Href()->ViewService(DBO()->Service->Id->Value)));
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

		// BreadCrumb menu
		BreadCrumb()->ViewAccount(DBO()->Service->Account->Value);
		BreadCrumb()->ViewService(DBO()->Service->Id->Value, DBO()->Service->FNN->Value);
		BreadCrumb()->SetCurrentPage("Change Plan");

		// Context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		$this->LoadPage('plan_change');

		return TRUE;
	
	}
	
	
	// This is used when unarchiving a service to check if its FNN has since been used by another service
	// It returns a status (defined in ui_app/definitions.php) which will be used to determine whether the 
	// Service can be unarchived, or a new service is required
	private function _GetFNNStatus($intService, $strFNN)
	{
		// Retrieve any services that are currently using $strFNN but aren't $intService
		$selFNN = new StatementSelect("Service", "*", "FNN=<FNN> AND (ClosedOn IS NULL OR ClosedOn >= NOW())");
		
		if ($selFNN->Execute(Array('FNN' => $strFNN)))
		{
			// At least one record was returned, which means the FNN is currently in use by an active service
			return FNN_CURRENTLY_IN_USE;
		}
		
		// Check if the FNN has been used by another archived service since $intService was archived
		//TODO! Joel, this is where you are up to
		$selFNN
	
		
		$selFNN = new StatementSelect("Service", "*", "FNN=<FNN> AND Service != <Service>");
		$intRecCount = $selFNN->Execute(Array('FNN' => $strFNN, 'Service' => $intService));
		
		if (!$intRecCount)
		{
			// The Service is free to use the FNN
			return FNN_AVAILABLE;
		}
		
		$selFNN = New StatementSelect("Service", "*", "FNN=<FNN>");
		
		$arrServices = $selFNN->FetchAll();
		
		foreach ($arrServices = )
		
		$arrService = $selFNN->Fetch();
		
		if ($selFNN->Execute())
		{
			// The FNN is currently being used 
		}
	}
	
	//----- DO NOT REMOVE -----//
	
}
