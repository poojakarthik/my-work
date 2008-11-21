<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../../' . 'flex.require.php';

class Cli_App_Automatic_Barring extends Cli
{
	const SWITCH_EFFECTIVE_DATE = "e";
	const SWITCH_TEST_RUN = "t";
	const SWITCH_LIST_RUN = "r";

	function run()
	{
		$now = time();
		$this->runDateTime = date('Y-m-d H:i:s', $now);

		$barSummary = array();
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

			// This query is repeated by the ListLatePaymentAccounts function. Consider revising.
			$arrInvoiceRunIds = ListInvoiceRunsForAutomaticInvoiceActionAndDate($action, $now);
			if (!count($arrInvoiceRunIds))
			{
				$this->log("No applicable invoice runs found for barring.");
			}
			else
			{
				// Regardless of the outcome, we want to send an email to let users know that barring was applied to
				// and invoice run, even if no accounts require barring.
				$sendEmail = TRUE;
			}

			$this->log('Beginning database transaction.');
			$conConnection = DataAccess::getDataAccess();
			$conConnection->TransactionStart();


			$this->log($arrArgs[self::SWITCH_LIST_RUN] ? "Listing accounts that would be barred" : "barring accounts");

			$effectiveDate = $arrArgs[self::SWITCH_EFFECTIVE_DATE];

			$errors = 0;

			$arrGeneralErrors = array();

			$mixResult = ListAutomaticBarringAccounts($effectiveDate, $action);

			$barOutcome = array();
			
			$barFailuers = array();

			$manualBars = array();
			$manualBarAccounts = array();
			$autoBars = array();
			$autoBarAccounts = array();

			if (!is_array($mixResult))
			{
				$message = "ERROR: Failed to find accounts to bar automatically.";
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
					$this->log("Barring $nrAccounts accounts.");
					// We will send an email to say which accounts/services were barred
					$sendEmail = TRUE;
				}
				else
				{
					$this->log("No accounts require barring.");
				}
				foreach($mixResult as $account)
				{
					$accountId 			= intval($account['AccountId']);
					$accountGroupId		= intval($account['AccountGroupId']);
					$customerGroupId 	= intval($account['CustomerGroupId']);
					$invoiceRunId 		= $account['invoice_run_id'];
					$customerGroupName 	= $account['CustomerGroupName'];
					$amountOverdue 		= $account['Overdue'];

					if (!array_key_exists($customerGroupName, $barSummary))
					{
						$barSummary[$customerGroupName] = array();
						$barFailuers[$customerGroupName] = array();
						$manualBars[$customerGroupName] = array();
						$manualBarAccounts[$customerGroupName] = array();
						$autoBars[$customerGroupName] = array();
						$autoBarAccounts[$customerGroupName] = array();
					}
					$barSummary[$customerGroupName][$accountId] = array('manual' => array(), 'auto' => array(), 'failed' => array());

					$this->log('Barring account ' . $accountId);
					try
					{
						$barOutcome[$accountId] = BarAccount($accountId, $accountGroupId, TRUE, $invoiceRunId);
					}
					catch (Exception $e)
					{
						$message = "ERROR: Failed to bar account $accountId for customer group $customerGroupName ($customerGroupId).\n" . $e->getMessage();
						$this->log($message, TRUE);
						$barFailuers[$accountId] = $message;
						$errors++;
						throw new Exception($message);
					}

					if (count($barOutcome[$accountId]['BARRED']))
					{
						$autoBarAccounts[$customerGroupName][] = $accountId;
						$arrFNNs = array();
						foreach($barOutcome[$accountId]['BARRED'] as $intServiceId => $arrDetails)
						{
							$arrFNNs[] = $arrDetails['FNN'] ." ($intServiceId)"; 
							$autoBars[$customerGroupName][] = $accountId . ',' . $arrDetails['FNN'];
						}
						$barSummary[$customerGroupName][$accountId]['auto'] = $arrFNNs;
						$this->log("The following services for account $accountId were automatically barred:\n" . implode("   \n", $arrFNNs));
					}
					else
					{
						$this->log("No services for account $accountId were automatically barred.");
					}

					if (count($barOutcome[$accountId]['NOT_BARRED']))
					{
						$manualBarAccounts[$customerGroupName][] = $accountId;
						$arrFNNs = array();
						foreach($barOutcome[$accountId]['NOT_BARRED'] as $intServiceId => $arrDetails)
						{
							$arrFNNs[] = $arrDetails['FNN'] ." ($intServiceId)"; 
							$manualBars[$customerGroupName][] = $accountId . ',' . $arrDetails['FNN'];
						}
						$barSummary[$customerGroupName][$accountId]['manual'] = $arrFNNs;
						$this->log("The following services for account $accountId need to be barred manually:\n" . implode("   \n", $arrFNNs));
					}
					else
					{
						$this->log("All services for account $accountId were automatically barred.");
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

					$this->log('Starting new transaction to update invoice run events.');
					$conConnection->TransactionStart();
				}

				if (!empty($arrInvoiceRunIds))
				{

					$this->log('Marking effected Invoice Runs as automatic bar listed.');
					foreach ($arrInvoiceRunIds as $invoiceRunId)
					{
						$result = $this->changeInvoiceRunAutoActionDateTime($invoiceRunId, $action);
						if ($result !== TRUE)
						{
							$arrGeneralErrors[] = $result;
							$this->log($result, TRUE);
						}
					}

					// We will send an email to say that barring was run, even if no accounts were actually barred (unlikely!)
					$sendEmail = TRUE;
				}
				else
				{
					$this->log('No invoice runs required updating.');
				}

				$this->log('Committing transaction.');
				$conConnection->TransactionCommit();
			}

			if (!$sendEmail)
			{
				$this->log("No changes were required, so not sending email. Exiting normally.");
				return 0;
			}

			$strListing = $arrArgs[self::SWITCH_LIST_RUN] ? ' listing' : '';

			// We now need to build a report detailing actions taken for each of the customer groups
			$this->log("Building report");
			$arrTmpActions = array();
			if (!empty($barSummary)) $arrTmpActions[] = 'barring';
			$strTmpActions = implode(' and ', $arrTmpActions);

			// If there were no actions performed and we are still sending an email, we must have been barring
			if (!$strTmpActions) $strTmpActions = 'barring';

			$subject = ($errors ? '[FAILURE]' : '[SUCCESS]') . ($arrArgs[self::SWITCH_TEST_RUN] ? ' [TEST]' : '') . " Automated $strTmpActions log for$strListing run dated " . $this->runDateTime;

			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$report[] = "***TEST RUN - NO DATABASE CHANGES WERE COMMITTED***";
				$report[] = "";
			}
			if ($errors)
			{
				$report[] = $arrArgs[self::SWITCH_LIST_RUN] ? "***ERRORS WERE DETECTED WHILST RUNNING AUTOMATED BARRING LISTING***" : "***ERRORS WERE DETECTED WHILST RUNNING AUTOMATED BARRING***";
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


			if (!empty($barSummary))
			{
				foreach ($barSummary as $custGroup => $custGroupBreakdown)
				{
					$report[] = "Summary of Barring$strListing for $custGroup: -";
					if (!empty($autoBarAccounts[$custGroup]))
					{
						$nrAccounts = count($autoBarAccounts[$custGroup]);
						$nrServices = count($autoBars[$custGroup]);
						$actionDesc = $arrArgs[self::SWITCH_LIST_RUN] ? ' would be' : ($nrServices == 1 ? ' was' : ' were');
						$report[] = "$nrServices service" . ($nrServices == 1 ? "" : "s") . " for $nrAccounts account" . ($nrAccounts == 1 ? "" : "s") . " " . $actionDesc . " barred automatically.";
					}
					else
					{
						$actionDesc = $arrArgs[self::SWITCH_LIST_RUN] ? 'would be' : 'were';
						$report[] = "No services or accounts $actionDesc barred automatically.";
					}
					if (!empty($manualBarAccounts[$custGroup]))
					{
						$nrAccounts = count($manualBarAccounts[$custGroup]);
						$nrServices = count($manualBars[$custGroup]);
						$actionDesc = $arrArgs[self::SWITCH_LIST_RUN] ? 'would need to be' : ($nrServices ==  1 ? 'needs to be' : 'need to be');
						$report[] = "$nrServices service" . ($nrServices == 1 ? "" : "s") . " for $nrAccounts account" . ($nrAccounts == 1 ? "" : "s") . " $actionDesc barred manually.";
					}
					else
					{
						$actionDesc = $arrArgs[self::SWITCH_LIST_RUN] ? 'would need to be' : 'need to be';
						$report[] = "No services or accounts $actionDesc barred manually.";
					}
					$report[] = "";
					$report[] = "";
				}
			}


			if (!empty($barSummary))
			{
				$report[] = "Breakdown of barring$listing by customer group: -";
				foreach ($barSummary as $custGroup => $custGroupBreakdown)
				{
					$report[] = "";
					$report[] = "";
					$report[] = "Customer Group: $custGroup";
					$report[] = "";

					if (!empty($barFailuers[$custGroup]))
					{
						$report[] = '***AUTOMATED BARRING ERRORS DETECTED***';
						$report[] = 'The following automated barring failures were detected: -';
						foreach($barFailuers['failed'] as $intAccountId => $strError)
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
						$actionDesc = $arrArgs[self::SWITCH_LIST_RUN] ? ' would be' : ($intCount == 1 ? ' was' : ' were');
						if ($intCount)
						{
							$report[] = 'The following ' . $intCount . ' service' . ($intCount == 1 ? '' : 's') . ' for account ' . $intAccountId . $actionDesc . ' barred automatically: -';
							$report[] = implode(', ', $breakdown['auto']);
						}
						else
						{
							$report[] = 'No services' . $actionDesc . ' barred automatically for account ' . $intAccountId . '.';
						}
						
						$intCount = count($breakdown['manual']);
						$actionDesc = $arrArgs[self::SWITCH_LIST_RUN] ? ' would' : '';
						if ($intCount)
						{
							$report[] = 'The following ' . $intCount . ' service' . ($intCount == 1 ? '' : 's') . ' for account ' . $intAccountId . $actionDesc . ' need to be barred manually: -';
							$report[] = implode(', ', $breakdown['manual']);
						}
						else
						{
							$report[] = 'No services' . $actionDesc . ' need to be barred manually for account ' . $intAccountId . '.';
						}
						$report[] = "";
					}
				}
			}
			else
			{
				$report[] = "No accounts required barring.";
			}

			$body = implode("\r\n", $report);

			$this->log("Adding attachments...");
			$nl = "\n";
			$attachments = array();
			$strOutcome = $arrArgs[self::SWITCH_LIST_RUN] ? '_Services_Proposed_To_Be_Manually_Barred_' : '_Services_To_Be_Manually_Barred_';
			foreach($manualBars as $custGroup => $list)
			{
				$custGroup = str_replace(' ', '_', $custGroup);
				if (count($list))
				{
					$attachment = array();
					$attachment[self::EMAIL_ATTACHMENT_NAME] = $custGroup.$strOutcome . date('Y_m_d_H_i_s') . '.csv';
					$attachment[self::EMAIL_ATTACHMENT_MIME_TYPE] = 'text/csv';
					$attachment[self::EMAIL_ATTACHMENT_CONTENT] = "Account,FNN$nl" . implode($nl, $list);
					$attachments[] = $attachment;

					fwrite($f =fopen(FILES_BASE_PATH.'/'.$custGroup.$strOutcome . date('Y_m_d_H_i_s') . '.csv', 'w+'), $attachment[self::EMAIL_ATTACHMENT_CONTENT]);
					fclose($f);
				}
			}
			$strOutcome = $arrArgs[self::SWITCH_LIST_RUN] ? '_Services_That_Would_Be_Automatically_Barred_' : '_Automatically_Barred_Services_';
			foreach($autoBars as $custGroup => $list)
			{
				$custGroup = str_replace(' ', '_', $custGroup);
				if (count($list))
				{
					$attachment = array();
					$attachment[self::EMAIL_ATTACHMENT_NAME] = $custGroup.$strOutcome . date('Y_m_d_H_i_s') . '.csv';
					$attachment[self::EMAIL_ATTACHMENT_MIME_TYPE] = 'text/csv';
					$attachment[self::EMAIL_ATTACHMENT_CONTENT] = "Account,FNN$nl" . implode($nl, $list);
					$attachments[] = $attachment;

					fwrite($f =fopen(FILES_BASE_PATH.'/'.$custGroup.$strOutcome . date('Y_m_d_H_i_s') . '.csv', 'w+'), $attachment[self::EMAIL_ATTACHMENT_CONTENT]);
					fclose($f);
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
			$subject = '[ERROR]'. ($arrArgs[self::SWITCH_TEST_RUN] ? ' [TEST]' : '') .' Automatic barring failed - Database transaction rolled back at ' . date('Y-m-d H:i:s');
			$body = array();
			$body[] = 'The automatic account barring process failed. The database transaction was rolled back. The following error details are available: -'; 
			$body[] = '';
			$body[] = $exception->getMessage();
			if (count($report))
			{
				$body[] = '';
				$body[] = '';
				$body[] = 'The following (probably partial) report was generated: -';
				$body[] = implode("\r\n", $report);
			}
			else if (count($barSummary))
			{
				$body[] = '';
				$body[] = '';
				$body[] = 'The problem may have occurred during the barring stage. The following barring information had been cached: -';
				$body[] = '$barSummary = ' . var_export($barSummary, TRUE);
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
