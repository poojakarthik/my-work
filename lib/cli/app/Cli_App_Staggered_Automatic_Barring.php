<?php

// Note: Suppress errors whilst loading application as there may well be some if the 
// database model files have not yet been generated.
$_SESSION = array();
// Load Flex.php
require_once(dirname(__FILE__) . "/../../classes/Flex.php");
Flex::load();

class Cli_App_Staggered_Automatic_Barring extends Cli
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

		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();


			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode. The database will not be updated.", TRUE);
			}

			$action = $arrArgs[self::SWITCH_LIST_RUN] ? AUTOMATIC_INVOICE_ACTION_BARRING_LIST : AUTOMATIC_INVOICE_ACTION_BARRING;

			$db = Data_Source::get();

			// We need to check to see if the customer groups for the invoice runs are configured to use the staggered barring,
			// that the baring should have started, that it has not completed, that it has not been run already today,
			// that it should happen today and that it should happen on or before the current time.
			// If so, we should load the invoice run id, the customer group for it and the max number of accounts to bar per day.
			$barDate = $arrArgs[self::SWITCH_LIST_RUN] 
				? 'FROM_DAYS(TO_DAYS(NOW()) + (CASE WHEN listing_time_of_day < barring_time_of_day THEN 0 ELSE 1 END))'
				: 'DATE(NOW())';
			
			$thisDay = date('N');
			$previousDay = $thisDay - 1;
			if (!$previousDay) $previousDay = 7;
			
			$dayMatch = $arrArgs[self::SWITCH_LIST_RUN] 
				? "((listing_time_of_day < barring_time_of_day AND apc.barring_days LIKE '%$thisDay%') OR (listing_time_of_day >= barring_time_of_day AND apc.barring_days LIKE '%$previousDay%'))"
				: "apc.barring_days LIKE '%$thisDay%'";
			
			$strSQL = "
				SELECT  
					ir.Id AS \"invoice_run_id\",  
					ir.customer_group_id AS \"customer_group_id\",  
					apc.max_barrings_per_day,  
					CASE WHEN ap.commencement_date_vip <= $barDate THEN 1 ELSE 0 END AS \"bar_vip\", 
					CASE WHEN ap.commencement_date_first <= $barDate THEN 1 ELSE 0 END AS \"bar_first\", 
					CASE WHEN ap.commencement_date_normal <= $barDate THEN 1 ELSE 0 END AS \"bar_normal\" 
				FROM InvoiceRun ir, automated_invoice_run_process ap, automated_invoice_run_process_config apc 
				WHERE apc.enabled = 1 
				  AND (ap.commencement_date_normal <= $barDate OR ap.commencement_date_first <= $barDate OR ap.commencement_date_vip <= $barDate)
				  AND ap.completed_date IS NULL
				  AND (ap.last_" . ($arrArgs[self::SWITCH_LIST_RUN] ? "listing" : "processed") . "_date IS NULL OR ap.last_" . ($arrArgs[self::SWITCH_LIST_RUN] ? "listing" : "processed") . "_date < DATE(NOW()))
				  AND $dayMatch
				  AND apc." . ($arrArgs[self::SWITCH_LIST_RUN] ? "list" : "barr") . "ing_time_of_day < TIME(NOW()) 
				  AND ir.id = ap.invoice_run_id 
				  AND (ir.customer_group_id = apc.customer_group_id OR (ir.customer_group_id IS NULL AND apc.customer_group_id IS NULL))
				";

			if (PEAR::isError($result = $db->query($strSQL)))
			{
				$this->showUsage('ERROR: Failed to load applicable invoice runs details: ' . $result->getMessage() . "\n\n" . $strSQL . "\n\n");
				return 1;
			}

			$invoiceRuns = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
			
			if (!count($invoiceRuns))
			{
				$this->log("No invoice runs require barring at this time.");
				return 0;
			}

			$arrInvoiceRuns = array();
			$intAbsoluteMax = 0;
			foreach ($invoiceRuns as $invoiceRun)
			{
				$invoiceRun['barrings_today'] = 0;
				$invoiceRun['complete'] = TRUE;
				$invoiceRun['remaining'] = 0;
				$arrInvoiceRuns[$invoiceRun['invoice_run_id']] = $invoiceRun;
				$intAbsoluteMax = max($intAbsoluteMax, $invoiceRun['max_barrings_per_day']);
			}
			$arrInvoiceRunIds = array_keys($arrInvoiceRuns);

			$this->log('Beginning database transaction.');
			$db->beginTransaction();

			$conConnection = DataAccess::getDataAccess();
			$conConnection->TransactionStart();

			$this->log($arrArgs[self::SWITCH_LIST_RUN] ? "Listing accounts that would be barred" : "Listing accounts to be barred");

			$effectiveDate = $arrArgs[self::SWITCH_EFFECTIVE_DATE];

			$errors = 0;

			$arrGeneralErrors = array();

			// Barring needs to be done on a per-invoicerun basis...

			$mixResult = ListStaggeredAutomaticBarringAccounts($effectiveDate, $arrInvoiceRunIds);

			$this->log($arrArgs[self::SWITCH_LIST_RUN] ? "Listed accounts that would be barred..." : "Listed accounts to be barred...");

			$barOutcome = array();
			
			$barFailuers = array();

			$manualBars = array();
			$manualBarAccounts = array();
			$autoBars = array();
			$autoBarAccounts = array();

			$intNrAccountsAutomaticallyBarred = 0;

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
					$this->log("Barring accounts...");
				}
				else
				{
					$this->log("No accounts require barring.");
				}
				foreach($mixResult as $account)
				{
					$invoiceRunId 		= $account['invoice_run_id'];

					if ($intNrAccountsAutomaticallyBarred >= $intAbsoluteMax)
					{
						//$this->log('Already barred combined daily limit of ' . $intAbsoluteMax . ' accounts.');
						$arrInvoiceRuns[$invoiceRunId]['complete']= FALSE;
						$arrInvoiceRuns[$invoiceRunId]['remaining']++;
						continue;
					}

					$accountId 			= intval($account['AccountId']);
					$accountGroupId		= intval($account['AccountGroupId']);
					$customerGroupId 	= intval($account['CustomerGroupId']);
					$customerGroupName 	= $account['CustomerGroupName'];
					$amountOverdue 		= $account['Overdue'];
					$ranking	 		= intval($account['ranking']);

					if (($ranking == -2 /* VIP   */ && !$arrInvoiceRuns[$invoiceRunId]['bar_vip']) ||
					    ($ranking == -1 /* FIRST */ && !$arrInvoiceRuns[$invoiceRunId]['bar_first']) ||
					    ($ranking >= 0  /* NORMAL*/ && !$arrInvoiceRuns[$invoiceRunId]['bar_normal']))
					{
						// We aren't barring accounts of this status yet
						$this->log('Not started barring accounts of status ' . $ranking . ' for invoice run ' . $invoiceRunId . ' yet.');
						$arrInvoiceRuns[$invoiceRunId]['complete'] = FALSE;
						$arrInvoiceRuns[$invoiceRunId]['remaining']++;
						continue;
					}

					if ($arrInvoiceRuns[$invoiceRunId]['max_barrings_per_day'] <= $arrInvoiceRuns[$invoiceRunId]['barrings_today'] )
					{
						$this->log('Already barred daily limit of ' . $arrInvoiceRuns[$invoiceRunId]['max_barrings_per_day'] . ' for invoice run ' . $invoiceRunId . ' yet.');
						$arrInvoiceRuns[$invoiceRunId]['complete'] = FALSE;
						$arrInvoiceRuns[$invoiceRunId]['remaining']++;
						continue;
					}
					
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

						// Increment the number of accounts automatically barred (in total and for the invoice run)
						$intNrAccountsAutomaticallyBarred++;
						$arrInvoiceRuns[$invoiceRunId]['barrings_today']++;
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
				$db->rollback();
				$conConnection->TransactionRollback();
			}
			else
			{
				if ($arrArgs[self::SWITCH_LIST_RUN])
				{
					$this->log('Rolling back database changes as this is only for listing purposes.');
					$db->rollback();
					$conConnection->TransactionRollback();

					$this->log('Starting new transaction to update invoice run events.');
					$db->beginTransaction();
					$conConnection->TransactionStart();
				}

				if (!empty($arrInvoiceRunIds))
				{

					$this->log('Marking automatic_invoice_process records as updated/completed.');
					$date = date("Y-m-d");
					foreach ($arrInvoiceRuns as $invoiceRunId => $details)
					{
						if ($arrArgs[self::SWITCH_LIST_RUN])
						{
							$strSQL = "UPDATE automated_invoice_run_process SET last_listing_date = '$date'";
						}
						else if ($details['complete'])
						{
							$strSQL = "UPDATE automated_invoice_run_process SET last_processed_date = '$date', completed_date = '$date'";
							$result = $this->changeInvoiceRunAutoActionDateTime($invoiceRunId, $details['complete']);
							if ($result !== TRUE)
							{
								$arrGeneralErrors[] = $result;
								$this->log($result, TRUE);
							}
						}
						else
						{
							$strSQL = "UPDATE automated_invoice_run_process SET last_processed_date = '$date'";
						}
						$strSQL .= " WHERE invoice_run_id = " . $invoiceRunId . ";";

						if (PEAR::isError($result = $db->query($strSQL)))
						{
							throw new Exception($result->getMessage());
						}
					}

				}
				else
				{
					$this->log('No invoice runs required updating.');
				}

				$this->log('Committing transaction.');
				$db->commit();
				$conConnection->TransactionCommit();
			}

			$strListing = $arrArgs[self::SWITCH_LIST_RUN] ? ' listing' : '';





			//
			// We now need to build a report detailing actions taken for each of the customer groups
			//

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


			$report[] = "Note: After this staggered barring the following overdue accounts " . ($arrArgs[self::SWITCH_LIST_RUN] ? 'would' : '') . " remain unbarred: -";
			foreach ($arrInvoiceRuns as $invoiceRunId => $details)
			{
				
				if ($details['remaining'])
				{
					$report[] = "     Invoice run: $invoiceRunId - " . $details['remaining'] . " accounts.";
				}
				else
				{
					$report[] = "     Invoice run: $invoiceRunId -  None - Staggered barring " . ($arrArgs[self::SWITCH_LIST_RUN] ? "would be complete" : "has completed") . " for this invoice run.";
				}
			}
			$report[] = "";
			$report[] = "";

			if (!empty($barSummary))
			{
				foreach ($barSummary as $custGroup => $custGroupBreakdown)
				{
					$report[] = "Summary of Barring$strListing for $custGroup: -";
					if (!empty($autoBarAccounts[$custGroup]))
					{
						$nrAccounts = count($autoBarAccounts[$custGroup]);
						$nrServices = count($autoBars[$custGroup]);
						$actionDesc = $arrArgs[self::SWITCH_LIST_RUN] ? 'would be' : ($nrServices == 1 ? 'was' : 'were');
						$report[] = "$nrServices service" . ($nrServices == 1 ? "" : "s") . " for $nrAccounts account" . ($nrAccounts == 1 ? "" : "s") . " $actionDesc barred automatically.";
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

					$f = fopen(FILES_BASE_PATH . '/' . $attachment[self::EMAIL_ATTACHMENT_NAME], 'w');
					fwrite($f, $attachment[self::EMAIL_ATTACHMENT_CONTENT]);
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

					$f = fopen(FILES_BASE_PATH . '/' . $attachment[self::EMAIL_ATTACHMENT_NAME], 'w');
					fwrite($f, $attachment[self::EMAIL_ATTACHMENT_CONTENT]);
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
			$db->rollback();
			
			$this->log('Sending error report via email.');
			$subject = '[ERROR]'. ($arrArgs[self::SWITCH_TEST_RUN] ? ' [TEST]' : '') .' Automatic barring' . ($arrArgs[self::SWITCH_LIST_RUN] ? ' list' : '') . ' failed - Database transaction rolled back at ' . date('Y-m-d H:i:s');
			$body = array();
			$body[] = 'The staggered automatic account barring' . ($arrArgs[self::SWITCH_LIST_RUN] ? ' list' : '') . ' process failed. The database transaction was rolled back. The following error details are available: -'; 
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
		$qryQuery = Data_Source::get();
		$invoiceRunId = $qryQuery->escape($invoiceRunId);
		$strSQL = "UPDATE automatic_invoice_run_event SET actioned_datetime = '$this->runDateTime' WHERE invoice_run_id = $invoiceRunId AND automatic_invoice_action_id = $intAutomaticInvoiceAction";
		$message = TRUE;
		if (PEAR::isError($result = $qryQuery->query($strSQL)))
		{
			$message = 'Failed to update automatic_invoice_run_event.actioned_datetime to ' . $this->runDateTime . ' for invoice run ' . $invoiceRunId . '  and event ' . $intAutomaticInvoiceAction . '. '. $result->getMessage();
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
