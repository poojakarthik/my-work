<?php
/**
 * Account_Collection_Event_History
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Account_Collection_Event_History
 */
class Account_Collection_Event_History extends ORM_Cached
{
    protected 			$_strTableName			= "account_collection_event_history";
    protected static	$_strStaticTableName	= "account_collection_event_history";





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

    public function getEvent()
    {
	return Collection_Event::getForId($this->collection_event_id);
    }

    public static function getWaitingEvents($iAccountId = null)
    {
        return $iAccountId!== null ? self::getFor(array('completed_datetime'=>'null', 'account_id'=>$iAccountId, 'account_collection_event_status_id'=>ACCOUNT_COLLECTION_EVENT_STATUS_SCHEDULED)) : self::getFor(array('completed_datetime'=>'null', 'account_collection_event_status_id'=>ACCOUNT_COLLECTION_EVENT_STATUS_SCHEDULED));
    }

    public static function getForAccountId($iAccountId)
    {
	    return self::getFor(array('account_id'=>$iAccountId));
    }
    /**
     *  @todo this is a quick and dirty implementation - improve it
     * @param <type> $iAccountId
     * @return <type>
     */
    public static function getMostRecentForAccountId($iAccountId, $iStatus = NULL)
    {
	    $sStatusClause = $iStatus!== NULL ? " AND account_collection_event_status_id = $iStatus" : NULL;
	    $oQuery = new Query();
	    $mResult = $oQuery->Execute("SELECT *
					FROM account_collection_event_history ach
					WHERE scheduled_datetime = (SELECT max(scheduled_datetime) FROM account_collection_event_history WHERE account_id = $iAccountId)
					AND ach.account_id = $iAccountId $sStatusClause
					ORDER by ach.id DESC LIMIT 1");
	    $oResult = NULL;
	    if ($mResult)
	    {
		$aRecord = $mResult->fetch_assoc();
		if ($aRecord)
		    $oResult = new self($aRecord);
	    }
	    else
	    {
		throw new Exception("Failed to retrieve event for account $iAccountId");
	    }

	    return $oResult;

    }



    public static function getFirstForAccountId($iAccountId)
    {
	$oQuery = new Query();
	$mResult = $oQuery->Execute("SELECT *
				    FROM account_collection_event_history ach
				    WHERE ach.account_id = $iAccountId
				    AND ach.id > COALESCE((SELECT max(account_collection_event_history.id)
						  FROM account_collection_event_history
						  JOIN collection_event ce ON (ce.id = account_collection_event_history.collection_event_id AND account_collection_event_history.account_id = $iAccountId)
						  JOIN collection_event_type cet ON (cet.id = ce.collection_event_type_id and cet.system_name = 'EXIT_COLLECTIONS')
						  ), 0)
				    ORDER BY ach.scheduled_datetime, ach.id
				    LIMIT 1");
	if ($mResult)
	{
	    $mRecord = $mResult->fetch_assoc();
	    return $mRecord ? new self($mRecord) : null;
	}

	throw new Exception("Failed to retrieve first event for account $iAccountId");
    }




    public static function getForLedger($bCountOnly=false, $iLimit=0, $iOffset=0, $aSort=null, $aFilter=null)
    {
	$sInvocationId = "	(
								CASE
									WHEN	ceti.enforced_collection_event_invocation_id IS NOT NULL	THEN	ceti.enforced_collection_event_invocation_id
									WHEN	cet.collection_event_invocation_id IS NOT NULL				THEN	cet.collection_event_invocation_id
									WHEN	csce.collection_event_invocation_id IS NOT NULL				THEN	csce.collection_event_invocation_id
									WHEN	ce.collection_event_invocation_id IS NOT NULL				THEN	ce.collection_event_invocation_id
									ELSE	NULL
								END
							)";
	$aAliases =	array(
					'account_collection_event_history_id'	=> "aceh.id",
		    'account_id' 							=> "a.id",
		    'account_name' 							=> "a.BusinessName",
		    'customer_group_internal_name' 			=> "c.internal_name",
		    'collection_scenario_id' 				=> "cs.id",
		    'collection_scenario_name' 				=> "cs.name",
		    'collection_event_id' 					=> "ce.id",
		    'collection_event_type_id' 				=> "ce.collection_event_type_id",
		    'ollection_event_type_name' 			=> "cet.name",
		    'collection_event_name' 				=> "ce.name",
		    'scheduled_datetime' 					=> "aceh.scheduled_datetime",
		    'completed_datetime' 					=> "aceh.completed_datetime",
		    'account_collection_event_status_id' 	=> "aceh.account_collection_event_status_id",
		    'account_collection_event_status_name'	=> "aces.name",
		    'collection_event_invocation_id'		=> $sInvocationId
				);

	// ORDER BY clause (with field alias' for category and type)
	$sOrderByClause	= StatementSelect::generateOrderBy($aAliases, $aSort);

	// WHERE clause info
	$aWhereInfo = StatementSelect::generateWhere($aAliases, $aFilter);

	// Query the temp table 'followup_search'
	$sSearchFrom = "account_collection_event_history aceh
	    JOIN 		Account a ON (a.Id = aceh.account_id)
	    JOIN 		CustomerGroup c ON (a.CustomerGroup = c.Id)
	    JOIN 		collection_event ce ON (aceh.collection_event_id = ce.id)
	    JOIN 		collection_event_type cet ON (cet.id = ce.collection_event_type_id)
					JOIN 		collection_event_type_implementation ceti ON (ceti.id = cet.collection_event_type_implementation_id)
	    JOIN 		account_collection_event_status aces ON (aceh.account_collection_event_status_id = aces.id)
					LEFT JOIN 	collection_scenario_collection_event csce ON (aceh.collection_scenario_collection_event_id = csce.id)
	    LEFT JOIN 	collection_scenario cs ON (cs.id = csce.collection_scenario_id)";

	// Get the count of the unlimited results
	$oCountSelect	=	new StatementSelect(
									$sSearchFrom,
									'COUNT(aceh.id) AS count',
									$aWhereInfo['sClause'],
									'',	// Order By
									'' 	// Limit
								);
	if ($oCountSelect->Execute($aWhereInfo['aValues']) === FALSE)
	{
		throw new Exception_Database("Failed to retrieve record count, query - ". $oCountSelect->Error());
	}

	// Get count row
	$aCount	= $oCountSelect->Fetch();

	if ($bCountOnly)
	{
		// Only want the count, return it
		return $aCount['count'];
	}


	// LIMIT clause
	$sLimitClause = StatementSelect::generateLimit($iLimit, $iOffset);

	$sSelectClause = "  aceh.id AS account_collection_event_history_id,
		    a.id AS account_id,
		    a.BusinessName AS account_name,
		    c.internal_name AS customer_group_internal_name,
		    cs.id AS collection_scenario_id,
		    cs.name AS collection_scenario_name,
		    ce.id AS collection_event_id,
		    ce.collection_event_type_id AS collection_event_type_id,
		    cet.name AS collection_event_type_name,
		    ce.name AS collection_event_name,
		    aceh.scheduled_datetime AS scheduled_datetime,
		    aceh.completed_datetime AS completed_datetime,
		    aceh.account_collection_event_status_id AS account_collection_event_status_id,
		    aces.name AS account_collection_event_status_name,
						{$sInvocationId} AS collection_event_invocation_id
						";

	// Get the limited + offset results
	$oSearchSelect	=	new StatementSelect(
							$sSearchFrom,
							$sSelectClause,
							$aWhereInfo['sClause'],
							$sOrderByClause,
							$sLimitClause
						);
	if ($oSearchSelect->Execute($aWhereInfo['aValues']) === FALSE)
	{
		throw new Exception_Database("Failed to retrieve records, query - ". $oSearchSelect->Error());
	}

	// Return the results as well as the count
	return array('aData' => $oSearchSelect->FetchAll(), 'iCount' => $aCount['count']);

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