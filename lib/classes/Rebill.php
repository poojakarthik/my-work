<?php
/**
 * Rebill
 *
 * This is a class that extends ORM_Cached
 *
 * @class	Rebill
 */
class Rebill extends ORM_Cached
{
	protected 			$_strTableName			= "rebill";
	protected static	$_strStaticTableName	= "rebill";
	
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

	public static function getForAccountId($iAccountId, $bSecondLatest=false)
	{
		$oSelect	= self::_preparedStatement('selByAccountId');
		$oSelect->Execute(array('account_id' => $iAccountId));
		
		if ($aLatest = $oSelect->Fetch())
		{
			if ($bSecondLatest)
			{
				// Try second latest
				$aSecondLatest	= $oSelect->Fetch();
				
				if ($aSecondLatest)
				{
					// Got it use it
					return new self($aSecondLatest);
				}
				
				// Don't have second latest, fallback to latest
			}
			
			// Either want latest or don't have second latest
			return new self($aLatest);
		}
		else
		{
			return false;
		}
	}
	
	public function getDetails()
	{
		switch ($this->rebill_type_id)
		{
			case REBILL_TYPE_MOTORPASS:
				return Rebill_Motorpass::getForRebillId($this->id);
				break;
		}
	}

	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement	Name of the statement
	 * 
	 * @return	Statement					The requested Statement
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1");
					break;
				case 'selByAccountId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "account_id = <account_id>", "created_timestamp DESC");
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