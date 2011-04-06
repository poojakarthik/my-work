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
	
	public function isActive()
	{
		return (($this->Active == 1) ? true : 0);
	}
	
	public static function getForCarrierModuleType($mCarrierModuleType, $bIncludeInactive=false)
	{
		return self::getForCarrierModuleTypeAndCustomerGroup($mCarrierModuleType, null, $bIncludeInactive);
	}
	
	public static function getForCarrierModuleTypeAndCustomerGroup($mCarrierModuleType, $iCustomerGroupId=null, $bIncludeInactive=false)
	{
		$oStatement	= self::_preparedStatement('selForCarrierModuleTypeAndCustomerGroup');
		
		if (false === $oStatement->Execute(array('carrier_module_type_id'=>ORM::extractId($mCarrierModuleType), 'customer_group_id'=>$iCustomerGroupId, 'include_inactive'=>(($bIncludeInactive) ? 1 : 0))))
		{
			throw new Exception_Database($oStatement->Error());
		}
		
		$aRecords	= array();
		while ($aRecord = $oStatement->Fetch())
		{
			$oInstace					= new self($aRecord);
			$aRecords[$oInstace->id]	= $oInstace;
		}
		
		return $aRecords;
	}
	
	public static function getForDefinition($mCarrierModuleType, $mResourceType, $mCarrier, $mCustomerGroup=null, $bIncludeInactive=false)
	{
		$oStatement	= self::_preparedStatement('selForDefinition');
		
		$iRows	= $oStatement->Execute(array('carrier_module_type_id'=>ORM::extractId($mCarrierModuleType), 'resource_type_id'=>ORM::extractId($mResourceType), 'carrier_id'=>ORM::extractId($mCarrier), 'customer_group_id'=>ORM::extractId($mCustomerGroup), 'include_inactive'=>(int)!!$bIncludeInactive));
		if ($iRows === false)
		{
			throw new Exception_Database($oStatement->Error());
		}
		
		$aRecords	= array();
		while ($aRecord = $oStatement->Fetch())
		{
			$oInstace					= new self($aRecord);
			$aRecords[$oInstace->id]	= $oInstace;
		}
		
		return $aRecords;
	}
	
	public static function getLatestActiveForCarrierModuleType($iCarrierModuleType)
	{
		$oStatement	= self::_preparedStatement('selLatestActiveForCarrierModuleType');
		if ($oStatement->Execute(array('carrier_module_type_id' => $iCarrierModuleType)) === false)
		{
			throw new Exception_Database($oStatement->Error());
		}
		
		if ($aRow = $oStatement->Fetch())
		{
			return new self($aRow);
		}
		return null;
	}
	
	public function getConfig()
	{
		if (!isset($this->_oCarrierModuleConfigSet))
		{
			$this->_oCarrierModuleConfigSet	= Carrier_Module_Config_Set::getForCarrierModule($this);
		}
		return $this->_oCarrierModuleConfigSet;
	}
	
	public static function searchFor($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$oFrequencyTypes = Constant_Group::getConstantGroup('FrequencyType');
		
		$aAliases = array(
						'id'						=> "cm.id",
						'carrier_id'				=> "cm.Carrier",
						'carrier_name'				=> "c.Name",
						'customer_group_id'			=> "cm.customer_group",
						'customer_group_name'		=> "cg.internal_name",
						'carrier_module_type_id'	=> "cm.Type",
						'carrier_module_type_name'	=> "cmt.name",
						'file_type_id'				=> "rt.id",
						'file_type_name'			=> "rt.name",
						'module'					=> "cm.Module",
						'description'				=> "cm.description",
						'frequency_type'			=> "cm.FrequencyType",
						'frequency_type_name'		=> "(
															CASE
																WHEN cm.FrequencyType = ".FREQUENCY_SECOND."
																THEN '".$oFrequencyTypes->getConstantName(FREQUENCY_SECOND)."'
																WHEN cm.FrequencyType = ".FREQUENCY_MINUTE."
																THEN '".$oFrequencyTypes->getConstantName(FREQUENCY_MINUTE)."'
																WHEN cm.FrequencyType = ".FREQUENCY_HOUR."
																THEN '".$oFrequencyTypes->getConstantName(FREQUENCY_HOUR)."'
																WHEN cm.FrequencyType = ".FREQUENCY_DAY."
																THEN '".$oFrequencyTypes->getConstantName(FREQUENCY_DAY)."'
															END
														)",
						'frequency'					=> "cm.Frequency",
						'frequency_value'			=> "(
															cm.Frequency 
															* 
															cm.FrequencyType
															*
															(
																CASE
																	WHEN cm.FrequencyType = ".FREQUENCY_SECOND."
																	THEN 1
																	WHEN cm.FrequencyType = ".FREQUENCY_MINUTE."
																	THEN 60
																	WHEN cm.FrequencyType = ".FREQUENCY_HOUR."
																	THEN 3600
																	WHEN cm.FrequencyType = ".FREQUENCY_DAY."
																	THEN 86400
																END
															)
														)",
						'last_sent_datetime'		=> "cm.LastSentOn",
						'earliest_delivery'			=> "cm.EarliestDelivery",
						'is_active'					=> "cm.Active",
						'is_active_label'			=> "IF(cm.Active = 1, 'Active', 'Disabled')",
					);
		
		$sFrom = "	CarrierModule cm
					JOIN Carrier c ON (c.Id = cm.Carrier)
					JOIN carrier_module_type cmt ON (cmt.id = cm.Type)
					JOIN resource_type rt ON (rt.id = cm.FileType)
					LEFT JOIN CustomerGroup cg ON (cg.Id = cm.customer_group)";
		if ($bCountOnly)
		{
			$sSelect 	= "COUNT(cm.id) AS count";
			$sOrderBy	= "";
			$sLimit		= "";
		}
		else
		{
			$aSelectLines = array();
			foreach ($aAliases as $sAlias => $sClause)
			{
				$aSelectLines[] = "{$sClause} AS {$sAlias}";
			}
			$sSelect	= implode(', ', $aSelectLines);
			$sOrderBy	= Statement::generateOrderBy($aAliases, get_object_vars($oSort));
			$sLimit		= Statement::generateLimit($iLimit, $iOffset);
		}
		
		$aWhere	= Statement::generateWhere($aAliases, get_object_vars($oFilter));
		$sWhere	= $aWhere['sClause'];
		
		$oSelect = new StatementSelect($sFrom, $sSelect, $sWhere, $sOrderBy, $sLimit);
		if ($oSelect->Execute($aWhere['aValues']) === false)
		{
			throw new Exception_Database("Failed to get search results. ".$oSelect->Error());
		}
		
		Log::getLog()->log($oSelect->_strQuery);
		
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1");
					break;
				case 'selForCarrierModuleTypeAndCustomerGroup':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Type = <carrier_module_type_id> AND (ISNULL(<customer_group_id>) OR ISNULL(customer_group) OR <customer_group_id> = customer_group) AND (<include_inactive> = 1 OR Active = 1)");
					break;
				case 'selForDefinition':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	self::$_strStaticTableName,
																					"*",
																					"	Type = <carrier_module_type_id>
																						AND FileType = <resource_type_id>
																						AND Carrier = <carrier_id>
																						AND <customer_group_id> <=> customer_group
																						AND (<include_inactive> = 1 OR Active = 1)");
					break;
				case 'selLatestActiveForCarrierModuleType':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Type = <carrier_module_type_id> AND Active = 1", "Id DESC", 1);
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