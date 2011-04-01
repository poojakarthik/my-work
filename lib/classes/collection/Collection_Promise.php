<?php
/**
 * Collection_Promise
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Collection_Promise
 */
class Collection_Promise extends ORM_Cached
{
	protected 			$_strTableName			= "collection_promise";
	protected static	$_strStaticTableName	= "collection_promise";
	
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

        	public static function getActivePromises()
        {
            return self::getFor(array('completed_datetime' => 'null'));
        }

	public function getCurrentForAccountId($iAccountId)
	{
		$aCurrent = self::getFor(array('account_id' => $iAccountId, 'completed_datetime' => 'NULL'));
		return (count($aCurrent) > 0 ? $aCurrent[0] : null);
	}

	public function getForAccountId($iAccountId, $bActiveOnly = true)
	{
	    if ($bActiveOnly)
	    {
			$aResult = self::getFor(array('account_id' => $iAccountId, 'completed_datetime'=>'null'));
			return count($aResult) > 0 ? $aResult[0] : null;
	    }
	    else
	    {
			return self::getFor(array('account_id' => $iAccountId));
	    }
	}

	public static function getFor($aCriteria)
	{
	    $aWhere	    = StatementSelect::generateWhere(null, $aCriteria);
	    $oQuery	    = new StatementSelect(self::$_strStaticTableName, "*", $aWhere['sClause']);
	    $mixResult	    = $oQuery->Execute($aWhere['aValues']);
	    $arrRecordSet   = $oQuery->FetchAll();
	    $aResult	    = array();
	    foreach($arrRecordSet as $aRecord)
	    {
		$aResult[] = new self($aRecord);
	    }
	    return $aResult;
	}

	public function getInstalments() {
		return Collection_Promise_Instalment::getForPromiseId($this->id);
	}

	public function getReason() {
		return Collection_Promise_Reason::getForId($this->collection_promise_reason_id);
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