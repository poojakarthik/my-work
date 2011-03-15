<?php

class Collection_Logic_Scenario_Event implements DataLogic
{
    protected $oDO;

    /*
     * Collection_Logic_Event object
     */
    protected $oCollectionEvent;
    
     /*
     * Collection_Logic_Scenario_Event object
     */
    protected $oPrerequisiteCollectionEvent;

    public function __construct($mDefinition)
    {
        if (is_numeric($mDefinition))
        {
            //implement
        }
        else if (get_class($mDefinition) == 'Collection_Scenario_Collection_Event')
        {
            $this->oDO = $mDefinition;
        }
        else
        {
            throw new Exception('bad parameter passed into Collection_Logic_Scenario_Event constructor');
        }
    }

    public function getCollectionEvent()
    {
        if ($this->oCollectionEvent === null)
                $this->oCollectionEvent = Collection_Logic_Event::getForId($this->oDO->id);
        return  $this->oCollectionEvent;
    }    
        
    /**
     *  the invocation is defined on the event level,but can be overridden on the scenarion_event level
     * 
     */
    public function getInvocationId()
    {
        if ($this->collection_event_invocation_id != null)
                return $this->collection_event_invocation_id;
        return $this->getCollectionEvent()->collection_event_invocation_id;
    }

    public static function getForScenario($oScenario) 
    {
        $aEvents = Collection_Scenario_Collection_Event::getForScenarioId($oScenario->id);
        $aEventObjects = array();
        foreach($aEvents as $oEvent)
        {
            $aEventObjects[$oEvent->id] = new self($oEvent);
        }
        return $aEventObjects;
    }

	public function save()
	{
	}
	
	public function toArray()
	{
	}
	   
	public function __get($sField)
	{
	}
	
	public function __set($sField, $mValue)
	{
	}
}

?>
