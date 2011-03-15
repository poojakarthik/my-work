<?php

class JSON_Handler_Collection_Event_Instance extends JSON_Handler
{
	protected	$_JSONDebug	= '';

	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getDataset($bCountOnly=false, $iLimit, $iOffset, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			if ($bCountOnly)
			{
				// Return count only
				return array('bSuccess' => true, 'iRecordCount' => Logic_Collection_Event_Instance::getForLedger(true, null, null, null, $oFilter));
			}
			
			// Get the dataset and the record count
			$iLimit 	= ($iLimit === 0 ? null : $iLimit);
			$aResult 	= Logic_Collection_Event_Instance::getForLedger($bCountOnly, $iLimit, $iOffset, $oSort, $oFilter);
			
			$aRecords	= array();
			$i			= $iOffset;
			foreach ($aResult['aData'] as $aRecord)
			{
				$aRecords[$i] = $aRecord;
				$i++;
			}
			
			return	array(
						'bSuccess'		=> true,
						'aRecords'		=> $aRecords,
						'iRecordCount'	=> $aResult['iCount']
					);
		}
		catch (JSON_Handler_Collection_Event_Instance_Exception $oException)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage()
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
	
	public function generateLedgerFile($oSort=null, $oFilter=null, $sFileType)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aColumns =	array(
							'account_id' 							=> 'Account|int',
							'account_name' 							=> 'Business Name',
							'collection_scenario_name'				=> 'Scenario',
							'collection_event_type_name'			=> 'Event Type',
							'collection_event_name'					=> 'Event',
							'scheduled_datetime' 					=> 'Scheduled On',
							'account_collection_event_status_name'	=> 'Status'
						);
			$aResult = Logic_Collection_Event_Instance::getForLedger(false, null, null, $oSort, $oFilter);
			
			// Build list of lines for the file
			$aLines		= array();
			$aRecords	= $aResult['aData'];
			foreach ($aRecords as $aRecord)
			{
				$aLine = array();
				foreach ($aColumns as $sField => $sTitle)
				{
					$mValue = $aRecord[$sField];
					if (($sField == 'account_collection_event_status_name') && ($aRecord['account_collection_event_status_id'] == ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED))
					{
						$sCompletedDatetime	= date('d/m/Y H:i', strtotime($aRecord['completed_datetime']));
						$mValue 			.= " ({$sCompletedDatetime})";
					}
					$aLine[$sTitle] = $mValue;
				}
				$aLines[] = $aLine;
			}
			
			switch ($sFileType)
			{
				case 'CSV':
					$sFileExtension = 'csv';
					$sMIME			= 'text/csv';
					break;
				case 'Excel2007':
					$sFileExtension = 'xlsx';
					$sMIME			= 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
					break;
			}
			
			$sFilename	= "collections_event_ledger_".date('YmdHis').".{$sFileExtension}";
			$sFilePath	= FILES_BASE_PATH."/temp/{$sFilename}";
			
			$oSpreadsheet = new Logic_Spreadsheet(array_keys($aLines[0]), $aLines, $sFileType);
            $oSpreadsheet->saveAs($sFilePath, $sFileType);
			
			return array('bSuccess' => true, 'sFilename' => $sFilename, 'sMIME' => $sMIME);
		}
		catch (Exception $oException)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return array('bSuccess' => false, 'sMessage' => $sMessage);
		}
	}
	
	public function getForIds($aInstanceIds)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aInstances = array();
			foreach ($aInstanceIds as $iId)
			{
				// Determine action_type_id
				$oEventInstance	= new Logic_Collection_Event_Instance($iId);
				$oEvent 		= Collection_Event::getForId($oEventInstance->collection_event_id);
				$oEventType 	= Collection_Event_Type::getForId($oEvent->collection_event_type_id);
				
				switch ($oEventType->collection_event_type_implementation_id)
				{
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_ACTION:
						$oDetails = Collection_Event_Action::getForCollectionEventId($oEvent->id)->toStdClass();
						break;
						
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_CHARGE:
						$oDetails = Collection_Event_Charge::getForCollectionEventId($oEvent->id)->toStdClass();
						break;
						
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_CORRESPONDENCE:
						$oDetails = Collection_Event_Correspondence::getForCollectionEventId($oEvent->id)->toStdClass();
						break;
						
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_OCA:
						$oDetails = Collection_Event_OCA::getForCollectionEventId($oEvent->id)->toStdClass();
						break;
						
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_REPORT:
						$oDetails = Collection_Event_Report::getForCollectionEventId($oEvent->id)->toStdClass();
						break;
						
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_SEVERITY:
						$oDetails = Collection_Event_Severity::getForCollectionEventId($oEvent->id)->toStdClass();
						break;
				}
				
				$aInstance = $oEventInstance->toArray();
				
				$aInstance['collection_event']							= $oEvent->toArray();
				$aInstance['collection_event']['detail']				= $oDetails;
				$aInstance['collection_event']['collection_event_type']	= $oEventType->toArray();
				
				$aInstance['collection_event_invocation_id']	= $oEventInstance->getInvocationId();
				$aInstance['account']							= Account::getForId($oEventInstance->account_id)->toArray();
				
				if ($oEventInstance->completed_employee_id)
				{
					$aInstance['completed_employee'] = Employee::getForId($oEventInstance->completed_employee_id)->toArray();
				} 
				
				$aInstances[$iId] = $aInstance;
			}
			
			return	array(
						'bSuccess'	=> true,
						'aResults'	=> $aInstances
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
}

class JSON_Handler_Collection_Event_Instance_Exception extends Exception
{
	// No changes
}

?>