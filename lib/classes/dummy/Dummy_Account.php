<?php
class Dummy_Account extends Dummy
{
	protected 			$_sIdField			= 	'Id';
	protected 			$_sTableName		= 	'Account';
	protected static 	$_sStaticTableName	= 	'Account';
	protected 			$_aProperties		= 	array(
													'name'				=> null,
													'account_class_id'	=> null,
                                                                                                        'collection_severity_id' =>null
												);
	
	 /**
         * returns an array of Account objects representing all accounts which are:
         * 1 are currently in collections, defined by most recent account_collection_event_history record not being for the 'exit collections' event
         * 2 OR are not in collections (as defined under 1) but have collectables with a balance > 0 that are not part of an active promise
         * 
         */
        public static function getForBatchCollectionsProcess()
        {
            $aAccounts = self::getAll();
            $aResults = array();
            foreach($aAccounts as $oAccount)
            {
                $oHistory = Dummy_Account_Collection_Event_History::getMostRecentForAccountId($oAccount->Id);
                $oEvent = Dummy_Collection_Event::getForId($oHistory->collection_event_id);
                if ($oEvent !== null && $oEvent->collection_event_type_id != COLLECTION_EVENT_TYPE_EXIT_COLLECTIONS)
                {
                    $aResults[] = $oAccount;
                }
                else
                {
                    $aCollectables = Dummy_Collectable::getForAccountId($oAccount->Id);
                    foreach ($aCollectables as $oCollectable)
                    {
                        if ($oCollectable->balance>0  )
                        {
                            if ($oCollectable->collection_promise_id === null)
                            {
                                $aResults[] = $oAccount;
                            }
                            else
                            {
                                $oPromise = Dummy_Collection_Promise::getForId($oCollectable->collection_promise_id);
                                if ($oPromise->completed_datetime != null)
                                {
                                     $aResults[] = $oAccount;
                                }
                            }
                        }
                    }
                }

            }

            return $aResults;

        }
        
        
        public function __construct($aProperties=array(), $bLoadById=false)
	{
		parent::__construct($aProperties, $bLoadById);
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
	// END: REQUIRED FUNCTIONS
}
?>