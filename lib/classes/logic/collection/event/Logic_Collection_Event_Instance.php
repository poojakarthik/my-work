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
	public static function queueForscheduling($oAccount, $mItemToSchedule)
	{
		// Return an instance of this class
		$oEventInstance					= new self(new Account_Collection_Event_History());
		$oEventInstance->account_id		= $oAccount->Id;
		

		if ($mItemToSchedule instanceof Logic_Collection_Scenario_Event)
		{
			// A scenario event, set both event id and scenario event id
			$oEventInstance->collection_event_id						= $mItemToSchedule->collection_event_id;
			$oEventInstance->collection_scenario_collection_event_id	= $mItemToSchedule->id;
			$oEventInstance->collectable_id								= $oAccount->getSourceCollectable()->id;
		}
		else if (is_numeric($mItemToSchedule) && $mItemToSchedule === COLLECTION_EVENT_TYPE_IMPLEMENTATION_EXIT_COLLECTIONS)
		{
			//exit event, does not belong to a scenario
			$oEvent = new Logic_Collection_Event_ExitCollections($oEventInstance);
			$oEventInstance->collection_event_id	= $oEvent->id;
			//regardless of scenario changes since the last event, for the exit event the source collectable is the same as for the most recent event.
			$oEventInstance->collectable_id			= $oAccount->getMostRecentCollectionEventInstance()->collectable_id;
		}

		//$oEventInstance->scheduled_datetime					= DataAccess::getDataAccess()->getNow();
		$oEventInstance->account_collection_event_status_id = ACCOUNT_COLLECTION_EVENT_STATUS_SCHEDULED;
		//$oEventInstance->save();
		$oEventInstance->addToQueue();
		//Log::getLog()->log('Scheduled event \''.$oEventInstance->getEventName().'\' for account Id '.$oAccount->Id);
		return $oEventInstance;
	}

	private function addToQueue()
	{
		if (!array_key_exists($this->oDO->collection_event_id, self::$aEventInstancesWaitingForCompletion))
		   self::$aEventInstancesWaitingForCompletion[$this->oDO->collection_event_id] = array();
		self::$aEventInstancesWaitingForCompletion[$this->oDO->collection_event_id][] = $this;
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
	 * 
	 */
	public function invoke($aParameters = null)
	{

			//$oDataAccess	= DataAccess::getDataAccess();
			//$oDataAccess->TransactionStart();
			//try
			//{
				// Invoke the event
				////Log::getLog()->log("About to invoke event '{$this->oDO->collection_event_id}'");
				$this->oEvent = Logic_Collection_Event::getForEventInstance($this);
				$this->oEvent->invoke($aParameters);
				//$oDataAccess->TransactionCommit();
			//}
			//catch(Exception $e)
			//{
		//		$oDataAccess->TransactionRollback();
		//		throw $e;
		//	}



	}

	public function getAccount()
	{
		if ($this->account_id !== NULL)
			return Logic_Account::getForId($this->account_id);

		return NULL;
	}

	public function setException($e)
	{
		$this->oException = $e;
	}

	public function getException()
	{
		return  $this->oException;
	}

	public function getNextEventToSchedule()
	{

	}

	/**
	 * Logic_Collection_Event_Instance::_registerWithArray
	 * adds $this to self::$aEventInstancesWaitingForCompletion, an associative array with key == collection_event_id value = Logic_Collection_Event_Instance object
	 *
	 */
//	public function _registerWithArray() {
//
//		if (!array_key_exists($this->oDO->collection_event_id, self::$aEventInstancesWaitingForCompletion))
//		   self::$aEventInstancesWaitingForCompletion[$this->oDO->collection_event_id] = array();
//		self::$aEventInstancesWaitingForCompletion[$this->oDO->collection_event_id][] = $this;
//	}

	  /**
	 * Logic_Collection_Event_Instance:: completeWaitingInstances
	 *  self::$aEventInstancesWaitingForCompletion is a 2 dimensional array, and contains all scheduled events that should be invoked and completed,grouped by event id
	   * this method iterates over the array, so in effect handles the invocation and completion per event, which is necessary as some events require completion for all accounts in batch
	   * if invocation fails and throws an exception, invocation of the event for all subsequent accounts is still attempted. If completion fails, the failure is for all accounts involved.
	   * @return the number of event instances that were completed
	 */

	public static function completeWaitingInstances($aParameters = null,  $bCompleteSchedulingFirst = FALSE)
	{
		$aEventInstances = self::$aEventInstancesWaitingForCompletion;
		$iCompletedEvents = 0;
		$aFailedInstances = array();
		$aSucceededInstances = array();

		if (count($aEventInstances) > 0)
			Log::getLog()->log("Processing Events.......");
		else
			Log::getLog()->log("No Events to process.......This is the end.......");

		foreach($aEventInstances as $iEventId => $aCollectionEvents)
		{
			$sName = Collection_Event::getForId($iEventId)->name;
			Log::getLog()->log("Processing '$sName' Events......");
			$oDataAccess = DataAccess::getDataAccess();
			$oDataAccess->TransactionStart();
			try
			{
				$aInstancesToInvokeAndComplete = array();
				//first complete the scheduling process, if required
				//at this time we do this only for the manually invoked events
				//the automatically invoked events are scheduled within the same transaction as their invoke and complete
				//scheduling at this stage is simply saving the event object to the database
				if ($bCompleteSchedulingFirst)
				{
					
					foreach($aCollectionEvents as $oEvent)
					{
						if ($oEvent->getInvocationId() === COLLECTION_EVENT_INVOCATION_AUTOMATIC)
						{
							$aInstancesToInvokeAndComplete[] = $oEvent;
						}
						else
						{
							$oDataAccess	= DataAccess::getDataAccess();
							$oDataAccess->TransactionStart();
							try
							{
								$oEvent->scheduled_datetime = DataAccess::getDataAccess()->getNow();
								$oEvent->save();
								$oDataAccess->TransactionCommit();
								Logic_Collection_BatchProcess_Report::queueEvent($oEvent);
								Log::getLog()->log("Scheduled manual event for Account $oEvent->account_id, Event: ". $oEvent->getEventName());
							}
							catch(Exception $e)
							{
								$oDataAccess->TransactionRollback();
								Logic_Collection_BatchProcess_Report::queueEvent($oEvent);
								if ($e instanceof Exception_Database)
								{
									$aFailedInstances = array_merge($aFailedInstances, Logic_Collection_BatchProcess_Report::commit($e));
									throw $e;
								}
								else
								{
									Log::getLog()->log("Exception occurred during event scheduling. Account: $oEvent->account_id, Event:". $oEvent->getEventName().". Only this event instance will be rolled back.");
									$aFailedInstances = array_merge($aFailedInstances, Logic_Collection_BatchProcess_Report::commit($e));
								}
							}
						}
					}
				}
				else
				{
					$aInstancesToInvokeAndComplete = $aCollectionEvents;
				}


				if (count($aInstancesToInvokeAndComplete) > 0)
				{
					$oDataAccess	= DataAccess::getDataAccess();
					$oDataAccess->TransactionStart();
					Logic_Stopwatch::getInstance()->lap();
					
					$aSuccesfullyInvokedInstances = array();
					try
					{
						//this first checks if this event is eligible to be invoked today
						if (Collections_Schedule::getEligibility($iEventId))
						{
							$sEventName;
							foreach ($aInstancesToInvokeAndComplete as $oEventInstance)
							{
								
								$oDataAccess	= DataAccess::getDataAccess();
								$oDataAccess->TransactionStart();
								try
								{
									//schedule first
									if ($bCompleteSchedulingFirst)
									{
										$oEventInstance->scheduled_datetime = DataAccess::getDataAccess()->getNow();
										//at this point we must save because some events require the account_event_hisroty_id as a FK reference. Only after save() do we have an id
										$oEventInstance->save();
									}

									$sEventName = $sEventName === null ? $oEventInstance->getEventName() : $sEventName;
									$invocationParameters = $aParameters !== null ? $aParameters[$oEventInstance->id] : null;
									$oEventInstance->invoke($invocationParameters);
									Logic_Collection_BatchProcess_Report::queueEvent($oEventInstance);
									$oDataAccess->TransactionCommit();
									$aSuccesfullyInvokedInstances[] = $oEventInstance;
								}
								catch(Exception $e)
								{
									$oDataAccess->TransactionRollback();
									Logic_Collection_BatchProcess_Report::queueEvent($oEventInstance);
									if ($e instanceof Exception_Database)
									{
										$aFailedInstances = array_merge($aFailedInstances,Logic_Collection_BatchProcess_Report::commit($e));
										Log::getLog()->log("Database Exception occurred during '$sEventName' event invocation. The process will be cancelled and rolled back.");
										throw $e;
									}
									else
									{
										Log::getLog()->log("Exception occurred during '$sEventName' event invocation. Only this event instance will be rolled back.");
										$aFailedInstances = array_merge($aFailedInstances,Logic_Collection_BatchProcess_Report::commit($e));
									}
								}
							}


							//now complete all succesfully invoked instances
							$sClassName = Logic_Collection_Event::getClassNameForId($iEventId);
							call_user_func(array( $sClassName, 'complete'), $aSuccesfullyInvokedInstances);							
							$aSucceededInstances = array_merge($aSucceededInstances, $aSuccesfullyInvokedInstances);
							$oDataAccess->TransactionCommit();
							Log::getlog()->log("Invoked and completed ".count($aSuccesfullyInvokedInstances)." '$sEventName' events in : ".Logic_Stopwatch::getInstance()->lap()." seconds.");
							$sEventName = NULL;

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
						$sEventName = NULL;
						// Exception caught, rollback db transaction
						$oDataAccess->TransactionRollback();
						if ($e instanceof Exception_Database)
						{
							Logic_Collection_BatchProcess_Report::addException($e);
							$aFailedInstances = array_merge($aFailedInstances,Logic_Collection_BatchProcess_Report::commit($e));
							throw $e;
						}
						else
						{
							Log::getLog()->log($e->__toString());
							$aFailedInstances = array_merge($aFailedInstances,Logic_Collection_BatchProcess_Report::commit($e));
						}
					}
				}
				$oDataAccess->TransactionCommit();
				Logic_Collection_BatchProcess_Report::commit();
			}
			catch(Exception $e)
			{				
				// Exception caught, rollback db transaction
				$oDataAccess->TransactionRollback();
				Log::getLog()->log("Processing $sName failed, all instances will be rolled back.");
				if ($e instanceof Exception_Database)
				{
					Logic_Collection_BatchProcess_Report::addException($e);
					$aFailedInstances = array_merge($aFailedInstances,Logic_Collection_BatchProcess_Report::commit($e));
					throw $e;
				}
				else
				{			
					Log::getLog()->log($e->__toString());
					$aFailedInstances = array_merge($aFailedInstances,Logic_Collection_BatchProcess_Report::commit($e));
				}
			}
		}
		
		Logic_Collection_BatchProcess_Report::commit();
		self::$aEventInstancesWaitingForCompletion = array();
		return array('success'=>$aSucceededInstances, 'failure'=>$aFailedInstances);
	}



	/**
	 * Completes any event instances that are passed in, with optional parameters for each
	 * @param <type> $aParameters - associative array with key == account_collection_event_history.id and value == array of parameters to pass into the invoke method
	 */
	public static function completeScheduledInstancesFromUI($aParameters)
	{
		self::$aEventInstancesWaitingForCompletion = array();
		foreach (array_keys($aParameters) as $iInstanceId)
		{
			$oInstance = new self($iInstanceId);
			$oInstance->addToQueue();
		}

		return self::completeWaitingInstances($aParameters);
	}

	/**
	 * retrieves from the database any scheduled but not completed instances, and completes the auto invoke ones
	 * @param <type> $aAccounts 
	 */
//	public static function completeScheduledInstancesForAccounts($aAccounts)
//	{
//		self::$aEventInstancesWaitingForCompletion = array();
//		foreach ($aAccounts as $oAccount)
//		{
//			//there can only be one waiting event for an account, but the getWaitingEvents() method returns an array.....
//			$aEvent = self::getWaitingEvents($oAccount->id);
//			$oInstance = reset($aEvent);
//			if (count($aEvent) > 0 && ($oAccount->shouldCurrentlyBeInCollections() || $oInstance->isExitEvent()))
//			{
//				if ($oInstance->getInvocationId() == COLLECTION_EVENT_INVOCATION_AUTOMATIC)
//				{
//					$oInstance->addToQueue();
////					if (!array_key_exists($oInstance->collection_event_id, $aEventInstances))
////						$aEventInstances[$oInstance->collection_event_id] = array();
////					$aEventInstances[$oInstance->collection_event_id][] = $oInstance;
//				}
//
//			}
//		}
//		self::completeWaitingInstances();
//	}

	public function complete()
	{
		// Mark the even instance as completed
		$iUserId	= Flex::getUserId();
		$iUserId	= ($iUserId === null ? Employee::SYSTEM_EMPLOYEE_ID : $iUserId);

		$this->oDO->completed_datetime 					= DataAccess::getDataAccess()->getNow();
		$this->oDO->completed_employee_id				= $iUserId;
		$this->oDO->account_collection_event_status_id	= ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED;
		$this->oDO->save();		
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
