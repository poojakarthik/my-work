<?php
/**
 * FollowUp_Recurring_Modify_Reason
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	FollowUp_Recurring_Modify_Reason
 */
class FollowUp_Recurring_Modify_Reason extends ORM_Cached
{
	protected 			$_strTableName			= "followup_recurring_modify_reason";
	protected static	$_strStaticTableName	= "followup_recurring_modify_reason";
	
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

	public static function searchFor($iLimit=null, $iOffset=null, $aSort=null, $aFilter=null, $bCountOnly=false)
	{
		$sFromClause	= self::$_strStaticTableName;
		$sSelectClause	= '*';
		$sWhereClause	= '';
		$sOrderByClause	= '';
		$sLimitClause	= '';
		
		// WHERE clause
		$aWhereInfo		= StatementSelect::generateWhere(null, $aFilter);
		
		if ($bCountOnly)
		{
			$sSelectClause	= 'COUNT(id) AS count';
		}
		else
		{		
			// ORDER BY clause
			$sOrderByClause	= Statement::generateOrderBy(null, $aSort);
			
			// LIMIT clause
			$sLimitClause	= Statement::generateLimit($iLimit, $iOffset);
		}
		
		// Get records
		$oSelect	= new StatementSelect($sFromClause, $sSelectClause, $aWhereInfo['sClause'], $sOrderByClause, $sLimitClause);
		if ($oSelect->Execute($aWhereInfo['aValues']) === FALSE)
		{
			throw new Exception_Database("Failed to retrieve records for '{self::$_strStaticTableName} Search' query - ". $oSelect->Error());
		}
		
		if ($bCountOnly)
		{
			$aCount	= $oSelect->Fetch();
			return $aCount['count'];
		}
		else
		{
			// Create objects for each
			$aFollowUpCategorys	= array();
			while ($aRow = $oSelect->Fetch())
			{
				$aFollowUpCategorys[$aRow['id']]	= new self($aRow);
			}
			
			return $aFollowUpCategorys;
		}
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
}
?>