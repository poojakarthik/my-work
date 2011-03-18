<?php

class Logic_Collection_Scenario implements DataLogic 
{
    protected $oDO;

    // Array of Collection_Logic_Scenario_Event objects
    protected $aScenarioEvents;


	public function __construct($mDefinition)
	{
		$this->oDO	= null;
		if ($mDefinition instanceof Collection_Scenario)
		{
			$this->oDO = $mDefinition;
		}
		else if (is_array($mDefinition))
		{
			$this->oDO = new Collection_Scenario($mDefinition);
		}
                else if (is_numeric($mDefinition))
                {
                    $this->oDO = Collection_Scenario::getForId($mDefinition);
                }
	}

    public function getInitialScenarioEvent($iDayOffset) 
    {
    	
    	$iDayOffset += $this->oDO->day_offset;
    	
    	// Determine if the first event has been reached
		$aScenarioEvents 	= $this->getEvents();
		$oFirst				= array_shift($aScenarioEvents);
		////Log::getLog()->log("Day offset = {$iDayOffset}, needs to be atleast {$oFirst->day_offset}");
		
		if ($iDayOffset >= $oFirst->day_offset)
		{
			////Log::getLog()->log("Found initial event ($oFirst->id).");
			return $oFirst;
		}
		
		////Log::getLog()->log("Not ready for initial event: offset={$iDayOffset}, should be: {$oFirst->day_offset}");
		return null;
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

	public function getEvents()
	{
		if ($this->aScenarioEvents === null)
		{
			$this->aScenarioEvents = Logic_Collection_Scenario_Event::getForScenario($this);
		}
		return $this->aScenarioEvents;
	}

    /**
     * gets the next event to be scheduled
     * @param <type> $oMostRecentEventInstance - most recent event, null if there was none
     * @param <type> $sDueDate - duedate of the oldest collectable on the account, used to calculate the day offset if there was no previous event, or if the previous event belonged to a different scenario
     * @return <type> scenario event or null
     */
    public function getEventToTrigger($oMostRecentEventInstance, $sStartDate, $bIgnoreDayOffsetRules = FALSE)
    {
       if ($oMostRecentEventInstance!== null && $oMostRecentEventInstance->completed_datetime === null && !$bIgnoreDayOffsetRules)
       {
           ////Log::getLog()->log("No event should be triggered, the previous event is still in a scheduled (not completed) state");
           
           return null; //previous event is still awaiting completion, no new event to be triggered
       }
       
       $aScenarioEvents = $this->getEvents();
       $iMostRecentEventScenario    = $oMostRecentEventInstance!= null && $oMostRecentEventInstance->getScenario()!=null ? $oMostRecentEventInstance->getScenario()->id : null;
       $oMostRecentScenarioEvent    = $oMostRecentEventInstance!= null ? $oMostRecentEventInstance->getScenarioEvent() : null;
       $sPointOfReference           = $oMostRecentEventInstance!= null ?date('Y-m-d', Flex_Date::truncate($oMostRecentEventInstance->completed_datetime, 'd', false)) : $sStartDate;
       $iDayOffset = Flex_Date::difference( $sPointOfReference,  Data_Source_Time::currentDate(), 'd');
        if ($iMostRecentEventScenario != $this->id)
        {
            ////Log::getLog()->log("No prerequisite, getting initial event");
            return $this->getInitialScenarioEvent($iDayOffset);
        }
        
        foreach($aScenarioEvents as $oEvent)
        {
            ////Log::getLog()->log("Checking event {$oEvent->id} ({$iDayOffset} against {$oEvent->day_offset}), ({$oEvent->prerequisite_collection_scenario_collection_event_id} against {$oMostRecentScenarioEvent->id})");
            if (($oEvent->prerequisite_collection_scenario_collection_event_id == $oMostRecentScenarioEvent->id) && ($iDayOffset >= $oEvent->day_offset || $bIgnoreDayOffsetRules))
            {
            	////Log::getLog()->log("Found next event");
            	return $oEvent;
            }
        }
        
        ////Log::getLog()->log("No event found given the current day offset or prerequisite rules");
        
        return null;
    }

    public function evaluateThresholdCriterion($fAmount, $fBalance)
    {
	$iPercentage = $fAmount > 0 ? ($fBalance/$fAmount) * 100 : 0;

	if ( $iPercentage >= $this->threshold_percentage && $fBalance >= $this->threshold_amount)
	{
	    return true;
	}
	else
	{
	    return false;
	}
    }

    public function save()
    {
    	return $this->oDO->save();
    }
    
    public function toArray()
    {
    	return $this->oDO->toArray();
    }
    
    public function __get($sField)
    {
    	switch ($sField)
    	{
	    case 'day_offset':
		$mOffset = $this->oDO->day_offset;
		return $mOffset === null ? 0 : $mOffset;
	    default:
    			return $this->oDO->{$sField};
    			break;
    	}
    }
    
    public function __set($sField, $mValue)
    {
    	$this->oDO->{$sField} = $mValue;
    }

    public function display()
    {
        ////Log::getLog()->log('Details for Scenario '.$this->id);
        ////Log::getLog()->log('Scenario Name: '.$this->name);
        ////Log::getLog()->log('Scenario Day offset: '.$this->day_offset);
        ////Log::getLog()->log('Scenario entry percentage: '.$this->entry_threshold_percentage);
        ////Log::getLog()->log('Scenario entry amount: '.$this->entry_threshold_amount);
        ////Log::getLog()->log('Scenario exit percentage: '.$this->exit_threshold_percentage);
        ////Log::getLog()->log('Details exit amount: '.$this->exit_threshold_amount);
    }

    public static function getForId($iScenarioId)
    {
        return new self(Collection_Scenario::getForId($iScenarioId));
    }


    
    public static function searchFor($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$sFrom	= "	collection_scenario csc
					LEFT JOIN	collection_severity cs ON (cs.id = csc.initial_collection_severity_id)
					JOIN		working_status ws ON (ws.id = csc.working_status_id)";
		if ($bCountOnly)
		{
			$sSelect	= "COUNT(csc.id) AS scenario_count";
			$sOrderBy	= '';
			$sLimit		= '';
		}
		else
		{
			$sSelect = "csc.*,
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
		
		$aWhere 	= 	Statement::generateWhere(
							array('working_status_id' => 'csc.working_status_id'), 
							get_object_vars($oFilter)
						);
		$oSelect	=	new StatementSelect(
							$sFrom, 
							$sSelect, 
							$aWhere['sClause'], 
							$sOrderBy, 
							$sLimit
						);
		
		if ($oSelect->Execute($aWhere['aValues']) === FALSE)
		{
			throw new Exception_Database("Failed to get Scenarios. ". $oSelect->Error());
		}
		
		if ($bCountOnly)
		{
			$aRow = $oSelect->Fetch();
			return $aRow['scenario_count'];
		}
		
		$aResults = array();
		while ($aRow = $oSelect->Fetch())
		{
			$aResults[$aRow['id']] = $aRow;
		}
		
		return $aResults;
	}
}
?>
