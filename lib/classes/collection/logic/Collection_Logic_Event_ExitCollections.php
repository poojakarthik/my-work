<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Collection_Logic_Event_ExitCollections
 *
 * @author JanVanDerBreggen
 */
class Collection_Logic_Event_ExitCollections extends Collection_Logic_Event {

    public function __construct($mDefinition = null)
    {
        if (get_class($mDefinition == 'Collection_Event'))
        {
           $this->oParentDO = $mDefinition;
          
        }
        else if ($mDefinition!== null)
        {
           throw new Exception('bad parameter passed into Collection_Logic_Event_ExitCollections constructor');
        }
    }

    protected function _invoke() {

    }

}
?>



?>
