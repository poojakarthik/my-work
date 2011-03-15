<?php

class Collection_Logic_Scenario_Instance
{
    protected $oScenario;
    protected $oDO;

    public static function getForAccount($oAccount) 
    {

    }

    public function getNextScheduledEvent($sDueDate, $oMostRecentCollectionEvent)
    {
        return $this->oScenario->getEventToTrigger(Flex_Date::difference($sDueDate,  Data_Source_Time::currentDate(), 'd'), $oMostRecentCollectionEvent);
    }
}
?>
