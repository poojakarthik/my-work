<?php
/**
 * Rate_Plan_Discount
 *
 * Models the rate_plan_discount Table
 *
 * @class	Rate_Plan_Discount
 */
class Rate_Plan_Discount extends ORM_Cached
{
	protected 			$_strTableName			= "rate_plan_discount";
	protected static	$_strStaticTableName	= "rate_plan_discount";
	
	/**
	 * getForRatePlanId
	 * 
	 * Returns an array of Rate_Plan_Discount objects associated with a given Rate Plan.
	 * This method will add results to the Cache, however it will not read from the Cache
	 * 
	 * @return	array
	 */
	public static function getForRatePlanId($iRatePlanId)
	{
		$aRatePlanDiscounts	= array();
		
		$oSelectRatePlanDiscounts	= self::_preparedStatement('selByRatePlanId');
		$iResult					= $oSelectRatePlanDiscounts->Execute(array('rate_plan_id'=>$iRatePlanId));
		if ($iResult === false)
		{
			throw new Exception_Database($oSelectRatePlanDiscounts->Error());
		}
		while ($aRatePlanDiscount = $oSelectRatePlanDiscounts->Fetch())
		{
			// Create new Discount_Record_Type object and manually add to the Cache
			$oRatePlanDiscount	= new self($aRatePlanDiscount);
			self::addToCache($oRatePlanDiscount);
			
			$aRatePlanDiscounts[$oRatePlanDiscount->id]	= $oRatePlanDiscount;
		}
		
		return $aRatePlanDiscounts;
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
					
				case 'selByRatePlanId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "rate_plan_id = <rate_plan_id>");
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