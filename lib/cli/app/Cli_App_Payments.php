<?php
class Cli_App_Payments extends Cli {
	const SWITCH_TEST_RUN = 't';
	const SWITCH_MODE = 'm';
	const SWITCH_PAYMENT_ID = 'p';
	const SWITCH_FILE_IMPORT_ID = 'f';
	const SWITCH_FILE_IMPORT_DATA_ID = 'd';
	const SWITCH_LIMIT = 'x';
	const SWITCH_FILE_EXPORT = 'e';

	const MODE_PREPROCESS = 'PREPROCESS';
	const MODE_PROCESS = 'PROCESS';
	const MODE_DISTRIBUTE = 'DISTRIBUTE';
	const MODE_EXPORT = 'EXPORT';
	const MODE_DIRECT_DEBIT = 'DIRECTDEBIT';
	const MODE_DEBUG = 'DEBUG';
	const MODE_REVERSE = 'REVERSE';
	const MODE_REVERSE_EXPORT = 'REVERSEEXPORTFILE';

	const DIRECT_DEBIT_INELIGIBLE_BANK_ACCOUNT = 'Invalid Bank Account reference';
	const DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD = 'Invalid Credit Card reference';
	const DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD_EXPIRY = 'Credit card has expired';
	const DIRECT_DEBIT_INELIGIBLE_AMOUNT = 'Overdue balance is too small';
	const DIRECT_DEBIT_INELIGIBLE_RETRY = 'Invoice Run has already been Direct Debited';

	function run() {
		try {
			// The arguments are present and in a valid format if we get past this point.
			$this->_aArgs = $this->getValidatedArguments();

			$sMode = '_'.strtolower($this->_aArgs[self::SWITCH_MODE]);
			if (!method_exists($this, $sMode)) {
				throw new Exception("Invalid Mode '{$sMode}'");
			} else {
				$oDataAccess = DataAccess::getDataAccess();

				// TEST MODE: Start Transaction
				if ($this->_aArgs[self::SWITCH_TEST_RUN] && !$oDataAccess->TransactionStart()) {
					throw new Exception_Database($oDataAccess->Error());
				}

				try {
					// Call the approrite MODE method
					$this->$sMode();

					// TEST MODE: Force Rollback
					if ($this->_aArgs[self::SWITCH_TEST_RUN]) {
						throw new Exception("TEST MODE");
					}
				} catch (Exception $oException) {
					// TEST MODE: Rollback
					if ($this->_aArgs[self::SWITCH_TEST_RUN]) {
						$oDataAccess->TransactionRollback();
					}
					throw $oException;
				}

				// TEST MODE: Commit
				if ($this->_aArgs[self::SWITCH_TEST_RUN]) {
					$oDataAccess->TransactionCommit();
				}
			}
		} catch (Exception $oException) {
			echo "\n".$oException."\n";
			return 1;
		}
	}

	protected function _preprocess() {
		// Ensure that Payment Import isn't already running, then identify that it is now running
		Flex_Process::factory(Flex_Process::PROCESS_PAYMENTS_PREPROCESS)->lock();

		// Optional FileImport.Id parameter
		$iFileImportId = $this->_aArgs[self::SWITCH_FILE_IMPORT_ID];
		if ($iFileImportId && ($oFileImport = File_Import::getForId($iFileImportId))) {
			if ($oFileImport->Status !== FILE_COLLECTED) {
				throw new Exception("Only Files with Status FILE_COLLECTED (".FILE_COLLECTED.") can be pre-Processed");
			}

			// Make sure that we have a Carrier Module defined to process this File
			Carrier_Module::getForDefinition(MODULE_TYPE_NORMALISATION_PAYMENT, $oFileImport->FileType, $oFileImport->Carrier);
		}

		// Optional Limit Parameter
		$iLimit = (isset($this->_aArgs[self::SWITCH_LIMIT]) ? (int)$this->_aArgs[self::SWITCH_LIMIT] : null);

		// Process the Files
		try {
			Resource_Type_File_Import_Payment::preProcessFiles($iFileImportId, $iLimit);
		} catch (Exception $oException) {
			// TODO: Transaction if testing
			throw $oException;
		}
	}

	protected function _process() {
		// Ensure that Payment Normalisation isn't already running, then identify that it is now running
		Flex_Process::factory(Flex_Process::PROCESS_PAYMENTS_PROCESS)->lock();

		// Optional file_import_data.id parameter
		$iFileImportDataId = $this->_aArgs[self::SWITCH_FILE_IMPORT_DATA_ID];
		if ($iFileImportDataId && ($oFileImportData = File_Import_Data::getForId($iFileImportDataId))) {
			if ($oFileImportData->file_import_data_status_id !== FILE_IMPORT_DATA_STATUS_IMPORTED) {
				throw new Exception("Only File Data with Status FILE_IMPORT_DATA_STATUS_IMPORTED (".FILE_IMPORT_DATA_STATUS_IMPORTED.") can be Normalised");
			}

			// Make sure that we have a Carrier Module defined to process this File
			Carrier_Module::getForDefinition(MODULE_TYPE_NORMALISATION_PAYMENT, $oFileImport->FileType, $oFileImport->Carrier);
		}

		// Optional Limit Parameter
		$iLimit = (isset($this->_aArgs[self::SWITCH_LIMIT]) ? (int)$this->_aArgs[self::SWITCH_LIMIT] : null);

		// Process the Records
		try {
			Resource_Type_File_Import_Payment::processRecords($iFileImportDataId, $iLimit);
		} catch (Exception $oException) {
			// TODO: Transaction if testing
			throw $oException;
		}
	}

	protected function _debug() {
		// Mandatory file_import_data.id parameter
		$iFileImportDataId = $this->_aArgs[self::SWITCH_FILE_IMPORT_DATA_ID];
		$oFileImportData = File_Import_Data::getForId($iFileImportDataId);

		// Process the record
		DataAccess::getDataAccess()->TransactionStart(false);
		try {
			// Prepare the Importer
			$oFileImport = File_Import::getForId($oFileImportData->file_import_id);
			$oCarrierModule = reset(Carrier_Module::getForDefinition(MODULE_TYPE_NORMALISATION_PAYMENT, $oFileImport->FileType, $oFileImport->Carrier));
			$sImporterClass = $oCarrierModule->Module;
			Flex::assert(
				is_subclass_of($sImporterClass, Resource_Type_File_Import_Payment),
				"Carrier Module #{$oCarrierModule->Id}'s Class '{$sImporterClass}' does not inherit from ".Resource_Type_File_Import_Payment,
				array(
					'oFileImportData' => $oFileImportData->toArray(),
					'oCarrierModule' => $oCarrierModule->toArray()
				),
				"Payment Processing: Carrier Module with Invalid Class"
			);
			$oImporter = new $sImporterClass($oCarrierModule, $oFileImport);

			Log::getLog()->log("Data String: \n".$oFileImportData->data."\n");

			// Process the Record
			$aData = $oImporter->processRecord($oFileImportData->data);
			if ($aData === null) {
				Log::getLog()->log("Unimportant Record.  No Payment Response would be created.");
			} else {
				Log::getLog()->logIf($aData['oRawRecord'], "Raw Record: \n".print_r($aData['oRawRecord']->toArray(), true));
				Log::getLog()->logIf($aData['oPaymentResponse'], "Payment Response: \n".print_r($aData['oPaymentResponse']->toStdClass(), true));

				if ($aData['aTransactionData']) {
					Log::getLog()->log("Transaction Data: \n");
					foreach ($aData['aTransactionData'] as $oTransactionData) {
						Log::getLog()->log(print_r($oTransactionData->toStdClass(), true));
					}
				}
			}
		} catch (Exception $oException) {
			DataAccess::getDataAccess()->TransactionRollback(false);
			throw $oException;
		}
		DataAccess::getDataAccess()->TransactionRollback(false);

		return;
	}

	protected function _distribute() {
		// Optional Payment.Id parameter
		$iPaymentId = $this->_aArgs[self::SWITCH_PAYMENT_ID];
		if ($iPaymentId === null) {
			// Ensure that Payment Processing isn't already running, then identify that it is now running
			Flex_Process::factory(Flex_Process::PROCESS_PAYMENTS_DISTRIBUTE)->lock();
		}

		if ($iPaymentId && ($oPayment = Payment::getForId($iPaymentId))) {
			Log::getLog()->log("Applying Payment #{$iPaymentId}");
			Logic_Payment::distributeAll(array((int)$iPaymentId));
			return;
		}

		// Apply the Payments
		Logic_Payment::distributeAll();
	}

	protected function _export() {
		// Ensure that Payment Export isn't already running, then identify that it is now running
		Flex_Process::factory(Flex_Process::PROCESS_PAYMENTS_EXPORT)->lock();

		$oEmailQueue = Email_Flex_Queue::get();
		$oEmailQueue->setDebugAddress('ybs-admin@ybs.net.au');

		try {
			$bTestRun = $this->_aArgs[self::SWITCH_TEST_RUN];
			if ($bTestRun) {
				Log::getLog()->log("** TEST MODE **");
				Log::getLog()->log("The exported files will NOT be delivered, instead will be emailed to ybs-admin@ybs.net.au");

				// Enable file delivery testing (this will force emailling of all files to ybs-admin@ybs.net.au)
				Resource_Type_File_Deliver::enableTestMode();

				$oDataAccess = DataAccess::getDataAccess();
				if ($oDataAccess->TransactionStart() === false) {
					throw new Exception("Failed to START db transaction");
				}
				Log::getLog()->log("Transaction started");
			}

			Resource_Type_File_Export_Payment::exportDirectDebits();

			if ($bTestRun) {
				if ($oDataAccess->TransactionRollback() === false) {
					throw new Exception("Failed to ROLLBACK db transaction");
				}
				Log::getLog()->log("Transaction rolled back");
			} else {
				$oEmailQueue->commit();
			}

			// Send emails in the queue
			$oEmailQueue->send();
		} catch (Exception $oException) {
			throw new Exception("Failed to export. ".$oException->getMessage());
		}
	}

	protected function _reverse() {
		$oPayment = Payment::getForId($this->_aArgs[self::SWITCH_PAYMENT_ID]);

		if ($oPayment->getReversal()) {
			throw new Exception("Payment #{$oPayment->id} has already been reversed");
		} else {
			Log::getLog()->log("Reversing Payment #{$oPayment->id}...");
			$oPayment->reverse(Payment_Reversal_Reason::getForSystemName('AGENT_REVERSAL'));
			Log::getLog()->log("Successful!");
		}
	}

	private function _reverseExportFile() {
		$bTestRun = $this->_aArgs[self::SWITCH_TEST_RUN];
		$mFileExport = $this->_aArgs[self::SWITCH_FILE_EXPORT];

		$oDB = DataAccess::get();

		// Search for the file to reverse
		$oFileExportResults = $oDB->query('
			SELECT fe.*,
				COUNT(pr.id) AS payment_request_count
			FROM FileExport fe
				JOIN payment_request pr ON (pr.file_export_id = fe.Id)
			WHERE fe.FileName = <filename>
				OR fe.Id = <id>
			GROUP BY fe.Id
		', array(
			'filename' => (string)$mFileExport,
			'id' => preg_match('/^\s*(\d+)\s*$/', $mFileExport) ? (int)$mFileExport : null
		));
		$aFileExports = array();
		while ($aFileExport = $oFileExportResults->fetch_assoc()) {
			$aFileExports []= $aFileExport;
		}

		Log::get()->log(sprintf('Found %d files matching: %s', $oFileExportResults->row_count, $sFileName));
		if ($oFileExportResults->row_count > 1) {
			// Multiple matches: bail out
			Log::get()->log('There were multiple Payment Export files matching the supplied filename. Flex currently can\'t reverse Payment Export files with non-unique filenames. Please retry, supplying the Id from the file you want to reverse instead of the file name.');
			foreach ($aFileExports as $aFileExport) {
				Log::get()->log(var_export($aFileExport, true));
			}
			exit(1);
 		}
 		if ($oFileExportResults->row_count == 0) {
 			// No matches: bail out
 			Log::get()->log('There were no Payment Export files matching the supplied filename. This could mean that the payments have already been reversed or the file was never exported.');
 			exit(1);
 		}

 		$aFileExport = $aFileExports[0];
 		Log::get()->log(sprintf('Found %s with %d Payment Requests', $sFileName, $aFileExport['payment_request_count']));
 		Log::get()->log(var_export($aFileExport, true));

		// Check for Responses to Payment Requests (there should be none)
		Log::get()->log('Checking for Responses to Payment Requests (there should be none)…');
		$oPaymentResponsesResult = $oDB->query('
			SELECT prq.*,
				prs.created_datetime AS response_datetime
			FROM payment_request prq
				JOIN payment_response prs ON (prs.payment_id = prq.payment_id)
			WHERE prq.file_export_id = <file_export_id>
		', array(
			'file_export_id' => $aFileExport['Id']
		));
		if ($oPaymentResponsesResult->row_count) {
			// Payment Responses encountered: bail out
 			Log::get()->log('There were one or more Payment Responses found for the requests made in the supplied file. This file cannot be reversed.');
 			exit(1);
		}

 		$oDB->TransactionStart(false);
 		try {
 			// Update Payment Requests that where exported in this file
 			Log::get()->log('Updating Payment Requests that where exported in this file…');
 			$oPaymentRequestsUpdateResult = $oDB->query("
 				UPDATE payment_request prq
 				SET prq.file_export_id = NULL,
 					prq.payment_request_status_id = (SELECT id FROM payment_request_status WHERE system_name = 'PENDING')
 				WHERE prq.file_export_id = <file_export_id>
 			", array(
				'file_export_id' => $aFileExport['Id']
			));

			// Update Payment Requests that where exported in this file
 			Log::get()->log('Deleting File Export record for this file…');
 			$oDB->query("
 				DELETE FROM FileExport
 				WHERE Id = <file_export_id>
 			", array(
				'file_export_id' => $aFileExport['Id']
			));

 			if ($bTestRun) {
 				throw new Exception('Test Run');
 			}
 			throw new Exception('DEBUG: not ready for production!');

 			$oDB->TransactionCommit(false);

 			Log::get()->log(sprintf('Payment Export File #%d: %s reversed. %d previously exported Payment Requests now pending re-export.', $aFileExport['Id'], $aFileExport['FileName'], $oPaymentRequestsUpdateResult->num_rows));
 		} catch (Exception $oException) {
 			Log::get()->log(sprintf('ERROR: %s, rolling back transaction…', $oException->getMessage()));
 			$oDB->TransactionRollback(false);
 			throw $oException;
 		}

 		return;
	}

	const PROCPIPE_STDIN = 0;
	const PROCPIPE_STDOUT = 1;
	const PROCPIPE_STDERR = 2;
	public static function reduceFlattenReadPipes($aPipes, $aProcess) {
		if (isset($aProcess['aPipes'][self::PROCPIPE_STDOUT])) {
			$aPipes []= $aProcess['aPipes'][self::PROCPIPE_STDOUT];
		}
		if (isset($aProcess['aPipes'][self::PROCPIPE_STDERR])) {
			$aPipes []= $aProcess['aPipes'][self::PROCPIPE_STDERR];
		}
		return $aPipes;
	}

	const PARALLEL_PROCESS_DEFAULT = 1;
	const EXEC_STREAM_READ_CHUNK_SIZE = 8192;
	private static function _execParallel(array $aCommands, $iProcesses=self::PARALLEL_PROCESS_DEFAULT) {
		$aProcesses = array();
		$aResults = array();

		// NOTE: We reverse the "commands" array then pop off the end so that we can preserve keys to map output to input
		// This is not possible if we shift off the start of an array, because it reindexes
		$aCommands = array_reverse($aCommands, true);
		while (count($aCommands) || count($aProcesses)) {
			while (count($aCommands) && count($aProcesses) < $iProcesses) {
				$sCommand = end($aCommands);
				$mCommandKey = key($aCommands);
				array_pop($aCommands);

				// Execute command
				Log::get()->log('Executing: ' . $sCommand);
				$aPipes = null;
				$rProcess = proc_open($sCommand, array(
					self::PROCPIPE_STDIN => array('pipe', 'r'), // STDIN
					self::PROCPIPE_STDOUT => array('pipe', 'w'), // STDOUT
					self::PROCPIPE_STDERR => array('pipe', 'w') // STDERR
				), $aPipes);

				// Set STD* pipes to non-blocking
				stream_set_blocking($aPipes[self::PROCPIPE_STDIN], 0);
				stream_set_blocking($aPipes[self::PROCPIPE_STDOUT], 0);
				stream_set_blocking($aPipes[self::PROCPIPE_STDERR], 0);

				$aProcesses []= array(
					'rProcess' => $rProcess,
					'aPipes' => $aPipes,
					'sOutput' => '',
					'sError' => '',
					'sCommand' => $sCommand,
					'mCommandKey' => $mCommandKey
				);
			}

			// Wait until there is activity on one of our pipes
			//Log::get()->log('Commands: ' . count($aCommands));
			//Log::get()->log('Processes: ' . count($aProcesses));
			$aReadStreams = array_reduce($aProcesses, 'self::reduceFlattenReadPipes', array());
			$aWriteStreams = null;
			$aExceptStreams = null;

			//Log::get()->log('Waiting on processes...');
			if (false === @stream_select($aReadStreams, $aWriteStreams, $aExceptStreams, null)) {
				// Error
				throw new Exception('Error waiting for stream activity: ' . $php_errormsg);
			}

			// Activity: $aReadStreams contains pipes that have activity
			$aTerminatedProcesses = array();
			//Log::get()->log(print_r($aReadStreams, true));
			foreach ($aReadStreams as $rUpdatedStream) {
				// Read from the pipe & find its process
				$sRead = fread($rUpdatedStream, self::EXEC_STREAM_READ_CHUNK_SIZE);
				$bEOF = feof($rUpdatedStream);
				//Log::get()->log("Event on ({$rUpdatedStream}): " . var_export($sRead, true));
				foreach ($aProcesses as $mProcessKey=>&$aProcess) {
					if ($iPipe = array_search($rUpdatedStream, $aProcess['aPipes'], true)) {
						$sPipeDescription = null;
						switch ($iPipe) {
							case self::PROCPIPE_STDOUT:
								$sPipeDescription = 'STDOUT';
								$aProcess['sOutput'] .= $sRead;
								break;

							case self::PROCPIPE_STDERR:
								$sPipeDescription = 'STDERR';
								$aProcess['sError'] .= $sRead;
								break;
						}

						//Log::get()->log("{$sPipeDescription} for ({$aProcess['sCommand']}): " . var_export($sRead, true));

						if ($bEOF) {
							//Log::get()->log("{$sPipeDescription} for ({$aProcess['sCommand']}): EOF");
							unset($aProcess['aPipes'][$iPipe]);
							$aTerminatedProcesses []= $mProcessKey;
						}
					}
				}
				unset($aProcess);
			}

			// Check if processes are terminated
			foreach (array_unique($aTerminatedProcesses) as $mProcessKey) {
				$aProcess = $aProcesses[$mProcessKey];
				if (count($aProcess['aPipes']) === 1) {
					$aStatus = proc_get_status($aProcess['rProcess']);
					//Log::get()->log("  [{$aProcess['mCommandKey']}]: " . print_r($aStatus, true));
					if (!$aStatus['running']) {
						$aResults[$aProcess['mCommandKey']] = array(
							'sCommand' => $aProcess['sCommand'],
							'iExitCode' => $aStatus['exitcode'],
							'sOutput' => $aProcess['sOutput'],
							'sError' => $aProcess['sError']
						);

						Log::get()->log("Terminated ({$aStatus['exitcode']}): {$aProcess['sCommand']}");
						if ($aStatus['exitcode'] > 0) {
							Log::get()->log('  Output: ' . preg_replace('/\n/', "\n    ", $aProcess['sOutput']));
							Log::get()->log('  Error: ' . preg_replace('/\n/', "\n    ", $aProcess['sError']));
						}

						proc_close($aProcess['rProcess']);
						unset($aProcesses[$mProcessKey]);
					}
				}
			}
		}

		return $aResults;
	}

	const PARALLEL_PROCESS_DISTRIBUTION = 1;
	const DISTRIBUTION_ATTEMPTS_MAXIMUM = 4;
	private static function _distributePayments($aPayments) {
		Log::get()->log(sprintf('Distributing %d Payments...', count($aPayments)));

		// Prepare commands
		$aCommands = array();
		foreach ($aPayments as $iPaymentId) {
			$aCommands[$iPaymentId] = sprintf('php %s/cli/payments.php -m %s -p %d -v',
				Flex::getBase(),
				self::MODE_DISTRIBUTE,
				$iPaymentId
			);
		}

		// Attempt to distribute up to DISTRIBUTION_ATTEMPTS_MAXIMUM times
		$iAttempts = 0;
		while (count($aCommands) && $iAttempts < self::DISTRIBUTION_ATTEMPTS_MAXIMUM) {
			$aResults = self::_execParallel($aCommands, self::PARALLEL_PROCESS_DISTRIBUTION);

			$iAttempts++;
			$aCommands = array();
			foreach ($aResults as $iPaymentId=>$aResult) {
				if ($aResult['iExitCode'] > 0) {
					// Error: retry
					$aCommands[$iPaymentId] = $aResult['sCommand'];
				}
			}
		}
	}

	protected function _directdebit() {
		// The arguments are present and in a valid format if we get past this point.
		$aArgs = $this->getValidatedArguments();
		$bTestRun = (bool)$aArgs[self::SWITCH_TEST_RUN];

		$oEmailQueue = Email_Flex_Queue::get();
		$oEmailQueue->setDebugAddress('ybs-admin@ybs.net.au');

		if ($bTestRun) {
			// In test mode, start a db transaction
			$oDataAccess = DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false) {
				throw new Exception_Database("Failed to start db transaction");
			}
			Log::getLog()->log("Running in Test Mode, transaction started");
		}

		try {
			// Determine if this should be run
			if (Collections_Schedule::getEligibility(null, true)) {
				$iTimestamp = DataAccess::getDataAccess()->getNow(true);

				// Execute subprocesses
				// NOTE: Run Promises first, because there are much fewer of them
				$aPromiseDirectDebitSummary = $this->_runPromiseInstalmentDirectDebits($iTimestamp);
				//Log::getLog()->log("Promise Direct Debit Summary Data: ".print_r($aPromiseDirectDebitSummary, true));

				$aInvoiceDirectDebitSummary = $this->_runBalanceDirectDebits($iTimestamp);
				//Log::getLog()->log("Invoice Direct Debit Summary Data: ".print_r($aInvoiceDirectDebitSummary, true));

				// Combine summary data
				$aSummaryData = array(
					'iTimestamp' => $iTimestamp,
					'aCustomerGroups' => array()
				);
				$aCustomerGroups = Customer_Group::getAll();
				foreach ($aCustomerGroups as $iCustomerGroup=>$aData) {
					$aSummaryData['aCustomerGroups'][$iCustomerGroup] = array(
						'iInvoiceDirectDebitCount' => (isset($aInvoiceDirectDebitSummary['aCustomerGroups'][$iCustomerGroup])) ? $aInvoiceDirectDebitSummary['aCustomerGroups'][$iCustomerGroup]['iCount'] : 0,
						'fInvoiceDirectDebitValue' => (isset($aInvoiceDirectDebitSummary['aCustomerGroups'][$iCustomerGroup])) ? $aInvoiceDirectDebitSummary['aCustomerGroups'][$iCustomerGroup]['fValue'] : 0.0,
						'iPromiseDirectDebitCount' => (isset($aPromiseDirectDebitSummary['aCustomerGroups'][$iCustomerGroup])) ? $aPromiseDirectDebitSummary['aCustomerGroups'][$iCustomerGroup]['iCount'] : 0,
						'fPromiseDirectDebitValue' => (isset($aPromiseDirectDebitSummary['aCustomerGroups'][$iCustomerGroup])) ? $aPromiseDirectDebitSummary['aCustomerGroups'][$iCustomerGroup]['fValue'] : 0.0
					);
				}

				//Log::getLog()->log("Building Summary Emails with data: ".print_r($aSummaryData, true));

				// Summary Email
				$oDirectDebitSummaryEmail = Email_Notification::getForSystemName('DIRECT_DEBIT_REPORT');
				$oDirectDebitSummaryEmail->subject = 'Direct Debit Summary Report from '.date('d/m/Y H:i', $iTimestamp);
				$oDirectDebitSummaryEmail->html = self::_buildDirectDebitSummaryEmailHTMLContent($aSummaryData);
				$oDirectDebitSummaryEmail->text = self::_buildDirectDebitSummaryEmailTextContent($aSummaryData);
				$oEmailQueue->push($oDirectDebitSummaryEmail);
			} else {
				Log::getLog()->log("Direct debits cannot be processed today, check collections_schedule for more info.");
			}

			if ($bTestRun) {
				// In test mode, rollback all changes
				if ($oDataAccess->TransactionRollback() === false) {
					throw new Exception_Database("Failed to rollback db transaction");
				}
				Log::getLog()->log("Running in Test Mode, Transaction rolled back");
			} else {
				$oEmailQueue->commit();
			}

			// Flush the Email Queue
			$oEmailQueue->send();

			// Distribute Payments
			if (!$bTestRun) {
				self::_distributePayments(array_merge($aPromiseDirectDebitSummary['aPayments'], $aInvoiceDirectDebitSummary['aPayments']));
			}
		} catch (Exception $oEx) {
			if ($bTestRun) {
				// In test mode, rollback transaction
				if ($oDataAccess->TransactionRollback() === false) {
					throw new Exception_Database("Failed to rollback db transaction");
				}
				Log::getLog()->log("Transaction rolled back due to exception (in Test Mode)");
			}
			throw $oEx;
		}
	}

	private function _runBalanceDirectDebits($iTimestamp) {
		Log::getLog()->log("");
		Log::getLog()->log("Overdue Balance Direct Debits");
		Log::getLog()->log("");

		// Eligible for direct debits today, list the invoices that are eligible
		$oCollectionsConfig = Collections_Config::get();
		if (!$oCollectionsConfig || $oCollectionsConfig->direct_debit_due_date_offset === null) {
			throw new Exception("There is no direct debit due date offset configured in collections_config.");
		}

		$iTimestamp = ($iTimestamp) ? $iTimestamp : DataAccess::getDataAccess()->getNow(true);

		// Get Account records
		Log::getLog()->log("Getting accounts that are eligible");
		$mResult = Query::run("
			SELECT i.Id AS invoice_id,
						a.Id AS account_id,
						pt.direct_debit_minimum AS direct_debit_minimum,
						bt.description AS billing_type_description

			FROM Account a
						JOIN Invoice i ON (
							i.Account = a.Id
							/* Must be the latest non-Temporary Invoice */
							AND i.Id = (
								SELECT Id
								FROM Invoice
								WHERE Account = a.Id
											AND Status != ".INVOICE_TEMP."
											AND <effective_date> >= DueOn + INTERVAL <direct_debit_due_date_offset> DAY
								ORDER BY Id DESC
								LIMIT 1
							)
							/* Can't have been Direct Debited previously */
							AND ISNULL((
								SELECT invoice_id
								FROM payment_request_invoice pri
											JOIN payment_request pr ON (pr.id = pri.payment_request_id)
								WHERE invoice_id = i.Id
											AND pr.payment_request_status_id != ".PAYMENT_REQUEST_STATUS_CANCELLED."
								LIMIT 1
							))
						)
						JOIN payment_terms pt ON (
							pt.customer_group_id = a.CustomerGroup
							AND pt.id = (
								SELECT MAX(id)
								FROM payment_terms
								WHERE customer_group_id = a.CustomerGroup
							)
						)
						JOIN billing_type bt ON (
							bt.id = a.BillingType
							AND bt.id IN (".BILLING_TYPE_CREDIT_CARD.", ".BILLING_TYPE_DIRECT_DEBIT.")
						)

			WHERE a.Archived IN (".ACCOUNT_STATUS_ACTIVE.", ".ACCOUNT_STATUS_CLOSED.")
		", array(
			'effective_date' => date('Y-m-d', $iTimestamp),
			'direct_debit_due_date_offset' => (int)$oCollectionsConfig->direct_debit_due_date_offset
		));

		Log::getLog()->log("Got accounts");

		// Process each account
		$iAppliedCount = 0;
		$fAppliedValue = 0.0;
		$iDoubleUpsCount = 0;
		$aAccountsApplied = array();
		$sDatetime = date('Y-m-d H:i:s', $iTimestamp);
		$sPaidOn = date('Y-m-d', $iTimestamp);

		// Set up summary details per-Customer Group
		$aCustomerGroupSummary = array();
		$aCustomerGroups = Customer_Group::getAll();
		foreach ($aCustomerGroups as $iCustomerGroup=>$oCustomerGroup) {
			$aCustomerGroupSummary[$iCustomerGroup] = array(
				'iCount' => 0,
				'fValue' => 0.0
			);
		}

		// Arrays for recording error information
		$aIneligible = array(
			self::DIRECT_DEBIT_INELIGIBLE_BANK_ACCOUNT => 0,
			self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD => 0,
			self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD_EXPIRY => 0,
			self::DIRECT_DEBIT_INELIGIBLE_AMOUNT => 0,
			self::DIRECT_DEBIT_INELIGIBLE_RETRY => 0
		);

		$aAppliedAccountIds = array();
		$aPayments = array();
		while ($aRow = $mResult->fetch_assoc()) {
			// Check if the account has already been processed, potentially useless
			// but is here just to be sure that accounts aren't charged more than once
			$iAccountId = $aRow['account_id'];
			if (isset($aAccountsApplied[$iAccountId])) {
				$iDoubleUpsCount++;
				Log::getLog()->log("Already applied to account {$iAccountId}");
				continue;
			}
			$aAccountsApplied[$iAccountId] = true;

			$oAccount = Account::getForId($aRow['account_id']);

			// Check if this Invoice Run has been Direct Debited previously
			// This prevents us from constantly re-attempting to Direct Debit dishonoured payments
			// This *should* be handled by the SELECT query, but we'll leave it here just in case
			$oInvoice = Invoice::getForId($aRow['invoice_id']);
			if (Payment_Request::getForInvoice($oInvoice->id)) {
				Log::getLog()->log("ERROR: {$iAccountId} has already been Direct Debited for Invoice Run {$oInvoice->invoice_run_id}");
				$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_RETRY]++;
				continue;
			}

			// Offset Effective Date is today's date MINUS the Direct Debit Offset (as this offset is usually applied to the Due Date)
			// We then need to ADD 1 day, because we want people that are *Due* (but will not be overdue until tomorrow)
			$sOffsetEffectiveDate = date(
				'Y-m-d',
				strtotime(
					'+1 day', // We want due, not overdue
					strtotime(
						"-{$oCollectionsConfig->direct_debit_due_date_offset} days", // Apply offset
						$iTimestamp // Today
					)
				)
			);

			$fAmount = Rate::roundToCurrencyStandard($oAccount->getOverdueBalance($sOffsetEffectiveDate), 2);
			if ($fAmount < $aRow['direct_debit_minimum'] || $fAmount <= 0.0) {
				// Not enough of a balance to be eligible
				//Log::getLog()->log("ERROR: {$iAccountId} doesn't owe enough, ineligible amount: {$fAmount} (less than minimum, which is {$aRow['direct_debit_minimum']})");
				$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_AMOUNT]++;
				continue;
			}

			// Determine if the direct debit details are valid & the origin id (cc or bank account number) & payment type (for the payment)
			$oPaymentMethodDetail = $oAccount->getPaymentMethodDetails();
			$bDirectDebitable = false;
			$iPaymentType = null;
			$sOriginIdType = null;
			$mOriginId = null;
			switch($oAccount->BillingType) {
				case BILLING_TYPE_DIRECT_DEBIT:
					$iPaymentType = PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT;
					$oDirectDebit = DirectDebit::getForId($oAccount->DirectDebit);
					if ($oDirectDebit) {
						$bDirectDebitable = true;
						$sOriginIdType = Payment_Transaction_Data::BANK_ACCOUNT_NUMBER;
						$mOriginId = $oPaymentMethodDetail->AccountNumber;
					} else {
						// Ineligible due to invalid bank account
						Log::getLog()->log("ERROR: {$iAccountId} has an invalid bank account, id = {$oAccount->DirectDebit}");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_BANK_ACCOUNT]++;
					}
					break;
				case BILLING_TYPE_CREDIT_CARD:
					$iPaymentType = PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD;
					$oCreditCard = Credit_Card::getForId($oAccount->CreditCard);
					$sExpiry = "{$oCreditCard->ExpYear}-{$oCreditCard->ExpMonth}-01";
					$sCompareExpiry = "{$oCreditCard->ExpYear}-{$oCreditCard->ExpMonth}-01 + 1 month";
					$iExpiry = strtotime($sCompareExpiry);
					$iNow = time();
					if ($oCreditCard && ($iNow < $iExpiry)) {
						$bDirectDebitable = true;
						$sOriginIdType = Payment_Transaction_Data::CREDIT_CARD_NUMBER;
						$mOriginId = Credit_Card::getMaskedCardNumber(Decrypt($oPaymentMethodDetail->CardNumber));
					} else if ($iNow >= $iExpiry) {
						// Ineligible because credit card has expired
						Log::getLog()->log("ERROR: {$iAccountId} has an expired credit card: {$sExpiry} (".date('Y-m-d', strtotime($sCompareExpiry)).")");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD_EXPIRY]++;
					} else {
						// Ineligible due to invalid credit card
						Log::getLog()->log("ERROR: {$iAccountId} has an invalid credit card, id = {$oAccount->CreditCard}");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD]++;
					}
					break;
			}

			if ($bDirectDebitable) {
				DataAccess::getDataAccess()->TransactionStart(false);
				try {
					// Create Payment (using origin id, payment type, account & amount)
					$oPayment = Logic_Payment::factory(
						$iAccountId,
						$iPaymentType,
						$fAmount,
						PAYMENT_NATURE_PAYMENT,
						'',
						$sPaidOn,
						array(
							'aTransactionData' => array(
														$sOriginIdType => $mOriginId
													)
						),
						false
					);

					// Create payment_request (linked to the payment & invoice run id)
					$oPaymentRequest = Payment_Request::generatePending(
						$oAccount->Id, // Account id
						$iPaymentType, // Payment type
						$fAmount, // Amount
						$oInvoice->invoice_run_id, // Invoice run id
						Employee::SYSTEM_EMPLOYEE_ID, // Employee id
						$oPayment->id // Payment id
					);

					// Create payment_request_invoice
					$oPaymentRequestInvoice = new Payment_Request_Invoice();
					$oPaymentRequestInvoice->payment_request_id = $oPaymentRequest->id;
					$oPaymentRequestInvoice->invoice_id = $oInvoice->Id;
					$oPaymentRequestInvoice->save();

					// Update the payments transaction reference (this done separately because the transaction reference
					// is derived from the payment request)
					$oPayment->transaction_reference = $oPaymentRequest->generateTransactionReference();
					$oPayment->save();

					// Defer distribution to the end
					$aPayments []= $oPayment->id;
					$aAppliedAccountIds []= $oAccount->Id;
				} catch (Exception $oException) {
					DataAccess::getDataAccess()->TransactionRollback(false);
					throw $oException;
				}

				DataAccess::getDataAccess()->TransactionCommit(false);

				Log::getLog()->log("Account: {$oAccount->Id}, Payment: {$oPayment->id}, payment_request: {$oPaymentRequest->id}, Amount: {$fAmount}; Due: {$oInvoice->DueOn}");

				// Add to Customer Group Totals
				$aCustomerGroupSummary[$oAccount->CustomerGroup]['iCount']++;
				$aCustomerGroupSummary[$oAccount->CustomerGroup]['fValue'] = Rate::roundToCurrencyStandard($aCustomerGroupSummary[$oAccount->CustomerGroup]['fValue'] + $fAmount);

				// Add to General Totals
				$fAppliedValue = Rate::roundToCurrencyStandard($fAppliedValue + $fAmount);
				$iAppliedCount++;
			}
		}

		Log::getLog()->log("APPLIED: {$iAppliedCount}");
		Log::getLog()->log("INELIGIBLE: ".print_r($aIneligible, true));
		Log::getLog()->log("DOUBLE-UPS: {$iDoubleUpsCount} (This should always be zero)");

		return array(
			'iCount' => $iAppliedCount,
			'fValue' => $fAppliedValue,
			'aCustomerGroups' => $aCustomerGroupSummary,
			'aAccountsApplied' => $aAppliedAccountIds,
			'aPayments' => $aPayments
		);
	}

	private function _runPromiseInstalmentDirectDebits($iTimestamp=null) {
		Log::getLog()->log("");
		Log::getLog()->log("");
		Log::getLog()->log("Promise Instalment Direct Debits");
		Log::getLog()->log("");

		$iTimestamp = ($iTimestamp) ? $iTimestamp : DataAccess::getDataAccess()->getNow(true);

		// Eligible for direct debits today, list the instalments that are eligible
		$oCollectionsConfig = Collections_Config::get();
		if (!$oCollectionsConfig || $oCollectionsConfig->promise_direct_debit_due_date_offset === null) {
			throw new Exception("There is no promise direct debit due date offset configured in collections_config.");
		}

		$mResult = Query::run("
			SELECT cpi.id AS collection_promise_instalment_id,
						cp.account_id AS account_id,
						bt.description AS billing_type_description

			FROM collection_promise_instalment cpi
						JOIN collection_promise cp ON (
							cp.id = cpi.collection_promise_id
							AND cp.completed_datetime IS NULL
							AND cp.use_direct_debit = 1
						)
						JOIN Account a ON (a.Id = cp.account_id)
						JOIN billing_type bt ON (
							bt.id = a.BillingType
							AND bt.id IN (".BILLING_TYPE_CREDIT_CARD.", ".BILLING_TYPE_DIRECT_DEBIT.")
						)

			WHERE a.Archived IN (".ACCOUNT_STATUS_ACTIVE.", ".ACCOUNT_STATUS_CLOSED.")
						AND <effective_date> >= cpi.due_date + INTERVAL <promise_direct_debit_due_date_offset> DAY
						/* Instalment can't have been Direct Debited previously */
						AND ISNULL((
							SELECT prcpi.collection_promise_instalment_id
							FROM payment_request_collection_promise_instalment prcpi
										JOIN payment_request pr ON (pr.id = prcpi.payment_request_id)
							WHERE prcpi.collection_promise_instalment_id = cpi.id
										AND pr.payment_request_status_id != ".PAYMENT_REQUEST_STATUS_CANCELLED."
							LIMIT 1
						))

			LIMIT 50
		", array(
			'effective_date' => date('Y-m-d', $iTimestamp),
			'promise_direct_debit_due_date_offset' => (int)$oCollectionsConfig->promise_direct_debit_due_date_offset
		));

		// Process each instalment
		$iAppliedCount = 0;
		$fAppliedValue = 0.0;
		$sDatetime = date('Y-m-d H:i:s', $iTimestamp);
		$sPaidOn = date('Y-m-d', $iTimestamp);

		// Set up summary details per-Customer Group
		$aCustomerGroupSummary = array();
		$aCustomerGroups = Customer_Group::getAll();
		foreach ($aCustomerGroups as $iCustomerGroup=>$oCustomerGroup) {
			$aCustomerGroupSummary[$iCustomerGroup] = array(
				'iCount' => 0,
				'fValue' => 0.0
			);
		}

		// Arrays for recording error information
		$aIneligible = array(
							self::DIRECT_DEBIT_INELIGIBLE_BANK_ACCOUNT => 0,
							self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD => 0,
							self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD_EXPIRY => 0,
							self::DIRECT_DEBIT_INELIGIBLE_AMOUNT => 0
						);
		$aAppliedAccountIds = array();
		$aPayments = array();
		while ($aRow = $mResult->fetch_assoc()) {
			Log::getLog()->log("Collection Promise Instalment: {$aRow['collection_promise_instalment_id']}");
			$oInstalment = Collection_Promise_Instalment::getForId($aRow['collection_promise_instalment_id']);
			$oPayable = new Logic_Collection_Promise_Instalment($oInstalment);
			$fAmount = Rate::roundToCurrencyStandard($oPayable->getBalance(), 2);

			// Promise Instalment Direct Debits are not subject to the direct_debit_minimum
			// But they must be > $0.00
			if ($fAmount <= 0.0) {
				// Not enough of a balance to be eligible
				//Log::getLog()->log("ERROR: {$iAccountId} doesn't owe enough, ineligible amount: {$fAmount} (less than minimum, which is {$aRow['direct_debit_minimum']})");
				$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_AMOUNT]++;
				continue;
			}

			// Determine if the direct debit details are valid & the origin id (cc or bank account number) & payment type (for the payment)
			$oAccount = Account::getForId($aRow['account_id']);
			$oPaymentMethodDetail = $oAccount->getPaymentMethodDetails();
			$bDirectDebitable = false;
			$iPaymentType = null;
			$sOriginIdType = null;
			$mOriginId = null;
			switch($oAccount->BillingType) {
				case BILLING_TYPE_DIRECT_DEBIT:
					$iPaymentType = PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT;
					$oDirectDebit = DirectDebit::getForId($oAccount->DirectDebit);
					if ($oDirectDebit) {
						$bDirectDebitable = true;
						$sOriginIdType = Payment_Transaction_Data::BANK_ACCOUNT_NUMBER;
						$mOriginId = $oPaymentMethodDetail->AccountNumber;
					} else {
						// Ineligible due to invalid bank account
						Log::getLog()->log("ERROR: {$oAccount->Id} has an invalid bank account, id = {$oAccount->DirectDebit}");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_BANK_ACCOUNT]++;
					}
					break;
				case BILLING_TYPE_CREDIT_CARD:
					$iPaymentType = PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD;
					$oCreditCard = Credit_Card::getForId($oAccount->CreditCard);
					$sExpiry = "{$oCreditCard->ExpYear}-{$oCreditCard->ExpMonth}-01";
					$sCompareExpiry = "{$oCreditCard->ExpYear}-{$oCreditCard->ExpMonth}-01 + 1 month";
					$iExpiry = strtotime($sCompareExpiry);
					$iNow = time();
					if ($oCreditCard && ($iNow < $iExpiry)) {
						$bDirectDebitable = true;
						$sOriginIdType = Payment_Transaction_Data::CREDIT_CARD_NUMBER;
						$mOriginId = Credit_Card::getMaskedCardNumber(Decrypt($oPaymentMethodDetail->CardNumber));
					} else if ($iNow >= $iExpiry) {
						// Ineligible because credit card has expired
						Log::getLog()->log("ERROR: {$oAccount->Id} has an expired credit card: {$sExpiry} (".date('Y-m-d', strtotime($sCompareExpiry)).")");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD_EXPIRY]++;
					} else {
						// Ineligible due to invalid credit card
						Log::getLog()->log("ERROR: {$oAccount->Id} has an invalid credit card, id = {$oAccount->CreditCard}");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD]++;
					}
					break;
			}

			if ($bDirectDebitable) {
				DataAccess::getDataAccess()->TransactionStart(false);
				try {
					Log::getLog()->log("Is Direct Debitable - Creating Payment...");
					// Create Payment (using origin id, payment type, account & amount)
					$oPayment = Logic_Payment::factory(
									$oAccount->Id,
									$iPaymentType,
									$fAmount,
									PAYMENT_NATURE_PAYMENT,
									'',
									$sPaidOn,
									array(
										'aTransactionData' => array(
																	$sOriginIdType => $mOriginId
																)
									),
									false
								);

					Log::getLog()->log("Creating Payment Request...");
					// Create payment_request (linked to the payment & invoice run id)
					$oPaymentRequest = Payment_Request::generatePending(
												$oAccount->Id, // Account id
												$iPaymentType, // Payment type
												$fAmount, // Amount
												null, // Invoice run id
												Employee::SYSTEM_EMPLOYEE_ID, // Employee id
												$oPayment->id // Payment id
											);

					// Create payment_request_collection_promise_instalment
					$oPaymentRequestCollectionPromiseInstalment = new Payment_Request_Collection_Promise_Instalment();
					$oPaymentRequestCollectionPromiseInstalment->payment_request_id = $oPaymentRequest->id;
					$oPaymentRequestCollectionPromiseInstalment->collection_promise_instalment_id = $oInstalment->id;
					$oPaymentRequestCollectionPromiseInstalment->save();

					// Update the payments transaction reference (this done separately because the transaction reference
					// is derived from the payment request)
					$oPayment->transaction_reference = $oPaymentRequest->generateTransactionReference();
					$oPayment->save();

					// Defer distribtion to the end
					$aPayments []= $oPayment->id;
					$aAppliedAccountIds []= $oAccount->Id;
				} catch (Exception $oException) {
					DataAccess::getDataAccess()->TransactionRollback(false);
					throw $oException;
				}
				DataAccess::getDataAccess()->TransactionCommit(false);

				Log::getLog()->log("Complete (Account: {$oAccount->Id}, Payment: {$oPayment->id}, payment_request: {$oPaymentRequest->id}, Amount: {$fAmount}; Due: {$oInstalment->due_date})");

				// Add to Customer Group Totals
				$aCustomerGroupSummary[$oAccount->CustomerGroup]['iCount']++;
				$aCustomerGroupSummary[$oAccount->CustomerGroup]['fValue'] = Rate::roundToCurrencyStandard($aCustomerGroupSummary[$oAccount->CustomerGroup]['fValue'] + $fAmount);

				// Add to General Totals
				$fAppliedValue = Rate::roundToCurrencyStandard($fAppliedValue + $fAmount);
				$iAppliedCount++;
			} else {
				Log::getLog()->log("Is NOT Direct Debitable");
			}
			Log::getLog()->log("");
		}

		Log::getLog()->log("APPLIED: {$iAppliedCount}");
		Log::getLog()->log("INELIGIBLE: ".print_r($aIneligible, true));

		return array(
			'iCount' => $iAppliedCount,
			'fValue' => $fAppliedValue,
			'aCustomerGroups' => $aCustomerGroupSummary,
			'aAccountsApplied' => $aAppliedAccountIds,
			'aPayments' => $aPayments
		);
	}

	private static function _buildDirectDebitSummaryEmailHTMLContent($aData) {
		$D = new DOM_Factory();
		$oDOMDocument = $D->getDOMDocument();

		$oDOMDocument->formatOutput = true;

		// General Content
		$oContent = $D->div(
			$D->h1('Direct Debit Summary Report from '.date('d/m/Y H:i', $aData['iTimestamp'])),
			$D->table(
				$D->thead(
					$D->tr(
						$D->th(array('rowspan'=>2), 'Customer Group'),
						$D->th(array('colspan'=>2), 'Invoice Direct Debits'),
						$D->th(array('colspan'=>2), 'Promise Direct Debits')
					),
					$D->tr(
						$D->th($D->abbr(array('title'=>'Count'), '#')),
						$D->th($D->abbr(array('title'=>'Value'), '$')),
						$D->th($D->abbr(array('title'=>'Count'), '#')),
						$D->th($D->abbr(array('title'=>'Value'), '$'))
					)
				),
				$oTableBody = $D->tbody(),
				$D->tfoot(
					$D->tr(
						$D->th('Totals'),
						$oTotalInvoiceDirectDebitCount = $D->td(),
						$oTotalInvoiceDirectDebitValue = $D->td(),
						$oTotalPromiseDirectDebitCount = $D->td(),
						$oTotalPromiseDirectDebitValue = $D->td()
					)
				)
			),
			$D->div(array('class'=>'signature'),
				$D->p('Regards'),
				$D->p('Flexor')
			)
		);
		$oDOMDocument->appendChild($oContent);

		$iTotalInvoiceDirectDebitCount = 0;
		$fTotalInvoiceDirectDebitValue = 0.0;
		$iTotalPromiseDirectDebitCount = 0;
		$fTotalPromiseDirectDebitValue = 0.0;

		// Customer Group Details
		$aCustomerGroups = Customer_Group::getAll();
		foreach ($aCustomerGroups as $iCustomerGroup=>$oCustomerGroup) {
			// Data
			$iInvoiceDirectDebitCount = $aData['aCustomerGroups'][$iCustomerGroup]['iInvoiceDirectDebitCount'];
			$fInvoiceDirectDebitValue = $aData['aCustomerGroups'][$iCustomerGroup]['fInvoiceDirectDebitValue'];
			$iPromiseDirectDebitCount = $aData['aCustomerGroups'][$iCustomerGroup]['iPromiseDirectDebitCount'];
			$fPromiseDirectDebitValue = $aData['aCustomerGroups'][$iCustomerGroup]['fPromiseDirectDebitValue'];

			// Add a row for this Customer Group
			$oTableBody->appendChild(
				$D->tr(
					$D->td($oCustomerGroup->internal_name),
					$D->td($iInvoiceDirectDebitCount),
					$D->td('$'.number_format($fInvoiceDirectDebitValue, 2)),
					$D->td($iPromiseDirectDebitCount),
					$D->td('$'.number_format($fPromiseDirectDebitValue, 2))
				)
			);

			// Add to totals
			$iTotalInvoiceDirectDebitCount += $iInvoiceDirectDebitCount;
			$fTotalInvoiceDirectDebitValue += $fInvoiceDirectDebitValue;
			$iTotalPromiseDirectDebitCount += $iPromiseDirectDebitCount;
			$fTotalPromiseDirectDebitValue += $fPromiseDirectDebitValue;
		}

		// Totals
		$oTotalInvoiceDirectDebitCount->appendChild($D->getDOMDocument()->createTextNode($iTotalInvoiceDirectDebitCount));
		$oTotalInvoiceDirectDebitValue->appendChild($D->getDOMDocument()->createTextNode('$'.number_format($fTotalInvoiceDirectDebitValue, 2)));
		$oTotalPromiseDirectDebitCount->appendChild($D->getDOMDocument()->createTextNode($iTotalPromiseDirectDebitCount));
		$oTotalPromiseDirectDebitValue->appendChild($D->getDOMDocument()->createTextNode('$'.number_format($fTotalPromiseDirectDebitValue, 2)));

		//Log::getLog()->log($oDOMDocument->saveXML());

		// Configure Styles
		DOM_Style::style($oDOMDocument, array(
			'//*' => '
				font-family : "Helvetica Neue", Arial, sans-serif;
				color : #111;
			',
			'//table' => '
				border : 1px solid #333;
				border-collapse : collapse;
			',
			'//thead/tr/*|//tfoot/tr/*' => '
				background-color : #333;
				color : #eee;
				border : 0;
			',
			'//thead//*|//tfoot//*' => '
				color : #eee;
			',
			'//td|//th' => '
				vertical-align : top;
				padding : 0.2em 0.5em;
			',
			'//td' => '
				text-align : right;
			',
			'//tbody/tr/td[1]' => '
				text-align : left;
				font-weight : bold;
				padding-left : 0.2em;
				padding-right : 1em;
			',
			'//h1' => '
				font-size : 1.2em;
			',
			'//thead/tr[1]/th[not(1)]' => '
				width : 14em;
			',
			'//thead/tr[2]/th' => '
				width : 7em;
				background-color : #444;
			',
			'//tfoot/tr/*' => '
				font-weight : bold;
			',
			'//tfoot/tr/th[1]' => '
				text-align : left;
				padding-left : 0.2em
			',
			'//thead/tr[1]/th[1]' => '
				vertical-align : middle;
				padding-left : 1em;
				padding-right : 1em;
			',
			'//*[@class="signature"]/p[1]' => '
				margin-bottom : 0;
			',
			'//*[@class="signature"]/p[2]' => '
				margin-top : 0.2em;
				font-weight : bold;
			'
		));

		return "<!DOCTYPE html>\n".$oDOMDocument->saveXML($oContent);
	}

	private static function _buildDirectDebitSummaryEmailTextContent($aData) {
		$aLines = array();

		$aLines[] = 'Direct Debit Summary Report from '.date('d/m/Y H:i', $aData['iTimestamp']);
		$aLines[] = '';

		$iTotalInvoiceDirectDebitCount = 0;
		$fTotalInvoiceDirectDebitValue = 0.0;
		$iTotalPromiseDirectDebitCount = 0;
		$fTotalPromiseDirectDebitValue = 0.0;

		// Customer Group Details
		$aCustomerGroups = Customer_Group::getAll();
		foreach ($aCustomerGroups as $iCustomerGroup=>$oCustomerGroup) {
			// Data
			$iInvoiceDirectDebitCount = $aData['aCustomerGroups'][$iCustomerGroup]['iInvoiceDirectDebitCount'];
			$fInvoiceDirectDebitValue = $aData['aCustomerGroups'][$iCustomerGroup]['fInvoiceDirectDebitValue'];
			$iPromiseDirectDebitCount = $aData['aCustomerGroups'][$iCustomerGroup]['iPromiseDirectDebitCount'];
			$fPromiseDirectDebitValue = $aData['aCustomerGroups'][$iCustomerGroup]['fPromiseDirectDebitValue'];

			// Add a data set for this Customer Group
			$aLines[] = "\t".$oCustomerGroup->internal_name;
			$aLines[] = "\t\tInvoice Direct Debits #: ".$iInvoiceDirectDebitCount;
			$aLines[] = "\t\tInvoice Direct Debits $: ".'$'.number_format($fInvoiceDirectDebitValue, 2);
			$aLines[] = "\t\tPromise Direct Debits #: ".$iPromiseDirectDebitCount;
			$aLines[] = "\t\tPromise Direct Debits $".'$'.number_format($fPromiseDirectDebitValue, 2);

			// Add to totals
			$iTotalInvoiceDirectDebitCount += $iInvoiceDirectDebitCount;
			$fTotalInvoiceDirectDebitValue += $fInvoiceDirectDebitValue;
			$iTotalPromiseDirectDebitCount += $iPromiseDirectDebitCount;
			$fTotalPromiseDirectDebitValue += $fPromiseDirectDebitValue;
		}
		$aLines[] = '';

		// Totals
		$aLines[] = 'Total Invoice Direct Debits #: '.$iTotalInvoiceDirectDebitCount;
		$aLines[] = 'Total Invoice Direct Debits $: '.number_format($iTotalInvoiceDirectDebitCount, 2);
		$aLines[] = 'Total Promise Direct Debits #: '.$iTotalInvoiceDirectDebitCount;
		$aLines[] = 'Total Promise Direct Debits $: '.number_format($iTotalInvoiceDirectDebitCount, 2);

		// Signature
		$aLines[] = '';
		$aLines[] = 'Regards';
		$aLines[] = 'Flexor';

		return implode("\n", $aLines);
	}

	function getCommandLineArguments() {
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "No changes to the database.",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			),

			self::SWITCH_MODE => array(
				self::ARG_LABEL => "MODE",
				self::ARG_REQUIRED => TRUE,
				self::ARG_DESCRIPTION => "Payment operation to perform [".self::MODE_PREPROCESS."|".self::MODE_PROCESS."|".self::MODE_DISTRIBUTE."|".self::MODE_EXPORT."|".self::MODE_DIRECT_DEBIT."|".self::MODE_DEBUG."|".self::MODE_REVERSE."|".self::MODE_REVERSE_EXPORT."]",
				self::ARG_VALIDATION => 'Cli::_validInArray("%1$s", array("'.self::MODE_PREPROCESS.'","'.self::MODE_PROCESS.'","'.self::MODE_DISTRIBUTE.'","'.self::MODE_EXPORT.'","'.self::MODE_DIRECT_DEBIT.'","'.self::MODE_DEBUG.'","'.self::MODE_REVERSE.'","'.self::MODE_REVERSE_EXPORT.'"))'
			),

			self::SWITCH_PAYMENT_ID => array(
				self::ARG_REQUIRED => false,
				self::ARG_LABEL => "PAYMENT_ID",
				self::ARG_DESCRIPTION => "Payment Id (".self::MODE_DISTRIBUTE.", ".self::MODE_REVERSE." Modes only)",
				self::ARG_VALIDATION => 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_FILE_IMPORT_ID => array(
				self::ARG_REQUIRED => false,
				self::ARG_LABEL => "FILE_IMPORT_ID",
				self::ARG_DESCRIPTION => "File Import Id (".self::MODE_PREPROCESS.", ".self::MODE_PROCESS.", ".self::MODE_DISTRIBUTE." Modes only)",
				self::ARG_VALIDATION => 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_FILE_IMPORT_DATA_ID => array(
				self::ARG_REQUIRED => false,
				self::ARG_LABEL => "FILE_IMPORT_DATA_ID",
				self::ARG_DESCRIPTION => "File Import Data Id (".self::MODE_PROCESS.", ".self::MODE_DEBUG." Modes only)",
				self::ARG_VALIDATION => 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_FILE_EXPORT => array(
				self::ARG_REQUIRED => false,
				self::ARG_LABEL => "FILE_EXPORT",
				self::ARG_DESCRIPTION => "File Export file name or Id (".self::MODE_REVERSE_EXPORT." Mode only)",
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			),

			self::SWITCH_LIMIT => array(
				self::ARG_REQUIRED => false,
				self::ARG_LABEL => "LIMIT",
				self::ARG_DESCRIPTION => "Limit/Maximum Items to Process (".self::MODE_PREPROCESS.", ".self::MODE_PROCESS." Modes only)",
				self::ARG_VALIDATION => 'Cli::_validInteger("%1$s")'
			)
		);
	}
}