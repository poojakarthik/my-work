<?php
/**
 * Collectable
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Collectable
 */
class Collectable extends ORM_Cached
{
	protected 			$_strTableName			= "collectable";
	protected static	$_strStaticTableName	= "collectable";

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

	public static function getForPromiseId($iPromiseId)
	{
		return self::getFor(array('collection_promise_id' => $iPromiseId));
	}


        public static function getForAccount($iAccountId, $bBalanceOwing = true, $iType = Logic_Collectable::DEBIT)
        {
            if (!$bBalanceOwing)
            {
                return self::getFor(array('account_id' => $iAccountId));
            }
            else
            {
                if ($iType == Logic_Collectable::DEBIT )
                {
                    $oBalance = new stdClass();
                    $oBalance->mFrom = 0.0001;
                    return self::getFor(array('account_id'=>$iAccountId, 'balance'=>$oBalance));
                }
                else
                {
                    $oBalance = new stdClass();
                    $oBalance->mTo = -0.0001;
                    return self::getFor(array('account_id'=>$iAccountId, 'balance'=>$oBalance));
                }
            }


	}

	public static function resetBalanceForAccount($iAccountId)
	{
		$oQuery = new Query();
		$sSql = "UPDATE ".self::$_strStaticTableName." SET balance = amount WHERE account_id = $iAccountId";
		$oQuery->Execute($sSql);
	}

	public static function getFor($aCriteria)
	{
		$aWhere	= StatementSelect::generateWhere(null, $aCriteria);
		$oQuery	= new StatementSelect(self::$_strStaticTableName, "*", $aWhere['sClause'], 'due_date ASC');
		$mixResult			= $oQuery->Execute($aWhere['aValues']);
		$arrRecordSet	= $oQuery->FetchAll();
		$aResult = array();
		foreach($arrRecordSet as $aRecord)
		{
			$aResult[] = new self($aRecord);
		}
		return $aResult;
	}

	public static function getForInvoice($mInvoice, $bIncludePromises=false) {
		$oSelectForInvoice	= self::_preparedStatement('selForInvoice');
		if ($oSelectForInvoice->Execute(array('invoice_id'=>ORM::extractId($mInvoice),'include_promises'=>(int)$bIncludePromises)) === false) {
			throw new Exception_Database($oSelectForInvoice->Error());
		}
		return self::importResult($oSelectForInvoice->FetchAll());
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
				case 'selForInvoice':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(
						"	collectable c
							LEFT JOIN collection_promise cp ON (cp.id = c.collection_promise_id)",
						"c.*",
						"	c.invoice_id = <invoice_id>
							AND (<include_promises> != 0 OR c.collection_promise_id IS NULL OR cp.completed_datetime IS NOT NULL)",
						"id ASC"
					);
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