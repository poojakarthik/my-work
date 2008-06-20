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

		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode. Emails will not be sent to account holders.", TRUE);
			}

			$nowTimestamp = $arrArgs[self::SWITCH_EFFECTIVE_DATE];

			$errors = 0;

			$barSummary = array();
			$unbarSummary = array();

			$arrGeneralErrors = array();

			$autobarInvoiceRuns = array();



			$mixResult = ListAutomaticBarringAccounts($nowTimestamp);

			if (!is_array($mixResult))
			{
				$message = "ERROR: Failed to find accounts to bar automatically.";
				$this->log($message, 0);
				$arrGeneralErrors[] = $message;
				$errors++;
			}
			else
			{
				foreach($mixResult as $account)
				{
					$accountId 			= intval($account['AccountId']);
					$customerGroupId 	= intval($account['CustomerGroupId']);
					$invoiceRun 		= $account['InvoiceRun'];
					$customerGroupName 	= $account['CustomerGroupName'];
					$amountOverdue 		= $account['Overdue'];
					$bolCanAutomate 	= ($account['CanAutomate'] == 1);

					if (!array_key_exists($invoiceRun, $autobarInvoiceRuns))
					{
						$autobarInvoiceRuns[$invoiceRun] = 0;
					}
					$autobarInvoiceRuns[$invoiceRun]++;

					if (!array_key_exists($customerGroupName, $barSummary))
					{
						$barSummary[$customerGroupName] = array('manual' => array(), 'auto' => array(), 'failed' => array());
					}

					if ($bolCanAutomate)
					{
						try
						{
							BarAccount($accountId, TRUE);
							$barSummary[$customerGroupName]['auto'][] = $accountId;
						}
						catch (Exception $e)
						{
							$message = "ERROR: Failed to bar account $accountId for customer group $customerGroupName ($customerGroupId).\n" . $e->getMessage();
							$this->log($message, TRUE);
							$barSummary[$customerGroupName]['failed'][$accountId] = $message;
							$errors++;
						}
					}
					else
					{
						$barSummary[$customerGroupName]['manual'][] = $accountId;
					}
				}
			}


			foreach ($autobarInvoiceRuns as $invoiceRun => $invoiceRunCounts)
			{
				$result = $this->changeInvoiceRunAutoActionDateTime($invoiceRun, 'automatic_bar_datetime');
				if ($result !== TRUE)
				{
					$arrGeneralErrors[] = $result;
					$this->log($result, TRUE);
				}
			}




			$mixResult = ListAutomaticUnbarringAccounts($nowTimestamp);

			if (!is_array($mixResult))
			{
				$message = "ERROR: Failed to find accounts to unbar automatically.";
				$this->log($message, 0);
				$arrGeneralErrors[] = $message;
				$errors++;
			}
			else
			{
				foreach($mixResult as $account)
				{
					$accountId 			= intval($account['AccountId']);
					$customerGroupId 	= intval($account['CustomerGroupId']);
					$customerGroupName 	= $account['CustomerGroupName'];
					$amountOverdue 		= $account['Overdue'];
					$bolCanAutomate 	= ($account['CanAutomate'] == 1);

					if (!array_key_exists($customerGroupName, $unbarSummary))
					{
						$unbarSummary[$customerGroupName] = array('manual' => array(), 'auto' => array(), 'failed' => array());
					}

					if ($bolCanAutomate)
					{
						try
						{
							UnbarAccount($accountId, TRUE);
							$unbarSummary[$customerGroupName]['auto'][] = $accountId;
						}
						catch (Exception $e)
						{
							$message = "ERROR: Failed to unbar account $accountId for customer group $customerGroupName ($customerGroupId).\n" . $e->getMessage();
							$this->log($message, TRUE);
							$unbarSummary[$customerGroupName]['failed'][$accountId] = $message;
							$errors++;
						}
					}
					else
					{
						$unbarSummary[$customerGroupName]['manual'][] = $accountId;
					}
				}
			}




			// We now need to build a report detailing actions taken for each of the customer groups
			$this->log("Building report");
			$subject = ($errors ? '[FAILURE]' : '[SUCCESS]') . ($arrArgs[self::SWITCH_TEST_RUN] ? ' [TEST]' : '') . ' Automated late notice generation log for run dated ' . $this->runDateTime;
			$report = array();
			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$report[] = "***RUN TEST MODE - EMAILS WERE NOT SENT TO ACCOUNT HOLDERS***";
				$report[] = "";
			}
			if ($errors)
			{
				$report[] = "***ERRORS WERE DETECTED WHILST GENERATING LATE NOTICES***";
				$report[] = "";
			}
			else
			{
				$report[] = "The late notice generation completed without any errors being detected.";
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
			$report[] = "Breakdown of XML generation: -";
			foreach ($outputs as $letterType => $results)
			{
				$report[] = "    $letterType: " . $results['success'] . " XML files Created, " . $results['failure'] . " XML file generations Failed";
			}
			$report[] = "";
			$report[] = "";
			if (!empty($unbarSummary))
			{
				$report[] = "Breakdown of late notice generation by customer group (for successfully generated XML files only): -";
				foreach ($unbarSummary as $custGroup => $letterTypeSummarries)
				{
					$report[] = "";
					$report[] = "";
					$report[] = "Customer Group: $custGroup";

					foreach ($letterTypeSummarries as $letterType => $letterTypeSummary)
					{
						$report[] = "[Start of $letterType breakdown for Customer Group: $custGroup]";
						if (!empty($letterTypeSummary['errors']))
						{
							$report[] = "";
							$report[] = "***ERRORS ENCOUNTERED***";
							$report = array_merge($report, $letterTypeSummary['errors']);
							$report[] = "";
						}
						else
						{
							$report[] = "";
							$report[] = "No errors were encountered.";
							$report[] = "";
						}
						if (!empty($letterTypeSummary['prints']))
						{
							$report[] = "Print: " . count($letterTypeSummary['prints']) . " {$letterType}s were created for printing and stored in " . $letterTypeSummary['output_directory'] . '.';
						}
						else
						{
							$report[] = "Print: No documents were created for printing";
						}
						if (!empty($letterTypeSummary['emails']))
						{
							$report[] = "Email: " . count($letterTypeSummary['emails']) . " {$letterType}s were created and emailed.";
							$report[] = "Emails were sent for the following accounts: -";
							$report[] = implode(', ', $letterTypeSummary['emails']);
						}
						else
						{
							$report[] = "Email: No documents were emailed";
						}
						$report[] = "[End of $letterType breakdown for Customer Group: $custGroup]";
						$report[] = "";
					}

					$report[] = "[End of breakdown for Customer Group: $custGroup]";
					$report[] = "";
					$report[] = "";
				}
			}
			else
			{
				$report[] = "No automated late notices were generated.";
			}
			$body = implode("\r\n", $report);

			$this->log("Sending report");
			$outcome = $this->sendEmail("late_notice_run@yellowbilling.com.au", "ybs-admin@yellowbilling.com.au", $subject, $body);

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

	private function changeAccountAutomaticInvoiceAction($intAccount, $intFrom, $intTo, $strReason)
	{
		$error = '';

		$qryQuery = new Query();
		$strSQL = 'UPDATE Account SET last_automatic_invoice_action = ' . $intTo . ', last_automatic_invoice_action_datetime = \'' . $this->runDateTime . '\' WHERE Id = ' . $intAccount;
		if (!$outcome = $qryQuery->Execute($strSQL))
		{
			$message = ' Failed to update Account ' . $intAccount . ' last_automatic_invoice_action from ' . $intFrom . ' to ' . $intTo . '. '. $qryQuery->Error();
			$this->log($message, TRUE);
			$error .= $message;
		}

		// and creating a corresponding automatic_invoice_action_history entry.
		$qryQuery = new Query();
		$strSQL = 'INSERT INTO automatic_invoice_action_history (account, from_action, to_action, reason, change_datetime) ' .
				' VALUES (' .
				$intAccount . ', ' .
				$intFrom . ', ' .
				$intTo .', ' .
				'\'' . $qryQuery->EscapeString($strReason) . '\', ' .
				'\'' . $this->runDateTime . '\'' .
				')';
		if (!$outcome = $qryQuery->Execute($strSQL))
		{
			$message = ' Failed to create automatic_invoice_action_history entry for ' . $intAccount . ' change from ' . $intFrom . ' to ' . $intTo . '. '. $qryQuery->Error();
			$this->log($message, TRUE);
			$error .= $message;
		}
		return $error == '' ? TRUE : $error;
	}

	private function getPDFContent($custGroupId, $effectiveDate, $documentTypeId, $pathToXMLFile, $targetMedia)
	{
		$this->startErrorCatching();
		$fileContents = file_get_contents($pathToXMLFile);

		$pdfTemplate = new Flex_Pdf_Template(
						$custGroupId, 
						$effectiveDate, 
						$documentTypeId, 
						$fileContents, 
						$targetMedia, 
						TRUE);

		$pdf = $pdfTemplate->createDocument();
		$pdfTemplate->destroy();
		$pdf = $pdf->render();
		if ($this->getCachedError())
		{
			return FALSE;
		}
		return $pdf;
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
				self::ARG_DESCRIPTION	=> "is the effective date for the generation of notices in 'YYYY-mm-dd " .
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
