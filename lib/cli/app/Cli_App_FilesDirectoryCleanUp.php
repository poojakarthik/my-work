<?php

class Cli_App_FilesDirectoryCleanUp extends Cli {
	const SWITCH_TEST_MODE = 't';
	const SWITCH_MINIMUM_AGE = 'a';
	
	const FILE_TYPE_IMPORT = 1;
	const FILE_TYPE_DOWNLOAD = 2;

	const DELETE_RECORD_INCREMENT = 1000;
	const DOWNLOAD_SELECT_RECORD_INCREMENT = 100;

	private $_aArgs;
	private $_bTestMode;
	private $_sDatetimeCutoff;
	private $_aFilesToRemove = array();

	function run() {
		$oLog = Log::get();
		$this->_aArgs = $this->getValidatedArguments();
		$this->_bTestMode = (isset($this->_aArgs[self::SWITCH_TEST_MODE]) && $this->_aArgs[self::SWITCH_TEST_MODE]);

		if ($this->_bTestMode) {
			$oLog->log("[*] TEST MODE: no records or files will be deleted");
		}
		
		// Determine the datetime cutoff
		$iAge = (int)$this->_aArgs[self::SWITCH_MINIMUM_AGE];
		$aResult = Query::run("	SELECT	NOW() - INTERVAL {$iAge} DAY AS cutoff")->fetch_assoc();
		$this->_sDatetimeCutoff = $aResult['cutoff'];
		$oLog->log("[*] Cleaning up files that are atleast {$iAge} days old (On or Before: {$this->_sDatetimeCutoff})");

		// Main process
		$oDB = DataAccess::getDataAccess();
		$oDB->TransactionStart(false);
		try {
			$this->_removeDuplicateImportRecords();
			$this->_removeUnreferencedDownloadRecords();
		} catch (Exception $oEx) {
			// Unexpected Exception, rollback transaction and rethrow
			$oLog->log("[!] Exception: ".$oEx->getMessage());
			$oDB->TransactionRollback(false);
			throw $oEx;
		}

		if ($this->_bTestMode) {
			// Test mode, rollback db transaction
			$oLog->log("[*] TEST MODE: Rolling back db transaction, NOT removing files");
			$oDB->TransactionRollback(false);
		} else {
			// Not testing
			// Commit db transaction
			$oDB->TransactionCommit(false);

			// Remove the files associated with the db records that were removed
			$this->_removeFiles();
		}		

		// Generate the report of changes made
		$this->_generateReport();
	}
 
	private function _removeDuplicateImportRecords() {
		$oLog = Log::get();
		$oLog->log("[*] Removing duplicate FileImport records");
		$aFiles = array();
		$mResult = Query::run("	SELECT	fi.*
								FROM	FileImport fi
								WHERE	Status = <status>
								AND		ImportedOn <= <age>;",
								array(
									'status' => FILE_NOT_UNIQUE,
									'age' => $this->_sDatetimeCutoff
								));
		$aIdsToDelete = array();
		while ($aRow = $mResult->fetch_assoc()) {
			$oLog->log("[*] Duplicate FileImport: {$aRow['Id']} - {$aRow['Location']}");

			// Record the file data
			$aFiles[] = (object)array(
				'iType' => self::FILE_TYPE_IMPORT,
				'sFilePath' => $aRow['Location']
			);

			// Mark for deletion
			$aIdsToDelete[] = $aRow['Id'];
		}

		$oLog->log("[*] ".count($aIdsToDelete)." FileImport records to delete");

		if (!empty($aIdsToDelete)) {
			// Delete the duplicate records
			$oLog->log("[*] Deleting duplicate FileImport records (".count($aIdsToDelete)." records)");
			while (!empty($aIdsToDelete)) {
				$aSlice = array_splice($aIdsToDelete, 0, self::DELETE_RECORD_INCREMENT);
				if ($this->_bTestMode) {
					$oLog->log("[*] TEST MODE: Not deleting (".count($aSlice)." records)");
				} else {
					$oLog->log("[*] Deleting: (".count($aSlice)." records)");
					$sIdsToDelete = implode(', ', $aSlice);
					$mResult = Query::run("	DELETE FROM	FileImport
											WHERE		Id IN ({$sIdsToDelete});");
				}
			}
		}

		$this->_aFilesToRemove = $aFiles;
	}

	private function _removeUnreferencedDownloadRecords() {
		$oLog = Log::get();
		$oLog->log("[*] Removing Unreferenced FileDownload records");
		$aFiles = array();
		$aIdsToDelete = array();

		// First check how many records are of an appropriate age (if none, don't go any further)
		$aCount = Query::run("	SELECT	COUNT(fd.Id) AS count
								FROM	FileDownload fd
								WHERE	CollectedOn <= <age>;",
								array('age' => $this->_sDatetimeCutoff))->fetch_assoc();
		$oStopwatch = new Stopwatch();
		$iStartTime = $oStopwatch->split();
		if ($aCount['count'] > 0) {
			// There are records of an age, check them
			$oLog->log("[*] There are {$aCount['count']} FileDownload records of an appropriate age");
			$iStartId = 0;
			do {
				// Select the FileDownload records
				$iEndId = $iStartId + self::DOWNLOAD_SELECT_RECORD_INCREMENT;
				$oLog->log("[*] Checking FileDownload records with ids between {$iStartId} and {$iEndId} (Split: ".($oStopwatch->split() - $iStartTime)."; Lap: ".$oStopwatch->lap().")");
				$mResult = Query::run("	SELECT		fd.Id, fd.Location
										FROM		FileDownload fd
										LEFT JOIN	FileImport fi ON (
														fi.file_download = fd.Id
														AND fi.file_download IS NOT NULL
													)
										WHERE		fd.Id BETWEEN <start_id> AND <end_id>
										AND			CollectedOn <= <age>
										AND			fi.Id IS NULL;",
										array(
											'age' => $this->_sDatetimeCutoff,
											'start_id' => $iStartId,
											'end_id' => $iEndId
										));
				$iStartId = $iEndId;

				// Process the records
				while ($aRow = $mResult->fetch_assoc()) {
					$oLog->log("[*] Unreferenced FileDownload: {$aRow['Id']} - {$aRow['Location']}");

					// Record the file data
					$aFiles[] = (object)array(
						'iType' => self::FILE_TYPE_DOWNLOAD,
						'sFilePath' => $aRow['Location']
					);

					// Mark for deletion
					$aIdsToDelete[] = $aRow['Id'];
				}
			} while ($mResult->num_rows > 0);
		}		

		$oLog->log("[*] ".count($aIdsToDelete)." FileDownload records to delete");

		if (!empty($aIdsToDelete)) {
			// Delete the unreferenced records
			$oLog->log("[*] Deleting duplicate FileDownload records (".count($aIdsToDelete)." records)");
			while (!empty($aIdsToDelete)) {
				$aSlice = array_splice($aIdsToDelete, 0, self::DELETE_RECORD_INCREMENT);
				if ($this->_bTestMode) {
					$oLog->log("[*] TEST MODE: Not deleting (".count($aSlice)." records)");
				} else {
					$oLog->log("[*] Deleting: (".count($aSlice)." records)");
					$sIdsToDelete = implode(', ', $aSlice);
					$mResult = Query::run("	DELETE FROM	FileDownload
											WHERE		Id IN ({$sIdsToDelete});");
				}
			}
		}

		$this->_aFilesToRemove = array_merge($this->_aFilesToRemove, $aFiles);
	}

	private function _removeFiles() {
		$oLog = Log::get();
		foreach ($this->_aFilesToRemove as $oFile) {
			$oLog->log("[*] Removing file: {$oFile->sFilePath}");
			unlink($oFile->sFilePath);
		}
	}

	private function _generateReport() {
		$iDownloadCount = 0;
		$iImportCount = 0;
		foreach ($this->_aFilesToRemove as $oFile) {
			if ($oFile->iType == self::FILE_TYPE_DOWNLOAD) {
				$iDownloadCount++;
			} else {
				$iImportCount++;
			}
		}

		$oLog = Log::get();
		$oLog->log("[*] Download Records ".($this->_bTestMode ? 'To Be ' : '')."Removed: {$iDownloadCount}");
		$oLog->log("[*] Import Records ".($this->_bTestMode ? 'To Be ' : '')."Removed: {$iImportCount}");
	}

	function getCommandLineArguments() {
		return array(
			self::SWITCH_TEST_MODE => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "No changes will be made to the filesystem or the database.",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			),
			self::SWITCH_MINIMUM_AGE => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "Minimum Age (in days) of the files that will get cleaned up.",
				self::ARG_DEFAULT => 60,
				self::ARG_VALIDATION => 'Cli::_validInteger("%1$s")'
			)
		);
	}
}

?>