<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Account_Logic
 *
 * @author JanVanDerBreggen
 */
class Account_Logic implements DataLogic{
    protected $oDO;
    protected $aCollectionScenarioInstances;
    protected $aCollectables;
    protected $aPromises;

    protected static $aInstances = array();



    protected static function getInstance ($mDefinition)
    {
     	$oDO = is_numeric($mDefinition) ? Account::getForId($mDefinition) : (get_class($mDefinition) == 'Account' ? $mDefinition : null);
     	if ($oDO!== null && $oDO->id !== null && in_array($oDO->id, array_keys(self::$aInstances)))
     	{
        	return self::$aInstances[$oDO->id];
     	}
     	else
     	{
    		$x =  new self($oDO);
            self::$aInstances[$oDO->id] = $x;
            return $x;
     	}
    }
    
    private function __construct($mDefinition)
    {
        $this->oDO = is_numeric($mDefinition) ? Account::getForId($mDefinition) : (get_class($mDefinition) == 'Account' ? $mDefinition : null);
    }

    public function getMostRecentCollectionEventInstance() 
    {
        return Collection_Logic_Event_Instance::getMostRecentForAccount($this);
    }

   /**
    * @method Account_Logic::isCurrentlyInCollections
    * determines if an account is currently in collections as follows:
    * - if most recent Collection_Logic_Event_Instance (account_collection_event_history) is the 'exit
    *  event - return false
    * - else: return true
    */
    public function isCurrentlyInCollections()
    {
		
    }

    /**
     * @method Account_Logic::triggerNextScheduledScenarioEvent
     * if the account is currently in collections or should be in collections, the next scenario event is triggered,
     * based on the following rules:
     * - An event will be scheduled for accounts that are not in suspension AND are either currently in collections or have a current balance above the entry threshold
     * - If such an account is below the scenario exit threshold, the ExitCollections event is triggered
     * - Else: the next event is triggered, based on the due date of the account and the most recent event of the current scenario
     *
     * - if it is a manually invoked event, it is only scheduled
     * - if it is a fully automated event, it is scheduled and invoked
     * 
     * It is the responsibility of the caller of this method to ensure that any pending changes of scenario
     * have been completed before calling this method, eg if there is a broken promise on the account, this should
     * have been dealt with before calling this method.
     */
    public function triggerNextScheduledScenarioEvent()
    {
        $oScenarioInstance  = $this->getCurrentScenarioInstance();
        $sDueDate           = $this->getCurrentDueDate();
        $aCollectables      = $this->getCollectables();
        if ($this->isCurrentlyInCollections() || $this->getHighestOpenCollectableBalance() > $oScenarioInstance->entry_threshold)
        {
            if ($this->getHighestOpenCollectableBalance() < $oScenarioInstance->exit_threshold)
            {
                // trigger the 'exit collections' event
            }
            else
            {
                // trigger the next scheduled event
                $oMostRecentCollectionEventInstance = $this->getMostRecentCollectionEventInstance();
               	
               	// get the Collection_Logic_Scenario_Event object that should be scheduled
                $nextScheduledEvent = $this->getCurrentScenarioInstance()->getNextScheduledEvent($this->getCurrentDueDate(), $oMostRecentCollectionEvent->getScenarioEvent());
                $oEventInstance = Collection_Logic_Event_Instance::schedule($this, $nextScheduledEvent);
                if ($nextScheduledEvent->getInvocationId() != COLLECTION_EVENT_INVOCATION_MANUAL)
                {
                    $oEventInstance->invoke();
                }
            }
        }

    }

    public function setPromises($aPromises) 
    {
        $this->aPromises = $aPromises;
    }

  	public function addCollectable($oCollectable) 
  	{
        if ($this->aCollectables === null){
            $this->aCollectables = array();
        }
        $this->aCollectables[] = $oCollectable;
    }

    public function getCollectables()
    {
     	if ($this->aCollectables === null)
     	{
        	$this->aCollectables = Collectable_Logic::getForAccount($this);
        }
        return $this->aCollectables;
    }

   	public function getCurrentScenarioInstance()
   	{
   		if ($this->aCollectionScenarioInstances === null)
   		{
			$this->aCollectionScenarioInstances = Collection_Logic_Scenario_Instance::getForAccount($this);
   		}
   		
		foreach ($this->aCollectionScenarioInstances as $oInstance) 
   		{
   			// TODO: Here we should check if 'now' is between the start and end datetimes.
       		if ($oInstance->end_datetime === null)
            	return $oInstance;
       	}
       	throw new Collection_Logic_Exception('no current scenario for account');
  	}

   /**
    * sets end date on the current Collection_Logic_Scenario_Instance (account_collection_scenario record)
    * and creates a new  Collection_Logic_Scenario_Instance to be the current one
    * saves changes to the database
    * @param <type> $iCollectionScenarioId
    *
    */
   	public function setCurrentScenario($iCollectionScenarioId)
   	{

   	}

   /**
    * if effective enddate on a suspension is null, check against the proposed end date
    * the batch process will not set any fields in suspension records, just check
    */
   	public function isInSuspension()
   	{

   	}

    /**
     * returns a Collection_Logic_Promise object representing the active promise to pay on this account, or null if there is none
     */
    public function getActivePromise()
    {

    }

    /**
     * @method Account_Logic::getCurrentDueDate
     * returns the due date that will be the point of reference to work out which event should be triggered.
     * the date returned will be either:
     *  - the due date of the collectable that triggered most recent Collection_Logic_Event_Instance (account_collection_event_history record) IF that event instance was not the 'exit collections' event
     *  - the due date of the oldest currently open collectable
     */
	public function getCurrentDueDate()
    {

    }

    public static function getForId($iId)
    {
        return self::getInstance($iId);
    }
    
    public function __get($sField) 
    {

    }

    public function __set($sField, $mValue) 
    {

    }

    public function save() 
    {

    }

    public function toArray()
    {

    }

    /**
     * getAccountsForBatchCollectionProcess
     * @return array of Account_Logic objects that should be processed
     * These accounts are not currently suspended from collections, and either:
     * 1 are currently in collections, defined by most recent account_collection_event_history record not being for the 'exit collections' event
     * 2 OR are not in collections (as defined under 1) but have collectables with a balance > 0 that are not part of an active promise
     * 3 OR have an active promise to pay
     */
    public static function getAccountsForBatchCollectionProcess()
    {

        $aAccounts = array();
        $aAccountORMs = Account::getForBatchCollectionsProcess();
        foreach($aAccountORMs as $oORM)
        {

            $oAccount = self::getInstance($iAccountId);
            if (!$oAccount->isInSuspension())
            {
                $aPromises = array();
                foreach($aCollectableObjects as $oCollectable)
                {
                   $oAccount->addCollectable($oCollectable);

                    if ($oCollectable->belongsToPromise())
                    {
                        if (!in_array($oCollectable->promise_id, array_keys($aPromises)))
                        {
                            $aPromises[$oCollectable->promise_id] =  Collection_Logic_Promise::getForCollectable($oCollectable);
                        }
                        $oPromise = $aPromises[$oORM->promise_id];
                        $oPromise->addCollectable($oCollectable);
                    }
                 }

                 if (count($aPromises)>0)
                 {
                    $oAccount->setPromises($aPromises);
                 }

                $aAccounts[$iAccountId] = $oAccount;
            }
        }
        return $aAccounts;
    }
}
?>
