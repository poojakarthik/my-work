<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../../' . 'flex.require.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../pdf/Flex_Pdf.php';

class Cli_App_LateNoticeRun extends Cli
{
	const SWITCH_EFFECTIVE_DATE = "e";
	const SWITCH_TEST_RUN = "t";

	private $runDateTime = '';

	function run()
	{
		$now = time();
		$this->runDateTime = date('Y-m-d H:i:s', $now);
		$pathDate = date('Ymd', $now);

		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode. Emails will not be sent to account holders.", TRUE);
			}

			// Convert effective date to unix timestamp for start of day
			$day = intval(date('d', $arrArgs[self::SWITCH_EFFECTIVE_DATE]));
			$month = intval(date('m', $arrArgs[self::SWITCH_EFFECTIVE_DATE]));
			$year = intval(date('Y', $arrArgs[self::SWITCH_EFFECTIVE_DATE]));
			$todayTimestamp = mktime(0, 0, 0, $month, $day, $year, FALSE);

			$arrNoticeTypes = array(
				LETTER_TYPE_OVERDUE,
				LETTER_TYPE_SUSPENSION,
				LETTER_TYPE_FINAL_DEMAND);

			$outputs = array();

			$errors = 0;

			$arrSummary = array();

			$arrGeneralErrors = array();

			$invcoieRunAutoFields = array();

			foreach($arrNoticeTypes as $intNoticeType)
			{
				$mixResult = GenerateLatePaymentNotices($intNoticeType, $todayTimestamp);
				$strLetterType = GetConstantDescription($intNoticeType, "LetterType");

				// Notices were generated iff the results contain an 
				if ($mixResult === FALSE)
				{
					$message = "ERROR: Generating " . $strLetterType . "s failed, unexpectedly";
					$this->log($message, 0);
					$arrGeneralErrors[] = $message;
					$errors++;
				}
				else
				{
					$outputs[$strLetterType]['success'] = $mixResult['Successful'];
					$outputs[$strLetterType]['failure'] = $mixResult['Failed'];
					if ($mixResult['Failed'])
					{
						$errors++;
					}
					$this->log("{$strLetterType}s successfully generated  : {$mixResult['Successful']}");
					$this->log("{$strLetterType}s that failed to generate : {$mixResult['Failed']}");


					$newAutoInvAction = NULL;
					$autorunField = NULL;
					// Take action appropriate for the notice type
					switch ($intNoticeType)
					{
						case LETTER_TYPE_OVERDUE:
							$newAutoInvAction = AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE;
							$autorunField = 'automatic_overdue_datetime';
							break;
						case LETTER_TYPE_SUSPENSION:
							$newAutoInvAction = AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE;
							$autorunField = 'automatic_suspension_datetime';
							break;
						case LETTER_TYPE_FINAL_DEMAND:
							$newAutoInvAction = AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND;
							$autorunField = 'automatic_final_demand_datetime';
							break;
						default:
							continue;
					}

					if (!array_key_exists($autorunField, $invcoieRunAutoFields))
					{
						$invcoieRunAutoFields[$autorunField] = array();
					}

					// We now need to email/print each of the notices that have been generated
					foreach($mixResult['Details'] as $arrDetails)
					{
						$intCustGrp = $arrDetails['Account']['CustomerGroup'];
						$strCustGroupName = $arrDetails['Account']['CustomerGroupName'];
						$intAccountId = $arrDetails['Account']['AccountId'];
						$xmlFilePath = $arrDetails['XMLFilePath'];
						$intAutoInvoiceAction = $arrDetails['Account']['automatic_invoice_action'];

						$letterType = strtolower(str_replace(' ', '_', $strLetterType));
						if (!array_key_exists($intCustGrp, $arrSummary))
						{
							$arrSummary[$strCustGroupName] = array();
							$arrSummary[$strCustGroupName][$strLetterType]['emails'] = array();
							$arrSummary[$strCustGroupName][$strLetterType]['prints'] = array();
							$arrSummary[$strCustGroupName][$strLetterType]['errors'] = array();
							$arrSummary[$strCustGroupName][$strLetterType]['output_directory'] = 
								realpath(FILES_BASE_PATH . DIRECTORY_SEPARATOR . $letterType . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $pathDate . DIRECTORY_SEPARATOR . $custGroupName);
						}

						$invoiceRun = $arrDetails['Account']['InvoiceRun'];
						if (!array_key_exists($invoiceRun, $invcoieRunAutoFields[$autorunField]))
						{
							$invcoieRunAutoFields[$autorunField][$invoiceRun] = 0;
						}
						$invcoieRunAutoFields[$autorunField][$invoiceRun]++;

						switch ($arrDetails['Account']['DeliveryMethod'])
						{
							case BILLING_METHOD_POST:
								// We need to generate the pdf for the XML and save it to the 
								// files/type/pdf/date/cust_group/account.pdf storage
								// Need to add a note of this to the email
								$this->log("Generating print PDF $strLetterType for account ". $arrDetails['Account']['AccountId']);
								$pdfContent = $this->getPDFContent($intCustGrp, time(), $intNoticeType, $xmlFilePath, 'PRINT');

								// If the PDF generation failed.
								if (!$pdfContent)
								{
									$error = $this->getCachedError();
									$message = "Failed to generate PDF $strLetterType for " . $intAccountId . "\n" . $error;
									$arrSummary[$strCustGroupName][$strLetterType]['errors'][] = $message;
									$errors++;
									$this->log($message, TRUE);
								}
								// We have a PDF, so we should store it for sending to the printers
								else
								{
									$this->log("Storing PDF $strLetterType for account ". $intAccountId);
									$custGroupName = strtolower(str_replace(' ', '_', $arrDetails['Account']['CustomerGroupName']));

									$outputDirectory = $arrSummary[$strCustGroupName][$strLetterType]['output_directory'];

									if (!file_exists($outputDirectory))
									{
										RecursiveMkdir($outputDirectory);
									}

									// Write the PDF file contents to storage
									$targetFile = $outputDirectory . DIRECTORY_SEPARATOR . $intAccountId . '.pdf';
									$file = @fopen($targetFile, 'w');
									$ok = FALSE;
									if ($file)
									{
										$ok = @fwrite($file, $pdfContent);
									}
									if ($ok === FALSE)
									{
										$message = "Failed to write PDF $strLetterType for account $intAccountId to $targetFile.";
										$this->log($message, TRUE);
										$arrSummary[$strCustGroupName][$strLetterType]['errors'][] = $message;
									}
									else
									{
										@fclose($file);

										$arrSummary[$strCustGroupName][$strLetterType]['prints'][] = $intAccountId;
	
										// We need to log the fact that we've created it, by updating the account automatic_invoice_action
										$outcome = $this->changeAccountAutomaticInvoiceAction($intAccountId, $intAutoInvoiceAction, $newAutoInvAction, "$strLetterType stored for printing in $outputDirectory");
										if ($outcome !== TRUE)
										{
											$arrSummary[$strCustGroupName][$strLetterType]['errors'][] = $outcome;
											$errors++;
										}
									}
								}

								break;

							case BILLING_METHOD_EMAIL:
								// We can safely go ahead and generate this pdf.
								$this->log("Generating email PDF $strLetterType for account ". $intAccountId . ' to ' . $arrDetails['Account']['Email']);
								$pdfContent = $this->getPDFContent($intCustGrp, time(), $intNoticeType, $xmlFilePath, 'EMAIL');

								// If the PDF generation failed.
								if (!$pdfContent)
								{
									$error = $this->getCachedError();
									$message = "Failed to generate PDF $strLetterType for " . $intAccountId . "\n" . $error;
									$arrSummary[$strCustGroupName][$strLetterType]['errors'][] = $message;
									$errors++;
									$this->log($message, TRUE);
								}
								// We have a PDF, so we should email it
								else
								{
									$this->log("Emailing $strLetterType for account ". $intAccountId . ' to ' . $arrDetails['Account']['Email']);
									if ($arrArgs[self::SWITCH_TEST_RUN])
									{
										$this->log("...NOT!!!");
									}
									$custGroupName = $arrDetails['Account']['CustomerGroupName'];
									$fileName = str_replace(' ', '_', $strLetterType) . '.pdf';
									$emailTo = $arrDetails['Account']['Email'];
									$emailFrom = $arrDetails['Account']['EmailFrom'];
									$subject = "$custGroupName $strLetterType for Account $intAccountId";

									$name = trim($arrDetails['Account']['FirstName']);

									$a = 'a';
									if (strpos('aeiou', strtolower($strLetterType[0])) !== FALSE)
									{
										$a = 'an';
									}

				 					$strContent = "Dear $name,\r\n\r\n" .
				 								  "Please find attached $a $strLetterType from $custGroupName.\r\n\r\n" .
				 								  "Regards\r\n\r\n" .
				 								  "The Team at $custGroupName";

									if (!$arrArgs[self::SWITCH_TEST_RUN])
									{
										$outcome = $this->sendEmail($emailFrom, $emailTo, $subject, $strContent, $pdfContent, $fileName, 'application/pdf');
									}
									else
									{
										$outcome = TRUE;
										//$outcome = $this->sendEmail($emailFrom, "billing-notifications@yellowbilling.com.au", $subject, $strContent, $pdfContent, $fileName, 'application/pdf');
									}

									if ($outcome === TRUE)
									{
										$arrSummary[$strCustGroupName][$strLetterType]['emails'][] = $intAccountId;

										// We need to log the fact that we've sent it, by updating the account automatic_invoice_action
										$outcome = $this->changeAccountAutomaticInvoiceAction($intAccountId, $intAutoInvoiceAction, $newAutoInvAction, "$strLetterType emailed to $name ($emailTo)");
										if ($outcome !== TRUE)
										{
											$arrSummary[$strCustGroupName][$strLetterType]['errors'][] = $outcome;
											$errors++;
										}
									}
									else
									{
										// We need to log the fact that the sending of the email failed
										$message = "Failed to email $strLetterType PDF for account " . $intAccountId . ' to ' . $emailTo . ($outcome ? "\n$outcome" : '');
										$arrSummary[$strCustGroupName][$strLetterType]['errors'][] = $message;
										$errors++;
										$this->log($message, TRUE);
									}

								}
								break;
						}
					}
				}
			}

			foreach ($invcoieRunAutoFields as $autoField => $invoiceRunCounts)
			{
				foreach ($invoiceRunCounts as $invoiceRun => $count)
				{
					$result = $this->changeInvoiceRunAutoActionDateTime($invoiceRun, $autoField);
					if ($result !== TRUE)
					{
						$arrGeneralErrors[] = $result;
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
			if (!empty($arrSummary))
			{
				$report[] = "Breakdown of late notice generation by customer group (for successfully generated XML files only): -";
				foreach ($arrSummary as $custGroup => $letterTypeSummarries)
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
