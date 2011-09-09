<?php

class Exception_Set extends Exception implements JSON_Handler_Exception {
	private $_aExceptions = array();
	
	public function __construct() {
		parent::__construct('');
	}
	
	public function push($mException) {
		$oException = null;
		if (is_string($mException)) {
			$oException = new Exception($mException);
		} else if ($mException instanceof Exception) {
			$oException = $mException;
		}
		
		if ($oException !== null) {
			$this->_aExceptions[] = $oException;
		}
	}
	
	public function isEmpty() {
		return empty($this->_aExceptions);
	}
	
	public function getFriendlyMessage() {
		return '';
	}
	
	public function getDetailedMessage() {
		return '';
	}
	
	public function getData() {
		$aData = array();
		foreach ($this->_aExceptions as $oException) {
			// Generate primitive representation of the exception
			$aExceptionInterfaces	= class_implements($oException);
			$bJSONHandlerException	= isset($aExceptionInterfaces['JSON_Handler_Exception']);
			
			// Determine the inheritance hierarchy for the exception
			$aClasses	= array();
			$sClass 	= get_class($oException);
			while ($sClass !== false) {
				$aClasses[]	= $sClass;
				$sClass 	= get_parent_class($sClass);
			}
			
			$aExceptionData = array(
				'sMessage'		=> ($bJSONHandlerException ? $oException->getFriendlyMessage() : $oException->getMessage()),
				'aClasses'		=> $aClasses,
				'aStackTrace'	=> $oException->getTrace()
			);
			
			if ($bJSONHandlerException) {
				$aExceptionData['sDetailedMessage']	= $oException->getDetailedMessage();
				$aExceptionData['mData'] 			= $oException->getData();
			}
			
			$aData[] = $aExceptionData;
		}
		return $aData;
	}
}

?>