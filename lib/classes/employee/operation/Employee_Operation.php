<?php
/**
 * Employee_Operation
 *
 * Models a record of the employee_operation table
 *
 * Models a record of the employee_operation table
 *
 * @class	Employee_Operation
 */
class Employee_Operation extends ORM_Cached
{	
	protected 			$_strTableName			= "employee_operation";
	protected static	$_strStaticTableName	= "employee_operation";
	
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
	
	protected static function getMaxCacheSize()
	{
		return 100;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mObjects)
	{
		parent::addToCache($mObjects, __CLASS__);
	}

	public static function getForId($iId, $bSilentFail=false)
	{
		return parent::getForId($iId, $bSilentFail, __CLASS__);
	}
	
	public static function getAll($bForceReload=false)
	{
		return parent::getAll($bForceReload, __CLASS__);
	}
		
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	/**
	 * Retrieve all of the operations (overrides) for the employee id.
	 */
	public static function getForEmployeeId($iEmployeeId)
	{
		$aResult	= array();
		$oSelect	= self::_preparedStatement('selActiveByEmployeeId');
		$oSelect->Execute(array('employee_id' => $iEmployeeId));
		
		while ($aEmployeeOperation = $oSelect->Fetch())
		{
			$aResult[$aEmployeeOperation['id']]	= new self($aEmployeeOperation);
		}
		
		return $aResult;
	}

	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($sStatement)
	{
		static	$arrPreparedStatements	= Array();
		
		if (isset($arrPreparedStatements[$sStatement]))
		{
			return $arrPreparedStatements[$sStatement];
		}
		else
		{
			switch ($sStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "Name");
					break;
				case 'selActiveByEmployeeId':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "employee_id = <employee_id> AND (now() BETWEEN start_datetime AND end_datetime)");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$sStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$sStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$sStatement} does not exist!");
			}
			return $arrPreparedStatements[$sStatement];
		}
	}
}
?>