<?php

class Logic_Collection_Scenario_Instance
{
	protected $oScenario;
	protected $oDO;

	public function __construct($mDefinition)
	{
		$this->oDO	= null;
		if ($mDefinition instanceof Account_Collection_Scenario)
		{
			$this->oDO = $mDefinition;
		}
		else if (is_array($mDefinition))
		{
			$this->oDO = new Account_Collection_Scenario($mDefinition);
		}
		
		if ($this->oDO)
		{
			// Valid definition, load the scenario logic object
			$oScenarioDO		= Collection_Scenario::getForId($this->oDO->collection_scenario_id);
			$this->oScenario	= new Logic_Collection_Scenario($oScenarioDO);
		}
	}

	public function getScenario()
	{
		return $this->oScenario;
	}

	public static function getForAccount($oAccount, $bActiveOnly = true)
	{
		$aScenarios	= Account_Collection_Scenario::getForAccountId($oAccount->Id, $bActiveOnly);
		$aInstances	= array();
		foreach ($aScenarios as $oAccountCollectionScenario)
		{
			$aInstances[]	= new self($oAccountCollectionScenario);
		}
		return $aInstances;
	}


	public function getInitialScenarioEvent ($iDaysSinceStartOfCollections = NULL)
	{
		return $this->oScenario->getInitialScenarioEvent($iDaysSinceStartOfCollections);
	}

	

	public function getNextScheduledEvent($oMostRecentCollectionEvent, $sDueDate, $bIgnoreDayOffsetRules = FALSE)
	{
		//$iDayOffset = Flex_Date::difference($sDueDate,  Data_Source_Time::currentDate(), 'd');
		return $this->oScenario->getEventToTrigger( $oMostRecentCollectionEvent,  $sDueDate, $bIgnoreDayOffsetRules);
	}
	
	public function __get($sField)
	{
		switch ($sField)
		{
			case 'scenario':
				return $this->oScenario;
				break;
			default:
				return $this->oDO->{$sField};
				break;
		}
	}

	public function __set($sField, $mValue) 
	{
		$this->oDO->{$sField} = $mValue;
	}

	public function save() 
	{
		return $this->oDO->save();
	}

	public function toArray() 
	{
		return $this->oDO->toArray();
	}

	public function display()
	{
		////Log::getLog()->log('details for scenario instance '.$this->id);
		////Log::getLog()->log('Account id: '.$this->account_id);
		////Log::getLog()->log('Scenario id: '.$this->collection_scenario_id);
		$this->getScenario()->display();

	}
}
?>
