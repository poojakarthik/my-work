<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../../' . 'flex.require.php';

class Cli_App_ApplyLateFeesToAccounts extends Cli
{
	const SWITCH_LIST_RUN = "r";
	const SWITCH_TEST_RUN = "t";

	private $_ubiDecreaseLatePayment;
	private $_strChargeType;
	private $now;
	private $runDateTime = '';

	function run()
	{
		$arrSummary = array();

		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			$this->_init_();

			$action = $arrArgs[self::SWITCH_LIST_RUN] ? AUTOMATIC_INVOICE_ACTION_LATE_FEES_LIST : AUTOMATIC_INVOICE_ACTION_LATE_FEES;

			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode. Database changes will not be committed to the database.", TRUE);
			}

			// This query is repeated by the ListLatePaymentAccounts function. Consider revising.
			$arrInvoiceRunIds = ListInvoiceRunsForAutomaticInvoiceActionAndDate($action, $this->now);
			if (!count($arrInvoiceRunIds))
			{
				$this->log("No applicable invoice runs found. Exiting normally.");
				return 0;
			}

			$this->log('Beginning database transaction.');
			$conConnection = DataAccess::getDataAccess();
			$conConnection->TransactionStart();

			$accounts = ListLatePaymentAccounts($action, $this->now);

			foreach ($accounts as $account)
			{
				// If late payments fees are not disabled
				if ($account['DisableLatePayment'] !== 1)
				{
					$accountId = intval($account['AccountId']);
					$this->log('Applying a late fee to account ' . $accountId . ', if appropriate.');
					$bolFeeApplied = $this->applyLateFeeToAccountIfAppropriate($account, $arrArgs[self::SWITCH_LIST_RUN]);
					if ($bolFeeApplied)
					{
						$this->log('A late fee was applied to account ' . $accountId . '.');
						$arrSummary[] = $accountId;
					}
					else
					{
						$this->log('No late fee was applied to account ' . $accountId . '.');
					}
				}
			}


			if ($arrArgs[self::SWITCH_LIST_RUN])
			{
				$this->log('Rolling back late fee database changes as this is only for listing purposes.');
				$conConnection->TransactionRollback();
				$this->log('Starting a new transaction for updating the invoice run events.');
				$conConnection->TransactionStart();
			}

			// Mark the invoice run events as actioned
			foreach($arrInvoiceRunIds as $intInvoiceRunId)
			{
				$this->changeInvoiceRunAutoActionDateTime($intInvoiceRunId, $action);
			}

			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log('Rolling back database changes as this is only a test.');
				$conConnection->TransactionRollback();
			}
			else
			{
				$this->log('Committing transaction.');
				$conConnection->TransactionCommit();
			}


			$attachments = array();

			// We now need to build a report detailing actions taken for each of the customer groups
			$this->log("Building report");
			$strBase = $arrArgs[self::SWITCH_LIST_RUN] ? ' Proposed ' : ' Applied ';
			$strTest = $arrArgs[self::SWITCH_TEST_RUN] ? ' [TEST]' : '';
			$subject = '[SUCCESS]' . $strTest . $strBase . ' automated late payment fees log for run dated ' . $this->runDateTime;
			$report = array();
			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$report[] = "***RUN TEST MODE - DATABASE CHANGES WERE NOT COMMITTED***";
				$report[] = "";
			}

			$report[] = "Breakdown of applied late fees: -";

			if (count($arrSummary))
			{
				$report[] = "";
				$report[] = "Late payment fees were applied to the following " . count($arrSummary) . " accounts:";
				$report[] = "";
				$report[] = implode(", ", $arrSummary);
				$attachment = array();
				$attachment[self::EMAIL_ATTACHMENT_NAME] = "Automatic_Late_Fee_Accounts.csv";
				$attachment[self::EMAIL_ATTACHMENT_MIME_TYPE] = 'application/octet-stream';
				$attachment[self::EMAIL_ATTACHMENT_CONTENT] = implode("\r\n", $arrSummary);
				$attachments[] = $attachment;
			}
			else
			{
				$report[] = "No late payment fees were applied.";
			}

			$body = implode("\r\n", $report);

			$this->log("Sending report");
			$intNotification = $arrArgs[self::SWITCH_LIST_RUN] ? EMAIL_NOTIFICATION_LATE_FEE_LIST : EMAIL_NOTIFICATION_LATE_FEE_REPORT;
			if ($this->sendEmailNotification($intNotification, NULL, NULL, $subject, NULL, $body, $attachments))
			{
				$this->log("Report sent");
			}
			else
			{
				$this->log("Failed to email report.", TRUE);
			}

			$this->log("Finished.");
			return 0;
		}
		catch(Exception $exception)
		{
			$this->log('ERROR: ' . $exception->getMessage());

			$this->log('Rolling back database transaction.');
			$conConnection->TransactionRollback();
			
			$this->log('Sending error report via email.');
			$subject = '[ERROR]'. ($arrArgs[self::SWITCH_TEST_RUN] ? ' [TEST]' : '') .' Automatic late payments failed - Database transaction rolled back at ' . date('Y-m-d H:i:s');
			$body = array();
			$body[] = 'The automatic late payments process failed. The database transaction was rolled back. The following error details are available: -'; 
			$body[] = '';
			$body[] = $exception->getMessage();
			if (count($report))
			{
				$body[] = '';
				$body[] = '';
				$body[] = 'The following (probably partial) report was generated: -';
				$body[] = implode("\r\n", $report);
			}
			else if (count($arrSummary))
			{
				$body[] = '';
				$body[] = '';
				$body[] = 'The problem may have occurred during the processing of potential target accounts. The following summary information had been cached: -';
				$body[] = '$arrSummary = ' . var_export($arrSummary, TRUE);
			}
			$body = implode("\r\n", $body);

			$intNotification = $arrArgs[self::SWITCH_LIST_RUN] ? EMAIL_NOTIFICATION_LATE_FEE_LIST : EMAIL_NOTIFICATION_LATE_FEE_REPORT;
			$this->sendEmailNotification($intNotification, NULL, NULL, $subject, NULL, $body);
			$this->showUsage('ERROR: ' . $exception->getMessage());
			return 1;
		}
	}

	private function changeInvoiceRunAutoActionDateTime($invoiceRunId, $intAutomaticInvoiceAction)
	{
		$qryQuery = new Query();
		$strSQL = "UPDATE automatic_invoice_run_event SET actioned_datetime = '$this->runDateTime' WHERE invoice_run_id = $invoiceRunId AND automatic_invoice_action_id = $intAutomaticInvoiceAction";
		$message = TRUE;
		if (!$qryQuery->Execute($strSQL))
		{
			$message = ' Failed to update automatic_invoice_run_event.actioned_datetime to ' . $this->runDateTime . ' for invoice run ' . $invoiceRunId . '  and event ' . $intAutomaticInvoiceAction . '. '. $qryQuery->Error();
			$this->log($message, TRUE);
		}
		return $message;
	}

	function _init_()
	{
		$this->now = time();
		$this->runDateTime = date('Y-m-d H:i:s', $this->now);

		$arrData = Array();
		$arrData['DisableLatePayment']	= new MySQLFunction("CASE WHEN DisableLatePayment = 0 THEN NULL ELSE DisableLatePayment + 1 END");
		$this->_ubiDecreaseLatePayment = new StatementUpdateById("Account", $arrData);

		$this->_strChargeType	= "LP".date("my");
	}


	function applyLateFeeToAccountIfAppropriate($arrAccount, $noUpdates)
	{
		// Does this account qualify?
		if ($arrAccount['DisableLatePayment'] === 1)
		{
			// Never add late payment fees when disabled
			return FALSE;
		}

		// Are we ignoring this Late Payment Fee?
		if ($arrAccount['DisableLatePayment'] !== NULL)
		{
			if (!$noUpdates)
			{
				$arrData = Array();
				$arrData['Id']					= $arrAccount['AccountId'];
				$arrData['DisableLatePayment']	= new MySQLFunction("CASE WHEN DisableLatePayment = 0 THEN NULL ELSE DisableLatePayment + 1 END");
	
				// Update the number of times we ignore, and return
				$mxdReturn = $this->_ubiDecreaseLatePayment->Execute($arrData);
				if ($mxdReturn === FALSE)
				{
					throw new Exception('Failed to update DisableLatePayment field for account ' . $arrAccount['AccountId'] . '.');
				}
			}

			// Was this their last chance to avoid a fee?
			if ($arrAccount['DisableLatePayment'] != 0)
			{
				// Yes
				return FALSE;
			}
		}

		// Late fees should be applied to this account

		if ($noUpdates)
		{
			return TRUE;
		}

		$arrPaymentTerms = GetPaymentTerms($arrAccount['CustomerGroup']);

		$lateFee = $arrPaymentTerms['late_payment_fee'];

		// Add the charge
		$arrCharge = Array();
		$arrCharge['Nature']		= 'DR';
		$arrCharge['Notes']			= "Automatically Added Charge";
		$arrCharge['Description']	= "Late Payment Fee";
		$arrCharge['ChargeType']	= $this->_strChargeType;
		$arrCharge['Amount']		= $lateFee;
		$arrCharge['Status']		= CHARGE_APPROVED;
		$arrCharge['Account'] 		= $arrAccount['AccountId'];
		$arrCharge['AccountGroup'] 	= $arrAccount['AccountGroup'];
		$arrCharge['ChargedOn']		= date("Y-m-d");
		$arrCharge['CreatedOn']		= date("Y-m-d");

		// Return TRUE or throw an exception
		if (!$GLOBALS['fwkFramework']->AddCharge($arrCharge))
		{
			throw new Exception('Failed to add charge to account ' . $arrAccount['AccountId'] . " using details:\n" . var_export($arrCharge, TRUE));
		}
		else
		{
			$this->log('Adding a note to account ' . $arrAccount['AccountId'] . '.');
			$GLOBALS['fwkFramework']->AddNote("Late Payment Fee of \${$lateFee} automatically applied to account.", Note::SYSTEM_NOTE_TYPE_ID, USER_ID, $arrAccount['AccountGroup'], $arrAccount['AccountId']);

			$strReason = "Overdue balance of $" . $arrAccount['Overdue'] . " remained unpaid";
			ChangeAccountAutomaticInvoiceAction($arrAccount['AccountId'], $arrAccount['AccountStatus'], AUTOMATIC_INVOICE_ACTION_LATE_FEES, $strReason, $this->runDateTime, $arrAccount['invoice_run_id']);

			return TRUE;
		}
	}

	function getCommandLineArguments()
	{
		return array(

			self::SWITCH_LIST_RUN => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "for listing the would-be outcome [no database changes will be committed]",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
		
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "for testing script outcome [fully functional EXCEPT emails will not be sent to clients]",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
		
		);
	}
}


?>
