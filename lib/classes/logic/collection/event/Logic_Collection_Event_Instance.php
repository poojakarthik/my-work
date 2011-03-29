<?php
/**
 * Description of newPHPClass
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Event_Instance
{
	// account_collection_event_history ORM
	protected $oDO;

	// Optional, not supplied in the case of the 'reset' event
	protected $oScenarioEvent;

   protected $oEvent;

   protected $oException;

	protected static $aEventInstancesWaitingForCompletion = array();

	public function __construct($mDefinition)
	{
		if (is_numeric($mDefinition))
		{
			$this->oDO = Account_Collection_Event_History::getForId($mDefinition);
		}
		else if ($mDefinition instanceof Account_Collection_Event_History)
		{
			$this->oDO = $mDefinition;
		}
		else
		{
			throw new Exception('Bad parameter passed into Logic_Collection_Event_Instance constructor');
		}
	}

	public function getEventName()
	{
		return Collection_Event::getForId($this->collection_event_id)->name;
	}

	public function getScenario()
	{

		if ($this->collection_scenario_collection_event_id != null)
		{
			$oScenarioEvent = Logic_Collection_Scenario_Event::getForId($this->collection_scenario_collection_event_id);
			return new Logic_Collection_Scenario( $oScenarioEvent->collection_scenario_id);
		}

		return null;

	}


		/**
	 *  Here's the hierarchy, each earlier level overrides the later ones:
	 * 1 Collection_Event_Type_Implementation
	 * 2 Collection_Event_Type
	 * 3 Collection_Scenario_Collection_Event
	 * 4 Collection_Event
	 *
	 * If the invocation is not defined on any of these levels we throw a configuration exception
	 *
	 */
	public function getInvocationId()
	{

		$oEvent =  $this->getCollectionEvent();
		if ($oEvent->getInvocationId(true)!==null)
				return $oEvent->getInvocationId(true);
		if ($this->getScenarioEvent()!== null)
		{
			$iInvocation = $this->getScenarioEvent()->getScenarioEventInvocationId();
			if ($iInvocation !== null)
				return $iInvocation;
		}
	   if ($oEvent->getInvocationId(false)!==null)
				return $oEvent->getInvocationId();
		throw new Exception('Configuration Error: no invocation method defined for Event Instance with id '.$this->id);

	}

	public function getCollectionEvent()
	{
		return Logic_Collection_Event::getForEventInstance($this);
	}

	public function isExitEvent()
	{
	$iExitEventTypeId = Collection_Event_Type::getForSystmName('EXIT_COLLECTIONS')->id;
	return (Logic_Collection_Event::getEventTypeForId($this->collection_event_id) == $iExitEventTypeId);
	}

	/**
	 * Creates a new Logic_Collection_Event_Instance object and saves it's data
	 * @param <type> $mItemToSchedule, either a Logic_Collection_Scenario_Event object or a Logic_Collection_Event object
	 * @param <type> $oAccount
	 */
	public static function schedule($oAccount, $mItemToSchedule)
	{
		// Return an instance of this class
		$oEventInstance					= new self(new Account_Collection_Event_History());
		$oEventInstance->account_id		= $oAccount->Id;
		$oEventInstance->collectable_id	= $oAccount->getSourceCollectable()->id;

		if ($mItemToSchedule instanceof Logic_Collection_Scenario_Event)
		{
		// A scenario event, set both event id and scenario event id
		$oEventInstance->collection_event_id						= $mItemToSchedule->collection_event_id;
		$oEventInstance->collection_scenario_collection_event_id	= $mItemToSchedule->id;
		}
		else if (is_numeric($mItemToSchedule) && $mItemToSchedule === COLLECTION_EVENT_TYPE_IMPLEMENTATION_EXIT_COLLECTIONS)
		{
		// Just an event, set only the event id. This one didn't come from a scenario
		$oEvent = new Logic_Collection_Event_ExitCollections($oEventInstance);
		$oEventInstance->collection_event_id = $oEvent->id;
		}

		$oEventInstance->scheduled_datetime					= DataAccess::getDataAccess()->getNow();
		$oEventInstance->account_collection_event_status_id = ACCOUNT_COLLECTION_EVENT_STATUS_SCHEDULED;
		$oEventInstance->save();
		Log::getLog()->log('Scheduled event \''.$oEventInstance->getEventName().'\' for account Id '.$oAccount->Id);
		return $oEventInstance;
	}

	public static function getWaitingEvents($iAccountId = null)
	{
		$aWaitingEvents = Account_Collection_Event_History::getWaitingEvents($iAccountId);
		$aResults = array();
		foreach ($aWaitingEvents as $oEvent)
		{
			$aResults[] = new self($oEvent);
		}
		return $aResults;
	}



	/**
	 * Logic_Collection_Event_Instance::invoke
	 * Retrieves the Logic_Collection_Event object for this event instance and calls its invoke() method
	 * Registers $this with the static array that is later used to complete the event instance
	 */
	public function invoke($aParameters = null)
	{

			$oDataAccess	= DataAccess::getDataAccess();
			$oDataAccess->TransactionStart();
			try
			{
				// Invoke the event
				////Log::getLog()->log("About to invoke event '{$this->oDO->collection_event_id}'");
				$this->oEvent = Logic_Collection_Event::getForEventInstance($this);
				$this->oEvent->invoke($aParameters);
				$oDataAccess->TransactionCommit();
			}
			catch(Exception $e)
			{
		$oDataAccess->TransactionRollback();
		throw $e;
			}



	}

	public function setException($e)
	{
		$this->oException = $e;
	}

	public function getException()
	{
		return  $this->oException;
	}

	/**
	 * Logic_Collection_Event_Instance::_registerWithArray
	 * adds $this to self::$aEventInstancesWaitingForCompletion, an associative array with key == collection_event_id value = Logic_Collection_Event_Instance object
	 *
	 */
	public function _registerWithArray() {

		if (!array_key_exists($this->oDO->collection_event_id, self::$aEventInstancesWaitingForCompletion))
		   self::$aEventInstancesWaitingForCompletion[$this->oDO->collection_event_id] = array();
		self::$aEventInstancesWaitingForCompletion[$this->oDO->collection_event_id][] = $this;
	}

	  /**
	 * Logic_Collection_Event_Instance:: completeWaitingInstances
	 * Loops through  self::$aEventInstancesWaitingForCompletion, derives the event class name and calls the static complete method on it, passing in the set of event instances to be completed
	   * @return the number of event instances that were completed
	 */

	public static function completeWaitingInstances($bBypassChachedEvents = false, $aParameters = null, $iAccountId = null)
	{
		$aEventInstances = array();

		if ($bBypassChachedEvents)
		{			
			$aInstances =self::getWaitingEvents($iAccountId);
			foreach ($aInstances as $oInstance)
			{
				if ($oInstance->getInvocationId() == COLLECTION_EVENT_INVOCATION_AUTOMATIC)
				{
					if (!array_key_exists($oInstance->collection_event_id, $aEventInstances))
						$aEventInstances[$oInstance->collection_event_id] = array();
					$aEventInstances[$oInstance->collection_event_id][] = $oInstance;
				}
			}
		}
		else
		{
			$aEventInstances = self::$aEventInstancesWaitingForCompletion;
		}

		$iCompletedEvents = 0;
		foreach($aEventInstances as $iEventId => $aCollectionEvents)
		{
			$oDataAccess	= DataAccess::getDataAccess();
			$oDataAccess->TransactionStart();
			Logic_Stopwatch::getInstance()->lap();
			$sEventName;
			$aSuccesfullyInvokedInstances = array();
			try
			{
				if (Collections_Schedule::getEligibility($iEventId))
				{
					
					foreach ($aCollectionEvents as $oEventInstance)
					{
						if (!Logic_Collection_BatchProcess_Report::isFailedEventInstance($oEventInstance))
						{
							try
							{
								$sEventName = $sEventName === null ? $oEventInstance->getEventName() : $sEventName;
								$invocationParameters = $aParameters !== null ? $aParameters[$oEventInstance->id] : null;
								$oEventInstance->invoke($invocationParameters);
								$aSuccesfullyInvokedInstances[] = $oEventInstance;
							}
							catch(Exception $e)
							{
								Log::getLog()->log("Exception occurred during '$sEventName' event invocation. Only this event instance will be rolled back.");
								if ($e instanceof Exception_Database)
								{
									throw $e;
								}
								else
								{
									$oEventInstance->setException($e);
									Logic_Collection_BatchProcess_Report::addEvent($oEventInstance);
								}

							}
						}
					}

					//now complete all succesfully invoked instances
					$sClassName = Logic_Collection_Event::getClassNameForId($iEventId);
					call_user_func(array( $sClassName, 'complete'), $aSuccesfullyInvokedInstances);
					$iCompletedEvents += count($aSuccesfullyInvokedInstances);
					$oDataAccess->TransactionCommit();
					Log::getlog()->log("Invoked and completed ".count($aSuccesfullyInvokedInstances)." '$sEventName' events in : ".Logic_Stopwatch::getInstance()->lap()." seconds.");
				}
				else
				{
					throw new Exception("This event is not eligible to be invoked today.");
				}
			}
			catch (Exception $e)
			{
				Log::getLog()->log("Exception occurred during '$sEventName' event completion. All ".count($aSuccesfullyInvokedInstances)." invoked instances will be rolled back.");
				Log::getLog()->log("Exception Details: ");
				Log::getLog()->log($e->__toString());
				// Exception caught, rollback db transaction
				$oDataAccess->TransactionRollback();
				if ($e instanceof Exception_Database)
				{
					Logic_Collection_BatchProcess_Report::addException($e);
					throw $e;
				}
				else
				{
					foreach ($aCollectionEvents as $oEvent)
					{
						$oEvent->setException($e);
						Logic_Collection_BatchProcess_Report::addEvent($oEvent);
					}
				}
			}

		}
		if (!$bBypassChachedEvents)
			self::$aEventInstancesWaitingForCompletion = array();
		return $iCompletedEvents;
	}

	/**
	 *
	 * @param <type> $aParameters - associative array with key == account_collection_event_history.id and value == array of parameters to pass into the invoke method
	 */
	public static function completeScheduledInstancesFromUI($aParameters)
	{
		foreach (array_keys($aParameters) as $iInstanceId)
		{
			$oInstance = new self($iInstanceId);
			$oInstance->_registerWithArray();
		}

		return self::completeWaitingInstances(false , $aParameters);
	}

	public static function completeScheduledInstancesForAccounts($aAccounts)
	{
		$aEventInstances = array();
		foreach ($aAccounts as $oAccount)
		{
			$aEvent = self::getWaitingEvents($oAccount->id);
			if (count($aEvent) > 0 && $oAccount->shouldCurrentlyBeInCollections())
			{				
				$oInstance = reset($aEvent);
				if ($oInstance->getInvocationId() == COLLECTION_EVENT_INVOCATION_AUTOMATIC)
				{
					if (!array_key_exists($oInstance->collection_event_id, $aEventInstances))
						$aEventInstances[$oInstance->collection_event_id] = array();
					$aEventInstances[$oInstance->collection_event_id][] = $oInstance;
				}
				
			}
		}
		self::$aEventInstancesWaitingForCompletion = $aEventInstances;
		self::completeWaitingInstances();
	}

	public function complete()
	{
		// Mark the even instance as completed
		$iUserId	= Flex::getUserId();
		$iUserId	= ($iUserId === null ? Employee::SYSTEM_EMPLOYEE_ID : $iUserId);

		$this->oDO->completed_datetime 					= DataAccess::getDataAccess()->getNow();
		$this->oDO->completed_employee_id				= $iUserId;
		$this->oDO->account_collection_event_status_id	= ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED;
		$this->oDO->save();
		Logic_Collection_BatchProcess_Report::addEvent($this);
	}

	public function cancel()
	{
		if ($this->account_collection_event_status_id != ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED )
		{
			$this->account_collection_event_status_id =  ACCOUNT_COLLECTION_EVENT_STATUS_CANCELLED;
			$this->completed_datetime = Data_Source_Time::currentTimestamp();
			$this->completed_employee_id = Flex::getUserId() == null ? Employee::SYSTEM_EMPLOYEE_ID : Flex::getUserId();
			$this->save();
		}
		else if ($this->account_collection_event_status_id == ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED || $this->account_collection_event_status_id != ACCOUNT_COLLECTION_EVENT_STATUS_CANCELLED)
		{
			throw new exception("Trying to cancel a scenario event that does not have a 'scheduled' status");
		}
	}

	public function getEvent()
	{
	   	return Logic_Collection_Event::getForEventInstance($this);
	}

	public function getScenarioEvent()
	{
		if ($this->oScenarioEvent === null && $this->collection_scenario_collection_event_id != null)
		{
			$this->oScenarioEvent = Logic_Collection_Scenario_Event::getForId($this->collection_scenario_collection_event_id);
		}
		return $this->oScenarioEvent;
	}

	public static function getForId($iId)
	{
		return new self($iId);
	}

	public static function getMostRecentForAccount($oAccount, $iStatus = NULL)
	{
		$oORM = Account_Collection_Event_History::getMostRecentForAccountId($oAccount->Id, $iStatus);
		return $oORM === null ? null : new self($oORM);
	}

	public static function getFirstForAccount($oAccount)
	{
		$oORM = Account_Collection_Event_History::getFirstForAccountId($oAccount->Id);
		return $oORM === null ? null : new self($oORM);
	}

	public static function getForLedger($bCountOnly=false, $iLimit=0, $iOffset=0, $oSort=null, $oFilter=null)
	{
		return Account_Collection_Event_History::getForLedger($bCountOnly, $iLimit, $iOffset, get_object_vars($oSort), get_object_vars($oFilter));
	}

	public function save()
	{
		return $this->oDO->save();
	}

	public function toArray()
	{
		$aArray = $this->oDO->toArray();
		$aArray['event_name'] = $this->getEventName();
		$aArray['exception'] = null;
		if ($this->getException()!== null)
		{
			$aArray['exception'] = array('message'=>$this->getException()->getMessage(), 'detail'=>$this->getException()->__toString() );
		}
		return $aArray;
	}

	public function __get($sField)
	{
		return $this->oDO->{$sField};
	}

	public function __set($sField, $mValue)
	{
		$this->oDO->{$sField} = $mValue;
	}
}
?>
