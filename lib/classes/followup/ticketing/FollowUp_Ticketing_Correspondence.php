<?php
/**
 * FollowUp_Ticketing_Correspondence
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	FollowUp_Ticketing_Correspondence
 */
class FollowUp_Ticketing_Correspondence extends ORM_Cached
{
	protected 			$_strTableName			= "followup_ticketing_correspondence";
	protected static	$_strStaticTableName	= "followup_ticketing_correspondence";
	
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

	public function getForFollowUpId($iFollowUpId)
	{
		$oSelect	= self::_preparedStatement('selByFollowUpId');
		$oSelect->Execute(array('followup_id' => $iFollowUpId));
		
		if ($aRow = $oSelect->Fetch())
		{
			return new self($aRow);	
		}
		
		return null;
	}

	public static function getFollowUpsForCorrespondence($iCorrespondenceId)
	{
		$aFollowUps	= array();
		$oSelect	= self::_preparedStatement('selByCorrespondenceId');
		$oSelect->Execute(array('ticketing_correspondence_id' => $iCorrespondenceId));
		
		while ($aRow = $oSelect->Fetch())
		{
			$aFollowUps[]	= FollowUp::getForId($aRow['followup_id']);	
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
				case 'selByFollowUpId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "followup_id = <followup_id>", NULL, 1);
					break;
				case 'selByCorrespondenceId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "ticketing_correspondence_id = <ticketing_correspondence_id>", NULL, 1);
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