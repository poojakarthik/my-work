<?php

class Cli_App_XMLBZIP extends Cli {
	const SWITCH_SUB_DIRECTORY = 'd';
	const SWITCH_REMOVE_XML_FILES = 'r';

	const DEBUG_LOGGING = false;

	function run() {
		try {
			$oLog = Log::get();
			$aArgs = $this->getValidatedArguments();
			$bRemoveXMLFiles = (isset($aArgs[self::SWITCH_REMOVE_XML_FILES]) && $aArgs[self::SWITCH_REMOVE_XML_FILES]);
			$aXMLFiles = array();
			$this->_listFilesInDirectory(FILES_BASE_PATH.$aArgs[self::SWITCH_SUB_DIRECTORY], $aXMLFiles);
			$oLog->log("[*] Generating bzipped versions of ".(count($aXMLFiles))." xml files");
			foreach ($aXMLFiles as $sXMLFile) {
				$sBZipFile = "{$sXMLFile}.bz2";
				$oLog->log("[+] Creating file: {$sBZipFile}");
				file_put_contents("compress.bzip2://{$sBZipFile}", file_get_contents($sXMLFile));
				if ($bRemoveXMLFiles) {
					$oLog->log("[-] Removing file: {$sXMLFile}");
					if (!unlink($sXMLFile)) {
						throw new Exception("Failed to remove file: {$sXMLFile}");
					}
				}
			}
		} catch (Exception $oEx) {
			// Unexpected Exception, log and rethrow
			$oLog->log("[!] Exception: ".$oEx->getMessage());
			throw $oEx;
		}
	}
 
	private function _listFilesInDirectory($sDirectory, &$aPaths=array()) {
		Log::get()->logIf(self::DEBUG_LOGGING, "[*] Checking Directory: {$sDirectory}");
		$aChildren = scandir($sDirectory);
		foreach ($aChildren as $sChild) {
			if (preg_match('/^\.(\.)?$/', $sChild)) {
				continue;
			}

			$sPath = "{$sDirectory}/{$sChild}";
			if (is_dir($sPath)) {
				$this->_listFilesInDirectory($sPath, $aPaths);
			} else if (preg_match('/^\d+\.xml$/', $sPath)){
				// An xml invoice
				Log::get()->logIf(self::DEBUG_LOGGING, "[*] XML File: {$sChild}");
				$aPaths[] = realpath($sPath);
			} else {
				Log::get()->logIf(self::DEBUG_LOGGING, "[*] Not a directory or xml file: {$sChild}");
			}
		}
	}

	function getCommandLineArguments() {
		return array(
			self::SWITCH_SUB_DIRECTORY => array(
				self::ARG_REQUIRED => true,
				self::ARG_DESCRIPTION => "The sub directory within the files directory",
				self::ARG_DEFAULT => null,
				self::ARG_VALIDATION => 'Cli::_validString("%1$s")'
			),
			self::SWITCH_REMOVE_XML_FILES => array(
				self::ARG_REQUIRED => false,
				self::ARG_DESCRIPTION => "If supplied, the original xml files will be removed after the bz2 version is created",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			)
		);
	}
}

?>