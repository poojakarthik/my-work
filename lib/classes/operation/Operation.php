<?php
/**
 * Operation
 *
 * Models the 'operation' Table and handles related functions
 *
 * @class	Operation
 */
class Operation extends ORM_Enumerated
{	
	protected			$_strTableName 			= "operation";
	protected static	$_strStaticTableName	= "operation";
	
	public static function userHasPermission($intOperation, $intEmployeeId=null)
	{
		$intEmployeeId	= ((int)$intEmployeeId >= 0) ? (int)$intEmployeeId : Flex::getUserId();
		
		// If the user is GOD, then it trumps all permission settings
		$objEmployee	= Employee::getForId(Flex::getUserId());
		if ($objEmployee->is_god)
		{
			return true;
		}
		else
		{
			// Do we have authorisation?
			return array_key_exists($intOperation, $objEmployee->getPermittedOperations());
		}
	}
	
	public static function assertPermission($intOperation, $bolLogOnErrorsOnly=false, $intEmployeeId=null)
	{
		$intEmployeeId	= ((int)$intEmployeeId >= 0) ? (int)$intEmployeeId : Flex::getUserId();
		
		$objOperation	= self::getForId($intOperation);
		
		// Do we have permission?
		$bolHasPermission	= self::userHasPermission($intOperation, $intEmployeeId);
		
		$objEmployeeOperationLog					= new Employee_Operation_Log();
		$objEmployeeOperationLog->employee_id		= $intEmployeeId;
		$objEmployeeOperationLog->operation_id		= $intOperation;
		$objEmployeeOperationLog->was_authorised	= (int)$bolHasPermission;
		
		// Should we log?
		if (!$bolLogOnErrorsOnly || ($bolLogOnErrorsOnly && !$bolHasPermission))
		{
			$objEmployeeOperationLog->save();
		}
		
		// If we have permission, return the Log object, otherwise throw an Exception
		if ($bolHasPermission)
		{
			return $objEmployeeOperationLog;
		}
		else
		{
			unset($objEmployeeOperationLog);
			throw new Exception_Operation_Authorisation("You are not permitted to perform the {$objOperation->name} operation");
		}
	}
	
	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Enumerated UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//
	
	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}
	
	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}
	
	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}
	
	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}
	
	public static function getForSystemName($strSystemName)
	{
		return parent::getForSystemName($strSystemName, __CLASS__);
	}
	
	public static function getIdForSystemName($strSystemName)
	{
		return parent::getIdForSystemName($strSystemName, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Enumerated UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//
	
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "name ASC");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>