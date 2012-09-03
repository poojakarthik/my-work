<?php

class Cli_App_CollectionCleanUp extends Cli {
	const SWITCH_TEST_MODE = 't';
	const SWITCH_MINIMUM_AGE = 'a';
	const SWITCH_MAXIMUM_ITEMS_AFFECTED = 'i';
	
	const FILE_TYPE_IMPORT = 1;
	const FILE_TYPE_DOWNLOAD = 2;

	const DELETE_RECORD_INCREMENT = 1000;
	const DOWNLOAD_SELECT_RECORD_INCREMENT = 100;

	const SECONDS_IN_DAY = 86400;

	private $_aArgs;
	private $_bTestMode;
	private $_iMaxRecordsAffected;
	private $_iMinimumAge;
	private $_sDatetimeCutoff;

	function run() {
		$oDB = DataAccess::getDataAccess();
		$oDB->TransactionStart(false);
		try {
			$oLog = Log::get();
			$this->_aArgs = $this->getValidatedArguments();
			$this->_bTestMode = (isset($this->_aArgs[self::SWITCH_TEST_MODE]) && $this->_aArgs[self::SWITCH_TEST_MODE]);
			$this->_iMaxRecordsAffected = (isset($this->_aArgs[self::SWITCH_MAXIMUM_ITEMS_AFFECTED]) ? $this->_aArgs[self::SWITCH_MAXIMUM_ITEMS_AFFECTED] : null);

			if ($this->_bTestMode) {
				$oLog->log("[*] TEST MODE: no records or files will be deleted");
			}
			
			// Determine the datetime cutoff
			$iAge = (int)$this->_aArgs[self::SWITCH_MINIMUM_AGE];
			$aResult = Query::run("	SELECT	NOW() - INTERVAL {$iAge} DAY AS cutoff")->fetch_assoc();
			$this->_iMinimumAge = $iAge;
			$this->_sDatetimeCutoff = $aResult['cutoff'];
			$oLog->log("[*] Cleaning up files that are atleast {$iAge} days old (On or Before: {$this->_sDatetimeCutoff})");

			$this->_removeDuplicateImportRecords();
			$oLog->log("[*] - - -");
			$this->_removeOrphanedImportFiles();
			$oLog->log("[*] - - -");
			$this->_removeArchivedDownloads();

			if ($this->_bTestMode) {
				// Test mode, rollback db transaction
				$oLog->log("[*] TEST MODE: Rolling back db transaction, NOT removing files");
				$oDB->TransactionRollback(false);
			} else {
				// Not testing
				// Commit db transaction
				$oDB->TransactionCommit(false);
			}
		} catch (Exception $oEx) {
			// Unexpected Exception, rollback transaction and rethrow
			$oLog->log("[!] Exception: ".$oEx->getMessage());
			$oDB->TransactionRollback(false);
			throw $oEx;
		}
	}
 
	private function _removeDuplicateImportRecords() {
		$oLog = Log::get();
		$oLog->log("[*] Removing duplicate FileImport records");
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
			if (($this->_iMaxRecordsAffected !== null) && (count($aIdsToDelete) >= $this->_iMaxRecordsAffected)) {
				// Maximum records affected, stop
				break;
			}

			$sCounter = (($this->_iMaxRecordsAffected !== null) ? "(".(count($aIdsToDelete) + 1)."/{$this->_iMaxRecordsAffected}) " : '');
			$oLog->log("[*] {$sCounter}Duplicate FileImport: {$aRow['Id']} - {$aRow['Location']}");
			
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
					// Delete the records
					$oDB = DataAccess::getDataAccess();
					$oDB->TransactionStart(false);
					try {
						$oLog->log("[*] Deleting: (".count($aSlice)." records)");
						$sIdsToDelete = implode(', ', $aSlice);
						$mResult = Query::run("	DELETE FROM	FileImport
												WHERE		Id IN ({$sIdsToDelete});");
					} catch (Exception $oEx) {
						$oDB->TransactionRollback(false);
						throw $oEx;
					}

					$oDB->TransactionCommit(false);
				}
			}
		}
	}

	private function _removeOrphanedImportFiles() {
		$oLog = Log::get();
		$oLog->log("[*] Removing orphaned (no FileImport record) import files");

		$aPaths = array();
		self::_listFilesInDirectory(FILES_BASE_PATH."import", $aPaths);

		$iFilesRemoved = 0;
		foreach ($aPaths as $sPath) {
			if (($this->_iMaxRecordsAffected !== null) && ($iFilesRemoved >= $this->_iMaxRecordsAffected)) {
				// Maximum records affected, stop
				break;
			}

			$aRow = Query::run("SELECT	Id
								FROM	FileImport
								WHERE	Location = <location>;",
								array('location' => $sPath))->fetch_assoc();
			if (!$aRow) {
				$sCounter = (($this->_iMaxRecordsAffected !== null) ? "(".($iFilesRemoved + 1)."/{$this->_iMaxRecordsAffected}) " : '');
				$oLog->log("[*] {$sCounter}No FileImport record, removing file: {$sPath}");
				
				if ($this->_bTestMode) {
					$oLog->log("[~] TEST MODE: Not removing file");
				} else {
					if (!unlink($sPath)) {
						throw new Exception("Failed to remove import file: {$sPath}");
					}

					$oLog->log("[-] Removed file");
				}

				$iFilesRemoved++;
			} else {
				$oLog->log("[~] Has Record: {$sPath} (#{$aRow['Id']})");
			}
		}

		$oLog->log("[*] {$iFilesRemoved} orphaned import files removed");
	}

	private function _removeArchivedDownloads() {
		$oLog = Log::get();
		$oLog->log("[*] Removing archived download files");

		// Get all archived downloads past the given age
		$aPaths = array();
		self::_listFilesInDirectory(FILES_BASE_PATH."download/archived", $aPaths, $this->_iMinimumAge);

		// Remove them
		$iFilesRemoved = 0;
		foreach ($aPaths as $sPath) {
			if (($this->_iMaxRecordsAffected !== null) && ($iFilesRemoved >= $this->_iMaxRecordsAffected)) {
				// Maximum records affected, stop
				break;
			}

			$sCounter = (($this->_iMaxRecordsAffected !== null) ? "(".($iFilesRemoved + 1)."/{$this->_iMaxRecordsAffected}) " : '');
			$oLog->log("[*] {$sCounter}Removing archived download file: {$sPath}");
			
			if ($this->_bTestMode) {
				$oLog->log("[~] TEST MODE: Not removing file");
			} else {
				if (!unlink($sPath)) {
					throw new Exception("Failed to remove archived download file: {$sPath}");
				}

				$oLog->log("[-] File removed");
			}

			$iFilesRemoved++;
		}

		$oLog->log("[*] {$iFilesRemoved} archived download files removed");
	}

	private static function _listFilesInDirectory($sDirectory, &$aPaths=array(), $iMinimumAgeInDays=0) {
		$aChildren = scandir($sDirectory);
		foreach ($aChildren as $sChild) {
			if (preg_match('/^\.(\.)?$/', $sChild)) {
				continue;
			}

			$sPath = "{$sDirectory}/{$sChild}";
			if (is_dir($sPath)) {
				self::_listFilesInDirectory($sPath, $aPaths);
			} else {
				$sPath = realpath($sPath);
				if (self::_isFileOfAge($sPath, $iMinimumAgeInDays)) {
					$aPaths[] = realpath($sPath);
				} else {
					//Log::get()->log("[~] File is not of age ({$iMinimumAgeInDays} days): {$sPath}");
				}
			}
		}
	}

	private static function _isFileOfAge($sFilePath, $iAgeInDays) {
		$bOfAge = (((time() - filectime($sFilePath)) / self::SECONDS_IN_DAY) > $iAgeInDays);
		//Log::get()->log("[~] File is of age ({$iAgeInDays} days): {$sFilePath}");
		return $bOfAge;
	}

	/*private static function _isFileRemovable($sLocation) {
		// Check if the file exists and that php has write permission on it's directory (so that it can be removed)
		$sDirectory = dirname($sLocation);
		if (!file_exists($sLocation)) {
			Log::get()->log("[~] Ignoring this file, cannot find it at the stored location: {$sLocation}");
			return false;
		} else if (!is_writable($sDirectory)) {
			Log::get()->log("[~] Ignoring this file, PHP doesn't have write permission on it's directory: {$sDirectory}");
			return false;
		}

		return true;
	}*/

	function getCommandLineArguments() {
		return array(
			self::SWITCH_TEST_MODE => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "No changes will be made to the filesystem or the database",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			),
			self::SWITCH_MINIMUM_AGE => array(
				self::ARG_REQUIRED => true,
				self::ARG_DESCRIPTION => "Minimum Age (in days) of the files that will get cleaned up",
				self::ARG_DEFAULT => 60,
				self::ARG_VALIDATION => 'Cli::_validInteger("%1$s")'
			),
			self::SWITCH_MAXIMUM_ITEMS_AFFECTED => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "Maximum number of items affected (import records/files and download files counted separately)",
				self::ARG_DEFAULT => null,
				self::ARG_VALIDATION => 'Cli::_validInteger("%1$s")'
			)
		);
	}
}

?>