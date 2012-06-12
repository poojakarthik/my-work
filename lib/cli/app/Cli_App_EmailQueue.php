<?php
require_once dirname(__FILE__) . '/' . '../../../' . 'flex.require.php';
require_once dirname(__FILE__) . '/' . '../../pdf/Flex_Pdf.php';

class Cli_App_EmailQueue extends Cli {
	const SWITCH_TEST_RUN = "t";
	const SWITCH_FLUSH = 'f';
	const SWITCH_QUEUE_ID = "q";
	const SWITCH_DEBUG_EMAIL_ADDRESS = "e";
	const SWITCH_DEBUG_EMAIL_ID = "i";
	const SWITCH_CLEANUP = 'c';
	const SWITCH_CLEANUP_AGE = 'a';

	const CLEANUP_TEST_SUMMARY_ONLY = false;
	const CLEANUP_AGE_DEFAULT = 60;
	
	function run() {
		try {
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();
			
			if ((int)$arrArgs[self::SWITCH_DEBUG_EMAIL_ID]) {
				$this->_debugEmail();
			} elseif ((bool)$arrArgs[self::SWITCH_CLEANUP]) {
				$this->_cleanup();
			} elseif ((bool)$arrArgs[self::SWITCH_FLUSH]) {
				$this->_flush();
			} else {
				throw new Exception("No mode of operation supplied.");
			}
			
			return 0;
		} catch(Exception $oException) {
			$this->showUsage('Error: '.$oException->getMessage());
			return 1;
		}
	}

	private function _debugEmail() {
		// Args
		$arrArgs = $this->getValidatedArguments();
		$bTestRun = (bool)$arrArgs[self::SWITCH_TEST_RUN];
		$iDebugEmailId = (int)$arrArgs[self::SWITCH_DEBUG_EMAIL_ID];
		$sDebugAddress = $arrArgs[self::SWITCH_DEBUG_EMAIL_ADDRESS];
		$sDebugAddress = !!$sDebugAddress ? $sDebugAddress : null;

		if ($bTestRun && $sDebugAddress) {
			$oEmail = Email::getForId($iDebugEmailId);
			if (!$oEmail) {
				throw new Exception("Invalid debug email id supplied");
			}
			
			Log::getLog()->log("SENDING DEBUG EMAIL");
			
			// TODO: MOVE THIS INTO THE EMAIL CLASS
			$oEmailFlex = new Email_Flex();
			$oEmailFlex->addTo($sDebugAddress);
			Log::getLog()->log("\trecipients: ".print_r(implode(',', $oEmailFlex->getRecipients()), true));
			
			Log::getLog()->log("\tsender: $oEmail->sender");
			$oEmailFlex->setFrom($oEmail->sender);
			$oEmailFlex->setSubject('[FLEX EMAIL QUEUE TEST RE-SEND] '.$oEmail->subject);
			$oEmailFlex->setBodyText($oEmail->text);
			if ($oEmail->html !== null) {
				$oEmailFlex->setBodyHtml($oEmail->html);
			}
			
			// Add attachments
			$aAttachments = $oEmail->getAttachments();
			foreach ($aAttachments as $oAttachment) {
				// Defaults for nullable fields
				$sDisposition = ($oAttachment->disposition === null ? Zend_Mime::DISPOSITION_ATTACHMENT : $oAttachment->disposition);
				$sEncoding = ($oAttachment->encoding === null ? Zend_Mime::ENCODING_BASE64 : $oAttachment->encoding);
				
				Log::getLog()->log("\tattachment: $oAttachment->filename ({$oAttachment->mime_type})");
				
				// Create/add the attachment
				$oEmailFlex->createAttachment($oAttachment->content, $oAttachment->mime_type, $sDisposition, $sEncoding, $oAttachment->filename);
			}
			
			$oEmailFlex->send();
			Log::getLog()->log("SENT"); 
		} else {
			throw new Exception("Cannot debug email {$iDebugEmailId}. Must be in test mode and have supplied a debug email address");
		}
	}

	private function _flush() {
		// Args
		$arrArgs = $this->getValidatedArguments();
		$bTestRun = (bool)$arrArgs[self::SWITCH_TEST_RUN];
		$iQueueId = (int)$arrArgs[self::SWITCH_QUEUE_ID];
		$sDebugAddress = $arrArgs[self::SWITCH_DEBUG_EMAIL_ADDRESS];
		$sDebugAddress = !!$sDebugAddress ? $sDebugAddress : null;

		Log::getLog()->log('here');
		$oDataAccess = DataAccess::getDataAccess();
		if ($bTestRun) {
			// Start transaction, in test mode
			if ($oDataAccess->TransactionStart() === false) {
				throw Exception("Failed to start database transaction");
			}
			
			Log::getLog()->log("-----");
			Log::getLog()->log("Test Mode Enabled - None of the email queues will be commited.");
			if ($sDebugAddress !== null) {
				Log::getLog()->log("\nDebug email address: {$sDebugAddress}, all emails will be sent to this address.");
			}
			Log::getLog()->log("-----");
		}
		
		$aEmailQueues = array();
		if($iQueueId && is_numeric($iQueueId)) {
			Log::getLog()->log("Will attempt to deliver single queue: {$iQueueId}");
			
			// Deliver the single queue
			$aEmailQueues[] = Email_Queue::getForId($iQueueId);
		} else {
			// Deliver all waiting email_queue records
			$aEmailQueues = Email_Queue::getWaitingQueues();
		}
		
		$iQueueCount = count($aEmailQueues);
		Log::getLog()->log("{$iQueueCount} queue".($iQueueCount == 1 ? '' : 's')." to deliver");
		
		if ($iQueueCount > 0) {
			foreach ($aEmailQueues as $oEmailQueue) {
				// Deliver the email queue, only commits (actually sends) if NOT in test mode
				$oEmailQueue->deliver($bTestRun, $sDebugAddress);
			}
			
			Log::getLog()->log("All queues delivered");
		}
		
		if ($bTestRun) {
			// Rollback transaction, in test mode
			Log::getLog()->log("Test mode, rolling back all database changes");
			if ($oDataAccess->TransactionRollback() === false) {
				throw new Exception("Failed to rollback database transaction");
			}
		}
	}

	private function _cleanup() {
		// Args
		$arrArgs = $this->getValidatedArguments();
		$iMinimumAge = (int)$arrArgs[self::SWITCH_CLEANUP_AGE];
		$iQueueId = ($arrArgs[self::SWITCH_QUEUE_ID]) ? (int)$arrArgs[self::SWITCH_QUEUE_ID] : null;
		$bTestRun = (bool)$arrArgs[self::SWITCH_TEST_RUN];

		if (!is_int($iMinimumAge) || $iMinimumAge < 0) {
			throw new Exception("Invalid minimum age ".var_export($arrArgs[self::SWITCH_CLEANUP_AGE], true)." encountered. Minimum age must be a positive (or zero) integer.");
		}

		// Determine which email queues will be cleaned up
		Log::get()->log("[*] Looking for Email Queues to clean up (at least ".var_export($iMinimumAge, true)." days old)...");
		$oSummaryResult = Query::run("
			SELECT		eq.*,
						COUNT(DISTINCT e.id) AS email_count,
						COUNT(DISTINCT ea.id) AS email_attachment_count,
						CAST(eq.delivered_datetime AS DATE) <= CURDATE() - INTERVAL <minimum_age_days> DAY AS cleanup_eligible
			FROM		email_queue eq
						LEFT JOIN email e ON (e.email_queue_id = eq.id)
						LEFT JOIN email_attachment ea ON (ea.email_id = e.id)
			WHERE		(<email_queue_id> IS NULL OR <email_queue_id> = eq.id)
			GROUP BY	eq.id
			ORDER BY	ISNULL(eq.delivered_datetime) ASC,
						eq.delivered_datetime ASC
		", array(
			'minimum_age_days' => $iMinimumAge,
			'email_queue_id' => $iQueueId
		));
		$iTotalQueues = 0;
		$iTotalEmails = 0;
		$iTotalAttachments = 0;
		$iEligibleQueues = 0;
		$iEligibleEmails = 0;
		$iEligibleAttachments = 0;
		while ($aEmailQueueSummary = $oSummaryResult->fetch_assoc()) {
			$iTotalQueues++;
			$iTotalEmails += $aEmailQueueSummary['email_count'];
			$iTotalAttachments += $aEmailQueueSummary['email_attachment_count'];
			if ($aEmailQueueSummary['cleanup_eligible']) {
				$iEligibleQueues++;
				$iEligibleEmails += $aEmailQueueSummary['email_count'];
				$iEligibleAttachments += $aEmailQueueSummary['email_attachment_count'];
				Log::get()->log("  [-] #{$aEmailQueueSummary['id']} (dispatched: ".var_export($aEmailQueueSummary['delivered_datetime'], true)."; emails: {$aEmailQueueSummary['email_count']}; attachments: {$aEmailQueueSummary['email_attachment_count']})");
			} else {
				Log::get()->log("  [~] #{$aEmailQueueSummary['id']} (dispatched: ".var_export($aEmailQueueSummary['delivered_datetime'], true)."; emails: {$aEmailQueueSummary['email_count']}; attachments: {$aEmailQueueSummary['email_attachment_count']})");
			}
		}

		Log::get()->log("[+] Intialising DB Transaction");
		DataAccess::getDataAccess()->TransactionStart(false);
		try {
			Log::get()->log("[-] Removing {$iEligibleQueues}/{$iTotalQueues} Email Queues (emails: {$iEligibleEmails}/{$iTotalEmails}; attachments: {$iEligibleAttachments}/{$iTotalAttachments})");
			if (!$bTestRun || self::CLEANUP_TEST_SUMMARY_ONLY === false) {
				// Perform cleanup/delete (if we're not in test mode, or test mode is transaction-rollback-based)
				// NOTE: This currently needs to be done in 2 steps, otherwise the ON DELETE RESRICT on email.email_queue_id fails
				Query::run("
					DELETE	ea,
							e
					FROM	email_queue eq
							LEFT JOIN email e ON (e.email_queue_id = eq.id)
							LEFT JOIN email_attachment ea ON (ea.email_id = e.id)
					WHERE	eq.delivered_datetime IS NOT NULL
							AND CAST(eq.delivered_datetime AS DATE) <= CURDATE() - INTERVAL <minimum_age_days> DAY
				", array(
					'minimum_age_days' => $iMinimumAge
				));
				Query::run("
					DELETE	eq
					FROM	email_queue eq
					WHERE	eq.delivered_datetime IS NOT NULL
							AND CAST(eq.delivered_datetime AS DATE) <= CURDATE() - INTERVAL <minimum_age_days> DAY
				", array(
					'minimum_age_days' => $iMinimumAge
				));

				// Count remaining queues, emails, and attachments
				$aPostDeleteSummary = Query::run("
					SELECT		COUNT(DISTINCT eq.id) AS email_queue_count,
								COUNT(DISTINCT e.id) AS email_count,
								COUNT(ea.id) AS email_attachment_count
					FROM		email_queue eq
								LEFT JOIN email e ON (e.email_queue_id = eq.id)
								LEFT JOIN email_attachment ea ON (ea.email_id = e.id)
				")->fetch_assoc();
				Log::get()->log("[*] Post-DELETE Summary");
				Log::get()->log("  [*] Email Queues: {$aPostDeleteSummary['email_queue_count']}");
				Log::get()->log("  [*] Emails: {$aPostDeleteSummary['email_count']}");
				Log::get()->log("  [*] Email Attachments: {$aPostDeleteSummary['email_attachment_count']}");
			}
			if ($bTestRun) {
				throw new Exception("TEST MODE");
			}
		} catch (Exception $oException) {
			// Rollback & re-throw
			Log::get()->log("[!] Rolling back DB Transaction (Exception: ".$oException->getMessage().")");
			DataAccess::getDataAccess()->TransactionRollback(false);
			throw $oException;
		}

		// Commit changes
		Log::get()->log("[+] Committing DB Transaction");
		DataAccess::getDataAccess()->TransactionCommit(false);
	}

	function getCommandLineArguments() {
		return array(

			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "for testing script outcome [fully functional EXCEPT only 1 email from each queue will be sent and all database changes rolled back]",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			),

			self::SWITCH_FLUSH => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "Flushes email queues pending dispatch",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			),

			self::SWITCH_QUEUE_ID => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "id of the specific email_queue to deliver/cleanup",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validInteger("%1$s");'
			),

			self::SWITCH_DEBUG_EMAIL_ID => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "id of a specific email to deliver to a given email address (-e & -t also required)",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validInteger("%1$s");'
			),

			self::SWITCH_DEBUG_EMAIL_ADDRESS => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "an email address to redirect all of the sent emails to [optional, effective only if -t option supplied]",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validString("%1$s");'
			),

			self::SWITCH_CLEANUP => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "Cleans up (i.e. deletes) old email queues (and their emails + attachments)",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			),

			self::SWITCH_CLEANUP_AGE => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "Minimum number of days since delivery for an email queue to be cleaned up",
				self::ARG_DEFAULT => self::CLEANUP_AGE_DEFAULT,
				self::ARG_VALIDATION => 'Cli::_validInteger("%1$s");'
			)
		);
	}
}