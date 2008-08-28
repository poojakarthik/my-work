<?php

//----------------------------------------------------------------------------//
// Customer_Status_Calculator
//----------------------------------------------------------------------------//
/**
 * Customer_Status_Calculator
 *
 * Encapsulates functionality required to calculate the CustomerStatus of a customer
 *
 * Encapsulates functionality required to calculate the CustomerStatus of a customer
 *
 * @class	Customer_Status_Calculator
 */
class Customer_Status_Calculator
{
	// Returns array of account ids for all accounts that are eligible for having a customer status calculated
	public static function getEligibleAccounts($intInvoiceRun)
	{
		// All Non Archived accounts are eligible
		$selEligibleAccounts = new StatementSelect("Account", "Id", "Archived != ". ACCOUNT_STATUS_ARCHIVED);
		
		if (($outcome = $selEligibleAccounts->Execute()) === FALSE)
		{
			throw new Exception("Failed to retrieve ids of accounts that are eligible for a customer status: ". $selEligibleAccounts->Error());
		}

		$arrAccountIds = array();
		while ($arrAccount = $selEligibleAccounts->Fetch())
		{
			$arrAccountIds[] = $arrAccount['Id'];
		}
		
		return $arrAccountIds;
	}
	
	// Returns the CustomerStatus of an account for the given invoice run (returns this as an object if $bolReturnAsObject == TRUE, else returns the id of the CustomerStatus)
	// $mixAccount can be a Customer_Status_Account_Details object or the id of an account
	// If $mixAccount is a Customer_Status_Account_Details object then $intInvoiceRunId will not be considered, as $mixAccount will already define the InvoiceRun in question
	// If $mixAccount is an account Id, then $intInvoiceRunId must be specified
	// returns FALSE if the Account does not satisfy any of the Customer_Statuses
	// throws exception on error
	public static function calculateFor($mixAccount, $intInvoiceRunId=NULL, $bolReturnAsObject=FALSE)
	{
		if (!is_object($mixAccount))
		{
			// Retrieve Customer_Status_Account_Details object
			$objAccount = new Customer_Status_Account_Details($mixAccount, $intInvoiceRunId);
		}
		else
		{
			$objAccount = &$mixAccount;
		}
		
		// Get list of Customer_Status objects ordered by precedence
		$arrCustomerStatuses = Customer_Status::getAllOrderedByPrecedence();
		
		$objAssignedStatus = NULL;
		
		foreach ($arrCustomerStatuses as $objCustomerStatus)
		{
			$strTestFunction = "test". $objCustomerStatus->test;
			
			// Execute the test function
			// It is assumed it exists, because call_user_func_array returns false if the method doesn't exist
			$bolSatisfied = call_user_func_array(array(__CLASS__, $strTestFunction), array(&$objAccount));
			
			if ($bolSatisfied)
			{
				$objAssignedStatus = $objCustomerStatus;
			}
		}
		
		if ($objAssignedStatus === NULL)
		{
			// The account did not satisfy any of the CustomerStatuses
			return FALSE;
		}
		
		return ($bolReturnAsObject)? $objAssignedStatus : $objAssignedStatus->id;
	}
	
	// This will calculate the status, and then update/insert the appropriate record in the customer_status_history folder AND return the Customer_Status_Assignment object created
	// returns FALSE if the Account does not satisfy any of the Customer_Statuses
	// throws exception on error
	// If $bolGetAsObject == FALSE then returns the id of the customer_status_record
	// If $bolGetAsObject == TRUE then returns a Customer_Status_Assignment object defining the Customer Status Assignment
	public static function updateFor($mixAccount, $intInvoiceRunId=NULL, $bolGetAsObject=FALSE)
	{
		if (!is_object($mixAccount))
		{
			// Retrieve Customer_Status_Account_Details object
			$objAccount = new Customer_Status_Account_Details($mixAccount, $intInvoiceRunId);
		}
		else
		{
			$objAccount = &$mixAccount;
		}
		
		$intCustomerStatus = self::calculateFor($objAccount);
		
		if ($intCustomerStatus === FALSE)
		{
			// A CustomerStatus could not be assigned
			return FALSE;
		}
		
		// Work out if the invoice has been paid
		if ($objAccount->invoiceId === NULL)
		{
			// The account doesn't have an invoice, for this invoice run
			$bolInvoicePaid = NULL;
		}
		elseif ($objAccount->invoiceSettledOn != NULL || $objAccount->invoiceBalance <= 0.01)
		{
			// The invoice has been paid (if invoice balance <= 0.01 then it is considered to have been paid)
			$bolInvoicePaid = TRUE;
		}
		else
		{
			$bolInvoicePaid = FALSE;
		}

		return Customer_Status_Assignment::declareAssignment($objAccount->accountId, $objAccount->invoiceRunId, $intCustomerStatus, $bolInvoicePaid, $bolGetAsObject);
	}
	
	
	// A lost customer has all services lost
	private static function testLostCustomer(&$objAccount)
	{
		return $objAccount->hasAllServicesLost();
	}
	
	// Account is with TIO if Account.tio_reference_number IS NOT NULL
	private static function testAccountWithTIO(&$objAccount)
	{
		return ($objAccount->tioReferenceNumber !== NULL);
	}
	
	// Account is in Dispute if its invoice has a disputed amount
	private static function testAccountInDispute(&$objAccount)
	{
		if ($objAccount->invoiceId === NULL)
		{
			// The Account didn't have an invoice generated for this invoice run, which voids this test
			return FALSE;
		}
		return ($objAccount->invoiceDisputed > 0);
	}
	
	private static function testAccountWithAustral(&$objAccount)
	{
		return ($objAccount->creditControlStatus == CREDIT_CONTROL_STATUS_WITH_AUSTRAL);
	}
	
	private static function testAccountReadyForAustral(&$objAccount)
	{
		return ($objAccount->creditControlStatus == CREDIT_CONTROL_STATUS_SENDING_TO_AUSTRAL);
	}
	
	private static function testAccountSentFinalDemand(&$objAccount)
	{
		return $objAccount->hadAutomaticInvoiceAction(AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND);
	}
	
	private static function testAccountHasBeenAutoBarred(&$objAccount)
	{
		return $objAccount->hadAutomaticInvoiceAction(AUTOMATIC_INVOICE_ACTION_BARRING);
	}
	
	private static function testAccountSentOverdueNotice(&$objAccount)
	{
		return $objAccount->hadAutomaticInvoiceAction(AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE);
	}
	
	// An account is in a 24 month contract if the customer has at least 1 service that has an outstanding 24 month contract
	private static function testNotIn24MonthContractAndNotYetSentOverdueNotice(&$objAccount)
	{
		// Test if the account has been sent an overdue notice
		if ($objAccount->hadAutomaticInvoiceAction(AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE))
		{
			// It has
			return FALSE;
		}
		
		// Check if the account is in contract
		return !$objAccount->isInContract(24);
	}
	
	private static function testIn24MonthContractAndNotDirectDebitAndNotYetSentOverdueNotice(&$objAccount)
	{
		// Test that the account is not on DirectDebit
		if ($objAccount->billingType == BILLING_TYPE_DIRECT_DEBIT || $objAccount->billingType == BILLING_TYPE_CREDIT_CARD)
		{
			// The Account is paid by Direct Debit (either through a bank account (BILLING_TYPE_DIRECT_DEBIT) or credit card (BILLING_TYPE_CREDIT_CARD))
			return FALSE;
		}
		
		// Test if the account has been sent an overdue notice
		if ($objAccount->hadAutomaticInvoiceAction(AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE))
		{
			// It has
			return FALSE;
		}
		
		// Check if the account is in contract
		return $objAccount->isInContract(24);
	}
	
	private static function testIn24MonthContractAndDirectDebitAndNotEmailedInvoice(&$objAccount)
	{
		// Test that the account is on DirectDebit
		if ($objAccount->billingType != BILLING_TYPE_DIRECT_DEBIT && $objAccount->billingType != BILLING_TYPE_CREDIT_CARD)
		{
			// The Account is not paid by Direct Debit (either through a bank account (BILLING_TYPE_DIRECT_DEBIT) or credit card (BILLING_TYPE_CREDIT_CARD))
			return FALSE;
		}
		
		// Test that the account is not emailed the invoice
		if ($objAccount->billingMethod == BILLING_METHOD_EMAIL)
		{
			// It is emailed the invoice
			return FALSE;
		}
		
		// Check if the account is in contract
		return $objAccount->isInContract(24);
	}
	
	private static function testIn24MonthContractAndDirectDebitAndEmailedInvoice(&$objAccount)
	{
		// Test that the account is on DirectDebit
		if ($objAccount->billingType != BILLING_TYPE_DIRECT_DEBIT && $objAccount->billingType != BILLING_TYPE_CREDIT_CARD)
		{
			// The Account is not paid by Direct Debit (either through a bank account (BILLING_TYPE_DIRECT_DEBIT) or credit card (BILLING_TYPE_CREDIT_CARD))
			return FALSE;
		}
		
		// Test that the account is emailed the invoice
		if ($objAccount->billingMethod != BILLING_METHOD_EMAIL)
		{
			// It is not emailed the invoice
			return FALSE;
		}
		
		// Check if the account is in contract
		return $objAccount->isInContract(24);
	}
	
}

?>
