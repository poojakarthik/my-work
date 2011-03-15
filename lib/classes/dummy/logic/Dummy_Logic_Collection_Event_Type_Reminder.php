<?php
class Dummy_Logic_Collection_Event_Type_Reminder extends Logic_Collection_Event 
{
    public function __construct($mDefinition = null)
    {
        if ($mDefinition instanceof Dummy_Collection_Event)
        {
 			$this->oParentDO = $mDefinition;
        }
        else if ($mDefinition !== null)
        {
			throw new Exception('Bad parameter passed into Dummy_Logic_Collection_Event_Type_Reminder constructor');
        }
    }

    protected function _invoke() 
    {
		Log::getLog()->log("************* Reminder event invoked: '{$this->oParentDO->name}'");
    }
}
?>
