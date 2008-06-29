<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../../' . 'flex.require.php';

class Cli_App_LateNoticeRunList extends Cli
{
	const SWITCH_EFFECTIVE_DATE = "e";

	private $runDateTime = '';

	function run()
	{
		$now = time();
		$this->runDateTime = date('Y-m-d H:i:s', $now);

		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

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

			foreach($arrNoticeTypes as $intNoticeType)
			{
				$arrAccounts = ListLatePaymentAccounts($intNoticeType, $todayTimestamp);
				$strLetterType = GetConstantDescription($intNoticeType, "LetterType");

				// Notices were generated iff the results contain an 
				if ($arrAccounts === FALSE)
				{
					$message = "ERROR: Listing accounts for " . $strLetterType . "s failed, unexpectedly";
					$this->log($message, TRUE);
					throw new Exception($message);
				}

				foreach($arrAccounts as $arrAccount)
				{
					if (!array_key_exists($arrAccount['CustomerGroupName'], $arrSummary))
					{
						$arrSummary[$arrAccount['CustomerGroupName']] = array();
					}
					if (!array_key_exists($strLetterType, $arrSummary[$arrAccount['CustomerGroupName']]))
					{
						$arrSummary[$arrAccount['CustomerGroupName']][$strLetterType] = array('prints'=>array(),'emails'=>array());
					}
					switch ($arrAccount['DeliveryMethod'])
					{
						case BILLING_METHOD_POST:
							$arrSummary[$arrAccount['CustomerGroupName']][$strLetterType]['prints'][] = $arrAccount['AccountId'];
							break;
						case BILLING_METHOD_EMAIL:
							$arrSummary[$arrAccount['CustomerGroupName']][$strLetterType]['emails'][] = $arrAccount['AccountId'];
							break;
					}
				}
			}

			// We now need to build a report detailing actions taken for each of the customer groups
			$this->log("Building report");
			$subject = 'Automated late notice list log for run dated ' . $this->runDateTime;
			$report = array();
			$attachments = array();
			$mimeTypes = array();
			$attachmentNames = array();
			if (count($arrSummary))
			{
				$report[] = "Breakdown of proposed late notice generation by customer group: -";
				foreach ($arrSummary as $custGroup => $letterTypeSummarries)
				{
					$report[] = "";
					$report[] = "";
					$report[] = "Customer Group: $custGroup";

					$attachment = '';

					foreach ($letterTypeSummarries as $letterType => $letterTypeSummary)
					{
						$report[] = "[Start of $letterType breakdown for Customer Group: $custGroup]";
						if (!empty($letterTypeSummary['prints']))
						{
							$report[] = "Print: " . count($letterTypeSummary['prints']) . " {$letterType}s would be created for printing.";
							$report[] = "Prints would be created for the following accounts: -";
							$report[] = implode(', ', $letterTypeSummary['prints']);
							$attachment .= implode(",Print\n", $letterTypeSummary['prints']).",Print\n";
						}
						else
						{
							$report[] = "Print: No documents would be created for printing";
						}
						if (!empty($letterTypeSummary['emails']))
						{
							$report[] = "Email: " . count($letterTypeSummary['emails']) . " {$letterType}s would be created and emailed.";
							$report[] = "Emails would be sent for the following accounts: -";
							$report[] = implode(', ', $letterTypeSummary['emails']);
							$attachment .= implode(",Email\n", $letterTypeSummary['prints']).",Email\n";
						}
						else
						{
							$report[] = "Email: No documents would be emailed";
						}
						$report[] = "[End of $letterType breakdown for Customer Group: $custGroup]";
						$report[] = "";
						
						if ($attachment)
						{
							$attachmentNames[] = str_replace(' ', '_', $custGroup) . '_' . str_replace(' ', '_', $letterType) . '.csv';
							$attachments[] = $attachment;
							$mimeTypes[] = 'text/csv';
						}
					}

					$report[] = "[End of breakdown for Customer Group: $custGroup]";
					$report[] = "";
					$report[] = "";
				}
			}
			else
			{
				$report[] = "No automated late notices would be generated.";
			}
			$body = implode("\r\n", $report);

			$this->log("Sending report");
			$outcome = $this->sendEmail("late_notice_run@yellowbilling.com.au", "ybs-admin@yellowbilling.com.au", $subject, $body, $attachments, $attachmentNames, $mimeTypes);

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

		);
	}

}


?>
