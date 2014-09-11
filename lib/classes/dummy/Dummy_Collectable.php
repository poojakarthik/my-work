<?php
class Dummy_Collectable extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'collectable';
	protected static 	$_sStaticTableName	= 	'collectable';
	protected 			$_aProperties		= 	array(


													'account_id'			=> null,
													'amount'				=> null,
													'balance'				=> null,
													'created_datetime'		=> null,
													'due_date'				=> null,
													'collectable_type_id'	=> null,
													'collection_promise_id'	=> null
												);
	
	public function __construct($aProperties=array(), $bLoadById=false)
	{
		parent::__construct($aProperties, $bLoadById);
	}
	
	public static function getForPromiseId($iPromiseId)
	{
		return self::getFor(array('collection_promise_id' => $iPromiseId));
	}

        public static function getForAccount($iAccountId)
        {
            return self::getFor(array('account_id' => $iAccountId));
        }
	
	/**
     * getCollectablesForBatchProcess
     * @returns: array of Collectable ORM, ordered by account id, where either:
     * - balance>0 (these are currently in collections, or probably should be, if they are above the scenario threshold)
     * OR
     * - balance == 0 AND most recent collection event <> 'reset event' (ie, the are in collections but should not be)
     * 
     * The set of returned ORMs may still contain balances that are below the threshold set in the scenario that will apply
     * These will be filtered out in the logic layer.
     */
	public static function getCollectablesForBatchProcess() 
    {
		$aCollectables 	= self::getAll();
		$aPicked		= array();
		foreach ($aCollectables as $oCollectable)
		{
			if ($oCollectable->balance > 0)
			{
				// Has unpaid balance, use it
				$aPicked[]	= $oCollectable;
			}
			else
			{
				// Check if the last event for the same account was of the type COLLECTION_EVENT_TYPE_EXIT_COLLECTIONS
				$oLastEvent = Dummy_Account_Collection_Event_History::getMostRecentForAccountId($oCollectable->account_id);
				if (($oLastEvent !== null) && ($oLastEvent->collection_event_type_id == COLLECTION_EVENT_TYPE_EXIT_COLLECTIONS))
				{
					$aPicked[] = $oCollectable;
				}
			}
		}
		return $aPicked;
    }

    public static function getForAccountId($iAccountId)
    {
        return self::getFor(array('account_id'=>$iAccountId));
    }


	
	// START: REQUIRED FUNCTIONS
	public static function getForId($iId)
	{
		return Dummy::getForId(get_class(), $iId);
	}
	
	public static function getAll()
	{
		return Dummy::getAll(get_class());
	}
	
	public static function getFor($aCriteria)
	{
		return Dummy::getFor(get_class(), $aCriteria);
	}
	// END: REQUIRED FUNCTIONS
}
?>