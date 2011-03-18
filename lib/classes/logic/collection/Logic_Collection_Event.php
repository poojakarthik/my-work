<?php
abstract class Logic_Collection_Event implements DataLogic 
{
    protected $oParentDO;
    protected $oEventTypeDO;
    protected $oImplementationDO;

    //data members that provide the context for event invocation
    protected $oCollectionEventInstance;
    protected $aInvocationParameters;


    abstract protected function _invoke($aParameters = null);

    final public function invoke($aParameters = null)
    {
    	if ($this->oCollectionEventInstance === NULL)
				throw new Exception("Cannot invoke an event unless there is an actual Collection Event Instance (account_collection_event_history).");
       
        $this->_invoke($aParameters);
    	////Log::getLog()->log("Event being invoked: ".get_class($this));
       
    }

    public function getAccount()
    {
        if ($this->oCollectionEventInstance === null)
                return null;
        return Logic_Account::getInstance($this->oCollectionEventInstance->account_id);
    }

    public function getScenario()
    {
        if ($this->oCollectionEventInstance === null)
                return null;
        if ($this->oCollectionEventInstance->collection_scenario_collection_event_id !== null)
        {
            $oScenarioEvent = Logic_Collection_Scenario_Event::getForId($this->oCollectionEventInstance->collection_scenario_collection_event_id);
             $oScenario = Logic_Collection_Scenario::getForId($oScenarioEvent->collection_scenario_id);

        }
        else
        {
             $oScenario = $this->getAccount()->getCurrentScenarioInstance()->getScenario();
        }

         return $oScenario;
       

    }

    /**
     * hierarchy:
     * 1 implementation level
     * 2 type level
     * 3 scenario event level - not included here!
     * 4 collection event level
     * @param <type> $bExcludeEventLevelDefinition - possibility to exclude the event level definition of the invocation, added because the scenario event level takes precedence
     * @return <type>
     */

    public function getInvocationId($bEnforcedInvocationOnly = false)
    {
        $oImplementation = $this->getImplementation();
        if ($oImplementation->collection_event_invocation_id !== null)
                return $oImplementation->collection_event_invocation_id;
        if ($this->getEventType()->collection_event_invocation_id !== null)
                $this->getEventType()->collection_event_invocation_id;
        return $bEnforcedInvocationOnly ? null : $this->oParentDO->collection_event_invocation_id;
       
    }

    protected function getImplementation()
    {
        $oEventType = $this->getEventType();
        if ($this->oImplementationDO === null)
                $this->oImplementationDO = Collection_Event_Type_Implementation::getForId($oEventType->collection_event_type_implementation_id);
        return  $this->oImplementationDO;
    }

    protected function getEventType()
    {
        if ($this->oEventTypeDO === null)
                $this->oEventTypeDO = Collection_Event_Type::getForId($this->collection_event_type_id);
        return $this->oEventTypeDO;
    }

    public static function getForId($iEventId) 
    {
        return self::makeEvent($iEventId);
    }

    public static function getEventTypeForId ($iEventId)
    {
        $oEventORM	= Collection_Event::getForId($iEventId);
        return $oEventORM->collection_event_type_id;
    }

    public static function getImplementationForId($iEventId)
    {
         $oEventORM	= Collection_Event::getForId($iEventId);
        $oTypeORM	= Dummy::getForId('collection_event_type', $oEventORM->collection_event_type_id);
        $oImplementationORM = Dummy::getForId('collection_event_type_implementation', $oTypeORM->collectionEvent_type_implementation_id);
        return $oImplementationORM->id;
    }

    public static function getClassNameForId($iEventId)
    {
        $oEventORM	= Collection_Event::getForId($iEventId);
        $oTypeORM	= Collection_Event_Type::getForId( $oEventORM->collection_event_type_id);
        $oImplementationORM = Collection_Event_Type_Implementation::getForId( $oTypeORM->collection_event_type_implementation_id);
     	return $oImplementationORM->class_name;
    }

    /**
     * @method runCollectionsBatchProcess
     * The process consists of three phases:
     * 1. Active promises to pay are processed: any broken promises will be set to ‘completed’ with status of COLLECTION_PROMISE_COMPLETION_BROKEN, which will trigger the needed change in scenario; any fulfilled promises will be set to completed with status COLLECTION_PROMISE_KEPT
     * 2. For each account that currently is or should be in collections,the next event according to its current scenario is triggered
     * 3. Any Logic_Collection_Event_Instances that were created and invoked (ie automatic invocation) are completed. This is a separate step to cater for events that are to be processed in a bulk manner, such as reports
     *
     * Where needed, multiple events are triggered on the same account (day offset = 0 on scenario event ). To cater for this, step 2 is done recursively until there are no more events left to complete
     */
    public static function runCollectionsBatchProcess()
    {        
        try
        {
            $oDataAccess	= DataAccess::getDataAccess();
            $oDataAccess->TransactionStart();
            $iAccountsBatchProcessIteration = 1;
            //first complete any automated events that were scheduled and invoked previously, but for some reason failed to complete
            Logic_Collection_Event_Instance::completeWaitingInstances(true);
            try
            {
                $aPromises =  Logic_Collection_Promise::getActivePromises();
                Logic_Collection_Promise::batchProcess($aPromises);
            }
            catch (Exception $e)
            {
                 Logic_Collection_BatchProcess_Report::addException($e);
                if ($e instanceof Exception_Database)
                {
                    throw $e;
                }
            }

            try
            {
                $aActiveSuspensions = Collection_Suspension::getActive();
               // Logic_Collection_Suspension::batchProcess($aActiveSuspensions);
            }
            catch(Exception $e)
            {
                if ($e instanceof Exception_Database)
                {
                    throw $e;
                }
                else
                {
                    Logic_Collection_BatchProcess_Report::addException($e);
                }
            }
           
            try
            {
                 ////Log::getLog()->log('&&&&&&&&& Accounts Batch Process Iteration '.$iAccountsBatchProcessIteration++.'  &&&&&&&&&&&&&');
                $aExcludedAccounts = Logic_Collection_BatchProcess_Report::getAccountsWithExceptions();
                $aAccounts = Logic_Account::getForBatchCollectionProcess($aExcludedAccounts);
                $iCompletedInstances = Logic_Account::batchProcessCollections($aAccounts);
                
                while ($iCompletedInstances > 0)
                {
                    ////Log::getLog()->log('&&&&&&&&& Accounts Batch Process Iteration '.$iAccountsBatchProcessIteration++.'  &&&&&&&&&&&&&');
                    $iCompletedInstances = Logic_Account::batchProcessCollections($aAccounts);
                    
                }
            }
            catch (Exception $e)
            {
                Logic_Collection_BatchProcess_Report::addException($e);
            }
             $oDataAccess->TransactionCommit();
        }

        catch (Exception $e)
        {
            // Exception caught, rollback db transaction
           $oDataAccess->TransactionRollback();
            Logic_Collection_BatchProcess_Report::addException($e);
        }

       
    }

    public static function getForEventInstance($oEventInstance) {
    	$sClassName	= self::getClassNameForId($oEventInstance->collection_event_id);
        return new $sClassName($oEventInstance);
    }

    public static function makeEvent($iEventId)
    {    	
        $oEventORM	= Collection_Event::getForId($iEventId);
        $sClassName	= self::getClassNameForId($iEventId);
        return new $sClassName($iEventId);
    }
    
    public function save()
    {
    	// Not sure if this is needed
    }

	public function toArray()
	{
		return $this->oParentDO->toArray();
	}

	public function __get($sField)
	{
		return $this->oParentDO->$sField;
	}
	
	public function __set($sField, $mValue)
	{
		// Not sure if this is needed
	}
	
	public static function searchFor($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$sFrom	= "	collection_event ce
					JOIN		collection_event_type cet ON (cet.id = ce.collection_event_type_id)
					LEFT JOIN	collection_event_invocation cei ON (cei.id = ce.collection_event_invocation_id)
					JOIN		status s ON (s.id = ce.status_id)";
		if ($bCountOnly)
		{
			$sSelect	= "COUNT(ce.id) AS event_count";
			$sOrderBy	= '';
			$sLimit		= '';
		}
		else
		{
			$sSelect = "ce.*, 
						cet.name AS collection_event_type_name, 
						cei.name AS collection_event_invocation_name, 
						s.name AS status_name";	
			$sOrderBy =	Statement::generateOrderBy(
							array(
								'id' 			=> 'ce.id', 
								'name' 			=> 'ce.name', 
								'description' 	=> 'ce.description'
							), 
							get_object_vars($oSort)
						);
			$sLimit = Statement::generateLimit($iLimit, $iOffset);
		}
		
		$aWhere 	= 	Statement::generateWhere(
							array(
								'collection_event_invocation_id'	=> 'ce.collection_event_invocation_id',
								'status_id' 						=> 'ce.status_id'
							), 
							get_object_vars($oFilter)
						);
		$oSelect	=	new StatementSelect(
							$sFrom, 
							$sSelect, 
							$aWhere['sClause'], 
							$sOrderBy, 
							$sLimit
						);
		
		if ($oSelect->Execute($aWhere['aValues']) === FALSE)
		{
			throw new Exception_Database("Failed to get Events. ". $oSelect->Error());
		}
		
		if ($bCountOnly)
		{
			$aRow = $oSelect->Fetch();
			return $aRow['event_count'];
		}
		
		$aResults = array();
		while ($aRow = $oSelect->Fetch())
		{
			$aResults[$aRow['id']] = $aRow;
		}
		
		return $aResults;
	}
}
?>


 