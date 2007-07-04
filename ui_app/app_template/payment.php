<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// payment.php
//----------------------------------------------------------------------------//
/**
 * payment
 *
 * contains all ApplicationTemplate extended classes relating to Payment functionality
 *
 * contains all ApplicationTemplate extended classes relating to Payment functionality
 *
 * @file		payment.php
 * @language	PHP
 * @package		framework
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplatePayment
//----------------------------------------------------------------------------//
/**
 * AppTemplatePayment
 *
 * The AppTemplatePayment class
 *
 * The AppTemplatePayment class.  This incorporates all logic for all pages
 * relating to Payments
 *
 *
 * @package	ui_app
 * @class	AppTemplatePayment
 * @extends	ApplicationTemplate
 */
class AppTemplatePayment extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// Add
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Performs the logic for the Make Payment popup window
	 * 
	 * Performs the logic for the Make Payment popup window
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
			DBO()->Error->Message = "The account with account id: '". DBO()->Account->Id->value ."' could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// check if a new payment is being submitted
		if (SubmittedForm('MakePayment', 'Make Payment'))
		{
			//NOTE: when a payment gets applied to an account group, just a single record is added to the payment table.
			//It will have an AccountGroup specified, but the account field will be set to NULL.  When a payment is 
			//Applied to a single account (not its account group) then both AccountGroup and Account are specified in the Payment record.
			
			// Check if the payment is being applied to a single account, or an account group
			if (DBO()->AccountToApplyTo->IsGroup->Value)
			{
				DBO()->Payment->AccountGroup	= DBO()->AccountToApplyTo->Id->Value;
				DBO()->Payment->Account			= NULL;
			}
			else
			{
				// set the Payment->AccountGroup to the AccountGroup of the Account that the payment is being applied to.
				// Note that this will not always be DBO()->Account->AccountGroup
				DBO()->PaymentAccount->Id = DBO()->AccountToApplyTo->Id->Value;
				DBO()->PaymentAcocunt->SetTable("Account");
				DBO()->PaymentAccount->Load();
				DBO()->Payment->AccountGroup = DBO()->PaymentAccount->AccountGroup->Value;
				DBO()->Payment->Account = DBO()->AccountToApplyTo->Id->Value;
			}
			
			// Only add the payment if it is not invalid
			if (!DBO()->Payment->IsInvalid())
			{
				//DBO()->Payment->PaymentType is already set
				//DBO()->Payment->Amount is already set
				//DBO()->Payment->TXNReference is already set
				
				DBO()->Payment->PaidOn = GetCurrentDateForMySQL();
				
				// User's details
				$dboUser = GetAuthenticatedUserDBObject();
				DBO()->Payment->EnteredBy = $dboUser->Id->Value;
				
				//Payment (don't worry about this property)
				DBO()->Payment->Payment = "";
				
				//DBO()->Payment->File does not need to be set
				//DBO()->Payment->SequenceNumber does not need to be set
				
				DBO()->Payment->Balance = DBO()->Payment->Amount->Value;
				
				DBO()->Payment->Status = PAYMENT_WAITING;
				
				//Save the payment

			}
			
			
			
			
			
			if (DBO()->AccountGroup->Id->Value)
			{
				//the payment is being applied to an account group
				DBO()->Status->Message = "DBO()->AccountGroup->Id->Value is set";
			}
			else
			{
				DBO()->Status->Message = "DBO()AccountGroup->Id->Value is null";
			}
			
			
		
			
		}
		
		
		// Load DBO and DBL objects required of the page
		// Get all accounts that belong to the same AccountGroup as this one
		DBL()->AvailableAccounts->AccountGroup = DBO()->Account->AccountGroup->Value;
		DBL()->AvailableAccounts->SetTable("Account");
		DBL()->AvailableAccounts->Load();
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('payment_add');

		return TRUE;
	}
	
}
