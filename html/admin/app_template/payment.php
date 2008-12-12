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
	 * This method assumes:
	 *			DBO()->Account->Id is set
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
			Ajax()->AddCommand("Alert", "The account with account id: '". DBO()->Account->Id->value ."' could not be found");
			return TRUE;
		}

		// Load DBO and DBL objects required of the page
		// Get all accounts that belong to the same AccountGroup as this one
		DBL()->AvailableAccounts->AccountGroup = DBO()->Account->AccountGroup->Value;
		DBL()->AvailableAccounts->SetTable("Account");
		DBL()->AvailableAccounts->Load();
		
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
			if (DBO()->Payment->IsInvalid())
			{
				// Something was invalid
				Ajax()->RenderHtmlTemplate("AccountPaymentAdd", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
				Ajax()->AddCommand("Alert", "ERROR: The Payment could not be saved. Invalid fields are highlighted");
				return TRUE;
			}
			
			// If the Payment Type was credit card, make sure you check that they have entered a valid credit card number
			if (DBO()->Payment->PaymentType->Value == PAYMENT_TYPE_CREDIT_CARD)
			{
				DBO()->Payment->CreditCardNum->Trim();
				
				if (!Validate("IsNotEmptyString", DBO()->Payment->CreditCardNum->Value))
				{
					// A Credit Card number has not been specified
					$strErrorMsg = "ERROR: A valid credit card number must be specified";
				}
				elseif (!CheckCC(DBO()->Payment->CreditCardNum->Value, DBO()->Payment->CreditCardType->Value))
				{
					// The credit card number is not a valid credit card number for the declared CreditCardType
					$strErrorMsg = "ERROR: The Credit Card is not a valid ". GetConstantDescription(DBO()->Payment->CreditCardType->Value, "CreditCard") . " number";
				}
				if ($strErrorMsg)
				{
					DBO()->Payment->CreditCardNum->SetToInvalid();
					Ajax()->RenderHtmlTemplate("AccountPaymentAdd", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
					Ajax()->AddCommand("Alert", $strErrorMsg);
					return TRUE;
				}
				
				// The Credit Card number is valid
				$strCreditCardNum			= str_replace(' ', '', DBO()->Payment->CreditCardNum->Value);
				$strCreditCardMasked		= substr($strCreditCardNum, 0, 6) ."...". substr($strCreditCardNum, -3);
				DBO()->Payment->OriginId	= $strCreditCardMasked;
			}
			else
			{
				// The PaymentType does not require us to define OriginId
				DBO()->Payment->OriginId	= NULL;
			}
			
			// The Payment details are valid.  Add a record to the Payment table
			// DBO()->Payment->PaymentType is already set
			// DBO()->Payment->Amount is already set
			// DBO()->Payment->TXNReference is already set
			
			// OriginType is always equal to PaymentType when a payment is manually entered
			DBO()->Payment->OriginType	= DBO()->Payment->PaymentType->Value;
			
			// If the payment amount has a leading dollar sign then strip it off
			DBO()->Payment->Amount->Trim();
			DBO()->Payment->Amount->Trim("ltrim", "$");
			
			DBO()->Payment->PaidOn = GetCurrentDateForMySQL();
			
			// User's details
			$dboUser = GetAuthenticatedUserDBObject();
			DBO()->Payment->EnteredBy = $dboUser->Id->Value;
			
			// Payment (don't worry about this property)
			DBO()->Payment->Payment = "";
			
			// DBO()->Payment->File does not need to be set
			// DBO()->Payment->SequenceNumber does not need to be set
			
			// Check if a credit card surcharge has to be added to the payment amount
			if (DBO()->Payment->PaymentType->Value == PAYMENT_TYPE_CREDIT_CARD && DBO()->Payment->ChargeSurcharge->Value)
			{
				// Add the surcharge to the payment
				DBO()->Payment->Amount = DBO()->Payment->Amount->Value * (1 + DBO()->Payment->CreditCardSurchargePercentage->Value);
			}
			
			DBO()->Payment->Balance = DBO()->Payment->Amount->Value;
			
			DBO()->Payment->Status = PAYMENT_WAITING;
			
			// Start the transaction
			TransactionStart();
			
			// Save the payment to the payment table of the vixen database
			if (!DBO()->Payment->Save())
			{
				// The payment could not be saved
				Ajax()->AddCommand("Alert", "ERROR: Saving the payment failed, unexpectedly.");
				return TRUE;
			}
			
			// The payment was successfully saved
			// If it was a credit card payment, then add an adjustment for the credit card surcharge
			if (DBO()->Payment->PaymentType->Value == PAYMENT_TYPE_CREDIT_CARD && DBO()->Payment->ChargeSurcharge->Value)
			{
				// Add the Credit Card Surcharge
				$bolResult = AddCreditCardSurcharge(DBO()->Payment->Id->Value);
				
				if ($bolResult === FALSE)
				{
					// Adding the Credit Card Surcharge failed.  Rollback the transaction
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Saving the payment failed, unexpectedly.  Failed during creation of the Credit Card Surcharge adjustment.");
					return TRUE;
				}
				
				// Build Credit Card Surcharge clause for the Success Alert
				if (DBO()->AccountToApplyTo->IsGroup->Value)
				{
					// The Payment has been applied to an entire Account Group.  Find out which Account belonging to the group has recieved the surcharge
					$selAccount	= new StatementSelect("Account", "Id, BusinessName, TradingName", "AccountGroup = <AccountGroup>", "(Archived != 1) DESC, Archived ASC, Id DESC", "1");
					$selAccount->Execute(Array("AccountGroup" => DBO()->AccountToApplyTo->Id->Value));
					$arrAccount = $selAccount->Fetch();
					
					$strAccountName = trim($arrAccount['BusinessName']);
					if ($strAccountName == "")
					{
						$strAccountName = trim($arrAccount['TradingName']);
					}
					
					$strCreditCardMsg = "<br />The Credit Card surcharge has been added as an adjustment to Account: {$arrAccount[Id]} $strAccountName";
				}
				else
				{
					// The payment was applied to a single account
					$strCreditCardMsg = "<br />The Credit Card surcharge has been added as an adjustment";
				}
			}
			
			// The payment has been successfully added.  Commit the Transaction
			TransactionCommit();
			
			//TODO! Add an appropriate System Note
			// Note: A payment can be added to an entire AccountGroup, in which case you should add the note to each Account within the group, and 
			// specify in the note that it has been applied to the entire group.
			// You should also detail the Credit Card Surcharge adjustment, if one was created, and discuss how this has affected the payment amount
			// You can retrieve the new payment amount by reloading the DBO()->Payment object as it will now have an ID
			
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("AlertReload", "The payment has been successfully added.$strCreditCardMsg");
			return TRUE;
		}
		
		// Initialise the popup
		DBO()->AccountToApplyTo->Id			= DBO()->Account->Id->Value;
		DBO()->AccountToApplyTo->IsGroup	= 0;
		DBO()->Payment->ChargeSurcharge		= 1;
		
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
			// Reversing Payments can not be done while billing is in progress
			if (IsInvoicing())
			{
				$strErrorMsg =  "Billing is in progress.  Payments cannot be reversed while this is happening.  ".
								"Please try again in a couple of hours.  If this problem persists, please ".
								"notify your system administrator";
				Ajax()->AddCommand("Alert", $strErrorMsg);
				return TRUE;
			}
			
			$strNoteMsg = "";
			
			// Make sure the payment can be retrieved from the database
			if (!DBO()->Payment->Load())
			{
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("Alert", "The payment with id: ". DBO()->Payment->Id->Value ." could not be found");
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
				$strErrorMsg .= DBO()->Payment->Status->AsCallback("GetConstantDescription", Array("payment_status"), RENDER_OUTPUT);
				$strErrorMsg .= "</div>\n";
				
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("Alert", $strErrorMsg);
				return TRUE;
			}
			
			// Check that the Invoicing process is not currently running, as payments cannot be reversed when this is happening
			if (IsInvoicing())
			{
				// Invoicing is currently running
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("Alert", "ERROR: The Invoicing process is currently running.  Payments cannot be reversed at this time.  Please try again later.");
				return TRUE;
			}
			
			// Reverse the payment
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
				Ajax()->AddCommand("Alert", "Reversing the payment failed");
				return TRUE;
			}
		}
		
		return TRUE;
	}
	
}
?>