<?php
class Collection_Logic_Scenario implements DataLogic 
{
    protected $oDO;

    /*
     * array of Collection_Logic_Scenario_Event objects
     */
    protected $aScenarioEvents;

    public function getEventToTrigger($iDayOffset, $oPrerequisite)
    {
    	if ($this->aScenarioEvents === null)
       		$this->aScenarioEvents = Collection_Logic_Scenario_Event::getForScenario($this);
		
        if ($oPrerequisite == null || $oPrerequisite->collection_scenario_id != $this->id)
                return $this->getInitialScenarioEvent();
         foreach($this->aScenarioEvents as $oEvent)
        {
            if (($oEvent->prerequisite_collection_scenario_collection_event_id == $oPrerequisite->id) && $iDayOffset >= $oEvent->day_offset)
        		return $oEvent;
            return null;
        }
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
