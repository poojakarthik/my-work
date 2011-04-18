<?php

class JSON_Handler_Test extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getAllTestClassDetails() {
		// List all of the classes (files) in the 'lib/classes/test' directory
		$aClasses	= array();
		$aFiles		= self::_getAllTestClassFilenames();
		foreach ($aFiles as $sFile) {
			$sClass = preg_replace('/\.php$/', '', $sFile);
			if ($sClass != 'Test') {
				$oReflection 		= new ReflectionClass($sClass);
				$aMethods 			= $oReflection->getMethods();
				$oInstance			= new $sClass();
				$aClasses[$sClass]	= array('sName' => $oInstance->getName(), 'aMethods' => array());
				foreach ($aMethods as $oMethod) {
					$sMethod = $oMethod->getName();
					if (Test::isTestMethod($sMethod)) {
						$aParams 									= $oMethod->getParameters();
						$aClasses[$sClass]['aMethods'][$sMethod]	= array();
						foreach ($aParams as $oParam) {
							$aClasses[$sClass]['aMethods'][$sMethod][] = array(
								'sName' 		=> $oParam->getName(), 
								'bIsOptional'	=> $oParam->isOptional()
							);
						}
					}
				}
			}
		}
		
		return array('aClasses' => $aClasses);
	}
	
	public function runTest($sClass, $sMethod, $aParameters) {
		if (!class_exists($sClass)) {
			throw new JSON_Handler_Test_Exception("Invalid test class supplied");
		}
		
		if (!method_exists($sClass, $sMethod)) {
			throw new JSON_Handler_Test_Exception("Invalid test class supplied");
		}
		
		// Start db transaction
		$oDataAccess = DataAccess::getDataAccess();
		if ($oDataAccess->TransactionStart() === false) {
			throw new Exception("Failed to start db transaction");
		}
		
		try {
			// Instantiate test class and run the test method, catching the return value
			$oInstance	= new $sClass();
			$mResult 	= call_user_func_array(array($oInstance, $sMethod), $aParameters);
		} catch (Exception $oEx) {
			// Exception, rollback db transaction and re-throw the exception
			if ($oDataAccess->TransactionRollback() === false) {
				throw new Exception("Failed to rollback db transaction, reason for rollback attempt = ".$oEx->getMessage());
			}
			throw $oEx;
		}
		
		// Rollback the db transaction so that no changes are committed
		if ($oDataAccess->TransactionRollback() === false) {
			throw new Exception("Failed to rollback db transaction");
		}
		
		return array('mResult' => $mResult);
	}
	
	public function getTestDataset($bCountOnly, $iLimit, $iOffset, $oSort, $oFilter) {
		$iRecordCount = Correspondence_Template::searchFor(true, $iLimit, $iOffset, $oSort, $oFilter);
		if ($bCountOnly) {
			return array('iRecordCount' => $iRecordCount);
		}
		
		$iLimit		= ($iLimit === null ? 0 : $iLimit);
		$iOffset	= ($iOffset === null ? 0 : $iOffset);
		$aData	 	= Correspondence_Template::searchFor(false, $iLimit, $iOffset, $oSort, $oFilter);
		$aResults	= array();
		$i			= $iOffset;
		
		foreach ($aData as $aRecord) {
			$aResults[$i] = $aRecord;
			$i++;
		}
		
		return array(
			'aRecords'		=> $aResults,
			'iRecordCount'	=> $iRecordCount
		);
	}
	
	protected static function _getAllTestClassFilenames() {
		$aFiles = array();
		self::_readFilesInDirectory(FLEX_BASE_PATH.'lib/classes/test', $aFiles);
		return $aFiles;
	}
	
	protected static function _readFilesInDirectory($sDirectory, &$aFiles) {
		$rHandler = opendir($sDirectory);
		while ($sFile = readdir($rHandler)) {
			if ($sFile != "." && $sFile != ".." && $sFile != ".svn") {
				if (preg_match('/\.php/', $sFile)) {
					// Class file
					$aFiles[] = $sFile;
				} else {
					// Directory
					self::_readFilesInDirectory("$sDirectory/{$sFile}", $aFiles);
				}
			}
		}
		closedir($rHandler);
	}
}

class JSON_Handler_Test_Exception extends Exception implements JSON_Handler_Exception {
	public function getFriendlyMessage() {
		return "FRIENDLY: ".$this->getMessage();
	}
	
	public function getDetailedMessage() {
		return "DETAILED: ".$this->getMessage();
	}
}

?>