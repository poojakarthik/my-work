<?php


/**
 * Description of Collection_Logic_Event_Severity
 *
 * @author JanVanDerBreggen
 */
class Collection_Logic_Event_Severity extends Collection_Logic_Event 
{
	protected $oSeverity;
    protected $oDO;
    public function __construct($mDefinition)
    {
        if (is_numeric($mDefinition))
        {
            //implement
        }
        else if (get_class($mDefinition == 'Collection_Event_Severity'))
        {
           $this->oDO = $mDefinition;
           //implement further
        }
        else if (get_class($mDefinition == 'Collection_Event'))
        {
           $this->oParentDO = $mDefinition;
           $this->oDO = Collection_Event_Severity::getForCollectionEventId( $this->oParentDO->id);
        }
        else
        {
           throw new Exception('bad parameter passed into Collection_Logic_Event_Report constructor');
        }
    }

    public function getSeverity()
    {
        if ($this->oSeverity === null)
            $this->oSeverity = Collection_Logic_Severity::getForId($this->collection_severity_id);
        return $this->oSeverity;
    }

    protected function _invoke() 
    {

    }    
}
?>
