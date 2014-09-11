<?php

class Logic_Collection_Scenario_Event implements DataLogic
{
	protected $oDO;
	protected $oCollectionEvent;
 	protected $oPrerequisiteCollectionEvent;

	public function __construct($mDefinition)
	{
		if (is_numeric($mDefinition))
		{
			$this->oDO = Collection_Scenario_Collection_Event::getForId($mDefinition);
		}
		else if ($mDefinition instanceof Collection_Scenario_Collection_Event)
		{
			$this->oDO = $mDefinition;
		}
		else
		{
			throw new Exception('bad parameter passed into Logic_Collection_Scenario_Event constructor');
		}
	}

   public function getEventName()
   {
		return $this->getCollectionEvent()->name;
   }

	/**
	 * this one returns the invocation ID that is set on the collection_scenario_collection_event level.
	 * This might not be the actual invocation id, as this is determined in dependence on other factors, as encoded in the getInvocationId method below.
	 * @return <type>
	 */
	public function getScenarioEventInvocationId()
	{
		return $this->collection_event_invocation_id;
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

		$iInvocation = $this->getScenarioEventInvocationId();
		if ($iInvocation !== null)
			return $iInvocation;

	   if ($oEvent->getInvocationId(false)!==null)
				return $oEvent->getInvocationId();
		throw new Exception('Configuration Error: no invocation method defined scenario event with id '.$this->id);

	}

	public function getCollectionEvent()
	{
		if ($this->oCollectionEvent === NULL)
				$this->oCollectionEvent = Logic_Collection_Event::getForId ($this->collection_event_id);
		return $this->oCollectionEvent;
	}

	public function getNext()
	{
		return $this->getScenario()->getScenarioEventAfter($this);
	}


	public function getScenario()
	{
		return Logic_Collection_Scenario::getForId($this->collection_scenario_id);
	}

	public static function getForId($iId)
	{
		return new self(Collection_Scenario_Collection_Event::getForId($iId));
	}

	public static function getForScenario($oScenario) 
	{
		$aEvents = Collection_Scenario_Collection_Event::getForScenarioId($oScenario->id);
		$aEventObjects = array();
		foreach($aEvents as $oEvent) {
			$aEventObjects[$oEvent->id] = new self($oEvent);
		}
		return $aEventObjects;
	}

	public function save()
	{
		return $this->oDO->save();
	}
	
	public function toArray()
	{
		return $this->oDO->toArray();
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