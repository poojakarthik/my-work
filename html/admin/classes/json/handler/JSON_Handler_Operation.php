<?php

class JSON_Handler_Operation extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getForId($iOperationId, $bolIncludePermissions=false)
	{
		try
		{
			// Get the Employee
			$oOperation	= Operation::getForId($iOperationId);
			$aOperation	= $oOperation->toArray();
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"			=> true,
							"oOperation"		=> $aOperation,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	public function getDataset($bCountOnly=false, $iLimit=0, $iOffset=0)
	{
		try
		{
			if ($bCountOnly)
			{
				// Count Only
				return array(
								"Success"			=> true,
								"intRecordCount"	=> self::_getDatasetLength(),
								"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
							);
			}
			else
			{
				// Include Data
				$iLimit		= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset	= ($iLimit === null) ? null : max((int)$iOffset, 0);
				
				
				// Retrieve list of Operations
				$oOperations	= Operation::getAll();
				$aResults		= array();
				$iCount			= 0;
				foreach ($oOperations as $iOperationId=>$oOperation)
				{
					if ($iCount >= $iOffset+$iLimit)
					{
						// Break out, as there's no point in continuing
						break;
					}
					elseif ($iCount >= $iOffset)
					{
						$oStdClass	= $oOperation->toStdClass();
						
						// Get list of prerequisites
						$aPrerequisites				= $oOperation->getPrerequisites();
						$oStdClass->aPrerequisites	= array();
						foreach ($aPrerequisites as $oOperationPrerequisite)
						{
							$oStdClass->aPrerequisites[]	= $oOperationPrerequisite->prerequisite_operation_id;
						}
						
						// Get list of dependants
						$aDependants			= $oOperation->getDependants();
						$oStdClass->aDependants	= array();
						foreach ($aPrerequisites as $oOperationPrerequisite)
						{
							$oStdClass->aDependants[]	= $oOperationPrerequisite->operation_id;
						}
						
						// Add to Result Set
						$aResults[$iCount+$iOffset]	= $oStdClass;
					}
					$iCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return array(
								"Success"			=> true,
								"arrRecords"		=> $aResults,
								"intRecordCount"	=> ($iLimit === null) ? count($aResults) : self::_getDatasetLength(),
								"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
							);
			}
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	private static function _getDatasetLength()
	{
		$qQuery	= new Query();
		
		// Retrieve COUNT() of Employees
		$sCountSQL	= "SELECT COUNT(id) AS record_count FROM operation WHERE 1";
		$rCount		= $qQuery->Execute($sCountSQL);
		if ($rCount === false)
		{
			throw new Exception($qQuery->Error());
		}
		if ($aCount = $rCount->fetch_assoc())
		{
			return $aCount['record_count'];
		}
	}
	
	public static function getOperations($bIncludeDependencyReferences=false)
	{
		static	$qQuery;
		$qQuery	= ($qQuery) ? $qQuery : new Query();
		
		// Get full list of Operations
		$aOperations	= Operation::getAll();
		
		// Convert to stdClasses
		$aReturn	= array();
		foreach ($aOperations as $iOperationId=>$oOperation)
		{
			$oStdClass	= $oOperation->toStdClass();
			
			if ($bIncludeDependencyReferences)
			{
				// Get list of dependencies
				$aPrerequisites				= $oOperation->getPrerequisites();
				$oStdClass->aPrerequisites	= array();
				
				foreach ($aPrerequisites as $oOperationPrerequisite)
				{
					$oStdClass->aPrerequisites[]	= $oOperationPrerequisite->prerequisite_operation_id;
				}
			}
			
			$aReturn[$oStdClass->id]	= $oStdClass;
		}
		
		return $aReturn;
	}
}
?>