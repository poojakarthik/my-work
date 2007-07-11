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
		// Should probably check user authorization here
		//TODO!include user authorisation
		AuthenticatedUser()->CheckAuth();

		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
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
				
				// Account details
				DBO()->Charge->Account		= DBO()->Account->Id->Value;
				DBO()->Charge->AccountGroup	= DBO()->Account->AccountGroup->Value;
				
				// User's details
				$dboUser 					= GetAuthenticatedUserDBObject();
				DBO()->Charge->CreatedBy	= $dboUser->Id->Value;
				
				// Date the adjustment was created (the current date)
				DBO()->Charge->CreatedOn	= GetCurrentDateForMySQL();
				
				// Details regarding the type of charge
				DBO()->Charge->ChargeType	= DBO()->ChargeType->ChargeType->Value;
				DBO()->Charge->Description	= DBO()->ChargeType->Description->Value;
				DBO()->Charge->Nature		= DBO()->ChargeType->Nature->Value;
				
				// if DBO()->Charge->Invoice->Value == 0 then set it to NULL;
				if (!DBO()->Charge->Invoice->Value)
				{
					DBO()->Charge->Invoice = NULL;
				}
				
				// status is dependent on the nature of the charge
				if (DBO()->Charge->Nature->Value == "CR")
				{
					DBO()->Charge->Status	= CHARGE_WAITING;
				}
				else
				{
					DBO()->Charge->Status	= CHARGE_APPROVED;
				}

				// Save the adjustment to the charge table of the vixen database
				if (!DBO()->Charge->Save())
				{
					DBO()->Status->Message = "The adjustment did not save";
				}
				else
				{
					DBO()->Status->Message = "The adjustment was successfully saved";
					
					Ajax()->AddCommand("ClosePopup", "AddAdjustmentPopupId");
					Ajax()->AddCommand("Alert", "The Adjustment has been successfully added");
					Ajax()->AddCommand('LoadCurrentPage');
					
					return TRUE;
				}
			}
			else
			{
				// Something was invalid 
				DBO()->Status->Message = "Adjustment could not be saved. Invalid fields are shown in red";
			}
		}
		
		// Load all charge types that aren't archived
		DBL()->ChargeTypesAvailable->Archived = 0;
		DBL()->ChargeTypesAvailable->SetTable("ChargeType");
		DBL()->ChargeTypesAvailable->OrderBy("Nature DESC");
		DBL()->ChargeTypesAvailable->Load();

		// load the last 6 invoices with the most recent being first
		DBL()->AccountInvoices->Account = DBO()->Account->Id->Value;
		DBL()->AccountInvoices->SetTable("Invoice");
		DBL()->AccountInvoices->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->AccountInvoices->SetLimit(6);
		DBL()->AccountInvoices->Load();
		
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
		// Should probably check user authorization here
		//TODO!include user authorisation
		AuthenticatedUser()->CheckAuth();

		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
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
			
				
				// Account details
				DBO()->RecurringCharge->Account			= DBO()->Account->Id->Value;
				DBO()->RecurringCharge->AccountGroup	= DBO()->Account->AccountGroup->Value;
				
				// Service details
				DBO()->RecurringCharge->Service			= NULL;
				
				// User's details
				DBO()->RecurringCharge->CreatedBy		= AuthenticatedUser()->_arrUser['Id'];
				
				// Approved By
				DBO()->RecurringCharge->ApprovedBy		= NULL;
				
				// Date the adjustment was created (the current date)
				DBO()->RecurringCharge->CreatedOn		= GetCurrentDateForMySQL();
				
				// Details regarding the type of charge
				DBO()->RecurringCharge->ChargeType			= DBO()->RecurringChargeType->ChargeType->Value;
				DBO()->RecurringCharge->Description			= DBO()->RecurringChargeType->Description->Value;
				DBO()->RecurringCharge->Nature				= DBO()->RecurringChargeType->Nature->Value;
				
				DBO()->RecurringCharge->StartedOn			= "";
				DBO()->RecurringCharge->LastChargedOn		= NULL;
				
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
				
				// Save the recurring adjustment to the charge table of the vixen database
				if (!DBO()->RecurringCharge->Save())
				{
					DBO()->Status->Message = "The recurring adjustment did not save";
				}
				else
				{
					DBO()->Status->Message = "The recurring adjustment was successfully saved";

					Ajax()->AddCommand("ClosePopup", "AddRecurringAdjustmentPopupId");
					Ajax()->AddCommand("Alert", "The recurring adjustment has been successfully added");
					Ajax()->AddCommand('LoadCurrentPage');
					return TRUE;
				}
			}
			else
			{
				// Something was invalid 
				DBO()->Status->Message = "Adjustment could not be saved. Invalid fields are shown in red";
			}
		}
		
		// Load all charge types that aren't archived
		DBL()->ChargeTypesAvailable->Archived = 0;
		DBL()->ChargeTypesAvailable->SetTable("RecurringChargeType");
		DBL()->ChargeTypesAvailable->OrderBy("Nature DESC");
		DBL()->ChargeTypesAvailable->Load();

		// load the last 6 invoices with the most recent being first
		DBL()->AccountInvoices->Account = DBO()->Account->Id->Value;
		DBL()->AccountInvoices->SetTable("Invoice");
		DBL()->AccountInvoices->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->AccountInvoices->SetLimit(6);
		DBL()->AccountInvoices->Load();
		
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
		// Should probably check user authorization here
		//TODO!include user authorisation AND MAKE SURE THEY HAVE PAYMENT REVERSE PERMISSIONS
		AuthenticatedUser()->CheckAuth();

		// Make sure the correct form was submitted
		if (SubmittedForm('DeleteRecord', 'Delete'))
		{
			if (!DBO()->Charge->Load())
			{
				DBO()->Error->Message = "The Charge with charge id: '". DBO()->Charge->Id->value ."' could not be found";
				$this->LoadPage('error');
				return FALSE;
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
					Ajax()->AddCommand("ClosePopup", "DeleteAdjustmentPopupId");
					Ajax()->AddCommand("Alert", "The adjustment could not be deleted.\nThere was a problem with updating the record in the database.");
					Ajax()->AddCommand("LoadCurrentPage");
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
						DBO()->Note->Employee = AuthenticatedUser()->_arrUser['Id'];
						DBO()->Note->Datetime = GetCurrentDateAndTimeForMySQL();
						
						if (!DBO()->Note->Save())
						{
							Ajax()->AddCommand("Alert", "The note could not be saved");
						}
					}
					
					// Add a system generated note regarding the deleting of the charge
					DBO()->Note->Clean();
					DBO()->Note->NoteType = SYSTEM_NOTE_TYPE;
					DBO()->Note->AccountGroup = DBO()->Charge->AccountGroup->Value;
					DBO()->Note->Account = DBO()->Charge->Account->Value;
					DBO()->Note->Employee = AuthenticatedUser()->_arrUser['Id'];
					DBO()->Note->Datetime = GetCurrentDateAndTimeForMySQL();
					DBO()->Note->Note = "Charge with Id: ". DBO()->Charge->Id->Value ." has been deleted";
					
					if (!DBO()->Note->Save())
					{
						Ajax()->AddCommand("Alert", "The automatic system note could not be saved");
					}
					
					Ajax()->AddCommand("ClosePopup", "DeleteAdjustmentPopupId");
					Ajax()->AddCommand("Alert", "The adjustment was successfully deleted");
					Ajax()->AddCommand("LoadCurrentPage");
					return TRUE;
				}
			}
			else
			{
				//the charge cannot be deleted 
				Ajax()->AddCommand("ClosePopup", "DeleteAdjustmentPopupId");
				Ajax()->AddCommand("Alert", "The adjustment could not be deleted.\nCheck the status of the adjustment.");
				Ajax()->AddCommand("LoadCurrentPage");
				return TRUE;
			}
		}
		return TRUE;
	}
}
