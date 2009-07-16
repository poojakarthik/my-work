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
	
	public function getRecords($bCountOnly=false, $iLimit=0, $iOffset=0)
	{
		try
		{
			if ($bCountOnly)
			{
				// Count Only
				return array(
								"Success"			=> true,
								"intRecordCount"	=> self::_getRecordCount(),
								"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
							);
			}
			else
			{
				// Include Data
				$iLimit		= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset	= ($iLimit === null) ? null : max((int)$iOffset, 0);
				
				$qQuery	= new Query();
				
				// Retrieve list of Employees
				$sGetAllSQL	= "SELECT * FROM Operation WHERE 1";
				$sGetAllSQL	.= ($iLimit !== null) ? " LIMIT {$iLimit} OFFSET {$iOffset}" : '';
				$rGetAll	= $qQuery->Execute($sGetAllSQL);
				if ($rGetAll === false)
				{
					throw new Exception($qQuery->Error());
				}
				$aResults	= array();
				$iCount		= 0;
				while ($aResult = $rGetAll->fetch_assoc())
				{
					$aResults[$iCount+$iOffset]	= $aResult;
					$iCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return array(
								"Success"			=> true,
								"arrRecords"		=> $aResults,
								"intRecordCount"	=> ($iLimit === null) ? count($aResults) : self::_getRecordCount(),
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
	
	private static function _getRecordCount()
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
	
	public function getOperationTree()
	{
		try
		{
			$qQuery	= new Query();
			
			// Get Operations Tree
			$aOperationsTree	= self::buildOperationTree();
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"			=> true,
							"oPermissions"		=> $aOperationsTree,
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
	
	public static function buildOperationTree($iOperationId=null)
	{
		static $qQuery;
		$qQuery	= ($qQuery) ? $qQuery : new Query();
		
		$aOperations	= array();
		
		if ($iOperationId === null)
		{
			$sOperations	= "	SELECT		o.id		AS operation_id
								
								FROM		`operation` o
											LEFT JOIN operation_prerequesite op_parent ON (o.id = op_parent.operation_id)
											LEFT JOIN operation_prerequesite op_child ON (o.id = op_child.prerequesite_operation_id)
								
								WHERE		1
								
								GROUP BY	o.id
								
								HAVING		COUNT(op_parent.id) = 0";
		}
		else
		{
			$sOperations	= "	SELECT		op_child.id		AS operation_id
								
								FROM		`operation` o
											JOIN operation_prerequesite op_parent ON (o.id = op_parent.operation_id)
											LEFT JOIN operation_prerequesite op_child ON (o.id = op_child.prerequesite_operation_id)
								
								WHERE		o.id = {$iOperationId}";
		}
		
		$Operations	= $qQuery->Execute($sOperations);
		if ($Operations === false)
		{
			throw new Exception($qQuery->Error());
		}
		while ($aOperationId = $Operations->fetch_assoc())
		{
			$iOperationId	= $aOperationId['operation_id'];
			
			$oOperationEntity	= Operation::getForId($iOperationId);
			$oOperation			= $oOperationEntity->toStdClass();
			
			$oOperation->oOperations	= self::buildOperationTree($iOperationId);
			
			$aOperations[$iOperationId]	= $oOperation;
		}
		
		return $aOperations;
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
				$oStdClass->aPrerequisites	= $oOperation->getPrerequisites();
			}
			
			$aReturn[$oStdClass->id]	= $oStdClass;
		}
		
		return $aReturn;
	}
}
?>