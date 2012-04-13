<?php
class Logic_Collection_Scenario implements DataLogic {
	protected $oDO;

	// Array of Collection_Logic_Scenario_Event objects
	protected $aScenarioEvents;

	public function __construct($mDefinition) {
		$this->oDO	= null;
		if ($mDefinition instanceof Collection_Scenario) {
			$this->oDO = $mDefinition;
		} else if (is_array($mDefinition)) {
			$this->oDO = new Collection_Scenario($mDefinition);
		} else if (is_numeric($mDefinition)) {
			//Log::get()->log("Getting Logic_Collection_Scenario for Id ".var_export($mDefinition, true));
			$this->oDO = Collection_Scenario::getForId($mDefinition);
			//Log::get()->log("DO for Logic_Collection_Scenario: ".print_r($this->oDO, true));
		}
	}

	public function getInitialScenarioEvent() {		
		$aScenarioEvents = $this->getEvents();
		return array_shift($aScenarioEvents);
	}

	/**
	 *  Collection_Logic_Scenario::getEventToTrigger
	 *
	 * determines the next event that should be scheduled for this scenario, based on the params passed in:
	 * - if $oPrerequisite === null OR $oPrerequisite belongs to a different scenario: the first event of the scenario is returned
	 * - else the event that has $oPrerequisite as its prerequisite and satisfies the day offset criteria is returned
	 *
	 * @param <type> $iDayOffset - days between the due date and the current date
	 * @param <type> $oPrerequisite - most recently completed event
	 * @return <type> the event to trigger, or null if no event qualifies
	 */
	public function getEvents() {
		if ($this->aScenarioEvents === null) {
			$this->aScenarioEvents = Logic_Collection_Scenario_Event::getForScenario($this);
		}
		return $this->aScenarioEvents;
	}

	/**
	 * returns the scneario event after the scenario event passed in as parameter
	 * this is the method to use if you wish to retrieve the next event without wanting to determine whether is should actually be scheduled at this time
	 * @param Logic_Collection_Scenario_Event $oEvent
	 * @return <type>
	 */
	public function getScenarioEventAfter(Logic_Collection_Scenario_Event $oEvent=null) {
		if ($oEvent === NULL || $oEvent->collection_scenario_id !== $this->id) {
			return $this->getInitialScenarioEvent (0, true);
		}
		$aScenarioEvents = $this->getEvents();
		$bThisOne = false;
		
		foreach($aScenarioEvents as $iId=>$oEventObject) {			
			 if ($oEventObject->prerequisite_collection_scenario_collection_event_id === $oEvent->id) {
				return $oEventObject;
			}
		}

		return null;
	}

	public function evaluateThresholdCriterion($fAmount, $fBalance) {
		$iPercentage = $fAmount > 0 ? ($fBalance/$fAmount) * 100 : 0;
		if ($iPercentage >= $this->threshold_percentage && $fBalance >= $this->threshold_amount) {
			return true;
		} else {
			return false;
		}
	}

	public function save() {
		return $this->oDO->save();
	}
	
	public function toArray() {
		return $this->oDO->toArray();
	}
	
	public function __get($sField) {
		$mValue = $this->oDO->$sField;
		//Log::get()->log("Getting field ".var_export($sField, true)." (Raw: ".var_export($mValue, true).")");
		switch ($sField) {
			case 'day_offset':
				return $mValue === null ? 0 : $mValue;
		}
		return $mValue;
	}
	
	public function __set($sField, $mValue) {
		$this->oDO->$sField = $mValue;
	}

	public function display() {
		////Log::getLog()->log('Details for Scenario '.$this->id);
		////Log::getLog()->log('Scenario Name: '.$this->name);
		////Log::getLog()->log('Scenario Day offset: '.$this->day_offset);
		////Log::getLog()->log('Scenario entry percentage: '.$this->entry_threshold_percentage);
		////Log::getLog()->log('Scenario entry amount: '.$this->entry_threshold_amount);
		////Log::getLog()->log('Scenario exit percentage: '.$this->exit_threshold_percentage);
		////Log::getLog()->log('Details exit amount: '.$this->exit_threshold_amount);
	}

	public static function getForId($iScenarioId) {
		return new self(Collection_Scenario::getForId($iScenarioId));
	}

	public static function searchFor($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null) {
		$sFrom	= "
			collection_scenario csc
			LEFT JOIN collection_severity cs ON (cs.id = csc.initial_collection_severity_id)
			JOIN working_status ws ON (ws.id = csc.working_status_id)";
		if ($bCountOnly) {
			$sSelect	= "COUNT(csc.id) AS scenario_count";
			$sOrderBy	= '';
			$sLimit		= '';
		} else {
			$sSelect = "
				csc.*,
				cs.name AS initial_collection_severity_name,
				IF(allow_automatic_unbar = 1, 'Yes', 'No') AS allow_automatic_unbar_name, 
				ws.name AS working_status_name";
			$sOrderBy =	Statement::generateOrderBy(
				array(
					'id' 			=> 'csc.id', 
					'name' 			=> 'csc.name', 
					'description' 	=> 'csc.description'
				), 
				get_object_vars($oSort)
			);
			$sLimit = Statement::generateLimit($iLimit, $iOffset);
		}
		
		$aWhere = Statement::generateWhere(
			array('working_status_id' => 'csc.working_status_id'), 
			get_object_vars($oFilter)
		);
		$oSelect = new StatementSelect(
			$sFrom, 
			$sSelect, 
			$aWhere['sClause'], 
			$sOrderBy, 
			$sLimit
		);
		
		if ($oSelect->Execute($aWhere['aValues']) === false) {
			throw new Exception_Database("Failed to get Scenarios. ".$oSelect->Error());
		}
		
		if ($bCountOnly) {
			$aRow = $oSelect->Fetch();
			return $aRow['scenario_count'];
		}
		
		$aResults = array();
		while ($aRow = $oSelect->Fetch()) {
			$aResults[$aRow['id']] = $aRow;
		}
		
		return $aResults;
	}
}
?>