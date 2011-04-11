<?php
/**
 * Description of Collection_Logic_Event_Action
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Event_Unbarring extends Logic_Collection_Event_Barring_BaseClass
{ 
	public function  __construct($mDefinition)
	{
		$this->iBarringLevel = BARRING_LEVEL_UNRESTRICTED;
		parent::__construct($mDefinition);
	}

	protected function _invoke($aParameters = null)
	{		
		$iRestrictionId = Collection_Restriction::getForSystemName(DISALLOW_AUTOMATIC_UNBARRING)->id;
		$iEmployeeId = null;
		if ($this->getScenario()->allow_automatic_unbar && !$this->getAccount()->getSeverity()->hasRestriction($iRestrictionId))
			$iEmployeeId = Flex::getUserId()!=null?Flex::getUserId():Employee::SYSTEM_EMPLOYEE_ID;
		$this->getAccount()->setBarringLevel( $this->iBarringLevel, $iEmployeeId);
	}   
}
?>
