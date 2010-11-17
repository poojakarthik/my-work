<?php

require_once dirname(__FILE__) . '/' . '../../../' . 'flex.require.php';
require_once dirname(__FILE__) . '/' . '../../pdf/Flex_Pdf.php';

class Cli_App_ApplyDirectDebits extends Cli
{
	const SWITCH_TEST_RUN	= "t";
	
	private $_oStmtAccountDebts	= null;
	private $_sRunDateTime		= null;
	private $_sPaidOn			= null;
	
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
			$this->_oStmtAccountDebts	= 	new StatementSelect(
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
			
			// Build list of customer groups
			$aInvoiceRunIds		= ListInvoiceRunsForAutomaticInvoiceActionAndDate(AUTOMATIC_INVOICE_ACTION_DIRECT_DEBIT, time());
			$aCustomerGroups	= array();
			foreach ($aInvoiceRunIds as $iInvoiceRunId)
			{
				$oInvoiceRun	= Invoice_Run::getForId($iInvoiceRunId);				
				if (!isset($aCustomerGroups[$oInvoiceRun->customer_group_id]))
				{
					$aCustomerGroups[$oInvoiceRun->customer_group_id]	= array();
				}
				$aCustomerGroups[$oInvoiceRun->customer_group_id][]	= $oInvoiceRun->Id;
			}
			
			$this->_sRunDateTime	= date('Y-m-d H:i:s');
			$this->_sPaidOn			= date('Y-m-d');
			$iAppliedCount			= 0;
			$iErrorCount			= 0;
			$iIgnoredCount			= 0;
			$iDoubleUpsCount		= 0;
			$aAccountsApplied		= array();
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
				
				Log::getLog()->log("Latest invoice run is {$iLatestInvoiceRunId} (of ".count($aInvoiceRunIds).")");
				
				// Get the accounts to apply direct debits to
				if ($this->_oStmtAccountDebts->Execute(array('CustomerGroup' => $iCustomerGroupId)) === false)
				{
					throw new Exception("Failed to get accounts for customer group {$iCustomerGroupId}");
				}
				
				while($aRow = $this->_oStmtAccountDebts->Fetch())
				{
					// TODO: CR135 -- remove this one certain that accounts won't get doubled up
					$iAccountId	= $aRow['account_id'];
					if ($aAccountsApplied[$iAccountId])
					{
						$iDoubleUpsCount++;
						Log::getLog()->log("Already applied to account {$iAccountId}");
						continue;
					}
					$aAccountsApplied[$iAccountId]	= true;
					
					// Determine if the direct debit details are valid
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
								//Log::getLog()->log("Valid bank account: account number = {$mOriginId}");
							}
							else
							{
								//Log::getLog()->log("INVALID bank account: id = {$oAccount->DirectDebit}");
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
								//Log::getLog()->log("Valid credit card: card number = {$mOriginId}");
							}
							else
							{
								//Log::getLog()->log("INVALID credit card: id = {$oAccount->CreditCard}, expiry = '{$oCreditCard->ExpYear}-{$oCreditCard->ExpMonth}'");
							}
							break;
					}
					
					if ($bDirectDebitable)
					{
						$fAmount	= round($oAccount->getOverdueBalance(), 2);
						if ($fAmount < $aRow['direct_debit_minimum'])
						{
							// Not enough of a balance to be worthy
							//Log::getLog()->log("Overdue Balance too small {$fAmount}");
							$iIgnoredCount++;
							continue;
						}
						
						//Log::getLog()->log("Creating payment request for account...");
						
						// Create Payment
						$oPayment				= new Payment();
						$oPayment->AccountGroup	= $oAccount->AccountGroup;
						$oPayment->Account		= $oAccount->Id;
						$oPayment->EnteredBy	= Employee::SYSTEM_EMPLOYEE_ID;
						$oPayment->Amount		= $fAmount;
						$oPayment->Balance		= $fAmount;
						$oPayment->PaidOn		= $this->_sPaidOn;
						$oPayment->OriginId		= $mOriginId;
						$oPayment->OriginType	= $iPaymentType;
						$oPayment->TXNReference	= $oAccount->Id.'.'.time();
						$oPayment->Status		= PAYMENT_WAITING;
						$oPayment->PaymentType	= $iPaymentType;
						$oPayment->Payment		= '';	// TODO: CR135 -- remove this before release (after the changes have been made to the dev db which remove this field)
						$oPayment->save();
						
						// Create payment_request
						$oPaymentRequest	= 	Payment_Request::generatePending(
													$oAccount->Id, 					// Account id
													$iPaymentType,					// Payment type
													$fAmount,						// Amount
													$iLatestInvoiceRunId,			// Invoice run id
													Employee::SYSTEM_EMPLOYEE_ID,	// Employee id
													$oPayment->Id					// Payment id
												);
						
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
											$this->_sRunDateTime, 
											$iInvoiceRunId
										);
							if ($mError !== TRUE)
							{
								Log::getLog()->log("ERROR: {$mError}");
								$iErrorCount++;
							}
						}
					}
					else
					{
						$iIgnoredCount++;
					}
				}
				
				foreach ($aInvoiceRunIds as $iInvoiceRunId)
				{
					// Update the automatic_invoice_run_event for the invoice run
					if ($this->changeInvoiceRunAutoActionDateTime($iInvoiceRunId) === false)
					{
						$iErrorCount++;
					}
				}
			}
			
			Log::getLog()->log("APPLIED: {$iAppliedCount}");
			Log::getLog()->log("IGNORED: {$iIgnoredCount}");
			Log::getLog()->log("ERRORS: {$iErrorCount}");
			Log::getLog()->log("DOUBLE-UPS: {$iDoubleUpsCount}");
			
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
	
	private function changeInvoiceRunAutoActionDateTime($iInvoiceRunId)
	{
		$oQuery 		= new Query();
		$iInvoiceRunId	= $oQuery->EscapeString($iInvoiceRunId);
		$sSQL			= "	UPDATE	automatic_invoice_run_event 
							SET 	actioned_datetime = '{$this->_sDatetime}' 
							WHERE 	invoice_run_id IN (
										SELECT 	Id 
										FROM 	InvoiceRun 
										WHERE 	invoice_run_id = '{$iInvoiceRunId}'
									) 
							AND 	automatic_invoice_action_id = ".AUTOMATIC_INVOICE_ACTION_DIRECT_DEBIT;
		if (!$oQuery->Execute($sSQL))
		{
			Log::getLog()->log("ERROR: Failed to update automatic_invoice_run_event for invoice_run {$iInvoiceRunId} to {$this->_sDateTime}.".$oQuery->Error());
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
