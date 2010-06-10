<?php
/**
 * FollowUp_Recurring_Action
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	FollowUp_Recurring_Action
 */
class FollowUp_Recurring_Action extends ORM_Cached
{
	protected 			$_strTableName			= "followup_recurring_action";
	protected static	$_strStaticTableName	= "followup_recurring_action";
	
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

	public static function getForFollowUpRecurringId($iFollowUpRecurringId)
	{
		$oSelect	= self::_preparedStatement('selByFollowUpRecurringId');
		$oSelect->Execute(array('followup_recurring_id' => $iFollowUpRecurringId));
		
		if ($aRow = $oSelect->Fetch())
		{
			return new self($aRow);	
		}
		
		return null;
	}

	public static function getFollowUpRecurringsForAction($iActionId)
	{
		$aFollowUps	= array();
		$oSelect	= self::_preparedStatement('selByActionId');
		$oSelect->Execute(array('action_id' => $iActionId));
		
		while ($aRow = $oSelect->Fetch())
		{
			$aFollowUps[]	= FollowUp_Recurring::getForId($aRow['followup_recurring_id']);	
		}
		
		return $aFollowUps;
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
				case 'selByFollowUpRecurringId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "followup_recurring_id = <followup_recurring_id>", NULL, 1);
					break;
				case 'selByActionId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "action_id = <action_id>", NULL, 1);
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