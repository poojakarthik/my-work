<?php
/**
 * Carrier_Module
 *
 * Represents a Record in the CarrierModule table
 *
 * @class	Carrier_Module
 */
class Carrier_Module extends ORM_Cached
{
	protected 			$_strTableName			= "CarrierModule";
	protected static	$_strStaticTableName	= "CarrierModule";
	
	protected			$_oCarrierModuleConfigSet;
	
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
	
	public static function getForCarrierModuleType($mCarrierModuleType, $bIncludeInactive=false)
	{
		return self::getForCarrierModuleTypeAndCustomerGroup($mCarrierModuleType, null, $bIncludeInactive);
	}
	
	public static function getForCarrierModuleTypeAndCustomerGroup($mCarrierModuleType, $iCustomerGroupId=null, $bIncludeInactive=false)
	{
		$oStatement	= self::_preparedStatement('selForCarrierModuleTypeAndCustomerGroup');
		
		if (false === $oStatement->Execute(array('carrier_module_type_id'=>ORM::extractId($mCarrierModuleType), 'customer_group_id'=>$iCustomerGroupId, 'include_inactive'=>(($bIncludeInactive) ? 1 : 0))))
		{
			throw new Exception($oStatement->Error());
		}
		
		$aRecords	= array();
		while ($aRecord = $oStatement->Fetch())
		{
			$oInstace					= new self($aRecord);
			$aRecords[$oInstace->id]	= $oInstace;
		}
		
		return $aRecords;
	}
	
	public function getConfig()
	{
		if (!isset($this->_oCarrierModuleConfigSet))
		{
			$this->_oCarrierModuleConfigSet	= Carrier_Module_Config_Set::getForCarrierModule($this);
		}
		return $this->_oCarrierModuleConfigSet;
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
				case 'selForCarrierModuleType':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Type = <carrier_module_type_id> AND (ISNULL(<customer_group_id>) OR <customer_group_id> = customer_group) AND (<include_inactive> = 0 OR Active = 1)");
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