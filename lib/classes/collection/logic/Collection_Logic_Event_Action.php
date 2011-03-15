<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Collection_Logic_Event_Action
 *
 * @author JanVanDerBreggen
 */
class Collection_Logic_Event_Action extends Collection_Logic_Event 
{	
	public function __construct($mDefinition)
	{
		if (is_numeric($mDefinition))
		{
		}
		else if (get_class($mDefinition == 'Collection_Event_Action'))
		{
			$this->oDO = $mDefinition;
		}
		else if (get_class($mDefinition == 'Collection_Event'))
		{
			$this->oParentDO = $mDefinition;
			$this->oDO = Collection_Event_Action::getForCollectionEventId( $this->oParentDO->id);
		}
	}

    protected function _invoke() 
    {

    }
}
?>
