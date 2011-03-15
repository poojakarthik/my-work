<?php
/**
 * Description of Collection_Logic_Event_Report
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Event_Milestone extends Logic_Collection_Event
{
   
    

    public function __construct($mDefinition)
    {
       
        if ($mDefinition instanceof Logic_Collection_Event_Instance)
        {
           $this->oCollectionEventInstance = $mDefinition;
           $this->oParentDO = Collection_Event::getForId($mDefinition->collection_event_id);

        }
    }    


    protected function _invoke($aParameters = null)
    {

    }

    public function __get($sField)
    {
        return $this->oParentDO->$sField;
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
