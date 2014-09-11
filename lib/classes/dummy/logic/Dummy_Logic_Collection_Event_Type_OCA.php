<?php
class Dummy_Logic_Collection_Event_Type_OCA extends Logic_Collection_Event 
{
    public function __construct($mDefinition = null)
    {
        if ($mDefinition instanceof Dummy_Collection_Event)
        {
 			$this->oParentDO = $mDefinition;
        }
        else if ($mDefinition !== null)
        {
			throw new Exception('bad parameter passed into Dummy_Logic_Collection_Event_Type_OCA constructor');
        }
    }

    protected function _invoke() 
    {
		Log::getLog()->log("************* OCA event invoked: '{$this->oParentDO->name}'");
    }
}
?>
