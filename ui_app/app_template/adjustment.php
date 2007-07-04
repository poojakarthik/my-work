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

				// Add the adjustment to the charge table of the database
				if (!DBO()->Charge->Save())
				{
					//echo "The charge did not save\n";
					DBO()->Status->Message = "The adjustment did not save";
				}
				else
				{
					//echo "Saved<br>\n";
					DBO()->Status->Message = "The adjustment was successfully saved";
					
					// Tell the page to reload
					//TODO!
					//$this->ReLoadPage();
					//$this->Location($href);
					return TRUE;
				}
			}
			else
			{
				// something was invalid 
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
	// InsertAdjustment  (This was made to be run in AJAX_MODE with the idea that it won't create a page)
	//------------------------------------------------------------------------//
	/**
	 * InsertAdjustment()
	 *
	 * Inserts a new record into the Charge table
	 * 
	 * Inserts a new record into the Charge table
	 * Also creates the reply JSON object that is sent back to the javascript ajaxHandler that called it
	 *
	 * @return		void
	 * @method
	 *
	 */
	function InsertAdjustment()
	{
		if (SubmittedForm('AddAdjustment', 'AddAdjustment'))
		{
			// Load the relating Account and ChargeType records
			DBO()->Account->Load();
			DBO()->ChargeType->Load();

			// Define all the required properties for the Charge record
			if ((!DBO()->Account->IsInvalid()) && (!DBO()->Charge->IsInvalid()) && (!DBO()->ChargeType->IsInvalid()))
			{
				DBO()->Charge->Account = DBO()->Account->Id->Value;
				DBO()->Charge->AccountGroup = DBO()->Account->AccountGroup->Value;
				$dboUser = GetAuthenticatedUserDBObject();
				DBO()->Charge->CreatedBy	= $dboUser->Id->Value;
				DBO()->Charge->CreatedOn	= GetCurrentDateForMySQL();
				DBO()->Charge->ChargeType	= DBO()->ChargeType->ChargeType->Value;
				DBO()->Charge->Description	= DBO()->ChargeType->Description->Value;
				DBO()->Charge->Nature		= DBO()->ChargeType->Nature->Value;
				
				// status is dependent on the nature of the charge
				if (DBO()->Charge->Nature->Value == "CR")
				{
					DBO()->Charge->Status	= CHARGE_WAITING;
				}
				else
				{
					DBO()->Charge->Status	= CHARGE_APPROVED;
				}

				// Add the adjustment to the charge table of the database
				if (!DBO()->Charge->Save())
				{
					echo "The charge did not save";
					die;
				}
				echo "Saved<br>\n";
				die;
			}
			die;
		}
		else
		{
			echo "SubmittedForm('AddAdjustment','AddAdjustment') returned false\n";
			die;
		}
		
	}
	
}
