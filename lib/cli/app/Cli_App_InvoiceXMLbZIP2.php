<?php

class Cli_App_InvoiceXMLbZIP2 extends Cli {
	const SWITCH_REMOVE_XML_FILES = 'd';

	private $_bRemoveXMLFiles;
	
	function run() {
		try {
			$oLog = Log::get();
			$aArgs = $this->getValidatedArguments();
			$bRemoveXMLFiles = (isset($aArgs[self::SWITCH_REMOVE_XML_FILES]) && $aArgs[self::SWITCH_REMOVE_XML_FILES]);
			$aXMLFiles = array();
			$this->_listFilesInDirectory(FILES_BASE_PATH."invoices", $aXMLFiles);
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
				$aPaths[] = realpath($sPath);
			}
		}
	}

	function getCommandLineArguments() {
		return array(
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