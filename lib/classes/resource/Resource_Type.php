<?php
/**
 * Resource_Type
 *
 * Models the Resource_Type table
 *
 * @class	Resource_Type
 */
class Resource_Type extends ORM_Cached
{
	protected 			$_strTableName			= "resource_type";
	protected static	$_strStaticTableName	= "resource_type";
	
	/**
	 * getFor()
	 *
	 * constructor
	 *
	 * @param	string	$strWhere					WHERE clause (can also include GROUP BY, ORDER BY and LIMIT clauses)
	 * @param	boolean	$bolAsArray		[optional]	If set to TRUE, will return Associative Arrays instead of objects
	 *
	 * @return	void
	 *
	 * @constructor
	 */
	public static function getFor($strWhere, $bolAsArray=false)
	{
		static	$qryQuery;
		$qryQuery	= ($qryQuery) ? $qryQuery : new Query();
		
		// Perform Query
		$strSQL		= "SELECT * FROM resource_type WHERE {$strWhere}";
		$resResult	= $qryQuery->Execute($strSQL);
		if ($resResult === false)
		{
			throw new Exception($qryQuery->Error());
		}
		else
		{
			// Return records as an array of either associative arrays, or Resource_Type objects
			$arrRecords	= array();
			while ($arrRecord = $resResult->fetch_assoc())
			{
				$arrRecords[]	= ($bolAsArray) ? $arrRecord : new Resource_Type($arrRecord);
			}
			return $arrRecords;
		}
	}
	
	/**
	 * validateFileName()
	 *
	 * constructor
	 *
	 * @param	integer	$intResourceType			WHERE clause (can also include GROUP BY, ORDER BY and LIMIT clauses)
	 * @param	boolean	$bolAsArray		[optional]	If set to TRUE, will return Associative Arrays instead of objects
	 *
	 * @return	void
	 *
	 * @constructor
	 */
	public function validateFileName($intResourceTypeId, $strFileName)
	{
		$intResourceTypeId	= (int)$intResourceTypeId;
		$arrResourceTypes	= Resource_Type::getFor("id = {$intResourceTypeId}", true);
		
		if ($arrResourceTypes[0])
		{
			return ($arrResourceTypes[0]['file_name_regex']) ? preg_match($arrResourceTypes[0]['file_name_regex'], $strFileName) : true;
		}
		else
		{
			return false;
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
	
	public static function importResult($aResultSet)
	{
		return parent::importResult($aResultSet, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
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
		static	$arrPreparedStatements	= Array();
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