<?php
/**
 * Description of Logic_Collection_Event_ExitCollections
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Event_ExitCollections extends Logic_Collection_Event 
{
    public function __construct($oEventInstance = null)
    {
        $iExitEventTypeId = Collection_Event_Type::getForSystmName('EXIT_COLLECTIONS')->id;

        $this->oCollectionEventInstance = $oEventInstance;
        $this->oParentDO = Collection_Event::getForType($iExitEventTypeId, true);
        if ($this->oParentDO === null)
                throw new Exception("Collections configuration error: no exit event defined in the database");
        
    }

    /**
     * The exit event triggers a reset scenario on the account
     */
    protected function _invoke($aParameters = null)
    {
        
        //TODO: implement whatever needs to be done at this point, as the specifications become clear
        //TODO: what to do with previous events that were not yet completed?
        if ($this->getAccount()->isBarred())
        {
            $iRestrictionId = Collection_Restriction::getForSystemName(DISALLOW_AUTOMATIC_UNBARRING)->id;
            $iEmployeeId = null;
            $iBarringLevelToSet = BARRING_LEVEL_UNRESTRICTED;
            if ($this->getScenario()->allow_automatic_unbar && !$this->getAccount()->getSeverity()->hasRestriction( $iRestrictionId))
                    $iEmployeeId = Flex::getUserId()!=null?Flex::getUserId():Employee::SYSTEM_EMPLOYEE_ID;
            $this->getAccount()->setBarringLevel( $iBarringLevelToSet, $iEmployeeId);
        }
        $this->getAccount()->resetSeverity();
        $this->getAccount()->resetScenario();
        if ($this->getAccount()->hasPendingOCAReferral())
        {
            $this->getAccount()->cancelPendingOCAReferral();
        }

        $this->getAccount()->cancelScheduledScenarioEvents();

        

    }

    public static function complete($aEventInstances)
    {
        foreach ($aEventInstances as $oInstance)
        {
            $oInstance->complete();
        }
    }

	 public function __get($sField)
    {

		return $this->oParentDO->$sField;

    }
}
?>
