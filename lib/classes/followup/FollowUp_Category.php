<?php
/**
 * FollowUp_Category
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	FollowUp_Category
 */
class FollowUp_Category extends ORM_Cached
{
	protected 			$_strTableName			= "followup_category";
	protected static	$_strStaticTableName	= "followup_category";
	
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
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function searchFor($iLimit=null, $iOffset=null, $aSort=null, $aFilter=null)
	{
		$sFromClause	= self::$_strStaticTableName;
		$sSelectClause	= '*';
		$sWhereClause	= '';
		$sOrderByClause	= '';
		$sLimitClause	= '';
		$aColumns		= self::_getColumns();
		$oQuery			= new Query();
		
		// WHERE clause
		if (is_array($aFilter))
		{
			$aWhereConditions	= array();
			foreach ($aFilter as $sFieldName => $mValue)
			{
				// Validate the filter field name before adding it
				if (isset($aColumns[$sFieldName]))
				{
					if (is_array($mValue))
					{
						foreach ($mValue as $sName => $mVal)
						{
							$mVal	= self::_getColumnValue($oQuery, $mVal);
						}
						
						// Value is an array of values, convert to IN
						$aWhereConditions[]	= "{$sFieldName} IN (".implode(', ', $mValue).")";
					}
					else if (is_object($mValue) && $mValue->mFrom || $mValue->mTo)
					{
						$mFrom	= self::_getColumnValue($oQuery, $mValue->mFrom);
						$mTo	= self::_getColumnValue($oQuery, $mValue->mTo);
						
						if ($mValue->mFrom && $mValue->mTo)
						{
							// BETWEEN
							$aWhereConditions[]	= "{$sFieldName} BETWEEN {$mFrom} AND {$mTo}";
						}
						else if ($mValue->mFrom)
						{
							// > (Greater than)
							$aWhereConditions[]	= "{$sFieldName} > {$mFrom}";
						}
						else if ($mValue->mTo)
						{
							// < (Less than)
							$aWhereConditions[]	= "{$sFieldName} < {$mTo}";
						}
					}
					else
					{
						// Value is a single value
						$aWhereConditions[]	= "{$sFieldName} = ".self::_getColumnValue($oQuery, $mValue);
					}
				}
			}
			
			if (count($aWhereConditions))
			{
				$sWhereClause	= implode(' AND ', $aWhereConditions);
			}
		}
		
		// ORDER BY clause
		if (is_array($aSort))
		{
			$aSortFields	= array();
			foreach ($aSort as $sFieldName => $sDirection)
			{
				if (isset($aColumns[$sFieldName]))
				{
					$aSortFields[]	= "{$sFieldName} {$sDirection}";
				}
			}
			
			if (count($aSortFields))
			{
				$sOrderByClause	= implode(', ', $aSortFields);
			}
		}
		
		// LIMIT clause
		if ($iLimit !== NULL)
		{
			$sLimitClause = intval($iLimit);
			if ($iOffset !== NULL)
			{
				$sLimitClause .= " OFFSET ". intval($iOffset);
			}
			else
			{
				$iOffset = 0;
			}
		}
		
		// Get results
		$oSelect	= new StatementSelect($sFromClause, $sSelectClause, $sWhereClause, $sOrderByClause, $sLimitClause);
		if ($oSelect->Execute() === FALSE)
		{
			throw new Exception("Failed to retrieve records for '{self::$_strStaticTableName} Search' query - ". $oSelect->Error());
		}
		
		// Create 'self' objects for each
		$aResults	= array();
		while ($aRow = $oSelect->Fetch())
		{
			$aResults[$aRow['id']]	= new self($aRow);
		}
		
		return $aResults;
	}

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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
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
	
	// Retrieves a list of column names (array['ActualColumnName'] = tidyName)
	private static function _getColumns()
	{
		static $aColumns;
		
		if (!isset($aColumns))
		{
			$aTableDefine = DataAccess::getDataAccess()->FetchTableDefine(self::$_strStaticTableName);
			
			foreach ($aTableDefine['Column'] as $sName=>$aColumn)
			{
				$aColumns[$sName] = self::tidyName($sName);
			}
			$aColumns[$aTableDefine['id']] = self::tidyName($aTableDefine['id']);
		}
		
		return $aColumns;
	}
}
?>