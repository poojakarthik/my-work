<?php

class JSON_Handler_Collection_Scenario extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getForId($iScenarioId, $bLoadEvents=false)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oScenario	= new Logic_Collection_Scenario($iScenarioId);
			$aScenario	= $oScenario->toArray();
			if ($bLoadEvents)
			{
				$aScenarioEvents		= $oScenario->getEvents();
				$aScenarioEventsCopy	= array();
				foreach ($aScenarioEvents as $oScenarioEvent)
				{
					$aScenarioEvent = $oScenarioEvent->toArray();
					
					// Add event details, digging down to implementation
					$aEvent				= Collection_Event::getForId($oScenarioEvent->collection_event_id)->toArray();
					$aEventType			= Collection_Event_Type::getForId($aEvent['collection_event_type_id'])->toArray();
					$aImplementation	= Collection_Event_Type_Implementation::getForId($aEventType['collection_event_type_implementation_id'])->toArray();
					
					$aEventType['collection_event_type_implementation']	= $aImplementation;
					$aEvent['collection_event_type']					= $aEventType;
					$aScenarioEvent['collection_event'] 				= $aEvent;
					
					$aScenarioEventsCopy[$oScenarioEvent->id] = $aScenarioEvent; 
				}
				$aScenario['events'] = $aScenarioEventsCopy; 
			}
			return	array(
						'bSuccess'	=> true,
						'oScenario'	=> $aScenario
					);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function getAll($bActiveOnly=false, $bSelectableOnly=false, $aMustIncludeIds=null) {
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try {
			if ($bActiveOnly) {
				$aWorkingStatuses = array(WORKING_STATUS_ACTIVE);
			} else if ($bSelectableOnly) {
				$aWorkingStatuses = array(WORKING_STATUS_ACTIVE, WORKING_STATUS_INACTIVE);
			} else {
				$aWorkingStatuses = array(WORKING_STATUS_ACTIVE, WORKING_STATUS_INACTIVE, WORKING_STATUS_DRAFT);
			}
			
			// Add all given the status limits
			$aScenarios = Collection_Scenario::getForWorkingStatus($aWorkingStatuses);
			$aResults	= array();
			foreach ($aScenarios as $oScenario) {
				if ($oScenario->id !== null) {
					$aResults[$oScenario->id] = $oScenario->toStdClass();
				}
			}
			
			// Add the 'must-include' records
			if ($aMustIncludeIds !== null) {
				foreach ($aMustIncludeIds as $iId) {
					if (!isset($aResults[$iId])) {
						$oScenario = Collection_Scenario::getForId($iId);
						if ($oScenario->id) {
							$aResults[$iId] = $oScenario->toStdClass();
						}
					}
				}
			}
			
			return array(
				'bSuccess' => true,
				'aScenarios' => $aResults
			);
		} catch (Exception $e) {
			return array(
				'bSuccess' => false,
				'sMessage' => $e->getMessage()
			);
		}
	}
	
	public function getDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$iRecordCount = Logic_Collection_Scenario::searchFor(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly)
			{
				return	array(
							'bSuccess'		=> true,
							'iRecordCount'	=> $iRecordCount
						);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aScenarios = Logic_Collection_Scenario::searchFor(false, $iLimit, $iOffset, $oSort, $oFilter);
			$aResults	= array();
			$i			= $iOffset;
			foreach ($aScenarios as $aScenario)
			{
				$aResults[$i] = $aScenario;
				$i++;
			}
			
			return	array(
						'bSuccess'		=> true,
						'aRecords'		=> $aResults,
						'iRecordCount'	=> $iRecordCount
					);
		}
		catch (Exception $e)
		{
			$sMessage	= $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function createScenario($oDetails) {
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess = DataAccess::getDataAccess();
		try {
			$oDataAccess->TransactionStart(false);
			
			// Validation
			if ($oDetails->name === '') {
				$aErrors[] = 'No name was supplied';
			}
			
			if (strlen($oDetails->name) > 256) {
				$aErrors[] = 'Name is too long (maximum 256 chars)';
			}
			
			if ($oDetails->description === '') {
				$aErrors[] = 'No description was supplied';
			}
			
			if (strlen($oDetails->description) > 256) {
				$aErrors[] = 'Description is too long (maximum 256 chars)';
			}
			
			if ($oDetails->threshold_percentage === '') {
				$aErrors[] = 'No threshold percentage was supplied';
			}
			
			if ($oDetails->threshold_amount === '') {
				$aErrors[] = 'No threshold amount was supplied';
			}
			
			if ($oDetails->allow_automatic_unbar === null) {
				$aErrors[] = 'No allow automatic unbar (yes/no) was supplied';
			}
			
			if (count($aErrors) > 0) {
				return array(
					'bSuccess' => false,
					'aErrors' => $aErrors
				);
			}
			
			// Create collection_scenario
			if ($oDetails->id !== null) {
				$oScenario = Collection_Scenario::getForId($oDetails->id);
				
				// Clear all collection_scenario_collection_event records
				Collection_Scenario_Collection_Event::removeForScenarioId($oScenario->id);
			} else {
				$oScenario = new Collection_Scenario();
			}
			
			$oScenario->name = $oDetails->name;
			$oScenario->description = $oDetails->description;
			$oScenario->day_offset = $oDetails->day_offset;
			$oScenario->working_status_id = $oDetails->working_status_id;
			$oScenario->threshold_percentage = $oDetails->threshold_percentage;
			$oScenario->threshold_amount = $oDetails->threshold_amount;
			$oScenario->initial_collection_severity_id = $oDetails->initial_collection_severity_id;
			$oScenario->allow_automatic_unbar = (int)$oDetails->allow_automatic_unbar;

			if (($oDetails->broken_promise_collection_scenario_id !== null) && ($oDetails->broken_promise_collection_scenario_id !== '')) {
				$oScenario->broken_promise_collection_scenario_id = (int)$oDetails->broken_promise_collection_scenario_id;
			}

			if (($oDetails->dishonoured_payment_collection_scenario_id !== null) && ($oDetails->dishonoured_payment_collection_scenario_id !== '')) {
				$oScenario->dishonoured_payment_collection_scenario_id = (int)$oDetails->dishonoured_payment_collection_scenario_id;
			}
			
			$oScenario->save();
			
			// Create collection_scenario_collection_event(s)
			$oPrevious = null;
			foreach ($oDetails->collection_event_data as $oEventData) {
				$oScenarioEvent = new Collection_Scenario_Collection_Event();
				$oScenarioEvent->collection_scenario_id = $oScenario->id;
				$oScenarioEvent->collection_event_id = $oEventData->collection_event_id;
				$oScenarioEvent->collection_event_invocation_id = $oEventData->collection_event_invocation_id;
				$oScenarioEvent->day_offset = $oEventData->day_offset;
				$oScenarioEvent->prerequisite_collection_scenario_collection_event_id = ($oPrevious ? $oPrevious->id : null);
				$oScenarioEvent->save();
				$oPrevious = $oScenarioEvent;
			}
			
			$oDataAccess->TransactionCommit(false);

			return array(
				'bSuccess' => true,
				'iScenarioId' => $oScenario->id
			);
		} catch (Exception $e) {
			$oDataAccess->TransactionRollback(false);
			return array(
				'bSuccess' => false,
				'sMessage' => $e->getMessage()
			);
		}
	}
	
	public function setStatus($iScenarioId, $iWorkingStatusId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oScenario 						= Collection_Scenario::getForId($iScenarioId);
			$oScenario->working_status_id	= $iWorkingStatusId;
			$oScenario->save();
			
			return	array(
						'bSuccess'	=> true,
						'sDebug'	=> ($bUserIsGod ? $this->_JSONDebug : '')
					);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
}

class JSON_Handler_Collection_Scenario_Exception extends Exception
{
	// No changes
}

?>