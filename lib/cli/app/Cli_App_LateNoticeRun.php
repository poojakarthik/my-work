<?php

require_once dirname(__FILE__) . '/' . '../../../' . 'flex.require.php';
require_once dirname(__FILE__) . '/' . '../../pdf/Flex_Pdf.php';

class Cli_App_LateNoticeRun extends Cli
{
	const SWITCH_EFFECTIVE_DATE = "e";
	const SWITCH_TEST_RUN = "t";
	const SWITCH_SAMPLE = "p";
	
	const	EMAIL_BILLING_NOTIFICATIONS	= 'ybs-admin@ybs.net.au';

	private $runDateTime = '';

	function run()
	{
		$now = time();
		$this->runDateTime = date('Y-m-d H:i:s', $now);
		$pathDate = date('Ymd', $now);
		
		$dacFlex	= DataAccess::getDataAccess();
		
		if (!$dacFlex->TransactionStart())
		{
			$this->showUsage('ERROR: There was an error starting the transaction');
			return 1;
		}

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

			$aCorrespondenceToPost	= array();
			$aEmailsToSend			= array();
			
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
						$arrDeliveryMethodAccounts	= array(DELIVERY_METHOD_POST=>array(), DELIVERY_METHOD_EMAIL=>array());
						$arrCustomerGroups			= array();
						foreach ($mixResult['Details'] as $mixKey=>$arrDetails)
						{
							$arrDeliveryMethodAccounts[$arrDetails['Account']['DeliveryMethod']][$arrDetails['Account']['CustomerGroup']][]	= $mixKey;
						}
						
						// Pick a random sample for each Delivery Method
						foreach ($arrDeliveryMethodAccounts as $intDeliveryMethod=>$arrCustomerGroups)
						{
							foreach ($arrCustomerGroups as $intCustomerGroupId=>$arrAccounts)
							{
								$mixRandomKey	= array_rand($arrAccounts);
								$arrAccount		= $mixResult['Details'][$arrAccounts[$mixRandomKey]]['Account'];
								
								$arrSampleAccounts[$intDeliveryMethod][$intCustomerGroupId]	= $arrAccount['AccountId'];
								
								$this->log("{$arrAccount['AccountId']} has been selected as the random sample for {$arrAccount['CustomerGroupName']}:".GetConstantDescription($intDeliveryMethod, 'delivery_method'));
							}
						}
					}

					$aCorrespondenceToPost[$intNoticeType]	= array();
					$aEmailsToSend[$intNoticeType]			= array();
					
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
							$arrSummary[$strCustGroupName][$strLetterType]['output_directory'] = realpath(FILES_BASE_PATH) . '/' . $letterType . '/' . 'pdf' . '/' . $pathDate . '/' . $custGroupName;
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
											$xdirectory = $directory . '/' . $subDirectory;
											if (!file_exists($xdirectory))
											{
												$ok = @mkdir($xdirectory);
												if (!$ok)
												{
													$this->log("Failed to create directory for PDF output: $xdirectory", TRUE);
												}
											}
											$directory = $xdirectory . '/';
										}
										$outputDirectory = realpath($directory) . '/';
									}
									else
									{
										$outputDirectory = realpath($outputDirectory) . '/';
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
									
									$this->log("Generating Corerspondence Data for account ". $intAccountId);
									
									// Cache the correspondence data for the notice
									$aCorrespondence	= 	array(
																'account_id'						=> $intAccountId,
																'customer_group_id'					=> $intCustGrp,
																'correspondence_delivery_method_id'	=> Correspondence_Delivery_Method::getForId(CORRESPONDENCE_DELIVERY_METHOD_POST)->system_name,
																'account_name'						=> $arrDetails['Account']['BusinessName'],
																'title'								=> $arrDetails['Account']['Title'],
																'first_name'						=> $arrDetails['Account']['FirstName'],
																'last_name'							=> $arrDetails['Account']['LastName'],
																'address_line_1'					=> $arrDetails['Account']['AddressLine1'],
																'address_line_2'					=> $arrDetails['Account']['AddressLine2'],
																'suburb'							=> $arrDetails['Account']['Suburb'],
																'postcode'							=> $arrDetails['Account']['Postcode'],
																'state'								=> $arrDetails['Account']['State'],
																'email'								=> $arrDetails['Account']['Email'],
																'mobile'							=> $arrDetails['Account']['Mobile'],
																'landline'							=> $arrDetails['Account']['Landline'],
																'pdf_file_path'						=> $targetFile
															);
									
									$aCorrespondenceToPost[$intNoticeType][]	= $aCorrespondence;
									
									// This is the sample Post Notice -- email
									if ($this->_bolTestRun && $arrSampleAccounts[DELIVERY_METHOD_POST][$intCustGrp] === $intAccountId)
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
											$this->log("[SAMPLE:SUCCESS]: Sample POST {$strLetterType} for {$strCustGroupName} delivered to '".self::EMAIL_BILLING_NOTIFICATIONS."'");
										}
										else
										{
											$this->log("[SAMPLE:ERROR]: Unable to deliver Sample POST {$strLetterType} for {$strCustGroupName} to '".self::EMAIL_BILLING_NOTIFICATIONS."'");
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
									$this->log("Generating data to send $strLetterType email for account ". $intAccountId . ' to ' . $arrDetails['Account']['Email']);
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

				 					$strContent =	"Dear $name,\n\n" .
				 									"You may have overlooked payment of your account this month.  Please find attached urgent correspondence regarding your monthly invoice that requires your immediate attention.\n\n" .
				 									"If you have paid your account recently, we thank you and please disregard this email.\n\n" .
				 									"Please note that if you have paid your account by BPay it can take up to 5 business days to receive this payment.\n\n" .
				 									"Regards\n\n" .
				 									"Credit Department\n" .
				 									"$strCustGroupName\n" .
				 									'"The Small to Medium Size Business Specialists!"';

									$to = $arrArgs[self::SWITCH_TEST_RUN] ? self::EMAIL_BILLING_NOTIFICATIONS : $emailTo;

									$attachments = array();
									$attachment = array();
									$attachment[self::EMAIL_ATTACHMENT_NAME] = $fileName;
									$attachment[self::EMAIL_ATTACHMENT_MIME_TYPE] = 'application/pdf';
									$attachment[self::EMAIL_ATTACHMENT_CONTENT] = $pdfContent;
									$attachments[] = $attachment;

									if (!$this->_bolTestRun)
									{
										// Cache email data for sending later
										$aEmailsToSend[$intNoticeType][]	=	array(
																					'iCustomerGroup'	=> $intCustGrp,
																					'sCustomerGroup'	=> $strCustGroupName,
																					'sEmailTo'			=> $emailTo,
																					'sTo'				=> $to,
																					'sSubject'			=> $subject,
																					'sContent'			=> $strContent,
																					'aAttachments'		=> $attachments,
																					'bSilentFail'		=> true,
																					'iAccountId'		=> $intAccountId
																				);
										
										// We need to log the fact that we're sending it, by updating the account automatic_invoice_action
										$outcome = $this->changeAccountAutomaticInvoiceAction($intAccountId, $intAutoInvoiceAction, $intAutomaticInvoiceAction, "$strLetterType emailed to $name ($emailTo)", $invoiceRunId);
										if ($outcome !== TRUE)
										{
											$arrSummary[$strCustGroupName][$strLetterType]['errors'][] = $outcome;
											$errors++;
										}
									}
									
									// This is the sample Email Notice -- email
									if ($this->_bolTestRun && $arrSampleAccounts[DELIVERY_METHOD_EMAIL][$intCustGrp] === $intAccountId)
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
											$this->log("[SAMPLE:SUCCESS]: Sample EMAIL {$strLetterType} for {$strCustGroupName} delivered to '".self::EMAIL_BILLING_NOTIFICATIONS."'");
										}
										else
										{
											$this->log("[SAMPLE:ERROR]: Unable to deliver Sample {$strLetterType} for {$strCustGroupName} to '".self::EMAIL_BILLING_NOTIFICATIONS."'");
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

			// Create correspondence runs containing all notices to be posted, done BEFORE the emailing just incase there is invalid correspondence data
			$this->createCorrespondenceRuns($aCorrespondenceToPost);
			
			// Send all of the required emails (only if NOT testing)
			if ($this->_bolTestRun)
			{
				$this->log("Not sending emails, in TEST mode");
			}
			else
			{
				$this->log("Sending Emails to account holders");
				
				foreach ($aEmailsToSend as $iNoticeType => $aEmailData)
				{
					$sLetterType	= GetConstantDescription($iNoticeType, 'DocumentTemplateType');
					foreach ($aEmailData as $aEmail)
					{
						$this->log("Email {$sLetterType} being sent for account ".$aEmail['iAccountId']." to ".$aEmail['sEmailTo']);
						
						// Try and send email
						if (Email_Notification::sendEmailNotification(EMAIL_NOTIFICATION_LATE_NOTICE, $aEmail['iCustomerGroup'], $aEmail['sTo'], $aEmail['sSubject'], NULL, $aEmail['sContent'], $aEmail['aAttachments'], $aEmail['bSilentFail']))
						{
							$this->log("... Successful!");
							
							// ... Success
							$arrSummary[$aEmail['sCustomerGroup']][$sLetterType]['emails'][] = $aEmail['iAccountId'];
						}
						else
						{
							// ... Failure, we need to log this
							$sMessage	= "Failed to email $sLetterType PDF for account " . $aEmail['iAccountId'] . ' to ' . $aEmail['sEmailTo'];
							$arrSummary[$aEmail['sCustomerGroup']][$sLetterType]['errors'][] = $sMessage;
							$errors++;
							$this->log($sMessage, TRUE);
						}
					}
				}
			}
			
			// We now need to build a report detailing actions taken for each of the customer groups 
			if (!$sendEmail)
			{
				$this->log("No applicable accounts found. Exiting normally.");
				return 0;
			}

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
			
			if ($this->_bolTestRun)
			{
				throw new Exception("Test Mode!  Rolling back all database changes.");
			}
			if (!$dacFlex->TransactionCommit())
			{
				throw new Exception("Transaction Commit Failed");
			}

			$this->log("Finished.");
			return $errors;
		}
		catch(Exception $exception)
		{
			if (!$dacFlex->TransactionRollback())
			{
				$this->showUsage('ERROR: Transaction Rollback Failed');
				return 1;
			}
			
			$this->showUsage('ERROR: ' . $exception->getMessage());
			
			return 1;
		}
	}
	
	private function createCorrespondenceRuns($aCorrespondenceToPost=array())
	{
		$this->log("Creating Correspondence Runs");

		// Generate a correspondence run for each notice type and to it correspondence that is to be posted for that notice type
		foreach ($aCorrespondenceToPost as $iNoticeType => $aCorrespondenceData)
		{
			$this->log("Creating Correspondence Run for document template type '{$iNoticeType}'");
			
			// Determine the Correspondence (System) Template name from the notice type
			$sTemplateName	= null;
			switch ($iNoticeType)
			{
				case DOCUMENT_TEMPLATE_TYPE_FRIENDLY_REMINDER:
					$sTemplateName	= 'FRIENDLY_REMINDER';
					break;
				case DOCUMENT_TEMPLATE_TYPE_OVERDUE_NOTICE:
					$sTemplateName	= 'OVERDUE_NOTICE';
					break;
				case DOCUMENT_TEMPLATE_TYPE_SUSPENSION_NOTICE:
					$sTemplateName	= 'SUSPENSION_NOTICE';
					break;
				case DOCUMENT_TEMPLATE_TYPE_FINAL_DEMAND:
					$sTemplateName	= 'FINAL_DEMAND';
					break;
			}
			
			$this->log("... using Correspondence Template '{$sTemplateName}'");
			
			try
			{
				// Create Correspondence Run using the pre-deterimined (System) Correspondence Template name
				$this->log("Retrieving Template");
				
				$oTemplate	= Correspondence_Logic_Template::getForSystemName($sTemplateName, $aCorrespondenceData);
				
				$this->log("Template retrieved, creating Correspondence Run");
				
				$oRun	= $oTemplate->createRun(true);
				
				$this->log("Run created succesfully (id={$oRun->id})");
			}
			catch (Correspondence_DataValidation_Exception $oEx)
			{
				// Use the exception information to display a meaningful message
				$sErrorType	= GetConstantName($oEx->iError, 'correspondence_run_error');
				throw new Exception("Correspondence Run Processing Failed:\n - Validation errors in the correspondence data for the run: \n -- Error Type: $oEx->iError => '{$sErrorType}'");
			}
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
		
			self::SWITCH_SAMPLE => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "will send sample Notices to ".self::EMAIL_BILLING_NOTIFICATIONS,
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
		
		);
	}

}


?>
