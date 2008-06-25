<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../../' . 'flex.require.php';

class Cli_App_Automatic_Barring extends Cli
{
	const SWITCH_EFFECTIVE_DATE = "e";
	const SWITCH_TEST_RUN = "t";

	function run()
	{
		$now = time();
		$this->runDateTime = date('Y-m-d H:i:s', $now);

		$barSummary = array();
		$unbarSummary = array();
		$report = array();

		try
		{
			$this->log('Beginning database transaction.');
			$conConnection = DataAccess::getDataAccess();
			$conConnection->TransactionStart();


			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode. The database will not be updated.", TRUE);
			}
			else
			{
				$this->log("***THIS IS NOT A TEST***", TRUE);
			}

			$effectiveDate = $arrArgs[self::SWITCH_EFFECTIVE_DATE];

			$errors = 0;

			$arrGeneralErrors = array();

			$autobarInvoiceRuns = array();

			$mixResult = ListAutomaticBarringAccounts($effectiveDate);

			$barOutcome = array();
			$unbarOutcome = array();
			
			$barFailuers = array();
			$unbarFailuers = array();

			$manualUnbars = array();
			$manualBars = array();
			$autoUnbars = array();
			$autoBars = array();

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
					$invoiceRun 		= $account['InvoiceRun'];
					$customerGroupName 	= $account['CustomerGroupName'];
					$amountOverdue 		= $account['Overdue'];

					if (!array_key_exists($invoiceRun, $autobarInvoiceRuns))
					{
						$autobarInvoiceRuns[$invoiceRun] = 0;
					}
					$autobarInvoiceRuns[$invoiceRun]++;

					if (!array_key_exists($customerGroupName, $barSummary))
					{
						$barSummary[$customerGroupName] = array();
						$barFailuers[$customerGroupName] = array();
						$manualBars[$customerGroupName] = array();
						$autoBars[$customerGroupName] = array();
					}
					$barSummary[$customerGroupName][$accountId] = array('manual' => array(), 'auto' => array(), 'failed' => array());

					$this->log('Barring account ' . $accountId);
					try
					{
						$barOutcome[$accountId] = BarAccount($accountId, $accountGroupId, TRUE);
//var_dump($barOutcome[$accountId]);
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


			if (!empty($autobarInvoiceRuns))
			{
				$this->log('Marking effected Invoice Runs as automatically barred.');
				foreach ($autobarInvoiceRuns as $invoiceRun => $invoiceRunCounts)
				{
					$result = $this->changeInvoiceRunAutoActionDateTime($invoiceRun, 'automatic_bar_datetime');
					if ($result !== TRUE)
					{
						$arrGeneralErrors[] = $result;
						$this->log($result, TRUE);
					}
				}
			}
			else
			{
				$this->log('No invoice runs required updating.');
			}

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

					if (!array_key_exists($customerGroupName, $unbarSummary))
					{
						$unbarSummary[$customerGroupName] = array();
						$unbarFailuers[$customerGroupName] = array();
						$manualUnbars[$customerGroupName] = array();
						$autoUnbars[$customerGroupName] = array();
					}
					$unbarSummary[$customerGroupName][$accountId] = array('manual' => array(), 'auto' => array(), 'failed' => array());

					$this->log('Unbarring account ' . $accountId);
					try
					{
						$unbarOutcome[$accountId] = UnbarAccount($accountId, $accountGroupId, TRUE);
//var_dump($unbarOutcome[$accountId]);
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
					}				}
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



			// We now need to build a report detailing actions taken for each of the customer groups
			$this->log("Building report");
			$subject = ($errors ? '[FAILURE]' : '[SUCCESS]') . ($arrArgs[self::SWITCH_TEST_RUN] ? ' [TEST]' : '') . ' Automated barring log for run dated ' . $this->runDateTime;
			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$report[] = "***TEST RUN - NO DATABASE CHANGES WERE COMMITTED***";
				$report[] = "";
			}
			if ($errors)
			{
				$report[] = "***ERRORS WERE DETECTED WHILST RUNNING AUTOMATED [UN]BARRING***";
				$report[] = "";
			}
			else
			{
				$report[] = "The automated [un]barring completed without any errors being detected.";
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
			$report[] = "Breakdown of Barring: -";
			if (!empty($barSummary))
			{
				$report[] = "Breakdown of barring by customer group: -";
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
					else 
					{
						$report[] = "No automated barring failures were detected for customer group $custGroup.";
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
							$report[] = 'The following ' . $intCount . ' services for account ' . $intAccountId . ' were barred automatically: -';
							$report[] = implode(', ', $breakdown['auto']);
						}
						else
						{
							$report[] = 'No services were barred automatically for account ' . $intAccountId . '.';
						}
						
						$intCount = count($breakdown['manual']);
						if ($intCount)
						{
							$report[] = 'The following ' . $intCount . ' services for account ' . $intAccountId . ' should be barred manually: -';
							$report[] = implode(', ', $breakdown['manual']);
						}
						else
						{
							$report[] = 'No services require manual barring for account ' . $intAccountId . '.';
						}
						$report[] = "";
					}
				}
			}
			else
			{
				$report[] = "No accounts required barring.";
			}

			$report[] = "";
			$report[] = "";
			$report[] = "";
			$report[] = "Breakdown of Unbarring: -";
			if (!empty($unbarSummary))
			{
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
					else 
					{
						$report[] = "No automated unbarring failures were detected for customer group $custGroup.";
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
							$report[] = 'The following ' . $intCount . ' services for account ' . $intAccountId . ' were unbarred automatically: -';
							$report[] = implode(', ', $breakdown['auto']);
						}
						else
						{
							$report[] = 'No services were unbarred automatically for account ' . $intAccountId . '.';
						}
						
						$intCount = count($breakdown['manual']);
						if ($intCount)
						{
							$report[] = 'The following ' . $intCount . ' services for account ' . $intAccountId . ' should be unbarred manually: -';
							$report[] = implode(', ', $breakdown['manual']);
						}
						else
						{
							$report[] = 'No services require manual unbarring for account ' . $intAccountId . '.';
						}
						$report[] = "";
					}
				}
			}
			else
			{
				$report[] = "No accounts required unbarring.";
			}
			
			$body = implode("\r\n", $report);

			$this->log("Adding attachments...");
			$nl = "\n";
			$arrAttachments = array();
			$arrFileNames = array();
			$arrMimeTypes = array();
			foreach($manualBars as $custGroup => $list)
			{
				$custGroup = str_replace(' ', '_', $custGroup);
				if (count($list))
				{
					$arrFileNames[] = $custGroup.'_Services_To_Be_Manually_Barred_' . date('Y_m_d_H_i_s') . '.csv';
					$arrAttachments[] = "Account,FNN$nl" . implode($nl, $list);
					$arrMimeTypes[] = 'text/csv';
				}
			}
			foreach($manualUnbars as $custGroup => $list)
			{
				$custGroup = str_replace(' ', '_', $custGroup);
				if (count($list))
				{
					$arrFileNames[] = $custGroup.'_Services_To_Be_Manually_Unbarred_' . date('Y_m_d_H_i_s') . '.csv';
					$arrAttachments[] = "Account,FNN$nl" . implode($nl, $list);
					$arrMimeTypes[] = 'text/csv';
				}
			}
			foreach($autoBars as $custGroup => $list)
			{
				$custGroup = str_replace(' ', '_', $custGroup);
				if (count($list))
				{
					$arrFileNames[] = $custGroup.'_Automatically_Barred_Services_' . date('Y_m_d_H_i_s') . '.csv';
					$arrAttachments[] = "Account,FNN$nl" . implode($nl, $list);
					$arrMimeTypes[] = 'text/csv';
				}
			}
			foreach($autoUnbars as $custGroup => $list)
			{
				$custGroup = str_replace(' ', '_', $custGroup);
				if (count($list))
				{
					$arrFileNames[] = $custGroup.'_Automatically_Unbarred_Services_' . date('Y_m_d_H_i_s') . '.csv';
					$arrAttachments[] = "Account,FNN$nl" . implode($nl, $list);
					$arrMimeTypes[] = 'text/csv';
				}
			}

			$this->log("Sending report");
			$outcome = $this->sendEmail("AutomatedBarring@yellowbilling.com.au", "ybs-admin@yellowbilling.com.au, rebecca.u@yellowbilling.com.au", $subject, $body, $arrAttachments, $arrFileNames, $arrMimeTypes);

			if ($outcome === TRUE)
			{
				$this->log("Report sent");
			}
			else
			{
				$this->log("Failed to email report. ". ($outcome ? "\n$outcome" : ''), TRUE);
			}

			$this->log("Finished.");
			return $errors;
		}
		catch(Exception $exception)
		{
			$this->log('Rolling back database transaction.');
			$conConnection->TransactionRollback();
			
			$this->log('Sending error report via email.');
			$subject = '[ERROR]'. ($arrArgs[self::SWITCH_TEST_RUN] ? ' [TEST]' : '') .' Automatic [un]barring failed - Database transaction rolled back at ' . date('Y-m-d H:i:s');
			$body = array();
			$body[] = 'The automatic account [un]barring process failed. The database transaction was rolled back. The following error details are available: -'; 
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

			$outcome = $this->sendEmail("AutomatedBarring@yellowbilling.com.au", "ybs-admin@yellowbilling.com.au", $subject, $body);
			$this->showUsage('ERROR: ' . $exception->getMessage());
			return 1;
		}
	}

	private function changeInvoiceRunAutoActionDateTime($invoiceRun, $autoField)
	{
		$qryQuery = new Query();
		$invoiceRun = $qryQuery->EscapeString($invoiceRun);
		$strSQL = "UPDATE InvoiceRun SET $autoField = '$this->runDateTime' WHERE InvoiceRun = '$invoiceRun'";
		$message = TRUE;
		if (!$qryQuery->Execute($strSQL))
		{
			$message = ' Failed to update InvoiceRun ' . $invoiceRun . ' ' . $autoField . ' to ' . $this->runDateTime . '. '. $qryQuery->Error();
			$this->log($message, TRUE);
		}
		return $message;
	}

	private function sendEmail($from, $to, $subject, $message, $attachments=NULL, $attachmentNames=NULL, $attachmentMimeTypes=NULL)
	{
		$this->startErrorCatching();
		$arrHeaders = array('From' => $from, 'Subject' => $subject);

		// Send them
		$mimMime = new Mail_mime("\n");
		$mimMime->setTXTBody($message);

		if ($attachments !== NULL)
		{
			if (!is_array($attachments))
			{
				$attachments = array(0=>$attachments);
				$attachmentNames = array(0=>$attachmentNames);
				$attachmentMimeTypes = array(0=>$attachmentMimeTypes);
			}

			for ($i = 0, $l = count($attachments); $i < $l; $i++)
			{
				$attachment = $attachments[$i];
				$attachmentName = $attachmentNames[$i];
				$attachmentMimeType = $attachmentMimeTypes[$i];
				$mimMime->addAttachment($attachment, $attachmentMimeType, $attachmentName, FALSE);
			}
		}

		$strBody = $mimMime->get();
		$strHeaders = $mimMime->headers($arrHeaders);
		$emlMail =& Mail::factory('mail');
		$result = TRUE;

		if (!$emlMail->send($to, $strHeaders, $strBody))
		{
			// Sending the email failed. Retrieve any errors as they may help.
			$result = $this->getCachedError();
		}
		else
		{
			// Ignore any errors. but invoke anyway to restore the previous error handler
			$this->getCachedError();
		}
		// Sending the email succeeded
		return $result;
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
