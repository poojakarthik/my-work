<?php
/**
 * Description of Collection_Logic_Event_Action
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Event_Barring extends Logic_Collection_Event_Barring_BaseClass
{   

	public function  __construct($mDefinition)
	{
		$this->iBarringLevel = BARRING_LEVEL_BARRED;
		parent::__construct($mDefinition);
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
