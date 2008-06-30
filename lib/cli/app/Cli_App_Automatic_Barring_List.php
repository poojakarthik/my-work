<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../../' . 'flex.require.php';

class Cli_App_Automatic_Barring_List extends Cli
{
	const SWITCH_EFFECTIVE_DATE = "e";

	function run()
	{
		$now = time();
		$this->runDateTime = date('Y-m-d H:i:s', $now);

		$barSummary = array();
		$unbarSummary = array();
		$report = array();

		set_time_limit(0);

		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			$effectiveDate = $arrArgs[self::SWITCH_EFFECTIVE_DATE];

			$errors = 0;

			$this->log('Summarising barrable accounts...');
			$barSummary = $this->summarise('ListAutomaticBarringAccounts', 'barred', $effectiveDate);
			$this->log('Summarising unbarrable accounts...');
			$unbarSummary = $this->summarise('ListAutomaticUnbarringAccounts', 'unbarred', $effectiveDate);



			// We now need to build a report detailing actions taken for each of the customer groups
			$this->log("Building report");
			$subject = 'Proposed Automated Barring List for run dated ' . $this->runDateTime;

			$report[] = "*** PROPOSED BARRING ***";
			if (!empty($barSummary))
			{
				$report[] = "See attachments for details of accounts and services that would be barred.";
				$report[] = "";
				$report[] = "Summary of Proposed Barring: -";
				foreach ($barSummary as $custGroup => $arrCSV)
				{
					$report[] = "";
					$report[] = "Customer Group: $custGroup; " . count($arrCSV['services']) . " services would be barred for " . count($arrCSV['accounts']) . " accounts.";
					$report[] = "";
				}
			}
			else
			{
				$report[] = "No accounts require barring.";
			}

			$report[] = "";
			$report[] = "";
			$report[] = "";
			$report[] = "*** PROPOSED UNBARRING ***";
			if (!empty($unbarSummary))
			{
				$report[] = "See attachments for details of accounts and services that would be unbarred.";
				$report[] = "";
				$report[] = "Summary of Proposed Unbarring: -";
				foreach ($unbarSummary as $custGroup => $arrCSV)
				{
					$report[] = "";
					$report[] = "Customer Group: $custGroup; " . count($arrCSV['services']) . " services would be unbarred for " . count($arrCSV['accounts']) . " accounts.";
					$report[] = "";
				}
			}
			else
			{
				$report[] = "No accounts require unbarring.";
			}

			$body = implode("\r\n", $report);

			$this->log("Adding attachments...");
			$nl = "\n";
			$arrAttachments = array();
			$arrFileNames = array();
			$arrMimeTypes = array();
			foreach($barSummary as $custGroup => $list)
			{
				$custGroup = str_replace(' ', '_', $custGroup);
				if (count($list['services']))
				{
					$arrFileNames[] = $custGroup.'_Proposed_Account_Services_To_Be_Barred_' . date('Y_m_d_H_i_s') . '.csv';
					$arrAttachments[] = "Account,FNN$nl" . implode($nl, $list['services']);
					$arrMimeTypes[] = 'text/csv';
				}
			}
			foreach($unbarSummary as $custGroup => $list)
			{
				$custGroup = str_replace(' ', '_', $custGroup);
				if (count($list['services']))
				{
					$arrFileNames[] = $custGroup.'_Proposed_Account_Services_To_Be_Unbarred_' . date('Y_m_d_H_i_s') . '.csv';
					$arrAttachments[] = "Account,FNN$nl" . implode($nl, $list['services']);
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
			$this->log('Sending error report via email.');
			$subject = '[ERROR]'. ($arrArgs[self::SWITCH_TEST_RUN] ? ' [TEST]' : '') .' Listing of Automatic [un]barring service failed at ' . date('Y-m-d H:i:s');
			$body = array();
			$body[] = 'The listing of account services that would be automatically [un]barred failed. The following error details are available: -'; 
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
				$body[] = 'The problem may have occurred during the unbarring search stage. The following unbarring information had been cached: -';
				$body[] = '$unbarSummary = ' . var_export($unbarSummary, TRUE);
			}
			else if (count($barSummary))
			{
				$body[] = '';
				$body[] = '';
				$body[] = 'The problem may have occurred during the barring search stage. The following barring information had been cached: -';
				$body[] = '$barSummary = ' . var_export($barSummary, TRUE);
			}
			$body = implode("\r\n", $body);

			$outcome = $this->sendEmail("AutomatedBarring@yellowbilling.com.au", "ybs-admin@yellowbilling.com.au", $subject, $body);
			$this->showUsage('ERROR: ' . $exception->getMessage());
			return 1;
		}
	}

	function summarise($strFunctionName, $strActionDesc, $effectiveDate)
	{
			$mixResult = $strFunctionName($effectiveDate, TRUE);

			$arrSummary = array();
			
			if (!is_array($mixResult))
			{
				$message = "ERROR: Failed to find accounts that would be $strActionDesc.";
				$this->log($message, 0);
				throw new Exception($message);
			}

			$nrAccounts = count($mixResult);
			if ($nrAccounts)
			{
				$this->log("Found $nrAccounts accounts that would be $strActionDesc.");
			}
			else
			{
				$this->log("No accounts need to be $strActionDesc.");
			}
			foreach($mixResult as $account)
			{
				$accountId 			= intval($account['AccountId']);
				$customerGroupId 	= intval($account['CustomerGroupId']);
				$customerGroupName 	= $account['CustomerGroupName'];

				if (!array_key_exists($customerGroupName, $arrSummary))
				{
					$arrSummary[$customerGroupName] = array('services'=>array(), 'accounts'=>array());
				}

				$this->log("Looking for services of account $accountId.");
				try
				{
					$arrServices = ListServicesAndCarriersForAccount($accountId);
					foreach ($arrServices as $intServiceId => $arrServiceDetails)
					{
						$fnn = $arrServiceDetails['FNN'];
						$arrSummary[$customerGroupName]['services'] = "$accountId,$fnn"; 
						$arrSummary[$customerGroupName]['accounts'][$accountId] = $accountId;
					}
				}
				catch (Exception $e)
				{
					$message = "ERROR: Failed to find services of account $accountId and customer group $customerGroupName ($customerGroupId).\n" . $e->getMessage();
					$this->log($message, TRUE);
					throw new Exception($message);
				}
			}
			return $arrSummary;
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
		
		);
	}

}


?>
