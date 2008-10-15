<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../../' . 'flex.require.php';

class Cli_App_Automatic_Unbarring extends Cli
{
	const SWITCH_EFFECTIVE_DATE = "e";
	const SWITCH_TEST_RUN = "t";
	const SWITCH_LIST_RUN = "r";

	function run()
	{
		$now = time();
		$this->runDateTime = date('Y-m-d H:i:s', $now);

		$barSummary = array();
		$unbarSummary = array();
		$report = array();

		set_time_limit(0);

		$sendEmail = FALSE;

		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode. The database will not be updated.", TRUE);
			}

			$action = $arrArgs[self::SWITCH_LIST_RUN] ? AUTOMATIC_INVOICE_ACTION_BARRING_LIST : AUTOMATIC_INVOICE_ACTION_BARRING;

			$this->log('Beginning database transaction.');
			$conConnection = DataAccess::getDataAccess();
			$conConnection->TransactionStart();


			$this->log($arrArgs[self::SWITCH_LIST_RUN] ? "Listing accounts that would be unbarred" : "Unbarring accounts");

			$effectiveDate = $arrArgs[self::SWITCH_EFFECTIVE_DATE];

			$errors = 0;

			$arrGeneralErrors = array();

			$unbarOutcome = array();
			
			$unbarFailuers = array();

			$manualUnbars = array();
			$manualUnbarAccounts = array();
			$autoUnbars = array();
			$autoUnbarAccounts = array();

			$mixResult = ListAutomaticUnbarringAccounts($effectiveDate);

			if (!is_array($mixResult))
			{
				$message = "ERROR: Failed to find accounts to unbar automatically.";
				$this->log($message, 0);
				$arrGeneralErrors[] = $message;
				$errors++;
				throw new Exception($message);
			}
			else
			{
				$nrAccounts = count($mixResult);
				if ($nrAccounts)
				{
					$this->log("Unbarring $nrAccounts accounts.");
					// We will send an email to say which accounts/services were unbarred
					$sendEmail = TRUE;
				}
				else
				{
					$this->log("No accounts require unbarring.");
				}
				foreach($mixResult as $account)
				{
					$accountId 			= intval($account['AccountId']);
					$accountGroupId		= intval($account['AccountGroupId']);
					$customerGroupId 	= intval($account['CustomerGroupId']);
					$customerGroupName 	= $account['CustomerGroupName'];
					$amountOverdue 		= $account['Overdue'];
					$invoiceRunId 		= $account['invoice_run_id'];

					if (!array_key_exists($customerGroupName, $unbarSummary))
					{
						$unbarSummary[$customerGroupName] = array();
						$unbarFailuers[$customerGroupName] = array();
						$manualUnbars[$customerGroupName] = array();
						$autoUnbars[$customerGroupName] = array();
						$manualUnbarAccounts[$customerGroupName] = array();
						$autoUnbarAccounts[$customerGroupName] = array();
					}
					$unbarSummary[$customerGroupName][$accountId] = array('manual' => array(), 'auto' => array(), 'failed' => array());

					$this->log('Unbarring account ' . $accountId);
					try
					{
						$unbarOutcome[$accountId] = UnbarAccount($accountId, $accountGroupId, TRUE, $invoiceRunId);
					}
					catch (Exception $e)
					{
						$message = "ERROR: Failed to unbar account $accountId for customer group $customerGroupName ($customerGroupId).\n" . $e->getMessage();
						$this->log($message, TRUE);
						$unbarFailuers[$accountId] = $message;
						$errors++;
						throw new Exception($message);
					}

					if (count($unbarOutcome[$accountId]['UNBARRED']))
					{
						$autoUnbarAccounts[$customerGroupName][] = $accountId;
						$arrFNNs = array();
						foreach($unbarOutcome[$accountId]['UNBARRED'] as $intServiceId => $arrDetails)
						{
							$arrFNNs[] = $arrDetails['FNN'] ." ($intServiceId)"; 
							$autoUnbars[$customerGroupName][] = $accountId . ',' . $arrDetails['FNN'];
						}
						$unbarSummary[$customerGroupName][$accountId]['auto'] = $arrFNNs;
						$this->log("The following services for account $accountId were automatically unbarred:\n" . implode("   \n", $arrFNNs));
					}
					else
					{
						$this->log("No services for account $accountId were automatically unbarred.");
					}

					if (count($unbarOutcome[$accountId]['NOT_UNBARRED']))
					{
						$manualUnbarAccounts[$customerGroupName][] = $accountId;
						$arrFNNs = array();
						foreach($unbarOutcome[$accountId]['NOT_UNBARRED'] as $intServiceId => $arrDetails)
						{
							$arrFNNs[] = $arrDetails['FNN'] ." ($intServiceId)"; 
							$manualUnbars[$customerGroupName][] = $accountId . ',' . $arrDetails['FNN'];
						}
						$unbarSummary[$customerGroupName][$accountId]['manual'] = $arrFNNs;
						$this->log("The following services for account $accountId need to be unbarred manually:\n" . implode("   \n", $arrFNNs));
					}
					else
					{
						$this->log("All services for account $accountId were automatically unbarred.");
					}
				}
			}


			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log('Rolling back database changes as this is only a test.');
				$conConnection->TransactionRollback();
			}
			else
			{
				if ($arrArgs[self::SWITCH_LIST_RUN])
				{
					$this->log('Rolling back database changes as this is only for listing purposes.');
					$conConnection->TransactionRollback();
				}
				else
				{
					$this->log('Committing transaction.');
					$conConnection->TransactionCommit();
				}
			}

			if (!$sendEmail)
			{
				$this->log("No changes were required, so not sending email. Exiting normally.");
				return 0;
			}

			$strListing = $arrArgs[self::SWITCH_LIST_RUN] ? ' listing' : '';

			// We now need to build a report detailing actions taken for each of the customer groups
			$this->log("Building report");

			$subject = ($errors ? '[FAILURE]' : '[SUCCESS]') . ($arrArgs[self::SWITCH_TEST_RUN] ? ' [TEST]' : '') . " Automated unbarring log for$strListing run dated " . $this->runDateTime;

			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$report[] = "***TEST RUN - NO DATABASE CHANGES WERE COMMITTED***";
				$report[] = "";
			}
			if ($errors)
			{
				$report[] = $arrArgs[self::SWITCH_LIST_RUN] ? "***ERRORS WERE DETECTED WHILST RUNNING AUTOMATED UNBARRING LISTING***" : "***ERRORS WERE DETECTED WHILST RUNNING AUTOMATED UNBARRING***";
				$report[] = "";
			}

			if (!empty($arrGeneralErrors))
			{
				$report[] = "***GENERAL ERRORS***";
				$report[] = "The following general errors were encountered: -";
				$report = array_merge($report, $arrGeneralErrors);
				$report[] = "";
				$report[] = "";
			}


			if (!empty($unbarSummary))
			{
				foreach ($unbarSummary as $custGroup => $custGroupBreakdown)
				{
					$report[] = "Summary of Unbarring$strListing for $custGroup: -";
					if (!empty($autoUnbarAccounts[$custGroup]))
					{
						$nrAccounts = count($autoUnbarAccounts[$custGroup]);
						$nrServices = count($autoUnbars[$custGroup]);
						$actionDesc = $arrArgs[self::SWITCH_LIST_RUN] ? ' would be' : ($nrServices == 1 ? ' was' : ' were');
						$report[] = "$nrServices service" . ($nrServices == 1 ? "" : "s") . " for $nrAccounts account" . ($nrAccounts == 1 ? "" : "s") . " " . $actionDesc . " unbarred automatically.";
					}
					else
					{
						$actionDesc = $arrArgs[self::SWITCH_LIST_RUN] ? 'would be' : 'were';
						$report[] = "No services or accounts $actionDesc unbarred automatically.";
					}
					if (!empty($manualUnbarAccounts[$custGroup]))
					{
						$nrAccounts = count($manualUnbarAccounts[$custGroup]);
						$nrServices = count($manualUnbars[$custGroup]);
						$actionDesc = $arrArgs[self::SWITCH_LIST_RUN] ? 'would need to be' : ($nrServices ==  1 ? 'needs to be' : 'need to be');
						$report[] = "$nrServices service" . ($nrServices == 1 ? "" : "s") . " for $nrAccounts account" . ($nrAccounts == 1 ? "" : "s") . " $actionDesc unbarred manually.";
					}
					else
					{
						$actionDesc = $arrArgs[self::SWITCH_LIST_RUN] ? 'would need to be' : 'need to be';
						$report[] = "No services or accounts $actionDesc unbarred manually.";
					}
					$report[] = "";
					$report[] = "";
				}

				$report[] = "";
				$report[] = "";
				$report[] = "";

				$report[] = "Breakdown of unbarring by customer group: -";
				foreach ($unbarSummary as $custGroup => $custGroupBreakdown)
				{
					$report[] = "";
					$report[] = "";
					$report[] = "Customer Group: $custGroup";
					$report[] = "";

					if (!empty($unbarFailuers[$custGroup]))
					{
						$report[] = '***AUTOMATED UNBARRING ERRORS DETECTED***';
						$report[] = 'The following automated unbarring failures were detected: -';
						foreach($unbarFailuers['failed'] as $intAccountId => $strError)
						{
							$report[] = "***ERROR*** Account $intAccountId: $strError";
						}
					}
					$report[] = "";

					foreach ($custGroupBreakdown as $intAccountId => $breakdown)
					{
						$report[] = "";
						$report[] = "Customer Group: $custGroup; Account: $intAccountId";
						$report[] = "";

						$intCount = count($breakdown['auto']);
						if ($intCount)
						{
							$report[] = 'The following ' . $intCount . ' service' . ($intCount == 1 ? '' : 's') . ' for account ' . $intAccountId . ' ' . ($arrArgs[self::SWITCH_LIST_RUN] ? 'would be' : ($intCount == 1 ? 'was' : 'were')) . ' unbarred automatically: -';
							$report[] = implode(', ', $breakdown['auto']);
						}
						else
						{
							$report[] = 'No services ' . ($arrArgs[self::SWITCH_LIST_RUN] ? 'would be' : 'were') . ' unbarred automatically for account ' . $intAccountId . '.';
						}
						
						$intCount = count($breakdown['manual']);
						if ($intCount)
						{
							$report[] = 'The following ' . $intCount . ' service' . ($intCount == 1 ? '' : 's') . ' for account ' . $intAccountId . ($arrArgs[self::SWITCH_LIST_RUN] ? ' would' : '') . ' need to be unbarred manually: -';
							$report[] = implode(', ', $breakdown['manual']);
						}
						else
						{
							$report[] = 'No services ' . ($arrArgs[self::SWITCH_LIST_RUN] ? 'would ' : '') . 'require manually unbarring for account ' . $intAccountId . '.';
						}
						$report[] = "";
					}
				}
			}
			else
			{
				$report[] = "No accounts " . ($arrArgs[self::SWITCH_LIST_RUN] ? 'would ' : '') . "require unbarring.";
			}

			$body = implode("\r\n", $report);

			$this->log("Adding attachments...");
			$nl = "\n";
			$attachments = array();
			$strOutcome = $arrArgs[self::SWITCH_LIST_RUN] ? '_Services_Proposed_To_Be_Manually_Unbarred_' : '_Services_To_Be_Manually_Unbarred_';
			foreach($manualUnbars as $custGroup => $list)
			{
				$custGroup = str_replace(' ', '_', $custGroup);
				if (count($list))
				{
					$attachment = array();
					$attachment[self::EMAIL_ATTACHMENT_NAME] = $custGroup.$strOutcome . date('Y_m_d_H_i_s') . '.csv';
					$attachment[self::EMAIL_ATTACHMENT_MIME_TYPE] = 'text/csv';
					$attachment[self::EMAIL_ATTACHMENT_CONTENT] = "Account,FNN$nl" . implode($nl, $list);
					$attachments[] = $attachment;
				}
			}
			$strOutcome = $arrArgs[self::SWITCH_LIST_RUN] ? '_Services_That_Would_Be_Automatically_Unbarred_' : '_Automatically_Unbarred_Services_';
			foreach($autoUnbars as $custGroup => $list)
			{
				$custGroup = str_replace(' ', '_', $custGroup);
				if (count($list))
				{
					$attachment = array();
					$attachment[self::EMAIL_ATTACHMENT_NAME] = $custGroup.$strOutcome . date('Y_m_d_H_i_s') . '.csv';
					$attachment[self::EMAIL_ATTACHMENT_MIME_TYPE] = 'text/csv';
					$attachment[self::EMAIL_ATTACHMENT_CONTENT] = "Account,FNN$nl" . implode($nl, $list);
					$attachments[] = $attachment;
				}
			}

			$this->log("Sending report");
			$intNotification = $arrArgs[self::SWITCH_LIST_RUN] ? EMAIL_NOTIFICATION_AUTOMATIC_BARRING_LIST : EMAIL_NOTIFICATION_AUTOMATIC_BARRING_REPORT;
			if ($this->sendEmailNotification($intNotification, NULL, NULL, $subject, NULL, $body, $attachments))
			{
				$this->log("Report sent");
			}
			else
			{
				$this->log("Failed to email report.", TRUE);
			}

			$this->log("Finished.");
			return $errors;
		}
		catch(Exception $exception)
		{
			$this->log('Rolling back database transaction.');
			$conConnection->TransactionRollback();
			
			$this->log('Sending error report via email.');
			$subject = '[ERROR]'. ($arrArgs[self::SWITCH_TEST_RUN] ? ' [TEST]' : '') .' Automatic unbarring ' . ($arrArgs[self::SWITCH_LIST_RUN] ? 'list ' : '') . 'failed - Database transaction rolled back at ' . date('Y-m-d H:i:s');
			$body = array();
			$body[] = 'The automatic account unbarring ' . ($arrArgs[self::SWITCH_LIST_RUN] ? 'list ' : '') . 'process failed. The database transaction was rolled back. The following error details are available: -'; 
			$body[] = '';
			$body[] = $exception->getMessage();
			if (count($report))
			{
				$body[] = '';
				$body[] = '';
				$body[] = 'The following (probably partial) report was generated: -';
				$body[] = implode("\r\n", $report);
			}
			else if (count($unbarSummary))
			{
				$body[] = '';
				$body[] = '';
				$body[] = 'The problem may have occurred during the unbarring stage. The following unbarring information had been cached: -';
				$body[] = '$unbarSummary = ' . var_export($unbarSummary, TRUE);
				$body[] = '';
				$body[] = 'The loaded data at the time was as follows: -';
				$body[] = '$mixResult = ' . var_export($mixResult, TRUE);
			}
			$body = implode("\r\n", $body);

			$intNotification = $arrArgs[self::SWITCH_LIST_RUN] ? EMAIL_NOTIFICATION_AUTOMATIC_BARRING_LIST : EMAIL_NOTIFICATION_AUTOMATIC_BARRING_REPORT;
			$this->sendEmailNotification($intNotification, NULL, NULL, $subject, NULL, $body);
			$this->showUsage('ERROR: ' . $exception->getMessage());
			return 1;
		}
	}

	private function changeInvoiceRunAutoActionDateTime($invoiceRunId, $intAutomaticInvoiceAction)
	{
		$qryQuery = new Query();
		$invoiceRunId = $qryQuery->EscapeString($invoiceRunId);
		$strSQL = "UPDATE automatic_invoice_run_event SET actioned_datetime = '$this->runDateTime' WHERE invoice_run_id = $invoiceRunId AND automatic_invoice_action_id = $intAutomaticInvoiceAction";
		$message = TRUE;
		if (!$qryQuery->Execute($strSQL))
		{
			$message = ' Failed to update automatic_invoice_run_event.actioned_datetime to ' . $this->runDateTime . ' for invoice run ' . $invoiceRunId . '  and event ' . $intAutomaticInvoiceAction . '. '. $qryQuery->Error();
			$this->log($message, TRUE);
		}
		return $message;
	}

	function getCommandLineArguments()
	{
		return array(

			self::SWITCH_EFFECTIVE_DATE => array(
				self::ARG_LABEL			=> "EFFECTIVE_DATE",
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "is the effective date for barring in 'YYYY-mm-dd " .
										"format [optional, default is today]",
				self::ARG_DEFAULT		=> time(),
				self::ARG_VALIDATION	=> 'Cli::_validDate("%1$s")'
			),
		
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
