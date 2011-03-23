<?php
/**
 * Collection_Restriction
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Collection_Restriction
 */
class Collection_Suspension extends ORM_Cached
{
	protected 			$_strTableName			= "collection_suspension";
	protected static	$_strStaticTableName	= "collection_suspension";
	
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

    public static function getActive()
    {
        return self::getFor(array('effective_end_datetime' => 'null'));
    }

    public static function getActiveForAccount($iAccountId) {
        $aResult = self::getFor(array('effective_end_datetime' => 'null', 'account_id'=>$iAccountId));
        return count($aResult)>0 ? $aResult[0] : null;

    }

	public static function getForAccountId($iAccountId)
	{
		return self::getFor(array('account_id' => $iAccountId));
	}

    public static function getFor($aCriteria)
    {
        $aWhere	= StatementSelect::generateWhere(null, $aCriteria);
        $oQuery	= new StatementSelect(self::$_strStaticTableName, "*", $aWhere['sClause']);
        $mixResult			= $oQuery->Execute($aWhere['aValues']);
        $arrRecordSet	= $oQuery->FetchAll();
        $aResult = array();
        foreach($arrRecordSet as $aRecord)
        {
            $aResult[] = new self($aRecord);
        }
        return $aResult;
    }

	public function getReason() {
		return Collection_Suspension_Reason::getForId($this->collection_suspension_reason_id);
	}

	public function getEndReason() {
		return (isset($this->collection_suspension_end_reason_id)) ? Collection_Suspension_End_Reason::getForId($this->collection_suspension_end_reason_id) : null;
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

	public function end($iEndReasonId)
	{
		$this->effective_end_datetime				= DataAccess::getDataAccess()->getNow();
		$this->end_employee_id						= Flex::getUserId();
		$this->collection_suspension_end_reason_id 	= $iEndReasonId;
		$this->save();
	}

	public function calculateStartDatetime($sEffectiveDatetime=null)
	{
		$this->start_datetime = self::getEarliestStartDatetimeForAccount($this->account_id, $sEffectiveDatetime);
	}
	
	public static function getEarliestStartDatetimeForAccount($iAccountId, $sEffectiveDatetime=null)
	{
		$oAccount 			= Logic_Account::getInstance($iAccountId);
		$sDueDate 			= date('Y-m-d H:i:s', strtotime($oAccount->getCurrentDueDate()));
		$sEffectiveDatetime	= ($sEffectiveDatetime === null ? DataAccess::getDataAccess()->getNow() : $sEffectiveDatetime);
		$iNow 				= strtotime($sEffectiveDatetime);
		$iDue 				= strtotime($sDueDate);
		
		if ($iNow < $iDue)
		{
			// Account is not overdue yet, wait until the due date
			$sStartDate = $sDueDate;
		}
		else
		{
			// Account is in collections, start immediately
			$sStartDate = $sEffectiveDatetime;
		}
		
		return $sStartDate;
	}
	
	public function save()
	{
		if (strtotime($this->proposed_end_datetime) <= strtotime($this->start_datetime))
		{
			throw new Exception('Cannot end a suspension before it starts.');
		}
		
		parent::save();
	}

	public static function getSuspensionsForCurrentCollectionsPeriod($iAccountId, $sEffectiveDatetime=null)
	{
		$oSelect 			= self::_preparedStatement('selAllForCurrentSuspensionPeriod');
		$sEffectiveDatetime	= ($sEffectiveDatetime === null ? DataAccess::getDataAccess()->getNow() : $sEffectiveDatetime);
		if ($oSelect->Execute(array('account_id' => $iAccountId, 'effective_datetime' => $sEffectiveDatetime)) === false)
		{
			throw new Exception_Database("Failed to get suspensions for current collections period. ".$oSelect->Error());
		}
		return ORM::importResult($oSelect->FetchAll(), 'Collection_Suspension');
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
				case 'selAllForCurrentSuspensionPeriod':
					$arrPreparedStatements[$strStatement]	=	new StatementSelect(
																	"	collection_suspension cs
																		JOIN collection_suspension_reason csr ON (csr.id = cs.collection_suspension_reason_id)", 
																	"	cs.*", 
																	"	csr.system_name <> 'TIO_COMPLAINT'
																		AND     cs.account_id = <account_id>
																		AND     cs.start_datetime BETWEEN (
																		            COALESCE(
																		                (
																		                    SELECT  MIN(scheduled_datetime)
																		                    FROM    account_collection_event_history
																		                    WHERE   account_id = cs.account_id
																		                ), 
																		                '1970-01-01'
																		            )
																		        ) AND NOW()"
																);
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