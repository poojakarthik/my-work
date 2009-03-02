<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// adjustment.php
//----------------------------------------------------------------------------//
/**
 * adjustment
 *
 * contains all ApplicationTemplate extended classes relating to Adjustment functionality
 *
 * contains all ApplicationTemplate extended classes relating to Adjustment functionality
 *
 * @file		adjustment.php
 * @language	PHP
 * @package		framework
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateAdjustment
//----------------------------------------------------------------------------//
/**
 * AppTemplateAdjustment
 *
 * The AppTemplateAdjustment class
 *
 * The AppTemplateAdjustment class.  This incorporates all logic for all pages
 * relating to Adjustments
 *
 *
 * @package	ui_app
 * @class	AppTemplateAdjustment
 * @extends	ApplicationTemplate
 */
class AppTemplateAdjustment extends ApplicationTemplate
{
	//------------------------------------------------------------------------//
	// Add
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Performs the logic for the Add Adjustment popup window
	 * 
	 * Performs the logic for the Add Adjustment popup window
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Add()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolUserHasProperAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);

		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("Alert", "ERROR: The account with account id: '". DBO()->Account->Id->value ."' could not be found");
			return TRUE;
		}
		
		// Adjustments can not be added if the account is pending activation
		if (DBO()->Account->Archived->Value == ACCOUNT_STATUS_PENDING_ACTIVATION)
		{
			Ajax()->AddCommand("Alert", "The account is pending activation.  Adjustments cannot be added.");
			return TRUE;
		}
		
		// Check if the adjustment relates to a particular service
		if (DBO()->Service->Id->Value)
		{
			// A service has been specified.  Load it, to check that it actually exists
			if (!DBO()->Service->Load())
			{
				Ajax()->AddCommand("Alert", "ERROR: The service with service id: '". DBO()->Service->Id->value ."' could not be found");
				return TRUE;
			}
			
			// It is assumed that this is the newest most record modelling this service for the account
			// that the service belongs to.  Check that the service is currently active
			$objService = ModuleService::GetServiceById(DBO()->Service->Id->Value, DBO()->Service->RecordType->Value);
			if ($objService->GetStatus() == SERVICE_PENDING)
			{
				Ajax()->AddCommand("Alert", "This service is pending activation.  Adjustments can only be applied to active services.");
				return TRUE;
			}
			elseif (!$objService->IsCurrentlyActive())
			{
				Ajax()->AddCommand("Alert", "This service is not currently active on this account.  Adjustments can only be applied to active services.");
				return TRUE;
			}
		}

		// Load all charge types that aren't archived and aren't flagged as automatic_only
		DBL()->ChargeTypesAvailable->Archived = 0;
		DBL()->ChargeTypesAvailable->automatic_only = 0;
		
		// Only proper admins can create credit adjustments
		if (!$bolUserHasProperAdminPerm)
		{
			// The user can only create debit adjustments
			DBL()->ChargeTypesAvailable->Nature = 'DR';
		}
		DBL()->ChargeTypesAvailable->SetTable("ChargeType");
		DBL()->ChargeTypesAvailable->OrderBy("Nature DESC, Description");
		DBL()->ChargeTypesAvailable->Load();
		
		if (DBL()->ChargeTypesAvailable->RecordCount() == 0)
		{
			Ajax()->AddCommand("Alert", "There are currently no adjustment types defined");
			return TRUE;
		}

		// load the last 6 invoices with the most recent being first
		DBL()->AccountInvoices->Account = DBO()->Account->Id->Value;
		DBL()->AccountInvoices->SetTable("Invoice");
		DBL()->AccountInvoices->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->AccountInvoices->SetLimit(6);
		DBL()->AccountInvoices->Load();


		// check if an adjustment is being submitted
		if (SubmittedForm('AddAdjustment', 'Add Adjustment'))
		{
			// Load the relating Account and ChargeType records
			DBO()->ChargeType->Load();

			// Define all the required properties for the Charge record
			if ((!DBO()->Account->IsInvalid()) && (!DBO()->Charge->IsInvalid()) && (!DBO()->ChargeType->IsInvalid()))
			{
				// if the charge amount has a leading dollar sign then strip it off
				DBO()->Charge->Amount = ltrim(trim(DBO()->Charge->Amount->Value), '$');
				
				// Check that the charge amount is not negative
				if (floatval(DBO()->Charge->Amount->Value < 0))
				{
					Ajax()->AddCommand("Alert", "ERROR: The Adjustment cannot be a negative value");
					return TRUE;
				}
				
				// Remove GST from this amount
				DBO()->Charge->Amount = RemoveGST(DBO()->Charge->Amount->Value);
				
				// Account details
				DBO()->Charge->Account		= DBO()->Account->Id->Value;
				DBO()->Charge->AccountGroup	= DBO()->Account->AccountGroup->Value;
				
				// Service details
				if (DBO()->Service->Id->Value)
				{
					DBO()->Charge->Service	= DBO()->Service->Id->Value;
				}
				
				// User's details
				$dboUser 					= GetAuthenticatedUserDBObject();
				DBO()->Charge->CreatedBy	= $dboUser->Id->Value;
				
				// Date the adjustment was created (the current date)
				$strCurrentDate = GetCurrentDateForMySQL();
				DBO()->Charge->CreatedOn	= $strCurrentDate;
				DBO()->Charge->ChargedOn	= $strCurrentDate;
				
				// Details regarding the type of charge
				DBO()->Charge->ChargeType	= DBO()->ChargeType->ChargeType->Value;
				DBO()->Charge->Description	= DBO()->ChargeType->Description->Value;
				DBO()->Charge->Nature		= DBO()->ChargeType->Nature->Value;
				
				// Only ProperAdmins can create credit adjustments
				if (DBO()->Charge->Nature->Value == 'CR' && !$bolUserHasProperAdminPerm)
				{
					// The user is not a proper admin but is trying to create a credit adjustment
					Ajax()->AddCommand("Alert", "ERROR: You do not have permission to create credit adjustments");
					return TRUE;
				}
				
				// if DBO()->Charge->Invoice->Value == 0 then set it to NULL;
				if (!DBO()->Charge->Invoice->Value)
				{
					DBO()->Charge->Invoice = NULL;
				}
				
				// Set the status to CHARGE_WAITING
				DBO()->Charge->Status = CHARGE_WAITING;

				$arrData = DBO()->Charge->AsArray();

				TransactionStart();
				$intChargeId = Framework()->AddCharge($arrData);

				// Save the adjustment to the charge table of the vixen database
				if ($intChargeId === FALSE)
				{
					// The adjustment did not save
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Saving the adjustment failed, unexpectedly");
					return TRUE;
				}
				else
				{
					DBO()->Charge->Id = $intChargeId;
					// The adjustment was successfully saved
					TransactionCommit();
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "The Adjustment has been successfully added");
					return TRUE;
				}
			}
			else
			{
				// Something was invalid
				Ajax()->RenderHtmlTemplate("AdjustmentAdd", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
				Ajax()->AddCommand("Alert", "ERROR: Adjustment could not be saved. Invalid fields are highlighted");
				return TRUE;
			}
		}
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('adjustment_add');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// AddRecurring
	//------------------------------------------------------------------------//
	/**
	 * AddRecurring()
	 *
	 * Performs the logic for the Add Recurring Adjustment popup window
	 * 
	 * Performs the logic for the Add Recurring Adjustment popup window
	 *
	 * @return		void
	 * @method
	 *
	 */
	function AddRecurring()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("Alert", "The account with account id: '". DBO()->Account->Id->value ."' could not be found");
			return TRUE;
		}

		// Adjustments can not be added if the account is pending activation
		if (DBO()->Account->Archived->Value == ACCOUNT_STATUS_PENDING_ACTIVATION)
		{
			Ajax()->AddCommand("Alert", "The account is pending activation.  Adjustments cannot be added.");
			return TRUE;
		}

		// Check if the adjustment relates to a particular service
		if (DBO()->Service->Id->Value)
		{
			// A service has been specified.  Load it, to check that it actually exists
			if (!DBO()->Service->Load())
			{
				Ajax()->AddCommand("Alert", "The service with service id: '". DBO()->Service->Id->value ."' could not be found");
				return TRUE;
			}
			// It is assumed that this is the newest most record modelling this service for the account
			// that the service belongs to.  Check that the service is currently active
			$objService = ModuleService::GetServiceById(DBO()->Service->Id->Value, DBO()->Service->RecordType->Value);
			if ($objService->GetStatus() == SERVICE_PENDING)
			{
				Ajax()->AddCommand("Alert", "This service is pending activation.  Adjustments can only be applied to active services.");
				return TRUE;
			}
			elseif (!$objService->IsCurrentlyActive())
			{
				Ajax()->AddCommand("Alert", "This service is not currently active on this account.  Adjustments can only be applied to active services.");
				return TRUE;
			}
			
		}

		// Load all charge types that aren't archived
		DBL()->ChargeTypesAvailable->Archived = 0;
		DBL()->ChargeTypesAvailable->SetTable("RecurringChargeType");
		DBL()->ChargeTypesAvailable->OrderBy("Nature DESC, Description");
		DBL()->ChargeTypesAvailable->Load();

		if (DBL()->ChargeTypesAvailable->RecordCount() == 0)
		{
			Ajax()->AddCommand("Alert", "There are currently no recurring adjustment types defined");
			return TRUE;
		}


		// load the last 6 invoices with the most recent being first
		DBL()->AccountInvoices->Account = DBO()->Account->Id->Value;
		DBL()->AccountInvoices->SetTable("Invoice");
		DBL()->AccountInvoices->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->AccountInvoices->SetLimit(6);
		DBL()->AccountInvoices->Load();

		// check if an adjustment is being submitted
		if (SubmittedForm('AddRecurringAdjustment', 'Add Adjustment'))
		{
			// Load the relating Account and ChargeType records
			DBO()->RecurringChargeType->Load();

			// Define all the required properties for the Charge record
			if ((!DBO()->Account->IsInvalid()) && (!DBO()->RecurringCharge->IsInvalid()) && (!DBO()->RecurringChargeType->IsInvalid()))
			{
				// if the money values have leading dollar signs then strip it off
				DBO()->RecurringCharge->MinCharge = ltrim(trim(DBO()->RecurringCharge->MinCharge->Value), '$');
				DBO()->RecurringCharge->RecursionCharge = ltrim(trim(DBO()->RecurringCharge->RecursionCharge->Value), '$');
			
				// Remove GST from Minimum Charge and Recursion Charge
				DBO()->RecurringCharge->MinCharge = RemoveGST(DBO()->RecurringCharge->MinCharge->Value);
				DBO()->RecurringCharge->RecursionCharge = RemoveGST(DBO()->RecurringCharge->RecursionCharge->Value);
				
				// Account details
				DBO()->RecurringCharge->Account			= DBO()->Account->Id->Value;
				DBO()->RecurringCharge->AccountGroup	= DBO()->Account->AccountGroup->Value;
				
				// Service details
				if (DBO()->Service->Id->Value)
				{
					DBO()->RecurringCharge->Service		= DBO()->Service->Id->Value;
				}

				// User's details
				DBO()->RecurringCharge->CreatedBy		= AuthenticatedUser()->_arrUser['Id'];
				
				// Approved By
				DBO()->RecurringCharge->ApprovedBy		= NULL;
				
				$strCurrentDate						= GetCurrentISODate();
				$intNow								= strtotime(GetCurrentISODateTime());
				$intCurrentDay						= intval(date("d", $intNow));
				$intCurrentMonth					= intval(date("m", $intNow));
				$intCurrentYear						= intval(date("Y", $intNow));
				DBO()->RecurringCharge->CreatedOn	= $strCurrentDate;

				if ($intCurrentDay >= 29 && $intCurrentDay <= 31)
				{
					// The StartedOn date has to snap to either the 28th or the 1st of next month
					switch (intval(DBO()->RecurringCharge->SnapToDayOfMonth->Value))
					{
						case 1:
								// Set the start date to the 1st of next month
								DBO()->RecurringCharge->StartedOn = date("Y-m-d", mktime(0, 0, 0, $intCurrentMonth + 1, 1, $intCurrentYear));
								break;
							
						case 28:
						default:
								// Set the start date to the 28th of the current month
								DBO()->RecurringCharge->StartedOn = date("Y-m-d", mktime(0, 0, 0, $intCurrentMonth, 28, $intCurrentYear));
								break;
					}
					
				}
				else
				{
					DBO()->RecurringCharge->StartedOn = $strCurrentDate;
				}
				
				if (DBO()->RecurringCharge->in_advance->Value == TRUE)
				{
					// Charging in advance
					DBO()->RecurringCharge->LastChargedOn = NULL;
				}
				else
				{
					// Charging in arrears
					DBO()->RecurringCharge->LastChargedOn = DBO()->RecurringCharge->StartedOn->Value;
				}
				
				// Details regarding the type of charge
				DBO()->RecurringCharge->ChargeType			= DBO()->RecurringChargeType->ChargeType->Value;
				DBO()->RecurringCharge->Description			= DBO()->RecurringChargeType->Description->Value;
				DBO()->RecurringCharge->Nature				= DBO()->RecurringChargeType->Nature->Value;
				
				DBO()->RecurringCharge->RecurringFreqType	= DBO()->RecurringChargeType->RecurringFreqType->Value;
				DBO()->RecurringCharge->RecurringFreq		= DBO()->RecurringChargeType->RecurringFreq->Value;
				
				// These have already been set				
				//DBO()->RecurringCharge->MinCharge
				//DBO()->RecurringCharge->RecursionCharge
				
				DBO()->RecurringCharge->CancellationFee		= DBO()->RecurringChargeType->CancellationFee->Value;
				DBO()->RecurringCharge->Continuable			= DBO()->RecurringChargeType->Continuable->Value;
				DBO()->RecurringCharge->PlanCharge			= DBO()->RecurringChargeType->PlanCharge->Value;
				DBO()->RecurringCharge->UniqueCharge		= DBO()->RecurringChargeType->UniqueCharge->Value;
				DBO()->RecurringCharge->TotalCharged		= 0;
				DBO()->RecurringCharge->TotalRecursions		= 0;
				DBO()->RecurringCharge->Archived			= 0;

				$strMinCharge		= OutputMask()->MoneyValue(addGST(DBO()->RecurringCharge->MinCharge->Value), 2, TRUE);
				$strRecursionCharge	= OutputMask()->MoneyValue(addGST(DBO()->RecurringCharge->RecursionCharge->Value), 2, TRUE);

				$strNote  = "Recurring charge created\n";
				$strNote .= "Type: " . DBO()->RecurringCharge->ChargeType->FormattedValue() . "\n";
				$strNote .= "Description: " . DBO()->RecurringCharge->Description->FormattedValue() . "\n";
				$strNote .= "Nature: " . DBO()->RecurringCharge->Nature->FormattedValue() . "\n";
				$strNote .= "Minimum Charge: $strMinCharge (inc GST)\n";
				$strNote .= "Recurring Charge: $strRecursionCharge (inc GST)\n";
				$strNote .= (DBO()->RecurringCharge->in_advance->Value == TRUE)? "Charged in advance\n" : "Charged in arrears\n";

				// Save the recurring adjustment to the charge table of the vixen database
				if (!DBO()->RecurringCharge->Save())
				{
					// The recurring adjustment did not save
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "ERROR: The recurring adjustment did not save");
					return TRUE;
				}
				else
				{
					// The recurring adjustment was successfully saved
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "The recurring adjustment has been successfully added");
					//TODO Have this fire a OnNewRecurringAdjustment Event
					//TODO Have this fire a OnNewNote Event
					
					// Save the system note
					// If Service ID is passed then this is creating a system note for a recurring charge linked to a service
					if (DBO()->Service->Id->Value)
					{
						$strNote = "Service $strNote";					
						SaveSystemNote($strNote, DBO()->Account->AccountGroup->Value, DBO()->Account->Id->Value, NULL, DBO()->Service->Id->Value);
					}
					// If no Service ID is passed then this is creating a system note for a recurring charge linked to an account
					else
					{
						$strNote = "Account $strNote";
						SaveSystemNote($strNote, DBO()->Account->AccountGroup->Value, DBO()->Account->Id->Value);					
					}
					
					return TRUE;
				}
			}
			else
			{
				// Something was invalid
				Ajax()->RenderHtmlTemplate("RecurringAdjustmentAdd", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
				Ajax()->AddCommand("Alert", "ERROR: Adjustment could not be saved. Invalid fields have been reset and highlighted");
				return TRUE;
			}
		}
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('recurring_adjustment_add');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// DeleteAdjustment
	//------------------------------------------------------------------------//
	/**
	 * DeleteAdjustment()
	 *
	 * Performs Delete Adjustment functionality
	 * 
	 * Performs Delete Adjustment functionality
	 *
	 * @return		void
	 * @method
	 *
	 */
	function DeleteAdjustment()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		
		// Make sure the correct form was submitted
		if (SubmittedForm('DeleteRecord'))
		{
			// Deleting Adjustments can not be done while billing is in progress
			if (IsInvoicing())
			{
				$strErrorMsg =  "Billing is in progress.  Adjustments cannot be deleted while this is happening.  ".
								"Please try again in a couple of hours.  If this problem persists, please ".
								"notify your system administrator";
				Ajax()->AddCommand("Alert", $strErrorMsg);
				return TRUE;
			}
			
			$strNoteMsg = "";
			$strSystemNoteMsg = "";
		
			if (!DBO()->Charge->Load())
			{
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("Reload", "The adjustment with id: ". DBO()->Charge->Id->Value ." could not be found");
				return TRUE;
			}
			
			// The charge can only be deleted if its status is CHARGE_WAITING or CHARGE_APPROVED
			if ((DBO()->Charge->Status->Value == CHARGE_WAITING) || (DBO()->Charge->Status->Value == CHARGE_APPROVED))
			{
				// Delete the charge
				DBO()->Charge->Status = CHARGE_DELETED;
				
				// Update the charge
				if (!DBO()->Charge->Save())
				{
					// The charge could not be updated
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("Alert", nl2br("The adjustment could not be deleted.\nThere was a problem with updating the record in the database."));
					return TRUE;
				}
				else
				{
					// The Charge was successfully updated.  Now add the user's note, if one was specified
					if (!DBO()->Note->IsInvalid())
					{
						DBO()->Note->NoteType = GENERAL_NOTE_TYPE;
						DBO()->Note->AccountGroup = DBO()->Charge->AccountGroup->Value;
						DBO()->Note->Account = DBO()->Charge->Account->Value;
						DBO()->Note->Service = DBO()->Charge->Service->Value;
						DBO()->Note->Employee = AuthenticatedUser()->_arrUser['Id'];
						DBO()->Note->Datetime = GetCurrentDateAndTimeForMySQL();
						
						if (!DBO()->Note->Save())
						{
							$strNoteMsg = "\nWarning: The operator's note could not be saved.";
						}
					}
					
					// Add a system generated note regarding the deleting of the charge
					$strNote  = GetEmployeeName(AuthenticatedUser()->_arrUser['Id']) . " deleted a " . DBO()->Charge->Nature->FormattedValue();
					$strNote .= " adjustment made on " . DBO()->Charge->CreatedOn->FormattedValue();
					// add GST to the charge amount
					$strChargeAmount = OutputMask()->MoneyValue(addGST(DBO()->Charge->Amount->Value), 2, TRUE);
					$strNote .= " for " . $strChargeAmount . " (inc GST)";
					$strNote .= "\nAdjustment Id: " . DBO()->Charge->Id->FormattedValue();
					$strNote .= "\nAdjustment Type: " . DBO()->Charge->ChargeType->FormattedValue();
					$strNote .= "\nDescription: " . DBO()->Charge->Description->FormattedValue();
					
					if (!SaveSystemNote($strNote, DBO()->Charge->AccountGroup->Value, DBO()->Charge->Account->Value, NULL, DBO()->Charge->Service->Value))
					{
						$strSystemNoteMsg = "\nWarning: The automatic system note could not be saved.";
					}
					
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", nl2br("The adjustment was successfully deleted.{$strNoteMsg}{$strSystemNoteMsg}"));
					return TRUE;
				}
			}
			else
			{
				// The Charge can not be deleted
				$strErrorMsg  = "<div class='PopupMedium'>\n";
				$strErrorMsg .= "ERROR: The adjustment can not be deleted due to its status.\n";
				$strErrorMsg .= DBO()->Charge->Id->AsOutput();
				$strErrorMsg .= DBO()->Charge->CreatedOn->AsOutput();
				$strErrorMsg .= DBO()->Charge->AccountGroup->AsOutput();
				$strErrorMsg .= DBO()->Charge->Account->AsOutput();
				$strErrorMsg .= DBO()->Charge->Amount->AsCallback("addGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
				$strErrorMsg .= DBO()->Charge->Status->AsCallback("GetConstantDescription", Array("ChargeStatus"), RENDER_OUTPUT);
				$strErrorMsg .= "</div>\n";
				
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("Alert", $strErrorMsg);
				return TRUE;
			}
		}
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// DeleteRecurringAdjustment
	//------------------------------------------------------------------------//
	/**
	 * DeleteRecurringAdjustment()
	 *
	 * Performs Delete Recurring Adjustment functionality
	 * 
	 * Performs Delete Recurring Adjustment functionality
	 *
	 * @return		void
	 * @method
	 *
	 */
	function DeleteRecurringAdjustment()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);
		
		// Make sure the correct form was submitted
		if (SubmittedForm('DeleteRecord'))
		{
			// Deleting Recurring Adjustments can not be done while billing is in progress
			if (IsInvoicing())
			{
				$strErrorMsg =  "Billing is in progress.  Adjustments cannot be deleted while this is happening.  ".
								"Please try again in a couple of hours.  If this problem persists, please ".
								"notify your system administrator";
				Ajax()->AddCommand("Alert", $strErrorMsg);
				return TRUE;
			}
			
			$strNoteMsg = "";
			$strSystemNoteMsg = "";
			
			if (!DBO()->RecurringCharge->Load())
			{
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("Alert", "The recurring adjustment with id: ". DBO()->RecurringCharge->Id->Value ." could not be found");
				return TRUE;
			}
			
			// The recurring charge can only be deleted if it is not currently archived
			if (DBO()->RecurringCharge->Archived->Value == 0)
			{
				// Recurring charges cannot be deleted during the Invoicing Process
				if (IsInvoicing())
				{
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("Alert", "ERROR: The Invoicing process is currently running.  Recurring adjustments cannot be cancelled at this time.  Please try again later.");
					return TRUE;
				}
			
				// Declare the transaction
				TransactionStart();
				
				// Set the archive status of the recurring charge to ARCHIVED
				DBO()->RecurringCharge->Archived = 1;
				
				// Update the recurring charge
				if (!DBO()->RecurringCharge->Save())
				{
					// The recurring charge could not be updated
					
					// rollback the Transaction (although it really doesn't matter at this stage)
					TransactionCommit();
					
					// Close the popup gracefully
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("Alert", "ERROR: The recurring adjustment could not be cancelled.  There was a problem with updating the RecurringCharge record in the database.");
					return TRUE;
				}
				// The recurring charge was successfully updated.
				
				// Calculate the amount left owing on the recurring adjustment
				$fltAmountOwing = DBO()->RecurringCharge->MinCharge->Value - DBO()->RecurringCharge->TotalCharged->Value;
				
				// Add a new debit charge if the Recurring Charge was a Debit and there is still money left owing on it
				if ((DBO()->RecurringCharge->Nature->Value == NATURE_DR) && ($fltAmountOwing > 0.0))
				{
					// The additional charge is equal to the money left owing plus the cancellation fee (excluding GST)
					$fltChargeAmount			= $fltAmountOwing + DBO()->RecurringCharge->CancellationFee->Value;
					DBO()->Charge->AccountGroup	= DBO()->RecurringCharge->AccountGroup->Value;
					DBO()->Charge->Account		= DBO()->RecurringCharge->Account->Value;
					DBO()->Charge->Service		= DBO()->RecurringCharge->Service->Value;
					DBO()->Charge->CreatedBy	= AuthenticatedUser()->_arrUser['Id'];
					DBO()->Charge->CreatedOn	= GetCurrentISODate();
					DBO()->Charge->ApprovedBy	= USER_ID;
					DBO()->Charge->ChargeType	= DBO()->RecurringCharge->ChargeType->Value;
					DBO()->Charge->Description	= "CANCELLATION: ". DBO()->RecurringCharge->Description->Value;
					DBO()->Charge->ChargedOn	= GetCurrentISODate();
					DBO()->Charge->Nature		= NATURE_DR;
					DBO()->Charge->Amount		= $fltChargeAmount;
					DBO()->Charge->Invoice		= NULL;
					DBO()->Charge->Notes		= DBO()->Note->Note->Value;
					DBO()->Charge->LinkType		= CHARGE_LINK_RECURRING_CANCEL;
					DBO()->Charge->LinkId		= DBO()->RecurringCharge->Id->Value;
					DBO()->Charge->Status		= CHARGE_APPROVED;
					
					$arrData = DBO()->Charge->AsArray();
					$intChargeId = Framework()->AddCharge($arrData);

					// Save the charge
					if ($intChargeId === FALSE)
					{
						// The charge could not be saved so rollback the transaction
						TransactionRollback();
						
						// Close the popup gracefully
						Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
						Ajax()->AddCommand("Alert", nl2br("ERROR: The recurring adjustment could not be cancelled.\nThere was a problem with generating the cancellation charge."));
						return TRUE;
					}
				}
				
				// Commit the transaction
				TransactionCommit();
				
				// Now add the user's note and the automatic note
				if (!DBO()->Note->IsInvalid())
				{
					DBO()->Note->NoteType = GENERAL_NOTE_TYPE;
					DBO()->Note->AccountGroup = DBO()->RecurringCharge->AccountGroup->Value;
					DBO()->Note->Account = DBO()->RecurringCharge->Account->Value;
					DBO()->Note->Service = DBO()->RecurringCharge->Service->Value;
					DBO()->Note->Employee = AuthenticatedUser()->_arrUser['Id'];
					DBO()->Note->Datetime = GetCurrentDateAndTimeForMySQL();
					
					if (!DBO()->Note->Save())
					{
						$strNoteMsg = "\nWarning: The operator's note could not be saved.";
					}
				}
				
				// Add a system generated note regarding the deleting of the charge
				$strNote  = "Recurring charge removed"; 
				$strNote .= "\nRecurring Adjustment Id: " . DBO()->RecurringCharge->Id->FormattedValue();
				$strNote .= "\nType: " . DBO()->RecurringCharge->ChargeType->FormattedValue();
				$strNote .= "\nDescription: " . DBO()->RecurringCharge->Description->FormattedValue();
				$strNote .= "\nNature: " . DBO()->RecurringCharge->Nature->FormattedValue();
				// add GST to the minimum charge
				$strMinCharge = OutputMask()->MoneyValue(AddGST(DBO()->RecurringCharge->MinCharge->Value), 2, TRUE);
				$strNote .= "\nMinimum Charge: $strMinCharge (inc GST)";
				// add GST to the recursion charge
				$strRecursionCharge = OutputMask()->MoneyValue(AddGST(DBO()->RecurringCharge->RecursionCharge->Value), 2, TRUE);
				$strNote .= "\nRecursion Charge: $strRecursionCharge (inc GST)";
				$strAlreadyCharged = OutputMask()->MoneyValue(AddGST(DBO()->RecurringCharge->TotalCharged->Value), 2, TRUE);
				$strNote .= "\nAlready Charged: $strAlreadyCharged (inc GST)";
				
				if (DBO()->RecurringCharge->Nature->Value == NATURE_DR && $fltAmountOwing > 0.0)
				{
					// An additional charge was made to account for the remainder of the MinCharge and CancellationFee
					$strAdditionalCharge = OutputMask()->MoneyValue(AddGST($fltChargeAmount), 2, TRUE);
					$strNote .= "\nAn additional charge was made for $strAdditionalCharge (inc GST) to account for the outstand portion of the minimum charge and the cancellation fee";
				}
				
				if (!SaveSystemNote($strNote, DBO()->RecurringCharge->AccountGroup->Value, DBO()->RecurringCharge->Account->Value, NULL, DBO()->RecurringCharge->Service->Value))
				{
					$strSystemNoteMsg = "\nWarning: The automatic system note could not be saved.";
				}
				
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("AlertReload", nl2br("The adjustment was successfully cancelled.{$strNoteMsg}{$strSystemNoteMsg}"));
				return TRUE;
			}
			else
			{
				// the recurring charge cannot be deleted
				$strErrorMsg  = "<div class='PopupMedium'>\n";
				$strErrorMsg .= "ERROR: The Recurring adjustment can not be cancelled as it is already marked as being cancelled.\n";
				$strErrorMsg .= DBO()->RecurringCharge->Id->AsOutput();
				$strErrorMsg .= DBO()->RecurringCharge->CreatedOn->AsOutput();
				$strErrorMsg .= DBO()->RecurringCharge->AccountGroup->AsOutput();
				$strErrorMsg .= DBO()->RecurringCharge->Account->AsOutput();
				$strErrorMsg .= DBO()->RecurringCharge->MinCharge->AsCallback("addGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
				$strErrorMsg .= DBO()->RecurringCharge->RecursionCharge->AsCallback("addGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
				$strErrorMsg .= DBO()->RecurringCharge->TotalCharged->AsCallback("addGST", NULL, RENDER_OUTPUT, CONTEXT_INCLUDES_GST);
				$strErrorMsg .= DBO()->RecurringCharge->Archived->AsOutput();
				$strErrorMsg .= "</div>\n";
				
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("Alert", $strErrorMsg);
				return TRUE;
			}
		}
		return TRUE;
	}

}
?>