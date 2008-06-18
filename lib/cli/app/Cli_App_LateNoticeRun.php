<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../../' . 'flex.require.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../pdf/Flex_Pdf.php';

class Cli_App_LateNoticeRun extends Cli
{
	const SWITCH_EFFECTIVE_DATE = "e";

	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			// Get date in YYYY-mm-dd format
			$now = substr($arrArgs[self::SWITCH_EFFECTIVE_DATE], 0, 10);
			// Convert to unix timestamp for date
			$day = intval(substr($now, 8, 2));
			$month = intval(substr($now, 5, 2));
			$year = intval(substr($now, 0, 4));
			$todayTimestamp = mktime(0, 0, 0, $month, $day, $year, FALSE);

			$arrNoticeTypes = array(
				LETTER_TYPE_OVERDUE,
				LETTER_TYPE_SUSPENSION,
				LETTER_TYPE_FINAL_DEMAND);

			$outputs = array();

			$errors = 0;

			$arrSummary = array();

			foreach($arrNoticeTypes as $intNoticeType)
			{
				$mixResult = GenerateLatePaymentNotices($intNoticeType, $todayTimestamp);
				$strLetterType = GetConstantDescription($intNoticeType, "LetterType");

				// Notices were generated iff the results contain an 
				if ($mixResult === FALSE)
				{
					$this->log("ERROR: Generating " . $strLetterType . "s failed, unexpectedly", 0);
					$errors += 1;
				}
				else
				{
					$newAutoInvAction = NULL;
					// Take action appropriate for the notice type
					switch ($intNoticeType)
					{
						case LETTER_TYPE_OVERDUE:
							$newAutoInvAction = AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE;
							break;
						case LETTER_TYPE_SUSPENSION:
							$newAutoInvAction = AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE;
							break;
						case LETTER_TYPE_FINAL_DEMAND:
							$newAutoInvAction = AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND;
							break;
						default:
							continue;
					}

					$this->log("Notices successfully generated  : {$mixResult['Successful']}");
					$this->log("Notices that failed to generate : {$mixResult['Failed']}");

					// We now need to email/print each of the notices that have been generated
					foreach($mixResult['Details'] as $arrDetails)
					{
						if (!array_key_exists($arrDetails['Account']['AccountGroup'], $arrSummary))
						{
							$arrSummary[$arrDetails['Account']['AccountGroup']] = array();
							$arrSummary[$arrDetails['Account']['AccountGroup']]['emails'] = array();
							$arrSummary[$arrDetails['Account']['AccountGroup']]['prints'] = array();
							$arrSummary[$arrDetails['Account']['AccountGroup']]['errors'] = array();
						}

						$intCustGrp = $arrDetails['Account']['CustomerGroup'];
						$intAccountId = $arrDetails['Account']['AccountId'];
						$xmlFilePath = $arrDetails['XMLFilePath'];
						$intAutoInvoiceAction = $arrDetails['Account']['automatic_invoice_action'];

						switch ($arrDetails['Account']['DeliveryMethod'])
						{
							case BILLING_METHOD_POST:
								// We need to generate the pdf for the XML and save it to the 
								// files/type/pdf/date/cust_group/account.pdf storage
								// Need to add a note of this to the email
								$this->log("Generating print PDF ($intNoticeType) for account ". $arrDetails['Account']['AccountId']);
								$pdfContent = $this->getPDFContent($intCustGrp, time(), $intNoticeType, $xmlFilePath, 'PRINT');

								// If the PDF generation failed.
								if (!$pdfContent)
								{
									$error = $this->getCachedError();
									$message = "Failed to generate PDF ($intNoticeType) for " . $intAccountId . "\n" . $error;
									$arrSummary[$intCustGrp]['errors'][] = $message;
									$this->log($message, TRUE);
								}
								// We have a PDF, so we should store it for sending to the printers
								else
								{
									$this->log("Storing notice ($intNoticeType) for account ". $intAccountId);
									$letterType = strtolower(str_replace(' ', '_', $strLetterType));
									$custGroupName = strtolower(str_replace(' ', '_', $arrDetails['Account']['CustomerGroupName']));

									if (array_key_exists('output_directory', $arrSummary[$intCustGrp]))
									{
										$targetFile = $arrSummary[$intCustGrp]['output_directory'];
									}
									else
									{
										$targetFile = FILES_BASE_PATH . DIRECTORY_SEPARATOR . $letterType . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR . $custGroupName;
										RecursiveMkdir($targetFile);

										$arrSummary[$intCustGrp]['output_directory'] = $targetFile;
									}

									// WIP This bit needs error handling
									$targetFile .= DIRECTORY_SEPARATOR . $intAccountId . '.pdf';
									$file = fopen($targetFile, 'w');
									fwrite($file, $pdfContent);
									fclose($file);

									$arrSummary[$intCustGrp]['prints'][] = $intAccountId;


									// We need to log the fact that we've created it, by updating the account automatic_invoice_action
									$outcome = $this->changeAccountAutomaticInvoiceAction($intAccountId, $intAutoInvoiceAction, $newAutoInvAction, "$strLetterType emailed to $name ($emailTo)");
									if ($outcome !== TRUE)
									{
										$arrSummary[$intCustGrp]['errors'][] = $outcome;
									}
									else
									{
										
									}
								}

								break;

							case BILLING_METHOD_EMAIL:
								// We can safely go ahead and generate this pdf.
								$this->log("Generating email PDF ($intNoticeType) for account ". $intAccountId . ' to ' . $arrDetails['Account']['Email']);
								$pdfContent = $this->getPDFContent($intCustGrp, time(), $intNoticeType, $xmlFilePath, 'EMAIL');

								// If the PDF generation failed.
								if (!$pdfContent)
								{
									$error = $this->getCachedError();
									$message = "Failed to generate PDF ($intNoticeType) for " . $intAccountId . "\n" . $error;
									$arrSummary[$intCustGrp]['errors'][] = $message;
									$this->log($message, TRUE);
								}
								// We have a PDF, so we should email it
								else
								{
									$this->log("Emailing notice ($intNoticeType) for account ". $intAccountId . ' to ' . $arrDetails['Account']['Email']);
									$custGroupName = $arrDetails['Account']['CustomerGroupName'];
									$fileName = str_replace(' ', '_', $strLetterType) . '.pdf';
									$emailTo = 'holiver@yellowbilling.com.au'; //$arrDetails['Account']['Email'];
									$emailFrom = 'me@yellowbilling.com.au';//$arrDetails['Account']['EmailFrom'];
									$subject = "$custGroupName $strLetterType";

									$name = array();
									if (trim($arrDetails['Account']['Title'])) $name[] = trim($arrDetails['Account']['Title']);
									if (trim($arrDetails['Account']['FirstName'])) $name[] = trim($arrDetails['Account']['FirstName']);
									if (trim($arrDetails['Account']['LastName'])) $name[] = trim($arrDetails['Account']['LastName']);
									$name = implode(' ', $name);

									$a = 'a';
									if (strpos('aeiou', strtolower($strLetterType[0])) !== FALSE)
									{
										$a = 'an';
									}

				 					$strContent = "Dear $name,\r\n\r\n" .
				 								  "Please find attached $a $strLetterType from $custGroupName\r\n\r\n" .
				 								  "Regards\r\n\r\n" .
				 								  "The Team at $custGroupName";

									$outcome = $this->sendEmail($emailFrom, $emailTo, $subject, $strContent, $pdfContent, $fileName, 'application/pdf');

									if ($outcome === TRUE)
									{
										$arrSummary[$intCustGrp]['emails'][] = $intAccountId;

										// We need to log the fact that we've sent it, by updating the account automatic_invoice_action
										$outcome = $this->changeAccountAutomaticInvoiceAction($intAccountId, $intAutoInvoiceAction, $newAutoInvAction, "$strLetterType emailed to $name ($emailTo)");
										if ($outcome !== TRUE)
										{
											$arrSummary[$intCustGrp]['errors'][] = $outcome;
										}
									}
									else
									{
										// We need to log the fact that the sending of the email failed
										$message = "Failed to email $strLetterType PDF for account " . $intAccountId . ' to ' . $emailTo . ($outcome ? "\n$outcome" : '');
										$arrSummary[$intCustGrp]['errors'][] = $message;
										$this->log($message, TRUE);
									}
									
								}
								break;
						}
					}
				}
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

	private function changeAccountAutomaticInvoiceAction($intAccount, $intFrom, $intTo, $strReason)
	{
		$error = '';

		$qryQuery = new Query();
		$strSQL = 'UPDATE Account SET last_automatic_invoice_action = ' . $intTo . ' WHERE Id = ' . $intAccount;
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
				' now()' .
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
		
		);
	}

}


?>
