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
 * @class	AppTemplateservice
 * @extends	ApplicationTemplate
 */
class AppTemplateservice extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// view
	//------------------------------------------------------------------------//
	/**
	 * view()
	 *
	 * Performs the logic for the service_view.php webpage
	 * 
	 * Performs the logic for the service_view.php webpage
	 *
	 * @return		void
	 * @method		view
	 *
	 */
	function view()
	{
		$pagePerms = PERMISSION_ADMIN;
		
		// Should probably check user authorization here
		AuthenticatedUser()->CheckAuth();
		
		AuthenticatedUser()->PermissionOrDie($pagePerms);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			// Add extra functionality for super-users
		}

		// Context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		// Breadcrumb menu
				
		// Setup all DBO and DBL objects required for the page
		
		//EXAMPLE:
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
		
		// Calculate unbilled charges (this includes all unbilled Adjustments(charges) and CDRs)
		// TODO test that the functionality works on catwalk with the CDR table
		//$fltUnbilledAdjustments					= UnbilledServiceChargeTotal(DBO()->Service->Id->Value);
		$fltUnbilledAdjustments					= UnbilledServiceChargeTotal(33260);
		$fltUnbilledCDRs						= UnbilledServiceCDRTotal(DBO()->Service->Id->Value);
		DBO()->Service->TotalUnbilledCharges 	= AddGST($fltUnbilledAdjustments + $fltUnbilledCDRs);
		
		
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('service_view');

		return TRUE;
	}
	
	function edit()
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
			//$bolUpdateFNN = FALSE;
			//$bolUpdateArchiveStatus = FALSE;
			if (DBO()->Service->IsInvalid())
			{
				// The form has not passed initial validation
				Ajax()->AddCommand("Alert", "Could not save the service.  Invalid fields are highlighted");
				Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, "ServiceEditDiv");
				return TRUE;
			}
			
			$bolUpdateFNN = FALSE;
			if (DBO()->Service->FNN->Value != DBO()->Service->CurrentFNN->Value)
			{		
				// This is entered if the FNN entered is different to the 
				// current FNN i.e. the user has entered a new FNN
				// ------------------------------------------------------
		
				if (DBO()->Service->FNN->Value != DBO()->Service->FNNConfirm->Value)
				{
					// This is entered if the FNN is different from FNNConfirm 
					// i.e. a typo when entering on the form
					// -------------------------------------------------------				
				
					DBO()->Service->FNN->SetToInvalid();
					DBO()->Service->FNNConfirm->SetToInvalid();
					Ajax()->AddCommand("Alert", "*Could not save the service.  Service # and Confirm Service # must be the same");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, "ServiceEditDiv");
					return TRUE;
				}
				
				// Make sure the new FNN is valid for the service type
				$intServiceType = ServiceType(DBO()->Service->FNN->Value);
				if ($intServiceType != DBO()->Service->ServiceType->Value)
				{
					// The FNN is invalid for the services servicetype, output an appropriate message
					DBO()->Service->FNN->SetToInvalid();
					Ajax()->AddCommand("Alert", "The FNN is invalid for the service type");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, "ServiceEditDiv");
					return TRUE;
				}
				
				// Test if the FNN is currently not being used
				$strWhere = "FNN LIKE \"". DBO()->Service->FNN->Value . "\"";
				DBL()->Service->Where->SetString($strWhere);
				DBL()->Service->Load();
				if (DBL()->Service->RecordCount() > 0)
				{	
					DBO()->Service->FNN->SetToInvalid();
					DBO()->Service->FNNConfirm->SetToInvalid();
					Ajax()->AddCommand("Alert", "This Service Number already exists in the Database");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, "ServiceEditDiv");
					return TRUE;
				}
				
				// the new FNN is valid flag it to update in the service record in the database
				$bolUpdateFNN = TRUE;
				
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
			}
			if (DBO()->Service->ActivateService->Value)
			{
				// we want to activate this service
				$bolActivateService = TRUE;
				
				// set ClosedOn date to null
				DBO()->Service->ClosedOn = NULL;
				
				// set ClosedBy to null
				DBO()->Service->ClosedBy = NULL;
				
				//TODO! probably need to run EnableELB
				
				// Declare properties to update
				$arrUpdateProperties[] = "ClosedOn";
				$arrUpdateProperties[] = "ClosedBy";
			}
			
			//TODO! If the service is being updated, an automatic note should be generated describing what happened
			// Check if the existing system does this
			
			// Save the changes to the Service Table, if count($arrUpdateProperties) > 0
			//TODO! (Start the transaction here)
			
				else
				{
					// all validation done and ok to write to database
					switch(DBO()->Service->ServiceType->Value)
					{
						case SERVICE_TYPE_MOBILE:
							$strColumnsToUpdate = "FNN";
							break;
						case SERVICE_TYPE_INBOUND:
							if (DBO()->ServiceInboundDetails->AnswerPoint->Value == "")
							{
								//
							}elseif (DBO()->ServiceInboundDetails->Configuration->Value == "")
							{
								//
							}
							else
							{
								//
							}
							break;
						case SERVICE_TYPE_LANDLINE:
							$strColumnsToUpdate = "FNN";
							beak;
						case SERVICE_TYPE_ADSL:
							$strColumnsToUpdate = "FNN, CostCentre";
							break;
					}
				}
				DBO()->Service->SetColumns($strColumnsToUpdate);
				
				Ajax()->AddCommand("Alert", "Service # and Confirm Service # the same, commit to database");
				Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, "ServiceEditDiv");
				return TRUE;
				
				// case statements multiple SERVICE_TYPE
				
				// then commit to database (one line)
			}
			// Check if we have to activate or de-activate the service
			// TODO 			
			
			// if the FNN hasn't changed but user has selected archive this service
			// --------------------------------------------------------------------
			
			/*
			
			if ()
			{
				//($bolUpdateFNN)//||
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "bolUpdateFNN entered", "Location" => Href()->ViewService(DBO()->Service->Id->Value)));
			
				// Everything has been validated on the form, so commit it to the database
				//DBO()->Service->SetColumns($strColumnsToUpdate);
				/*if($bolUpdateArchive)
				{
					//check which checkbox is selected
					Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "bolUpdateArchive entered", "Location" => Href()->ViewService(DBO()->Service->Id->Value)));
					return TRUE;
				}
			}

			*/

			if (!DBO()->Service->Save())
			{
				// The Service failed to update
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "ERROR: Updating the service details failed, unexpectedly", "Location" => Href()->ViewService(DBO()->Service->Id->Value)));
				return TRUE;
			}
			
			// The service details were successfully saved so go back to the last page
			Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "The service details were successfully updated", "Location" => Href()->ViewService(DBO()->Service->Id->Value)));
			return TRUE;
		}
		
		if (!DBO()->Service->Load())
		{
			DBO()->Error->Message = "The Service id: ". DBO()->Service->Id->Value ."you were attempting to view could not be found";
			$this->LoadPage('error');
			return FALSE;
		}

		
		// Store the current FNN to check between states that the FNN textbox has been changed
		DBO()->Service->CurrentFNN = DBO()->Service->FNN->Value;

		// Check if the service has been closed and if so check the checkbox
		
			/*if (($intClosedOn > $intTodaysDate))
			{
				DBO()->Service->Archive = ;
			}*/
		//}
		
		// Load context menu items specific to the View Service page
		// Context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();

		// Bread Crumb Menu
		BreadCrumb()->View_Service(DBO()->Service->Id->Value, DBO()->Service->FNN->Value);

		// Declare which page to use
		$this->LoadPage('service_edit');
		return TRUE;
	}	
	
	//----- DO NOT REMOVE -----//
	
}
