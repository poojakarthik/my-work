<?php
/**
 * Adjustment_Review_Outcome
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Adjustment_Review_Outcome
 */
class Adjustment_Review_Outcome extends ORM_Cached
{
	protected 			$_strTableName			= "adjustment_review_outcome";
	protected static	$_strStaticTableName	= "adjustment_review_outcome";
	
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

	public static function getForSystemName($sSystemName)
	{
		// Get record by system name
		$oSelect = self::_preparedStatement('selBySystemName');
		if ($oSelect->Execute(array('system_name' => $sSystemName)) === false)
		{
			throw new Exception_Database("Failed to get adjustment review outcome by system name '{$sSystemName}'. ".$oSelect->Error());
		}
		
		// Return ORM
		$aRow = $oSelect->Fetch();
		return ($aRow ? new self($aRow) : null);
	}
	
	public static function getForAdjustmentReviewOutcomeType($iAdjustmentReviewOutcomeTypeId)
	{
		// Get record by system name
		$oSelect = self::_preparedStatement('selByAdjustmentReviewOutcomeType');
		if ($oSelect->Execute(array('adjustment_review_outcome_type_id' => $iAdjustmentReviewOutcomeTypeId)) === false)
		{
			throw new Exception_Database("Failed to get adjustment review outcome by Adjustment Review Outcome Type '{$iAdjustmentReviewOutcomeTypeId}'. ".$oSelect->Error());
		}
		
		// Return ORM
		return ORM::importResult($oSelect->FetchAll(), 'Adjustment_Review_Outcome');
	}

	public function isActive()
	{
		return ($this->status_id == STATUS_ACTIVE);
	}

	public static function searchFor($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$aAliases = array(
						'id'									=> "aro.id",
						'name'									=> "aro.name",
						'description'							=> "aro.description",
						'is_system'								=> "IF(aro.system_name IS NOT NULL, 1, 0)",
						'status_id'								=> "aro.status_id",
						'status_name' 							=> "s.name",
						'adjustment_review_outcome_type_id'		=> "aro.adjustment_review_outcome_type_id",
						'adjustment_review_outcome_type_name'	=> "arot.name"
					);
		
		$sFrom = "	adjustment_review_outcome aro
					JOIN	adjustment_review_outcome_type arot ON (arot.id = aro.adjustment_review_outcome_type_id)
					JOIN	status s ON (s.id = aro.status_id)";
		if ($bCountOnly)
		{
			$sSelect	= "COUNT(aro.id) AS count";
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
		
		$aWhere 	= Statement::generateWhere($aAliases, get_object_vars($oFilter));
		$oSelect 	= new StatementSelect($sFrom, $sSelect, $aWhere['sClause'], $sOrderBy, $sLimit);
		if ($oSelect->Execute($aWhere['aValues']) === false)
		{
			throw new Exception_Database("Failed to get adjusment review outcome search results. ".$oSelect->Error());
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
				case 'selBySystemName':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "system_name = <system_name>", "id DESC", 1);
					break;
				case 'selByAdjustmentReviewOutcomeType':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "adjustment_review_outcome_type_id = <adjustment_review_outcome_type_id>", "name ASC");
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