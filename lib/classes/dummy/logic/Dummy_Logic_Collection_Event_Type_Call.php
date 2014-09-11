<?php
class Dummy_Logic_Collection_Event_Type_Call extends Logic_Collection_Event 
{
    public function __construct($mDefinition = null)
    {
       	if ($mDefinition instanceof Dummy_Collection_Event)
        {
 			$this->oParentDO = $mDefinition;
        }
        else if ($mDefinition !== null)
        {
			throw new Exception('bad parameter passed into Dummy_Logic_Collection_Event_Type_Call constructor');
        }
    }

    protected function _invoke() 
    {
		Log::getLog()->log("************* Call event invoked: '{$this->oParentDO->name}'");
    }
}
?>
