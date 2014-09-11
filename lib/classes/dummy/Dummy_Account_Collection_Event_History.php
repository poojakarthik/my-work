<?php
class Dummy_Account_Collection_Event_History extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'account_collection_event_history';
	protected static 	$_sStaticTableName	= 	'account_collection_event_history';
	protected 			$_aProperties		= 	array(


													'account_id' 								=> null,
													'collectable_id' 							=> null,
													'collection_event_id'						=> null,
													'collection_scenario_collection_event_id'	=> null,
													'scheduled_datetime'						=> null,
													'completed_datetime'						=> null,
													'completed_employee_id'						=> null,
													'account_collection_event_status_id'		=> null
												);
	
	public function __construct($aProperties=array(), $bLoadById=false)
	{
		parent::__construct($aProperties, $bLoadById);
	}
	
	public function getEvent()
	{
		return Dummy_Collection_Event::getForId($this->collection_event_id);
	}

        public static function getWaitingEvents()
        {
            return self::getFor(array('completed_datetime'=>null));
        }
	
	public static function getForAccountId($iAccountId)
	{
		$aRecords	= array();
		$aAll		= self::getAll();
		foreach ($aAll as $oRecord)
		{
			if ($oRecord->account_id == $iAccountId)
			{
				$aRecords[]	= $oRecord;
			}
		}
		return $aRecords;
	}
	
	public static function getMostRecentForAccountId($iAccountId)
	{
		$aRecords = self::getForAccountId($iAccountId);
		return (count($aRecords) > 0 ? array_pop($aRecords) : null);
	}

        public static function getForLedger($bCountOnly=false, $iLimit=0, $iOffset=0, $sSort=null, $oFilter=null)
        {
            		// ORDER BY clause (with field alias' for category and type)
		$sOrderByClause	=	StatementSelect::generateOrderBy(
								array(
									'followup_category_id'	=> 'fcat.name',
									'followup_type_id'		=> 'ft.name',
									'assigned_employee_id'	=> "CONCAT(e.FirstName, ' ', e.LastName)"
								),
								$aSort
							);

		// LIMIT clause
		$sLimitClause	= StatementSelect::generateLimit($iLimit, $iOffset);

		$aWhereInfoSearch	= StatementSelect::generateWhere(null, $aFilter);
                $sSelectClause = "  ac.id as 'account_collection_event_history_id,
                                    a.id as 'account_id',
                                    a.name as 'account_name',
                                    c.internal_name as 'customer_group_internal_name,
                                    cs.id as 'collection_scenario_id,
                                    cs.name as 'collection_scenario_name,
                                    cd.id as 'collection_event_id',
                                    cd.collection_event_type_id as 'collection_event_type_id',
                                    cet.name as 'collection_event_type_name',
                                    cd.name as 'collection_event_name',
                                    ac.scheduled_datetime as 'scheduled_datetime',
                                    ac.completed_datetime as 'completed_datetime',
                                    ac.account_colleciton_event_status_id as 'account_collection_event_status_id',
                                    aces.name as 'account_collection_event_status_name'
                                    ";
		// Query the temp table 'followup_search'
		$sSearchFrom	= 'account_collection_event_history ac
                                    JOIN account a ON (a.Id = ac.account_id)
                                    JOIN CustomerGroup c ON (a.CustomerGroup = c.Id)
                                    LEFT JOIN collection_scenario_collection_event cse ON (cse.collection_scenario_collection_event_id = cse.id)
                                    LEFT JOIN collection_scenario cs ON (cs.id = cse.collection_scenario_id)
                                    JOIN collection_event ce ON (ac.collection_event_id = ce.id)
                                    JOIN collection_event_type cet ON (cet.id = ce.collection_event_type_id)
                                    JOIN account_collection_event_status aces ON (ac.account_collection_event_status_id = aces.id)';

		// Get the count of the unlimited results
		$oFollowUpSearchCountSelect	=	new StatementSelect(
										$sSearchFrom,
										'COUNT(COALESCE(fs.followup_id, fs.followup_recurring_id)) AS count',
										$aWhereInfoSearch['sClause'],
										'',
										''
									);
		if ($oFollowUpSearchCountSelect->Execute($aWhereInfoSearch['aValues']) === FALSE)
		{
			throw new Exception_Database("Failed to retrieve records for '{self::$_strStaticTableName} Search' query - ". $oFollowUpSelect->Error());
		}

		$aCount	= $oFollowUpSearchCountSelect->Fetch();
		if ($bCountOnly)
		{
			// Only want the count, return it
			return $aCount['count'];
		}

		// Get the limited + offset results
		$oFollowUpSearchSelect	=	new StatementSelect(
										$sSearchFrom,
										'fs.*',
										$aWhereInfoSearch['sClause'],
										$sOrderByClause,
										$sLimitClause
									);
		if ($oFollowUpSearchSelect->Execute($aWhereInfoSearch['aValues']) === FALSE)
		{
			throw new Exception_Database("Failed to retrieve records for '{self::$_strStaticTableName} Search' query - ". $oFollowUpSelect->Error());
		}

		// Return the results as well as the count
		return array('aData' => $oFollowUpSearchSelect->FetchAll(), 'iCount' => $aCount['count']);
        }


		
	// START: REQUIRED FUNCTIONS
        public static function getFor($aCriteria)
	{
		return Dummy::getFor(get_class(), $aCriteria);
	}
        
	public static function getForId($iId)
	{
		return Dummy::getForId(get_class(), $iId);
	}
	
	public static function getAll()
	{
		return Dummy::getAll(get_class());
	}
	// END: REQUIRED FUNCTIONS
}
?>