<?php
/**
 * Delivery_Method
 *
 * Models a record of the delivery_method table
 *
 * @class	Delivery_Method
 */
class Delivery_Method extends ORM_Enumerated
{	
	protected 			$_strTableName			= "delivery_method";
	protected static	$_strStaticTableName	= "delivery_method";
	
	protected			$_arrCustomerGroupSettings;
	
	protected static	$_aAccountSettingOptions;
	
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
	
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Enumerated UNTIL WE START USING PHP 5.3 - START
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
	
	public static function getForSystemName($strSystemName)
	{
		return parent::getForSystemName($strSystemName, __CLASS__);
	}
	
	public static function getIdForSystemName($strSystemName)
	{
		return parent::getIdForSystemName($strSystemName, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Enumerated UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//
	
	/**
	 * getCustomerGroupSettings()
	 *
	 * Returns a Delivery_Method_Customer_Group object or array for the given Id
	 *
	 * @param	integer		$intCustomerGroupId					The Customer Group Id of the Record to return
	 * @param	[boolean	$bolAsArray				]	TRUE	: Return associative array
	 * 													FALSE	: Return Delivery_Method_Customer_Group object (default)
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public function getCustomerGroupSettings($intCustomerGroupId, $bolAsArray=false, $bolForceRecache=false)
	{
		// Cache if necessary
		if (!$this->_arrCustomerGroupSettings || $bolForceRecache)
		{
			$this->_arrCustomerGroupSettings	= array();
			
			// Cache the Enumeration
			$selCustomerGroupDeliveryMethod	= self::_preparedStatement('selCustomerGroupDeliveryMethod');
			if ($selCustomerGroupDeliveryMethod->Execute($this->toArray()) === false)
			{
				throw new Exception($selCustomerGroupDeliveryMethod->Error());
			}
			while ($arrCustomerGroupDeliveryMethod = $selCustomerGroupDeliveryMethod->Fetch())
			{
				$this->_arrCustomerGroupSettings[$arrCustomerGroupDeliveryMethod['customer_group_id']]	= new Customer_Group_Delivery_Method($arrCustomerGroupDeliveryMethod);
			}
		}
		
		// Retrieve the Data
		if (array_key_exists($intCustomerGroupId, $this->_arrCustomerGroupSettings))
		{
			return ($bolAsArray) ? $this->_arrCustomerGroupSettings[$intCustomerGroupId]->toArray() : $this->_arrCustomerGroupSettings[$intCustomerGroupId];
		}
		else
		{
			throw new Exception("Unable to find Delivery Method {$this->name} settings for Customer Group #{$intCustomerGroupId}");
		}
	}

	/**
	 * getAccountSettingOptions()
	 *
	 * Retrieves a list of Delivery_Method objects that are allowable options for the Account settings
	 *
	 * @param	bool		$bRefresh=false					If true, will refesh any established cache of the function results
	 * 
	 * @return	array										Array of allowable options (with the 'id' property as the key)
	 *
	 * @static
	 * @method
	 */
	public static function getAccountSettingOptions($bRefresh=false)
	{
		if (!isset(self::$_aAccountSettingOptions) || $bRefresh)
		{
			self::$_aAccountSettingOptions	= array();
			
			// Retrieve List
			$oAccountSettingOptions	= self::_preparedStatement('selAccountSettingOptions');
			if ($oAccountSettingOptions->Execute() === false)
			{
				throw new Exception($oAccountSettingOptions->Error());
			}
			while ($aDeliveryMethod = $oAccountSettingOptions->Fetch())
			{
				// Add to Account Setting Options cache
				self::$_aAccountSettingOptions[$aDeliveryMethod['id']]	= new Delivery_Method($aDeliveryMethod);
				
				// Add to general cache
				self::addToCache(self::$_aAccountSettingOptions[$aDeliveryMethod['id']]);
			}
		}
		
		// Return Array of valid Account Setting Options
		return self::$_aAccountSettingOptions;
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "name ASC");
					break;
				case 'selCustomerGroupDeliveryMethod':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("customer_group_delivery_method", "*", "delivery_method_id = <id> AND id = (SELECT id FROM customer_group_delivery_method cgdm2 WHERE delivery_method_id = <id> AND customer_group_id = customer_group_delivery_method.customer_group_id ORDER BY id DESC LIMIT 1)");
					break;
				case 'selAccountSettingOptions':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "account_setting = 1", "id ASC");
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