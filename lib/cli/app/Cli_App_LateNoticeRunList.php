<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../../' . 'flex.require.php';

class Cli_App_LateNoticeRunList extends Cli
{
	const SWITCH_EFFECTIVE_DATE = "e";
	const SWITCH_TEST_RUN = "t";

	private $runDateTime = '';

	function run()
	{
		$now = time();
		$this->runDateTime = date('Y-m-d H:i:s', $now);

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

			$arrNoticeTypes = array(
				DOCUMENT_TEMPLATE_TYPE_FRIENDLY_REMINDER => AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER_LIST,
				DOCUMENT_TEMPLATE_TYPE_OVERDUE_NOTICE => AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE_LIST,
				DOCUMENT_TEMPLATE_TYPE_SUSPENSION_NOTICE => AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE_LIST,
				DOCUMENT_TEMPLATE_TYPE_FINAL_DEMAND => AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND_LIST,
			);

			$outputs = array();

			$errors = 0;

			$arrSummary = array();

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

				$sendEmail = TRUE;

				$arrAccounts = ListLatePaymentAccounts($intAutomaticInvoiceAction, $arrArgs[self::SWITCH_EFFECTIVE_DATE]);
				$strLetterType = GetConstantDescription($intNoticeType, "DocumentTemplateType");

				// Notices were generated iff the results contain an 
				if ($arrAccounts === FALSE)
				{
					$message = "ERROR: Listing accounts for " . $strLetterType . "s failed, unexpectedly";
					$this->log($message, TRUE);
					throw new Exception($message);
				}

				if (!array_key_exists($intAutomaticInvoiceAction, $invoiceRunAutoFields))
				{
					$invoiceRunAutoFields[$intAutomaticInvoiceAction] = array();
				}

				foreach($arrAccounts as $arrAccount)
				{
					$invoiceRun = $arrAccount['InvoiceRun'];
					if (!array_key_exists($invoiceRun, $invoiceRunAutoFields[$intAutomaticInvoiceAction]))
					{
						$invoiceRunAutoFields[$intAutomaticInvoiceAction][$invoiceRun] = 0;
					}
					$invoiceRunAutoFields[$intAutomaticInvoiceAction][$invoiceRun]++;

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

			foreach ($invoiceRunAutoFields as $intAutomaticInvoiceAction => $invoiceRunCounts)
			{
				foreach ($invoiceRunCounts as $invoiceRun => $count)
				{
					$result = $this->changeInvoiceRunAutoActionDateTime($invoiceRun, $intAutomaticInvoiceAction);
					if ($result !== TRUE)
					{
						throw new Exception("Error: Failed to mark late nutice run list event as complete for invoice run $invoiceRun: $result");
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
				$this->log('Committing transaction.');
				$conConnection->TransactionCommit();
			}

			if (!$sendEmail)
			{
				$this->log("No applicable invoice runs found. Exiting normally.");
				return 0;
			}


			// We now need to build a report detailing actions taken for each of the customer groups
			$this->log("Building report");
			$subject = 'Automated late notice list log for run dated ' . $this->runDateTime;
			$report = array();
			$attachments = array();
			if (count($arrSummary))
			{
				$report[] = "Breakdown of proposed late notice generation by customer group: -";
				foreach ($arrSummary as $custGroup => $letterTypeSummarries)
				{
					$report[] = "";
					$report[] = "";
					$report[] = "Customer Group: $custGroup";

					foreach ($letterTypeSummarries as $letterType => $letterTypeSummary)
					{
						$attachment = '';

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
							$arrAttachment = array();
							$arrAttachment[self::EMAIL_ATTACHMENT_NAME] = str_replace(' ', '_', $custGroup) . '_' . str_replace(' ', '_', $letterType) . '.csv';
							$arrAttachment[self::EMAIL_ATTACHMENT_MIME_TYPE] = 'text/csv';
							$arrAttachment[self::EMAIL_ATTACHMENT_CONTENT] = $attachment;
							$attachments[] = $arrAttachment;
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
			if ($this->sendEmailNotification(EMAIL_NOTIFICATION_LATE_NOTICE_LIST, NULL, NULL, $subject, NULL, $body, $attachments))
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
			$this->showUsage('ERROR: ' . $exception->getMessage());
			return 1;
		}
	}

	private function changeInvoiceRunAutoActionDateTime($invoiceRun, $intAutomaticInvoiceAction)
	{
		$qryQuery = new Query();
		$invoiceRun = $qryQuery->EscapeString($invoiceRun);
		$strSQL = "UPDATE automatic_invoice_run_event SET actioned_datetime = '$this->runDateTime' WHERE invoice_run_id IN (SELECT Id FROM InvoiceRun WHERE InvoiceRun = '$invoiceRun') AND automatic_invoice_action_id = $intAutomaticInvoiceAction";

		$message = TRUE;
		if (!$qryQuery->Execute($strSQL))
		{
			$message = ' Failed to update automatic_invoice_run_event for invoice_run ' . $invoiceRun . ' and action ' . $intAutomaticInvoiceAction . ' to ' . $this->runDateTime . '. '. $qryQuery->Error();
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
