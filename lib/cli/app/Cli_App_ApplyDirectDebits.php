<?php

require_once dirname(__FILE__) . '/' . '../../../' . 'flex.require.php';
require_once dirname(__FILE__) . '/' . '../../pdf/Flex_Pdf.php';

class Cli_App_ApplyDirectDebits extends Cli
{
	const SWITCH_TEST_RUN	= "t";
	
	const ERROR_ACTIONING_EVENT			= 'Action invoice run event';
	const ERROR_ACCOUNT_INVOICE_ACTION	= 'Update account invoice action';
	
	const INELIGIBLE_BANK_ACCOUNT		= 'Invalid Bank Account reference';
	const INELIGIBLE_CREDIT_CARD		= 'Invalid Credit Card reference';
	const INELIGIBLE_CREDIT_CARD_EXPIRY	= 'Credit card has expired';
	const INELIGIBLE_AMOUNT				= 'Overdue balance is too small';
	
	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs	= $this->getValidatedArguments();
			$bTestRun	= (bool)$arrArgs[self::SWITCH_TEST_RUN];
			
			if ($bTestRun)
			{
				$oDataAccess	= DataAccess::getDataAccess();
				$oDataAccess->TransactionStart();
				Log::getLog()->log("Running in Test Mode, transaction started");
			}
			
			// Prepared Statement for listing direct debit eligible accounts within a customer group 
			$oStmtAccountDebts	= 	new StatementSelect(
												"	Account a
													JOIN payment_terms pt ON pt.customer_group_id = a.CustomerGroup
													JOIN billing_type bt ON bt.id = a.BillingType",
												"	a.Id AS account_id,
													pt.direct_debit_minimum,
													bt.description AS billing_type_description",
												"	a.CustomerGroup = <CustomerGroup>
 													AND a.Archived IN (".ACCOUNT_STATUS_ACTIVE.", ".ACCOUNT_STATUS_CLOSED.")
													AND pt.id IN (SELECT MAX(id) FROM payment_terms WHERE customer_group_id = a.CustomerGroup)
													AND bt.id IN (".BILLING_TYPE_CREDIT_CARD.", ".BILLING_TYPE_DIRECT_DEBIT.")",
												"	a.Id"
											);
			
			// Build list of customer groups & the invoice runs for each
			$aInvoiceRunIds		= ListInvoiceRunsForAutomaticInvoiceActionAndDate(AUTOMATIC_INVOICE_ACTION_DIRECT_DEBIT, time());
			$aCustomerGroups	= array();
			foreach ($aInvoiceRunIds as $iInvoiceRunId)
			{
				$oInvoiceRun	= Invoice_Run::getForId($iInvoiceRunId);
				Log::getLog()->log("Invoice Run to process: {$iInvoiceRunId} for customer group {$oInvoiceRun->customer_group_id}");			
				if (!isset($aCustomerGroups[$oInvoiceRun->customer_group_id]))
				{
					$aCustomerGroups[$oInvoiceRun->customer_group_id]	= array();
				}
				$aCustomerGroups[$oInvoiceRun->customer_group_id][]	= $oInvoiceRun->Id;
			}
			
			// Loop through the customer groups
			$sDatetime			= date('Y-m-d H:i:s');
			$sPaidOn			= date('Y-m-d');
			$iAppliedCount		= 0;
			$iDoubleUpsCount	= 0;
			$aAccountsApplied	= array();
			
			$aErrors	= array(
				self::ERROR_ACCOUNT_INVOICE_ACTION 	=> 0, 
				self::ERROR_ACTIONING_EVENT 		=> 0
			);
			
			$aIneligible	= array(
				self::INELIGIBLE_BANK_ACCOUNT 		=> 0, 
				self::INELIGIBLE_CREDIT_CARD 		=> 0,
				self::INELIGIBLE_CREDIT_CARD_EXPIRY	=> 0, 
				self::INELIGIBLE_AMOUNT 			=> 0
			);
			
			foreach ($aCustomerGroups as $iCustomerGroupId => $aInvoiceRunIds)
			{
				Log::getLog()->log("Customer Group {$iCustomerGroupId}");
				
				// Deterime the latest invoice run
				if (count($aInvoiceRunIds) == 1)
				{
					// Only one
					$iLatestInvoiceRunId	= $aInvoiceRunIds[0];
				}
				else
				{
					// Find the one with the latest BillingDate
					$iLatestInvoiceRunId	= null;
					$iLatestBillingDate		= 0;
					foreach ($aInvoiceRunIds as $iInvoiceRunId)
					{
						$iBillingDate	= strtotime(Invoice_Run::getForId($iInvoiceRunId)->BillingDate);
						if ($iBillingDate > $iLatestBillingDate)
						{
							$iLatestBillingDate		= $iBillingDate;
							$iLatestInvoiceRunId	= $iInvoiceRunId;
						}
					}
				}
				
				Log::getLog()->log("Latest invoice run is {$iLatestInvoiceRunId} (of ".count($aInvoiceRunIds)." invoice runs)");
				
				// Get the accounts to apply direct debits to
				if ($oStmtAccountDebts->Execute(array('CustomerGroup' => $iCustomerGroupId)) === false)
				{
					throw new Exception("Failed to get accounts for customer group {$iCustomerGroupId}");
				}
				
				while($aRow = $oStmtAccountDebts->Fetch())
				{
					// Check if the account has already been processed, potentially useless 
					// but is here just to be sure that accounts aren't charged more than once
					$iAccountId	= $aRow['account_id'];
					if ($aAccountsApplied[$iAccountId])
					{
						$iDoubleUpsCount++;
						Log::getLog()->log("Already applied to account {$iAccountId}");
						continue;
					}
					$aAccountsApplied[$iAccountId]	= true;
					
					// Determine if the direct debit details are valid & the origin id (cc or bank account number) & payment type (for the payment)
					$oAccount				= Account::getForId($aRow['account_id']);
					$oPaymentMethodDetail	= $oAccount->getPaymentMethodDetails();
					$bDirectDebitable		= false;
					$iPaymentType			= null;
					$mOriginId				= null;
					switch($oAccount->BillingType)
					{
						case BILLING_TYPE_DIRECT_DEBIT:
							$iPaymentType	= PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT;
							$oDirectDebit	= DirectDebit::getForId($oAccount->DirectDebit);
							if ($oDirectDebit)
							{
								$bDirectDebitable	= true;
								$mOriginId			= $oPaymentMethodDetail->AccountNumber;
							}
							else
							{
								// Ineligible due to invalid bank account
								$aIneligible[self::INELIGIBLE_BANK_ACCOUNT]++;
							}
							break;
						case BILLING_TYPE_CREDIT_CARD:
							$iPaymentType	= PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD;
							$oCreditCard	= Credit_Card::getForId($oAccount->CreditCard);
							$iExpiry		= strtotime("{$oCreditCard->ExpYear}-".($oCreditCard->ExpMonth + 1)."-01");
							$iNow			= time();
							if ($oCreditCard && ($iNow < $iExpiry))
							{
								$bDirectDebitable	= true;
								$sCardNumber		= Decrypt($oPaymentMethodDetail->CardNumber);
								$mOriginId			= substr($sCardNumber, 0, 6).'...'.substr($sCardNumber, -3);
							}
							else if ($iNow >= $iExpiry)
							{
								// Ineligible because credit card has expired
								$aIneligible[self::INELIGIBLE_CREDIT_CARD_EXPIRY]++;
							}
							else
							{
								// Ineligible due to invalid credit card
								$aIneligible[self::INELIGIBLE_CREDIT_CARD]++;
							}
							break;
					}
					
					if ($bDirectDebitable)
					{
						$fAmount	= round($oAccount->getOverdueBalance(), 2);
						if ($fAmount < $aRow['direct_debit_minimum'])
						{
							// Not enough of a balance to be eligible
							$aIneligible[self::INELIGIBLE_AMOUNT]++;
							continue;
						}
						
						// Create Payment (using origin id, payment type, account & amount)
						$oPayment				= new Payment();
						$oPayment->AccountGroup	= $oAccount->AccountGroup;
						$oPayment->Account		= $oAccount->Id;
						$oPayment->EnteredBy	= Employee::SYSTEM_EMPLOYEE_ID;
						$oPayment->Amount		= $fAmount;
						$oPayment->Balance		= $fAmount;
						$oPayment->PaidOn		= $sPaidOn;
						$oPayment->OriginId		= $mOriginId;
						$oPayment->OriginType	= $iPaymentType;
						$oPayment->Status		= PAYMENT_WAITING;
						$oPayment->PaymentType	= $iPaymentType;
						$oPayment->Payment		= '';	// TODO: CR135 -- remove this before release (after the changes have been made to the dev db which remove this field)
						$oPayment->save();
						
						// Create payment_request (linked to the payment & invoice run id)
						$oPaymentRequest	= 	Payment_Request::generatePending(
													$oAccount->Id, 					// Account id
													$iPaymentType,					// Payment type
													$fAmount,						// Amount
													$iLatestInvoiceRunId,			// Invoice run id
													Employee::SYSTEM_EMPLOYEE_ID,	// Employee id
													$oPayment->Id					// Payment id
												);
						
						// Update the payments transaction reference (this done separately because the transaction reference 
						// is derived from the payment request)
						$oPayment->TXNReference	= Payment_Request::generateTransactionReference($oPaymentRequest);
						$oPayment->save();
						
						Log::getLog()->log("Account: {$oAccount->Id}, Payment: {$oPayment->Id}, payment_request: {$oPaymentRequest->id}, Amount: {$fAmount}");
						
						$iAppliedCount++;
						
						// Change the accounts last_automatic_invoice_action
						foreach ($aInvoiceRunIds as $iInvoiceRunId)
						{
							$mError	= 	ChangeAccountAutomaticInvoiceAction(
											$oAccount->Id, 
											null, 
											AUTOMATIC_INVOICE_ACTION_DIRECT_DEBIT, 
											$aRow['billing_type_description']." applied for account {$oAccount->Id}", 
											$sDatetime, 
											$iInvoiceRunId
										);
							if ($mError !== TRUE)
							{
								Log::getLog()->log("ERROR: {$mError}");
								$aErrors[self::ERROR_ACCOUNT_INVOICE_ACTION]++;
							}
						}
					}
				}
				
				// Update the automatic_invoice_run_event for each invoice run linked to this customer group
				foreach ($aInvoiceRunIds as $iInvoiceRunId)
				{
					if ($this->_changeInvoiceRunAutoActionDateTime($iInvoiceRunId, $sDatetime) === false)
					{
						$aErrors[self::ERROR_ACTIONING_EVENT]++;
					}
				}
			}
			
			Log::getLog()->log("APPLIED: {$iAppliedCount}");
			Log::getLog()->log("INELIGIBLE: ".print_r($aIneligible, true));
			Log::getLog()->log("ERRORS: ".print_r($aErrors, true));
			Log::getLog()->log("DOUBLE-UPS: {$iDoubleUpsCount} (This should always be zero)");
			
			if ($bTestRun)
			{
				$oDataAccess->TransactionRollback();
				Log::getLog()->log("Transaction rolled back");
			}
			
			return 0;
		}
		catch(Exception $oException)
		{
			if ($bTestRun)
			{
				$oDataAccess->TransactionRollback();
			}
			
			$this->showUsage('Error: '.$oException->getMessage());
			return 1;
		}
	}
	
	private function _changeInvoiceRunAutoActionDateTime($iInvoiceRunId, $sDatetime)
	{
		$oQuery 		= new Query();
		$iInvoiceRunId	= $oQuery->EscapeString($iInvoiceRunId);
		$sSQL			= "	UPDATE	automatic_invoice_run_event 
							SET 	actioned_datetime = '{$sDatetime}' 
							WHERE 	invoice_run_id IN (
										SELECT 	Id 
										FROM 	InvoiceRun 
										WHERE 	invoice_run_id = '{$iInvoiceRunId}'
									) 
							AND 	automatic_invoice_action_id = ".AUTOMATIC_INVOICE_ACTION_DIRECT_DEBIT;
		if (!$oQuery->Execute($sSQL))
		{
			Log::getLog()->log("ERROR: Failed to update automatic_invoice_run_event for invoice_run {$iInvoiceRunId} to {$sDatetime}.".$oQuery->Error());
			return false;
		}
		return true;
	}

	function getCommandLineArguments()
	{
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "for testing script outcome [fully functional EXCEPT only one direct debit is applied and then all database changes are rolled back]",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			)
		);
	}
}

?>
