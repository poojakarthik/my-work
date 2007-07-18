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
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("AlertReload", "The account with account id: '". DBO()->Account->Id->value ."' could not be found");
			return TRUE;
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
				DBO()->Status->Message = "The Payment could not be saved. Invalid fields are highlighted.";
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
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		// Make sure the correct form was submitted
		if (SubmittedForm('DeleteRecord'))
		{
			$strNoteMsg = "";
			
			// Make sure the payment can be retrieved from the database
			if (!DBO()->Payment->Load())
			{
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("AlertReload", "The payment with id: ". DBO()->Payment->Id->Value ." could not be found");
				return TRUE;
			}
			
			// Make sure the payment can be reversed
			$intPaymentStatus = DBO()->Payment->Status->Value;
			if (($intPaymentStatus != PAYMENT_WAITING) && ($intPaymentStatus != PAYMENT_PAYING) && ($intPaymentStatus != PAYMENT_FINISHED))
			{
				// The payment can not be reversed
				$strErrorMsg  = "<div class='PopupMedium'>";
				$strErrorMsg .= "ERROR: The payment can not be reversed due to its status.";
				$strErrorMsg .= DBO()->Payment->Id->AsOutput();
				$strErrorMsg .= DBO()->Payment->PaidOn->AsOutput();
				$strErrorMsg .= DBO()->Payment->AccountGroup->AsOutput();
				$strErrorMsg .= DBO()->Payment->Account->AsOutput();
				$strErrorMsg .= DBO()->Payment->Amount->AsOutput();
				$strErrorMsg .= DBO()->Payment->Status->AsCallback("GetConstantDescription", Array("PaymentStatus"), RENDER_OUTPUT);
				$strErrorMsg .= "</div>\n";
				
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("AlertReload", $strErrorMsg);
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
						$strNoteMsg = "\nWarning: The operator's note could not be saved.";
					}
				}
				
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("AlertReload", nl2br("The payment was successfully reversed.{$strNoteMsg}"));
				return TRUE;
			}
			else
			{
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("AlertReload", "Reversing the payment failed");
				return TRUE;
			}
		}
		
		return TRUE;
	}
	
}
