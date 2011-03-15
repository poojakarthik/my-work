<?php
/**
 * Description of Collection_Logic_Event_Action
 *
 * @author JanVanDerBreggen
 */
abstract class Logic_Collection_Event_Barring_BaseClass extends Logic_Collection_Event
{
   protected $iBarringLevel;

    public function __construct($mDefinition, $aInvocationParameters = null)
    {
        $this->aInvocationParameters = $aInvocationParameters;

        if ($mDefinition instanceof Logic_Collection_Event_Instance)
        {
           $this->oCollectionEventInstance = $mDefinition;
           $this->oParentDO = Collection_Event::getForId($mDefinition->collection_event_id);

        }
        else
        {
           throw new Exception ('Bad definition of Logic_Collection_Event_Action');
        }
    }

    protected function _invoke($aParameters = null)
    {
        $iEmployeeId = Flex::getUserId()!=null?Flex::getUserId():Employee::SYSTEM_EMPLOYEE_ID;
        $this->getAccount()->setBarringLevel( $this->iBarringLevel, $iEmployeeId);

    }

    public static function complete($aEventInstances)
    {
        foreach ($aEventInstances as $oInstance)
        {
            $oInstance->complete();
        }
    }
}
?>
