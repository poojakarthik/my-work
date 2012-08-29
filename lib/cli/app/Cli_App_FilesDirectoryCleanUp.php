<?php

class Cli_App_FilesDirectoryCleanUp extends Cli {
	const SWITCH_TEST_MODE = 't';
	const SWITCH_DISPLAY_REPORT = 'r';

	const FILE_TYPE_IMPORT = 1;
	const FILE_TYPE_DOWNLOAD = 2;

	private $_aArgs;
	private $_bTestMode;
	private $_aFilesToRemove = array();

	function run() {
		$oLog = Log::get();

		$this->_aArgs = $this->getValidatedArguments();
		$this->_bTestMode = true;//(isset($aArgs[self::SWITCH_TEST_MODE]) && $aArgs[self::SWITCH_TEST_MODE]);
		$bGenerateReport = (isset($aArgs[self::SWITCH_DISPLAY_REPORT]) && $aArgs[self::SWITCH_DISPLAY_REPORT]);
		
		$oDB = DataAccess::getDataAccess();
		$oDB->TransactionStart(false);
		try {
			$this->_removeDuplicateImportRecords();
			$this->_removeUnreferencedDownloadRecords();
		} catch (Exception $oEx) {
			// Unexpected Exception, rollback transaction and rethrow
			$oDB->TransactionRollback(false);
			throw $oEx;
		}

		if ($this->_bTestMode) {
			// Test mode, rollback db transaction
			$oDB->TransactionRollback(false);
		} else {
			// Not testing
			// Commit db transaction
			$oDB->TransactionCommit(false);

			// Remove the files associated with the db records that were removed
			$this->_removeFiles();
		}		

		// Generate the report of changes made
		if ($bGenerateReport) {
			$this->_generateReport();
		}
	}

	private function _removeDuplicateImportRecords() {
		$aFiles = array();
		$mResult = Query::run("	SELECT	fi.*
								FROM	FileImport fi
								WHERE	Status = <status>;",
								array('status' => FILE_NOT_UNIQUE));
		while ($aRow = $mResult->fetch_assoc()) {
			// Record the file data
			$aFiles[] = (object)array(
				'iType' => self::FILE_TYPE_IMPORT,
				'sFilePath' => $aRow['Location'];
			);

			// Delete the record
			if ($this->_bTestMode) {
				$mResult = Query::run("	DELETE FROM	FileImport
										WHERE	Id = <id>;",
										array('id' => $aRow['Id']));
			}
		}

		$this->_aFilesToRemove = $aFiles;
	}

	private function _removeUnreferencedDownloadRecords() {
		$aFiles = array();

		$aFiles = array();
		$mResult = Query::run("	SELECT		fd.Id, fd.Location, COUNT(fi.Id) AS reference_count
								FROM		FileDownload fd
								LEFT JOIN	FileImport fi ON (fi.file_download = fd.Id)
								GROUP BY	fd.Id, fd.Location
								HAVING 		reference_count = 0;");
		while ($aRow = $mResult->fetch_assoc()) {
			// Record the file data
			$aFiles[] = (object)array(
				'iType' => self::FILE_TYPE_DOWNLOAD,
				'sFilePath' => $aRow['Location'];
			);

			// Delete the record
			if ($this->_bTestMode) {
				$mResult = Query::run("	DELETE FROM	FileDownload
										WHERE	Id = <id>;",
										array('id' => $aRow['Id']));
			}
		}

		$this->_aFilesToRemove = array_merge($this->_aFilesToRemove, $aFiles)
	}

	private function _removeFiles() {
		foreach ($this->_aFilesToRemove as $oFile) {
			unlink($oFile->sFilePath);
		}
	}

	private function _generateReport() {
		foreach ($this->_aFilesToRemove as $oFile) {

		}
	}

	function getCommandLineArguments() {
		return array(
			self::SWITCH_TEST_MODE => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "No changes will be made to the filesystem or the database.",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			),
			self::SWITCH_DISPLAY_REPORT => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "If supplied a report on the cleaned files will be output",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			)
		);
	}
}

?>