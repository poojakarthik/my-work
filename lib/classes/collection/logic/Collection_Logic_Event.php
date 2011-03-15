<?php
abstract class Collection_Logic_Event implements DataLogic 
{
    protected $oParentDO;
   

    abstract protected function _invoke();

    final public function invoke()
    {
        // put all the common logic in this function and call $this->_invoke() at the appropriate time
    }

    public static function getForId($iEventId) 
    {
        return self::makeEvent($iEventId);
    }

    public function save();

    public function toArray();

    public function __get($sField);
    
    public function __set($sField, $mValue);

    /**
     * @method runCollectionsBatchProcess
     * - Retrieves an array of Account_Logic objects that should likely be processed (this partly depends on the current scenario, which will only be fully clear once the promises have been processed)
     * The process itself consists of two phases:
     * 1. Accounts with active promises to pay are processed: any broken promises will be set to ‘completed’ with status of COLLECTION_PROMISE_BROKEN, which will trigger the needed change in scenario; any fulfilled promises will be set to completed with status COLLECTION_PROMISE_KEPT
     * 2. For each account,the next event according to its current scenario is triggered
     */
    public static function runCollectionsBatchProcess()
    {
        $aAccounts = Account_Logic::getAccountsForBatchCollectionProcess();
        
        // first process the promises, because they may trigger a change in scenario
        foreach ($aAccounts as $oAccount)
        {
            $oPromise = $oAccount->getActivePromise();
            if ($oPromise!== null)
            {
                if ( $oPromise->isBroken())
                {
                    $oPromise->complete(COLLECTION_PROMISE_BROKEN);
                }
                else if ($oPromise->isFulfilled())
                {
                    $oPromise->complete(COLLECTION_PROMISE_KEPT);
                }
            }
        }

        // now trigger the next event for each account
        foreach ($aAccounts as $oAccount)
        {
        	$oAccount->triggerNextScheduledScenarioEvent();
        }
    }

    public static function makeEvent($iEventId)
    {
         $oEventORM		= Collection_Event::getForId($iEventId);
         $oTypeORM		= Collection_Event_Type::getForId($oEventORM->collection_event_type_id);
         $sClassName	= $oTypeORM->class_name;
         return new $sClassName($oEventORM);
    }
}
?>


 