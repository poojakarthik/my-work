<?php
/**
 * Carrier_Payment_Type
 *
 * Represents a Record in the carrier_payment_type table
 *
 * @class	Carrier_Payment_Type
 */
class Carrier_Payment_Type extends ORM_Cached
{
	protected 			$_strTableName			= "carrier_payment_type";
	protected static	$_strStaticTableName	= "carrier_payment_type";
	
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
	
	public function calculateSurcharge($fAmount, $iPrecision=2)
	{
		return round($fAmount * (float)$this->surcharge_percent, 2);
	}
	
	public static function getForCarrierAndPaymentType($mCarrier, $mPaymentType)
	{
		$iCarrierId		= ORM::extractId($mCarrier);
		$iPaymentTypeId	= ORM::extractId($mPaymentType);
		
		$oStatement	= self::_preparedStatement('selForCarrierAndPaymentType');
		if ($oStatement->Execute(array('carrier_id'=>$iCarrierId, 'payment_type_id'=>$iPaymentTypeId)) === false)
		{
			throw new Exception_Database($oStatement->Error());
		}
		elseif ($aCarrierPaymentType = $oStatement->Fetch())
		{
			$oCarrierPaymentType				= new Carrier_Payment_Type($aCarrierPaymentType);
			self::addToCache($oCarrierPaymentType);
			return $oCarrierPaymentType;
		}
		else
		{
			return null;
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1");
					break;
				case 'selForCarrierAndPaymentType':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "carrier_id = <carrier_id> AND payment_type_id = <payment_type_id>", "id DESC", 1);
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