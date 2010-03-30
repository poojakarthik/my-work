<?php

class JSON_Handler_DataReport extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getAll()
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		try
		{
			// Retrieve the datareports & convert response to std classes
			$aDataReports 			= DataReport::getAll();
			$aStdClassDataReports 	= array();
			
			foreach ($aDataReports as $iId => $oDataReport)
			{
				$aStdClassDataReports[$iId]	= $oDataReport->toStdClass();				
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
						"Success"	=> true,
						"aRecords"	=> $aStdClassDataReports,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return array(
						"Success"	=> false,
						"Message"	=> 'ERROR: '.$e->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function getForId($iId)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		try
		{
			// Get the datareport orm object
			$oDataReport			= DataReport::getForId($iId);
			$oStdClassDataReport	= $oDataReport->toStdClass();
			
			// Unserialize the serialized data
			$aSQLFields	= unserialize($oStdClassDataReport->SQLFields);
			$oStdClassDataReport->SQLFields = $aSQLFields;
			$oStdClassDataReport->SQLSelect	= unserialize($oStdClassDataReport->SQLSelect);
			
			// Parse the SQLFields and define input information
			foreach ($aSQLFields as $sName => $aField)
			{
				$sType 					= $aField['Type'];
				$sDocumentationEntity 	= $aField['Documentation-Entity'];
				$sDocumentationField	= $aField['Documentation-Field'];
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
						"Success"		=> true,
						"oDataReport"	=> $oStdClassDataReport,
						"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return array(
						"Success"	=> false,
						"Message"	=> 'ERROR: '.$e->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function runReport($iId)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		try
		{
			$bDummy = false; // Delete me
		}
		catch (Exception $e)
		{
			return array(
						"Success"	=> false,
						"Message"	=> 'ERROR: '.$e->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
}

?>