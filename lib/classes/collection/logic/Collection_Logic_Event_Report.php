<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Collection_Logic_Event_Report
 *
 * @author JanVanDerBreggen
 */
class Collection_Logic_Event_Report extends Collection_Logic_Event 
{
    protected $oDO;
    public function __construct($mDefinition)
    {
       if (is_numeric($mDefinition))
       {
            //implement
       }
       else if (get_class($mDefinition == 'Collection_Event_Report'))
       {
           $this->oDO = $mDefinition;
           //implement further
       }
       else if (get_class($mDefinition == 'Collection_Event'))
       {
           $this->oParentDO = $mDefinition;
           $this->oDO = Collection_Event_Report::getForCollectionEventId( $this->oParentDO->id);
       }
       else
       {
           throw new Exception('bad parameter passed into Collection_Logic_Event_Report constructor');
       }
    }

    protected function _invoke() 
    {

    }
}
?>
