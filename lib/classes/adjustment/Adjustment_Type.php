<?php
/**
 * Adjustment_Type
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Adjustment_Type
 */
class Adjustment_Type extends ORM_Cached
{
	protected 			$_strTableName			= "adjustment_type";
	protected static	$_strStaticTableName	= "adjustment_type";
	
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

	public function archive()
	{
		$this->status_id = STATUS_INACTIVE;
		$this->save();
	}

	public function isActive()
	{
		return ($this->status_id == STATUS_ACTIVE);
	}

	public static function searchFor($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$aAliases = array(
						'id'										=> "at.id",
						'code'										=> "at.code",
						'description'								=> "at.description",
						'amount'									=> "at.amount",
						'is_amount_fixed'							=> "at.is_amount_fixed",
						'transaction_nature_id'						=> "at.transaction_nature_id",
						'status_id'									=> "at.status_id",
						'adjustment_type_invoice_visibility_id'		=> "at.adjustment_type_invoice_visibility_id",
						'transaction_nature_code'					=> "tn.code",
						'status_name' 								=> "s.name",
						'adjustment_type_invoice_visibility_name'	=> "ativ.name"
					);
		
		$sFrom = "	adjustment_type at
					JOIN	transaction_nature tn ON (tn.id = at.transaction_nature_id)
					JOIN	status s ON (s.id = at.status_id)
					JOIN	adjustment_type_invoice_visibility ativ ON (ativ.id = at.adjustment_type_invoice_visibility_id)";
		if ($bCountOnly)
		{
			$sSelect	= "COUNT(at.id) AS count";
			$sOrderBy	= "";
			$sLimit		= "";
		}
		else
		{
			$sSelect	= "	{$aAliases['id']} AS id,
							{$aAliases['code']} AS code,
							{$aAliases['description']} AS description,
							{$aAliases['amount']} AS amount,
							{$aAliases['is_amount_fixed']} AS is_amount_fixed,
							{$aAliases['transaction_nature_id']} AS transaction_nature_id,
							{$aAliases['status_id']} AS status_id,
							{$aAliases['adjustment_type_invoice_visibility_id']} AS adjustment_type_invoice_visibility_id,
							{$aAliases['transaction_nature_code']} AS transaction_nature_code,
							{$aAliases['status_name']} AS status_name,
							{$aAliases['adjustment_type_invoice_visibility_name']} AS adjustment_type_invoice_visibility_name
						  ";
			$sOrderBy	= Statement::generateOrderBy(null, get_object_vars($oSort));
			$sLimit		= Statement::generateLimit($iLimit, $iOffset);
		}
		
		$aWhere 	= Statement::generateWhere(null, get_object_vars($oFilter));
		$oSelect 	= new StatementSelect($sFrom, $sSelect, $aWhere['sClause'], $sOrderBy, $sLimit);
		if ($oSelect->Execute($aWhere['aValues']) === false)
		{
			throw new Exception_Database("Failed to get adjusment type search results. ".$oSelect->Error());
		}
		
		if ($bCountOnly)
		{
			$aRow = $oSelect->Fetch();
			return $aRow['count'];
		}
		
		return $oSelect->FetchAll();
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