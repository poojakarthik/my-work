<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../../' . 'flex.require.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../pdf/Flex_Pdf.php';

class Cli_App_LateNoticeRun extends Cli
{
	const SWITCH_EFFECTIVE_DATE = "e";
	const SWITCH_TEST_RUN = "t";
	
	const	EMAIL_BILLING_NOTIFICATIONS	= 'billing-notifications@yellowbilling.com.au';

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
			
			$this->_bolTestRun	= (bool)$arrArgs[self::SWITCH_TEST_RUN];
			if ($this->_bolTestRun)
			{
				$this->log("Running in test mode. Emails will not be sent to account holders.", TRUE);
			}

			if (!file_exists(FILES_BASE_PATH))
			{
				throw new Exception('The configured FILES_BASE_PATH does not exists. Please add a valid setting to the configuration file.');
			}

			$arrNoticeTypes = array(
				DOCUMENT_TEMPLATE_TYPE_FRIENDLY_REMINDER => AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER,
				DOCUMENT_TEMPLATE_TYPE_OVERDUE_NOTICE => AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE,
				DOCUMENT_TEMPLATE_TYPE_SUSPENSION_NOTICE => AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE,
				DOCUMENT_TEMPLATE_TYPE_FINAL_DEMAND => AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND,
			);

			$outputs = array();

			$errors = 0;

			$arrSummary = array();

			$arrGeneralErrors = array();

			$invoiceRunAutoFields = array();

			$sendEmail = FALSE;

			foreach($arrNoticeTypes as $intNoticeType => $intAutomaticInvoiceAction)
			{
				// This query is repeated by the GenerateLatePaymentNotices function. Consider revising.
				$arrInvoiceRunIds = ListInvoiceRunsForAutomaticInvoiceActionAndDate($intAutomaticInvoiceAction, $arrArgs[self::SWITCH_EFFECTIVE_DATE]);
				if (!count($arrInvoiceRunIds))
				{
					$this->log("No applicable invoice runs found for action type $intAutomaticInvoiceAction.");
					continue;
				}

				$mixResult = GenerateLatePaymentNotices($intAutomaticInvoiceAction, $arrArgs[self::SWITCH_EFFECTIVE_DATE]);
				$strLetterType = GetConstantDescription($intNoticeType, "DocumentTemplateType");

				$sendEmail = TRUE;

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

					if (!array_key_exists($intAutomaticInvoiceAction, $invoiceRunAutoFields))
					{
						$invoiceRunAutoFields[$intAutomaticInvoiceAction] = array();
					}
					
					// If we're in Test Mode, get samples
					$arrSampleAccounts	= array(DELIVERY_METHOD_POST=>null, DELIVERY_METHOD_EMAIL=>null);
					if ($this->_bolTestRun)
					{
						$arrDeliveryMethodAccounts	=array(DELIVERY_METHOD_POST=>array(), DELIVERY_METHOD_EMAIL=>array());
						foreach ($mixResult['Details'] as $mixKey=>$arrDetails)
						{
							$arrDeliveryMethodAccounts[$arrDetails['Account']['DeliveryMethod']][]	= $arrDetails['Account']['AccountId'];
						}
						
						// Pick a random sample for each Delivery Method
						foreach ($arrDeliveryMethodAccounts as $intDeliveryMethod=>$arrAccounts)
						{
							$mixRandomKey							= array_rand($arrAccounts);
							$arrSampleAccounts[$intDeliveryMethod]	= $arrAccounts[$mixRandomKey];
							
							$this->log("{$arrAccounts[$mixRandomKey]} has been selected as the random sample for ".GetConstantDescription($intDeliveryMethod, 'delivery_method'));
						}
					}

					// We now need to email/print each of the notices that have been generated
					foreach($mixResult['Details'] as $arrDetails)
					{
						$intCustGrp = $arrDetails['Account']['CustomerGroup'];
						$strCustGroupName = $arrDetails['Account']['CustomerGroupName'];
						$intAccountId = $arrDetails['Account']['AccountId'];
						$xmlFilePath = $arrDetails['XMLFilePath'];
						$intAutoInvoiceAction = $arrDetails['Account']['automatic_invoice_action'];

						$custGroupName = strtolower(str_replace(' ', '_', $strCustGroupName));

						$letterType = strtolower(str_replace(' ', '_', $strLetterType));
						if (!array_key_exists($strCustGroupName, $arrSummary))
						{
							$arrSummary[$strCustGroupName] = array();
						}

						if (!array_key_exists($strLetterType, $arrSummary[$strCustGroupName]))
						{
							$arrSummary[$strCustGroupName][$strLetterType]['emails'] = array();
							$arrSummary[$strCustGroupName][$strLetterType]['prints'] = array();
							$arrSummary[$strCustGroupName][$strLetterType]['errors'] = array();
							$arrSummary[$strCustGroupName][$strLetterType]['output_directory'] = realpath(FILES_BASE_PATH) . DIRECTORY_SEPARATOR . $letterType . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $pathDate . DIRECTORY_SEPARATOR . $custGroupName;
						}

						$invoiceRunId = $arrDetails['Account']['invoice_run_id'];
						if (!array_key_exists($invoiceRunId, $invoiceRunAutoFields[$intAutomaticInvoiceAction]))
						{
							$invoiceRunAutoFields[$intAutomaticInvoiceAction][$invoiceRunId] = 0;
						}
						$invoiceRunAutoFields[$intAutomaticInvoiceAction][$invoiceRunId]++;

						switch ($arrDetails['Account']['DeliveryMethod'])
						{
							case DELIVERY_METHOD_POST:
								// We need to generate the pdf for the XML and save it to the 
								// files/type/pdf/date/cust_group/account.pdf storage
								// Need to add a note of this to the email
								$this->log("Generating print PDF $strLetterType for account ". $arrDetails['Account']['AccountId']);
								$pdfContent = $this->getPDFContent($intCustGrp, $arrArgs[self::SWITCH_EFFECTIVE_DATE], $intNoticeType, $xmlFilePath, 'PRINT');

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

									$outputDirectory = $arrSummary[$strCustGroupName][$strLetterType]['output_directory'];

									if (!file_exists($outputDirectory))
									{
										$outputDirectories = explode('/', str_replace('\\', '/', $outputDirectory));
										$directory = '';
										foreach($outputDirectories as $subDirectory)
										{
											// If root directory on linux/unix
											if (!$subDirectory) 
											{
												continue;
											}
											$xdirectory = $directory . DIRECTORY_SEPARATOR . $subDirectory;
											if (!file_exists($xdirectory))
											{
												$ok = @mkdir($xdirectory);
												if (!$ok)
												{
													$this->log("Failed to create directory for PDF output: $xdirectory", TRUE);
												}
											}
											$directory = $xdirectory . DIRECTORY_SEPARATOR;
										}
										$outputDirectory = realpath($directory) . DIRECTORY_SEPARATOR;
									}
									else
									{
										$outputDirectory = realpath($outputDirectory) . DIRECTORY_SEPARATOR;
									}
									$arrSummary[$strCustGroupName][$strLetterType]['output_directory'] = $outputDirectory;

									// Write the PDF file contents to storage
									$targetFile = $outputDirectory . $intAccountId . '.pdf';

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
										$arrSummary[$strCustGroupName][$strLetterType]['pdfs'][] = $targetFile;
	
										// We need to log the fact that we've created it, by updating the account automatic_invoice_action
										if (!$arrArgs[self::SWITCH_TEST_RUN])
										{
											$outcome = $this->changeAccountAutomaticInvoiceAction($intAccountId, $intAutoInvoiceAction, $intAutomaticInvoiceAction, "$strLetterType stored for printing in $outputDirectory", $invoiceRunId);
											if ($outcome !== TRUE)
											{
												$arrSummary[$strCustGroupName][$strLetterType]['errors'][] = $outcome;
												$errors++;
											}
										}
									}
									
									// This is the sample Post Notice -- email
									if ($this->_bolTestRun && $arrSampleAccounts[DELIVERY_METHOD_POST] === $intAccountId)
									{
										$subject = "[SAMPLE:POST] $strCustGroupName $strLetterType for Account $intAccountId";
										
										$to = self::EMAIL_BILLING_NOTIFICATIONS;

					 					$strContent = "Please find attached a SAMPLE POST $strCustGroupName $strLetterType for Account $intAccountId.";

										$attachments = array();
										$attachment = array();
										$attachment[self::EMAIL_ATTACHMENT_NAME] = "{$intAccountId}.pdf";
										$attachment[self::EMAIL_ATTACHMENT_MIME_TYPE] = 'application/pdf';
										$attachment[self::EMAIL_ATTACHMENT_CONTENT] = $pdfContent;
										$attachments[] = $attachment;
	
										if (Email_Notification::sendEmailNotification(EMAIL_NOTIFICATION_LATE_NOTICE, $intCustGrp, self::EMAIL_BILLING_NOTIFICATIONS, $subject, NULL, $strContent, $attachments, TRUE))
										{
											$this->log("Sample POST {$strLetterType} delivered to '{".self::EMAIL_BILLING_NOTIFICATIONS."}'");
										}
										else
										{
											$this->log("ERROR: Unable to deliver Sample POST {$strLetterType} to '{".self::EMAIL_BILLING_NOTIFICATIONS."}'");
										}
									}
								}

								break;

							case DELIVERY_METHOD_EMAIL:
								// We can safely go ahead and generate this pdf.
								$this->log("Generating email PDF $strLetterType for account ". $intAccountId . ' to ' . $arrDetails['Account']['Email']);
								$pdfContent = $this->getPDFContent($intCustGrp, $arrArgs[self::SWITCH_EFFECTIVE_DATE], $intNoticeType, $xmlFilePath, 'EMAIL');

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
									$fileName = str_replace(' ', '_', $strLetterType) . '.pdf';
									$emailTo = $arrDetails['Account']['Email'];
									$emailFrom = $arrDetails['Account']['EmailFrom'];
									$subject = "$strCustGroupName $strLetterType for Account $intAccountId";

									$name = trim($arrDetails['Account']['FirstName']);

									$a = 'a';
									if (strpos('aeiou', strtolower($strLetterType[0])) !== FALSE)
									{
										$a = 'an';
									}

				 					$strContent = "Dear $name,\r\n\r\n" .
				 								  "Please find attached $a $strLetterType from $strCustGroupName.\r\n\r\n" .
				 								  "Regards\r\n\r\n" .
				 								  "The Team at $strCustGroupName";

									$to = $arrArgs[self::SWITCH_TEST_RUN] ? self::EMAIL_BILLING_NOTIFICATIONS : $emailTo;

									$attachments = array();
									$attachment = array();
									$attachment[self::EMAIL_ATTACHMENT_NAME] = $fileName;
									$attachment[self::EMAIL_ATTACHMENT_MIME_TYPE] = 'application/pdf';
									$attachment[self::EMAIL_ATTACHMENT_CONTENT] = $pdfContent;
									$attachments[] = $attachment;

									if (Email_Notification::sendEmailNotification(EMAIL_NOTIFICATION_LATE_NOTICE, $intCustGrp, $to, $subject, NULL, $strContent, $attachments, TRUE))
									{
										$arrSummary[$strCustGroupName][$strLetterType]['emails'][] = $intAccountId;

										// We need to log the fact that we've sent it, by updating the account automatic_invoice_action
										if (!$arrArgs[self::SWITCH_TEST_RUN])
										{
											$outcome = $this->changeAccountAutomaticInvoiceAction($intAccountId, $intAutoInvoiceAction, $intAutomaticInvoiceAction, "$strLetterType emailed to $name ($emailTo)", $invoiceRunId);
											if ($outcome !== TRUE)
											{
												$arrSummary[$strCustGroupName][$strLetterType]['errors'][] = $outcome;
												$errors++;
											}
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
									
									// This is the sample Email Notice -- email
									if ($this->_bolTestRun && $arrSampleAccounts[DELIVERY_METHOD_EMAIL] === $intAccountId)
									{
										$subject = "[SAMPLE:EMAIL]".$subject;
										
										$to = self::EMAIL_BILLING_NOTIFICATIONS;

					 					$strContent = "[SAMPLE:EMAIL]\r\n\r\n".$strContent."\r\n\r\n[SAMPLE:EMAIL]";

										$attachments = array();
										$attachment = array();
										$attachment[self::EMAIL_ATTACHMENT_NAME] = "{$intAccountId}.pdf";
										$attachment[self::EMAIL_ATTACHMENT_MIME_TYPE] = 'application/pdf';
										$attachment[self::EMAIL_ATTACHMENT_CONTENT] = $pdfContent;
										$attachments[] = $attachment;
	
										if (Email_Notification::sendEmailNotification(EMAIL_NOTIFICATION_LATE_NOTICE, $intCustGrp, self::EMAIL_BILLING_NOTIFICATIONS, $subject, NULL, $strContent, $attachments, TRUE))
										{
											$this->log("Sample EMAIL {$strLetterType} delivered to '{".self::EMAIL_BILLING_NOTIFICATIONS."}'");
										}
										else
										{
											$this->log("ERROR: Unable to deliver Sample {$strLetterType} Notice to '{".self::EMAIL_BILLING_NOTIFICATIONS."}'");
										}
									}

								}
								break;
						}
					}
				}
			}
			
			if (!$arrArgs[self::SWITCH_TEST_RUN])
			{
				foreach ($invoiceRunAutoFields as $intAutomaticInvoiceAction => $invoiceRunCounts)
				{
					foreach ($invoiceRunCounts as $invoiceRunId => $count)
					{
						$result = $this->changeInvoiceRunAutoActionDateTime($invoiceRunId, $intAutomaticInvoiceAction);
						if ($result !== TRUE)
						{
							$arrGeneralErrors[] = $result;
						}
					}
				}
			}

			foreach($arrSummary as $strCustGroupName => $letterTypes)
			{
				foreach($letterTypes as $strLetterType => $details)
				{
					if (count($details['pdfs']))
					{
						$letterType = strtolower(str_replace(' ', '_', $strLetterType));
						$custGroup = strtolower(str_replace(' ', '_', $strCustGroupName));
						$strTarPath = FILES_BASE_PATH . DIRECTORY_SEPARATOR . date('YmdHis', $now) . '.' .$letterType . '.' . $custGroup . '.tar';
						$this->log("Moving generated $strLetterType PDFs for customer group $strCustGroupName to $strTarPath");

						require_once "Archive/Tar.php";
						$objArchive = new Archive_Tar($strTarPath, NULL);
						$pathToRemove = $arrSummary[$strCustGroupName][$strLetterType]['output_directory'];
						$objArchive->addModify($details['pdfs'], NULL, $pathToRemove);

						// Remove the archived folder
						$this->log("Removing unarchived copies of PDFs");
						foreach ($details['pdfs'] as $strTempPdf)
						{
							unlink($strTempPdf);
						}

						$arrSummary[$strCustGroupName][$strLetterType]['output_directory'] = $strTarPath;
						$this->log("Finished moving $strLetterType PDFs for customer group $strCustGroupName to $strTarPath");
					}
					else
					{
						$this->log("There were no print $strLetterType PDFs for customer group $strCustGroupName");
					}
				}
			}

			if (!$sendEmail)
			{
				$this->log("No applicable invoice runs found. Exiting normally.");
				return 0;
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
			if (count($arrSummary))
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
			if (Email_Notification::sendEmailNotification(EMAIL_NOTIFICATION_LATE_NOTICE_REPORT, NULL, NULL, $subject, NULL, $body, NULL, TRUE))
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
			$this->showUsage('ERROR: ' . $exception->getMessage());
			return 1;
		}
	}

	private function changeInvoiceRunAutoActionDateTime($invoiceRunId, $intAutomaticInvoiceAction)
	{
		$qryQuery = new Query();
		$invoiceRunId = $qryQuery->EscapeString($invoiceRunId);
		$strSQL = "UPDATE automatic_invoice_run_event SET actioned_datetime = '$this->runDateTime' WHERE invoice_run_id IN (SELECT Id FROM InvoiceRun WHERE invoice_run_id = '$invoiceRunId') AND automatic_invoice_action_id = $intAutomaticInvoiceAction";

		$message = TRUE;
		if (!$qryQuery->Execute($strSQL))
		{
			$message = ' Failed to update automatic_invoice_run_event for invoice_run ' . $invoiceRunId . ' and action ' . $intAutomaticInvoiceAction . ' to ' . $this->runDateTime . '. '. $qryQuery->Error();
			$this->log($message, TRUE);
		}
		return $message;
	}

	private function changeAccountAutomaticInvoiceAction($intAccount, $intFrom, $intTo, $strReason, $invoiceRunId)
	{
		$error = ChangeAccountAutomaticInvoiceAction($intAccount, $intFrom, $intTo, $strReason, $this->runDateTime, $invoiceRunId);
		if ($error !== TRUE)
		{
			$this->log($error, TRUE);
		}
		return $error;
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
