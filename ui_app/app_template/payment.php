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
			// NOTE: when a payment gets applied to an account group, just a single record is added to the payment table.
			// It will have an AccountGroup specified, but the account field will be set to NULL.  When a payment is 
			// Applied to a single account (not its account group) then both AccountGroup and Account are specified in the Payment record.
			
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
				// UPDATE: Actually I think it will always be the same as DBO()->Account->AccountGroup because if it wasn't then it
				// wouldn't have been listed in the Account(s) combobox to begin with
				//DBO()->PaymentAccount->Id = DBO()->AccountToApplyTo->Id->Value;
				//DBO()->PaymentAccount->SetTable("Account");
				//DBO()->PaymentAccount->Load();
				//DBO()->Payment->AccountGroup = DBO()->PaymentAccount->AccountGroup->Value;
				//DBO()->Payment->Account = DBO()->AccountToApplyTo->Id->Value;
				
				// If the payment is to be applied to a single account (which belongs to the same account group that DBO()->Account->Id belongs to)
				// then the account group will have to be the same as DBO()->Account->Id's account group
				DBO()->Payment->AccountGroup	= DBO()->Account->AccountGroup->Value;
				DBO()->Payment->Account			= DBO()->AccountToApplyTo->Id->Value;
			}
			
			// Only add the payment if it is not invalid
			if (!DBO()->Payment->IsInvalid())
			{
				// DBO()->Payment->PaymentType is already set
				// DBO()->Payment->Amount is already set
				// DBO()->Payment->TXNReference is already set
				
				// if the payment amount has a leading dollar sign then strip it off
				DBO()->Payment->Amount = ltrim(trim(DBO()->Payment->Amount->Value), '$');
				
				DBO()->Payment->PaidOn = GetCurrentDateForMySQL();
				
				// User's details
				$dboUser = GetAuthenticatedUserDBObject();
				DBO()->Payment->EnteredBy = $dboUser->Id->Value;
				
				// Payment (don't worry about this property)
				DBO()->Payment->Payment = "";
				
				// DBO()->Payment->File does not need to be set
				// DBO()->Payment->SequenceNumber does not need to be set
				
				DBO()->Payment->Balance = DBO()->Payment->Amount->Value;
				
				DBO()->Payment->Status = PAYMENT_WAITING;
				
				// Save the payment to the payment table of the vixen database
				if (!DBO()->Payment->Save())
				{
					// The payment could not be saved
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "ERROR: The payment did not save.");
					return TRUE;
				}
				else
				{
					// The payment was successfully saved
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "The payment has been successfully added.");
					return TRUE;
				}
			}
			else
			{
				// Something was invalid 
				DBO()->Status->Message = "The Payment could not be saved. Invalid fields are shown in red";
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
	
	
	//------------------------------------------------------------------------//
	// Delete
	//------------------------------------------------------------------------//
	/**
	 * Delete()
	 *
	 * Performs Delete Payment functionality
	 * 
	 * Performs Delete Payment functionality
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Delete()
	{
		// Should probably check user authorization here
		//TODO!include user authorisation AND MAKE SURE THEY HAVE PAYMENT REVERSE PERMISSIONS
		AuthenticatedUser()->CheckAuth();

		// Check if the user has admin privileges
		$bolHasAdminPerm = AuthenticatedUser()->UserHasPerm(PRIVILEGE_ADMIN);
		
		//HACK HACK HACK!!!! remove this line when we have properly implemented users loging in
		$bolHasAdminPerm = TRUE;
		//HACK HACK HACK!!!!
		
		if (!$bolHasAdminPerm)
		{
			// The user does not have permission to delete the adjustment
			Ajax()->AddCommand("ClosePopup", "DeletePaymentPopupId");
			Ajax()->AddCommand("Alert", "ERROR: Cannot complete payment reverse operation.\nUser does not have permission to reverse payment records");
			Ajax()->AddCommand("LoadCurrentPage");
			return TRUE;
		}

		// Make sure the correct form was submitted
		if (SubmittedForm('DeleteRecord', 'Delete'))
		{
			if (!DBO()->Payment->Load())
			{
				Ajax()->AddCommand("ClosePopup", "DeletePaymentPopupId");
				Ajax()->AddCommand("Alert", "The payment with id: ". DBO()->Payment->Id->Value ." could not be found");
				Ajax()->AddCommand("LoadCurrentPage");
				return TRUE;
			}
			
			//reverse the payment
			$bolPaymentReversed = Framework()->ReversePayment(DBO()->Payment->Id->Value, AuthenticatedUser()->_arrUser['Id']);
			
			if ($bolPaymentReversed)
			{
				// Add the user's note, if one was specified
				if (!DBO()->Note->IsInvalid())
				{
					DBO()->Note->NoteType = GENERAL_NOTE_TYPE;
					DBO()->Note->AccountGroup = DBO()->Payment->AccountGroup->Value;
					DBO()->Note->Account = DBO()->Payment->Account->Value;
					DBO()->Note->Employee = AuthenticatedUser()->_arrUser['Id'];
					DBO()->Note->Datetime = GetCurrentDateAndTimeForMySQL();
					
					if (!DBO()->Note->Save())
					{
						Ajax()->AddCommand("Alert", "The note could not be saved");
					}
				}
				
				Ajax()->AddCommand("ClosePopup", "DeletePaymentPopupId");
				Ajax()->AddCommand("Alert", "The payment was successfully reversed");
				Ajax()->AddCommand("LoadCurrentPage");
				return TRUE;
			}
			else
			{
				Ajax()->AddCommand("ClosePopup", "DeletePaymentPopupId");
				Ajax()->AddCommand("Alert", "Reversing the payment failed");
				// You shouldn't have to reload the current page unless the javascript objects have been destroyed
				//Ajax()->AddCommand("LoadCurrentPage");
				return TRUE;
			}
		}
		
		return TRUE;
	}
	
}
