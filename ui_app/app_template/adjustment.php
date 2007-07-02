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

		// Setup all DBO and DBL objects required for the page
		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}

		//handle saving of data on this screen (the admin fee checkbox and the payment fee radio buttons)
		//check if the form was submitted
		if (SubmittedForm('AddAdjustment', 'Add Adjustment'))
		{
			//Save the AccountDetails
			if (!DBO()->Account->IsInvalid() && !DBO()->Charge->IsInvalid() && !DBO()->ChargeType->IsInvalid)
			{
				DBO()->ChargeType->Load();
				DBO()->Charge->Account = DBO()->Account->Id->value;
				DBO()->Charge->AccountGroup = DBO()->Account->AccountGroup->value;
				
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
			}
		}
		
		
		// Check if this charge is being added to a service, instead of an account
		//TODO! Joel: check if DBO()->Serivce->Id has been set.  
		// Currently you can not add an adjustment to a service using the 
		// invoices_and_payments page, so it will not be implemented at this stage
		
		// Load all charge types that aren't archived
		DBL()->ChargeType->Archived = 0;
		DBL()->ChargeType->OrderBy("Nature DESC");
		DBL()->ChargeType->Load();
		
		// load the last 6 invoices with the most recent being first
		DBL()->Invoice->Account = DBO()->Account->Id->Value;
		DBL()->Invoice->OrderBy("CreatedOn DESC");
		DBL()->Invoice->SetLimit(6);
		DBL()->Invoice->Load();
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('adjustment_add');

		return TRUE;
	}
}
