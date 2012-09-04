<?php

class Cli_App_CollectionCleanUp extends Cli {
	const SWITCH_TEST_MODE = 't';
	const SWITCH_MINIMUM_AGE = 'a';
	const SWITCH_MAXIMUM_ITEMS_AFFECTED = 'i';
	
	const DELETE_RECORD_INCREMENT = 1000;
	const DOWNLOAD_SELECT_RECORD_INCREMENT = 100;
	const SECONDS_IN_DAY = 86400;

	private $_aArgs;
	private $_bTestMode;
	private $_iMaxRecordsAffected;
	private $_iMinimumAge;
	private $_sDatetimeCutoff;

	function run() {
		try {
			$oLog = Log::get();
			$this->_aArgs = $this->getValidatedArguments();
			$this->_bTestMode = (isset($this->_aArgs[self::SWITCH_TEST_MODE]) && $this->_aArgs[self::SWITCH_TEST_MODE]);
			$this->_iMaxRecordsAffected = (isset($this->_aArgs[self::SWITCH_MAXIMUM_ITEMS_AFFECTED]) ? $this->_aArgs[self::SWITCH_MAXIMUM_ITEMS_AFFECTED] : null);

			if ($this->_bTestMode) {
				$oLog->log("[*] TEST MODE: no records or files will be deleted");
			}
			
			// Determine the datetime cutoff
			$this->_iMinimumAge = (int)$this->_aArgs[self::SWITCH_MINIMUM_AGE];
			$aResult = Query::run("	SELECT	NOW() - INTERVAL {$this->_iMinimumAge} DAY AS cutoff")->fetch_assoc();			
			$this->_sDatetimeCutoff = $aResult['cutoff'];
			$oLog->log("[*] Cleaning up files that are atleast {$this->_iMinimumAge} days old (On or Before: {$this->_sDatetimeCutoff})");

			$oLog->log("[*] - - -");
			$this->_removeOrphanedImportFiles();
			$oLog->log("[*] - - -");
			$this->_removeArchivedDownloads();
			$oLog->log("[*] - - -");
		} catch (Exception $oEx) {
			// Unexpected Exception, rollback transaction and rethrow
			$oLog->log("[!] Exception: ".$oEx->getMessage());
			throw $oEx;
		}
	}
 
	private function _removeOrphanedImportFiles() {
		$oLog = Log::get();
		$oLog->log("[*] Removing orphaned (no FileImport record) import files");

		$aPaths = array();
		self::_listFilesInDirectory(FILES_BASE_PATH."import", $aPaths, $this->_iMinimumAge);

		$iFilesRemoved = 0;
		foreach ($aPaths as $sPath) {
			if (($this->_iMaxRecordsAffected !== null) && ($iFilesRemoved >= $this->_iMaxRecordsAffected)) {
				// Maximum records affected, stop
				break;
			}

			$aRow = Query::run("SELECT	Id, Status
								FROM	FileImport
								WHERE	Location = <location>;",
								array('location' => $sPath))->fetch_assoc();
			$bDuplicate = ($aRow['Status'] == FILE_NOT_UNIQUE);
			if (!$aRow || $bDuplicate) {
				// Either missing a record or there is on and it's status is NOT_UNIQUE
				$sCounter = (($this->_iMaxRecordsAffected !== null) ? "(".($iFilesRemoved + 1)."/{$this->_iMaxRecordsAffected}) " : '');
				$oLog->log("[*] {$sCounter}".($bDuplicate ? 'Duplicate' : 'No')." FileImport record, removing file: {$sPath}");
				
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

	private static function _listFilesInDirectory($sDirectory, &$aPaths=array(), $iMinimumAgeInDays) {
		$aChildren = scandir($sDirectory);
		foreach ($aChildren as $sChild) {
			if (preg_match('/^\.(\.)?$/', $sChild)) {
				continue;
			}

			$sPath = "{$sDirectory}/{$sChild}";
			if (is_dir($sPath)) {
				self::_listFilesInDirectory($sPath, $aPaths, $iMinimumAgeInDays);
			} else {
				$sPath = realpath($sPath);
				if (self::_isFileOfAge($sPath, $iMinimumAgeInDays)) {
					$aPaths[] = realpath($sPath);
				}
			}
		}
	}

	private static function _isFileOfAge($sFilePath, $iAgeInDays) {
		$iAge = ((time() - filectime($sFilePath)) / self::SECONDS_IN_DAY);
		$bOfAge = ($iAge > $iAgeInDays);
		//Log::get()->log("[~] File is ".($bOfAge ? '' : 'NOT ')."of age ({$iAgeInDays} days): {$sFilePath} (Age: {$iAge} days)");
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
				self::ARG_DESCRIPTION => "No changes will be made to the filesystem",
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
				self::ARG_DESCRIPTION => "Maximum number of items (files) removed (import and download files counted separately)",
				self::ARG_DEFAULT => null,
				self::ARG_VALIDATION => 'Cli::_validInteger("%1$s")'
			)
		);
	}
}

?>