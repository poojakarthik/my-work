<?php
/**
 * Description of Collection_Logic_Promise
 * When a promise is created, we create as many collectables
 * as there are 'invoices' that the promise is derived from
 *
 * @author JanVanDerBreggen
 */
class Collection_Logic_Promise implements DataLogic 
{
    //put your code here
    protected $oPromiseDO;
    protected $aInstalments;
    protected $aCollectables;
    protected $oAccount;
	
	public function __construct()
	{
		
	}
	
    public function addCollectable($oCollectable)
    {
        if ($this->aCollectables === null)
       		$this->aCollectables = array();
        $this->aCollectables[$oCollectable->id] = $oCollectable;
    }

    public function getAccount()
    {
        if ($this->oAccount === null && $this->account_id!== null)
                $this->oAccount = Account_Logic::getForId($this->account_id);

        return $this->oAccount;
    }

    /**
     * returns true if the promise is not yet complete and the balance > sum of all instalments that are still outstanding
     */
    public function isBroken()
    {

    }

    /**
     * returns true if the promise is not yet complete and the balance == 0 and now()<= last instalment due date
     */
    public function isFulfilled()
    {

    }

    /**
     * @method complete
     * sets the status of a promise to 'complete' as follows:
     * - sets the collection_promise_completion_id
     * - sets the completed_employee_id
     * - sets the completed_employee_id
     * - if the promise is broken: the appropriate scenario is set to be the current scenario on the account
     *
     * @param <type> $iCompletionId - collection_promise_completion constant
     *
     */

    public function complete ($iCompletionId)
    {
        $this->collection_promise_completion_id = $iCompletionId;
        // For cli apps we use the system user id (0)
		$this->completed_employee_id = Flex::getUserId()!=null?Flex::getUserId():Employee::SYSTEM_EMPLOYEE_ID;
        $this->completed_datetime = Data_Source_Time::currentTimestamp();
        $oDataAccess	= DataAccess::getDataAccess();
        if (!$oDataAccess->TransactionStart())
        {
            throw new Exception("Failed to start template save transaction.");
        }
        try
        {
            if ($iCompletionId == COLLECTION_PROMISE_BROKEN)
            {
                $oAccount = $this->getAccount();
                $oAccount->setCurrentScenario($oPromise->collection_scenario_id);
            }
           	$this->save();
           	$oDataAccess->TransactionCommit();
           	return $this->id;
        }
        catch (Exception $e)
        {
			// Exception caught, rollback db transaction
			$oDataAccess->TransactionRollback();
			throw $e;
        }
    }

    public static function getForCollectable($oCollectable) 
    {

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
}
?>
