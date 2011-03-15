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

    public function getCollectionEvent()
    {
        if ($this->oCollectionEvent === null)
        {
        	$this->oCollectionEvent = Logic_Collection_Event::getForId($this->oDO->collection_event_id);
        }
        return $this->oCollectionEvent;
    }    
    
    public function getScenarioEventInvocationId()
    {
        return $this->collection_event_invocation_id;
    }

    public static function getForId($iId)
    {
        return new self(Collection_Scenario_Collection_Event::getForId($iId));
    }

    public static function getForScenario($oScenario) 
    {
        $aEvents 		= Collection_Scenario_Collection_Event::getForScenarioId($oScenario->id);
        $aEventObjects	= array();
        foreach($aEvents as $oEvent)
        {
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