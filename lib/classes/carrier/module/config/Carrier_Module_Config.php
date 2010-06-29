<?php
/**
 * Carrier_Module_Config
 *
 * Represents a Record in the CarrierModuleConfig table
 *
 * @class	Carrier_Module_Config
 */
class Carrier_Module_Config extends ORM_Cached
{
	protected 			$_strTableName			= "CarrierModuleConfig";
	protected static	$_strStaticTableName	= "CarrierModuleConfig";
	
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
	
	public static function getForCarrierModule($mCarrierModule)
	{
		$oStatement	= self::_preparedStatement('selForCarrierModule');
		
		if (false === $oStatement->Execute(array('carrier_module_id'=>ORM::extractId($mCarrierModule))))
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
	
	public function __get($sProperty)
	{
		$mValue	= parent::__get($sProperty);
		
		if (strtolower($sProperty) === 'value')
		{
			// Perform Cast
			$mRawValue	= $mValue;
			switch ($this->Type)
			{
				case DATA_TYPE_INTEGER:
					$mValue	= (int)$mValue;
					break;
					
				case DATA_TYPE_FLOAT:
					$mValue	= (float)$mValue;
					break;
					
				case DATA_TYPE_BOOLEAN:
					$mValue	= (bool)$mValue;
					break;
					
				case DATA_TYPE_SERIALISED:
				case DATA_TYPE_ARRAY:
					$mValue	= unserialize($mValue);
					break;
				
				case DATA_TYPE_STRING:
				default:
					$mValue	= (string)$mValue;
					break;
			}
			Log::getLog()->log("Casting .{$sProperty} from '{$mRawValue}' to Integer '".print_r($mValue, true)."'");
		}
		
		return $mValue;
	}
	
	public function __set($sProperty, $mValue)
	{
		if (strtolower($sProperty) === 'value')
		{
			// Perform Cast
			switch ($this->Type)
			{
				case DATA_TYPE_INTEGER:
					$mValue	= (int)$mValue;
					break;
					
				case DATA_TYPE_FLOAT:
					$mValue	= (float)$mValue;
					break;
					
				case DATA_TYPE_BOOLEAN:
					$mValue	= (bool)$mValue;
					break;
					
				case DATA_TYPE_SERIALISED:
				case DATA_TYPE_ARRAY:
					$mValue	= serialize($mValue);
					break;
				
				case DATA_TYPE_STRING:
				default:
					$mValue	= (string)$mValue;
					break;
			}
			$mValue	= (string)$mValue;
		}
		
		parent::__set($sProperty, $mValue);
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
				case 'selForCarrierModule':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "CarrierModule = <carrier_module_id>");
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