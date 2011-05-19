<?php

class JSON_Handler_Collection_Event extends JSON_Handler
{
	public function getScenarioEventForId($iId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aInstances = array();

			// Determine action_type_id
			$oEventInstance	= new Logic_Collection_Scenario_Event($iId);
			//$oEvent 		= Collection_Event::getForId($oEventInstance->collection_event_id);
			$oEvent = $oEventInstance->getCollectionEvent();
			$oEventType 	= Collection_Event_Type::getForId($oEvent->collection_event_type_id);


			$aInstance = $oEventInstance->toArray();
			$aInstance['collection_event']							= $oEventInstance->toArray();
			$aInstance['collection_event']['detail']				= $oEvent->toArray();
			$aInstance['collection_event']['collection_event_type']	= $oEventType->toArray();
			$aInstance['collection_event_invocation_id']	= $oEventInstance->getInvocationId();
			$aInstances[$iId] = $aInstance;


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
	
	public function getDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			if ($oFilter !== null)
			{
				if ($oFilter->collection_event_invocation_id === '0')
				{
					$oFilter->collection_event_invocation_id = 'NULL';
				}
			}
			
			$iRecordCount = Logic_Collection_Event::searchFor(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly)
			{
				return	array(
							'bSuccess'		=> true,
							'iRecordCount'	=> $iRecordCount
						);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aTypes 	= Logic_Collection_Event::searchFor(false, $iLimit, $iOffset, $oSort, $oFilter);
			$aResults	= array();
			$i			= $iOffset;
			foreach ($aTypes as $aEvent)
			{
				// Get event detail
				$oEventType	= Collection_Event_Type::getForId($aEvent['collection_event_type_id']);
				switch ($oEventType->collection_event_type_implementation_id)
				{
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_CORRESPONDENCE:
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_REPORT:
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_ACTION:
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_SEVERITY:
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_OCA:
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_CHARGE:
						$bHasDetails = true;
						break;
					
					default:
						$bHasDetails = false;
				}
				
				$aEvent['has_details']	= $bHasDetails;
				$aResults[$i] 			= $aEvent;
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
	
	public function getBarringRequestCountForDate($sDate)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$iRequestCount = Account_Barring_Level::getScheduledCountOnDayForBarringLevel($sDate, BARRING_LEVEL_TEMPORARY_BARRED);
			return array('iRequestCount' => $iRequestCount, 'bSuccess' => true);
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
	
	public function getTDCRequestCountForDate($sDate)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$iRequestCount = Account_Barring_Level::getScheduledCountOnDayForBarringLevel($sDate, BARRING_LEVEL_TEMPORARY_DISCONNECTION);
			return array('iRequestCount' => $iRequestCount, 'bSuccess' => true);
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
	
	public function invokeActionEvents($aEventInstanceDetails)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{		
            $aParameters = array();
            foreach ($aEventInstanceDetails as $iEventInstanceId => $sActionDetail)
            {
                $aParameters[$iEventInstanceId] = array('extra_details' => $sActionDetail);
            }
    
            Logic_Collection_Event_Instance::completeScheduledInstancesFromUI($aParameters);
        }
		catch (Exception $e)
		{
			// Do nothing
		}
		
		return	array(
					'bSuccess' 					=> true,
					'aCompletedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getCompletedEventInstances(true),
					'aFailedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getFailedEventInstances(true),
					'aExceptions' 				=> Logic_Collection_BatchProcess_Report::getExceptions(true)
				);
	}
		
	public function  invokeChargeEvent($aEventInstanceIds)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aParameters = array();
            foreach ($aEventInstanceIds as $iEventInstanceId)
            {
                $aParameters[$iEventInstanceId] = array();
            }

            Logic_Collection_Event_Instance::completeScheduledInstancesFromUI($aParameters);
		}
		catch (Exception $e)
		{
			// Do nothing
		}
		
		return	array(
					'bSuccess' 					=> true,
					'aCompletedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getCompletedEventInstances(true),
					'aFailedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getFailedEventInstances(true),
					'aExceptions' 				=> Logic_Collection_BatchProcess_Report::getExceptions(true)
				);
	}
	
	public function invokeCorrespondenceEvent($aEventInstanceIds)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aParameters = array();
            foreach ($aEventInstanceIds as $iEventInstanceId)
            {
                $aParameters[$iEventInstanceId] = array();
            }

            Logic_Collection_Event_Instance::completeScheduledInstancesFromUI($aParameters);
		}
		catch (Exception $e)
		{
			// Do nothing
		}
		
		return	array(
					'bSuccess' 					=> true,
					'aCompletedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getCompletedEventInstances(true),
					'aFailedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getFailedEventInstances(true),
					'aExceptions' 				=> Logic_Collection_BatchProcess_Report::getExceptions(true)
				);
	}
	
	public function invokeReportEvent($aEventInstanceIds)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aParameters = array();
            foreach ($aEventInstanceIds as $iEventInstanceId)
            {
                $aParameters[$iEventInstanceId] = array();
            }

            Logic_Collection_Event_Instance::completeScheduledInstancesFromUI($aParameters);
		}
		catch (Exception $e)
		{
			// Do nothing
		}
		
		return	array(
					'bSuccess' 					=> true,
					'aCompletedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getCompletedEventInstances(true),
					'aFailedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getFailedEventInstances(true),
					'aExceptions' 				=> Logic_Collection_BatchProcess_Report::getExceptions(true)
				);
	}
	
	public function invokeMilestoneEvent($aEventInstanceIds)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aParameters = array();
            foreach ($aEventInstanceIds as $iEventInstanceId)
            {
                $aParameters[$iEventInstanceId] = array();
            }

            Logic_Collection_Event_Instance::completeScheduledInstancesFromUI($aParameters);
		}
		catch (Exception $e)
		{
			// Do nothing
		}
		
		return	array(
					'bSuccess' 					=> true,
					'aCompletedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getCompletedEventInstances(true),
					'aFailedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getFailedEventInstances(true),
					'aExceptions' 				=> Logic_Collection_BatchProcess_Report::getExceptions(true)
				);
	}
	
	public function invokeSeverityEvent($aEventInstanceIds)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aParameters = array();
            foreach ($aEventInstanceIds as $iEventInstanceId)
            {
                 $aParameters[$iEventInstanceId] = array();
            }

            Logic_Collection_Event_Instance::completeScheduledInstancesFromUI($aParameters);
		}
		catch (Exception $e)
		{
			// Do nothing
		}
		
		return	array(
					'bSuccess' 					=> true,
					'aCompletedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getCompletedEventInstances(true),
					'aFailedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getFailedEventInstances(true),
					'aExceptions' 				=> Logic_Collection_BatchProcess_Report::getExceptions(true)
				);
	}
	
	public function invokeBarringEvent($aEventInstanceIds)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aParameters = array();
	        foreach ($aEventInstanceIds as $iEventInstanceId)
	        {
	            $aParameters[$iEventInstanceId] = array();
	        }
	
	        Logic_Collection_Event_Instance::completeScheduledInstancesFromUI($aParameters);
		}
		catch (Exception $e)
		{
			// Do nothing
		}
		
		return	array(
					'bSuccess' 					=> true,
					'aCompletedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getCompletedEventInstances(true),
					'aFailedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getFailedEventInstances(true),
					'aExceptions' 				=> Logic_Collection_BatchProcess_Report::getExceptions(true)
				);
	}
	
	public function invokeTDCEvent($aEventInstanceIds)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aParameters = array();
	        foreach ($aEventInstanceIds as $iEventInstanceId)
	        {
	            $aParameters[$iEventInstanceId] = array();
	        }
	
	        Logic_Collection_Event_Instance::completeScheduledInstancesFromUI($aParameters);
		}
		catch (Exception $e)
		{
			// Do nothing
		}
		
		return	array(
					'bSuccess' 					=> true,
					'aCompletedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getCompletedEventInstances(true),
					'aFailedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getFailedEventInstances(true),
					'aExceptions' 				=> Logic_Collection_BatchProcess_Report::getExceptions(true)
				);
	}
	
	public function invokeOCAEvent($aEventInstanceIds)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aParameters = array();
            foreach ($aEventInstanceIds as $iEventInstanceId)
            {
                $aParameters[$iEventInstanceId] = array();
            }

            Logic_Collection_Event_Instance::completeScheduledInstancesFromUI($aParameters);
		}
		catch (Exception $e)
		{
			// Do nothing
		}
		
		return	array(
					'bSuccess' 					=> true,
					'aCompletedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getCompletedEventInstances(true),
					'aFailedEventInstances' 	=> Logic_Collection_BatchProcess_Report::getFailedEventInstances(true),
					'aExceptions' 				=> Logic_Collection_BatchProcess_Report::getExceptions(true)
				);
	}
	
	public function getAvailableActionTypes()
	{
		$bGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aActionTypes 	= Action_Type::getAll();
			$aResults		= array();
			foreach ($aActionTypes as $oActionType)
			{
				// Check that there is an account association
				$aAssociationTypes 		= $oActionType->getAllowableActionAssociationTypes();
				$bHasAccountAssociation	= false;
				foreach ($aAssociationTypes as $oAssociation)
				{
					if ($oAssociation->id == ACTION_ASSOCIATION_TYPE_ACCOUNT)
					{
						$bHasAccountAssociation = true;
					}
				}
				
				if (($oActionType->active_status_id == ACTIVE_STATUS_ACTIVE) && !$oActionType->is_automatic_only && $bHasAccountAssociation)
				{
					// The Action Type is Active, is NOT automatic only and has an Account association... ALLOW IT!
					$aResults[$oActionType->id] = $oActionType->toStdClass();
				}
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							'bSuccess'		=> true,
							'aActionTypes'	=> $aResults
						);
		}
		catch (Exception $e)
		{
			return array(
							'bSuccess'	=> false,
							'sMessage'	=> ($bGod ? $e->getMessage() : 'There was an error accessing the database, please contact YBS for assistance.')
						);
		}
	}
	
	public function createEvent($oDetails)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Validation
			// ... Standard fields (collection_event)
			if ($oDetails->name === '')
			{
				$aErrors[] = 'No name was supplied';
			}
			
			if (strlen($oDetails->name) > 256)
			{
				$aErrors[] = 'Name is too long (maximum 256 chars)';
			}
			
			if ($oDetails->description === '')
			{
				$aErrors[] = 'No description was supplied';
			}
			
			if (strlen($oDetails->description) > 256)
			{
				$aErrors[] = 'Description is too long (maximum 256 chars)';
			}
			
			if ($oDetails->collection_event_type_id === null)
			{
				$aErrors[] = 'No event type was supplied';
			}
			else
			{
				// ... Detail fields (collection_event_?)
				$oEventType = Collection_Event_Type::getForId($oDetails->collection_event_type_id);
				switch ($oEventType->collection_event_type_implementation_id)
				{
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_CORRESPONDENCE:
						if ($oDetails->implementation_details->correspondence_template_id === null)
						{
							$aErrors[] = 'No correspondence template was supplied';
						}
						break;
						
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_REPORT:
						if ($oDetails->implementation_details->report_sql === '')
						{
							$aErrors[] = 'No report sql was supplied';
						}
						else
						{
							// Check for valid query
							$sSingleLine = preg_replace('/\n/', ' ', $oDetails->implementation_details->report_sql);
							if (!preg_match('/^SELECT(.|\n)*FROM(.|\n)*WHERE(.|\n)*(IN\s+\(\s?\<ACCOUNTS\>\s?\))/i', $sSingleLine))
							{
								$aErrors[] = 'Invalid SQL Query. Must be valid SQL and contain \'IN (<ACCOUNTS>)\' which is a place holder for Account Ids that are inserted at execution time.';
							}
						}
						
						if ($oDetails->implementation_details->email_notification_id === null)
						{
							$aErrors[] = 'No email notification was supplied';
						}
						
						if ($oDetails->implementation_details->collection_event_report_output_id === null)
						{
							$aErrors[] = 'No report output type was supplied';
						}
						break;
					
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_ACTION:
						if ($oDetails->implementation_details->action_type_id === null)
						{
							$aErrors[] = 'No action type was supplied';
						}
						break;
						
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_SEVERITY:
						if ($oDetails->implementation_details->collection_severity_id === null)
						{
							$aErrors[] = 'No severity was supplied';
						}
						break;
						
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_OCA:
						if ($oDetails->implementation_details->legal_fee_charge_type_id === null)
						{
							$aErrors[] = 'No charge type (legal fee) was supplied';
						}
						break;
						
					case COLLECTION_EVENT_TYPE_IMPLEMENTATION_CHARGE:
						if ($oDetails->implementation_details->charge_type_id === null)
						{
							$aErrors[] = 'No charge type was supplied';
						}
						
						if ($oDetails->implementation_details->allow_recharge === null)
						{
							$aErrors[] = 'No allow recharge value was supplied';
						}
						break;
				}
			}
			
			if (count($aErrors) > 0)
			{
				return 	array(
							'bSuccess'	=> false,
							'aErrors'	=> $aErrors
						);
			}
			
			// Save
			$oEvent 								= new Collection_Event();
			$oEvent->name 							= $oDetails->name;
			$oEvent->description 					= $oDetails->description;
			$oEvent->collection_event_type_id 		= $oDetails->collection_event_type_id;
			$oEvent->collection_event_invocation_id = $oDetails->collection_event_invocation_id;
			$oEvent->status_id 						= STATUS_ACTIVE;
			$oEvent->save();
			
			switch ($oEventType->collection_event_type_implementation_id)
			{
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_CORRESPONDENCE:
					$oCollectionEventCorrespondence 							= new Collection_Event_Correspondence();
					$oCollectionEventCorrespondence->collection_event_id 		= $oEvent->id;
					$oCollectionEventCorrespondence->correspondence_template_id	= (int)$oDetails->implementation_details->correspondence_template_id;
					if ($oDetails->implementation_details->document_template_type_id !== null && $oDetails->implementation_details->document_template_type_id != '')
					{
						$oCollectionEventCorrespondence->document_template_type_id = (int)$oDetails->implementation_details->document_template_type_id;
					}
					$oCollectionEventCorrespondence->save();
					break;
					
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_REPORT:
					$oCollectionEventReport 									= new Collection_Event_Report();
					$oCollectionEventReport->collection_event_id 				= $oEvent->id;
					$oCollectionEventReport->report_sql 						= $oDetails->implementation_details->report_sql;
					$oCollectionEventReport->email_notification_id 				= (int)$oDetails->implementation_details->email_notification_id;
					$oCollectionEventReport->collection_event_report_output_id 	= (int)$oDetails->implementation_details->collection_event_report_output_id;
					$oCollectionEventReport->save();
					break;
				
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_ACTION:
					$oCollectionEventAction							= new Collection_Event_Action();
					$oCollectionEventAction->collection_event_id 	= $oEvent->id;
					$oCollectionEventAction->action_type_id 		= (int)$oDetails->implementation_details->action_type_id;
					$oCollectionEventAction->save();
					break;
					
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_SEVERITY:
					$oCollectionEventSeverity							= new Collection_Event_Severity();
					$oCollectionEventSeverity->collection_event_id 		= $oEvent->id;
					$oCollectionEventSeverity->collection_severity_id 	= (int)$oDetails->implementation_details->collection_severity_id;
					$oCollectionEventSeverity->save();
					break;
					
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_OCA:
					$oCollectionEventOCA							= new Collection_Event_OCA();
					$oCollectionEventOCA->collection_event_id 		= $oEvent->id;
					$oCollectionEventOCA->legal_fee_charge_type_id	= (int)$oDetails->implementation_details->legal_fee_charge_type_id;
					$oCollectionEventOCA->save();
					break;
					
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_CHARGE:
					$oCollectionEventCharge							= new Collection_Event_Charge();
					$oCollectionEventCharge->collection_event_id 	= $oEvent->id;
					$oCollectionEventCharge->charge_type_id 		= (int)$oDetails->implementation_details->charge_type_id;
					
					if ($oDetails->implementation_details->flat_fee !== '')
					{
						// Flat fee, set the minimum_amount
						$oCollectionEventCharge->minimum_amount = (float)$oDetails->implementation_details->flat_fee;
					}
					else if ($oDetails->implementation_details->percentage_outstanding_debt !== '')
					{
						// Percentage of outstanding debt, set percentage_outstanding_debt and other fields if given
						$oCollectionEventCharge->percentage_outstanding_debt = ((float)$oDetails->implementation_details->percentage_outstanding_debt) / 100;
										
						if ($oDetails->implementation_details->minimum_amount !== '')
						{
							// Flat fee, set the minimum_amount
							$oCollectionEventCharge->minimum_amount = (float)$oDetails->implementation_details->minimum_amount;
						}
						
						if ($oDetails->implementation_details->maximum_amount !== '')
						{
							// Flat fee, set the minimum_amount
							$oCollectionEventCharge->maximum_amount = (float)$oDetails->implementation_details->maximum_amount;
						}
					}
					
					$oCollectionEventCharge->allow_recharge	= (int)$oDetails->implementation_details->allow_recharge;
					$oCollectionEventCharge->save();
					break;
					
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_EXIT_COLLECTIONS:
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_BARRING:
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_TDC:
					// No extra configuration required
					break;
			}
			
			return	array(
						'bSuccess'	=> true,
						'iEventId'	=> $oEvent->id
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
	
	public function getAll($bActiveOnly=false, $bScenarioEventsOnly=false)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aEvents 	= Collection_Event::getAll();
			$aResults 	= array();
			foreach ($aEvents as $oEvent)
			{
				// Check if active
				if (!$bActiveOnly || ($oEvent->isActive()))
				{
					// Check if the implementation is a scenario event implementation
					$oStdEvent 			= self::_getStdClassEvent($oEvent);
					$oImplementation	= $oStdEvent->collection_event_type->collection_event_type_implementation;
					if (!$bScenarioEventsOnly || $oImplementation->is_scenario_event)
					{
						$aResults[$oEvent->id] = $oStdEvent;
					}
				}
			}
			
			return	array(
						'bSuccess'	=> true,
						'aEvents'	=> $aResults
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
	
	public function setStatus($iEventId, $iStatusId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oEvent 			= Collection_Event::getForId($iEventId);
			$oEvent->status_id	= $iStatusId;
			$oEvent->save();
			
			return	array(
						'bSuccess'	=> true
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
	
	public function getForId($iEventId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			return	array(
						'bSuccess'	=> true,
						'oEvent'	=> self::_getStdClassEvent(Collection_Event::getForId($iEventId))
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
	
	public function getCorrespondenceTemplates()
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Only system, sql_accounts and NOT the invoice template
			$oQuery		= new Query();
			$mResult	= $oQuery->Execute("SELECT	ct.*, cs.correspondence_source_type_id
											FROM	correspondence_template ct
											JOIN	correspondence_source cs ON (cs.id = ct.correspondence_source_id)
											WHERE	cs.correspondence_source_type_id IN (".CORRESPONDENCE_SOURCE_TYPE_SYSTEM.", ".CORRESPONDENCE_SOURCE_TYPE_SQL_ACCOUNTS.")
											AND		ct.name <> 'Invoice';");
			if ($mResult === false)
			{
				throw new Exception("Failed to get correspondence templates for collections. ".$oQuery->Error());
			}
			
			$aResults = array();
			while ($aRow = $mResult->fetch_assoc())
			{
				$aResults[$aRow['id']] = $aRow;
			}
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						'bSuccess'	=> true,
						'aResults'	=> $aResults 
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
	
	public function getEventsForType($iEventType)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aEvents	= Collection_Event::getForType($iEventType);
			$aResults	= array();
			foreach ($aEvents as $oEvent)
			{
				$aResults[$oEvent->id] = $oEvent->toStdClass();
			}
			
			return	array(
						'bSuccess'	=> true,
						'aEvents'	=> $aResults
					);
		}
		catch (Exception $oException)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function getEventDetails($iEventId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oEvent 	= Collection_Event::getForId($iEventId);
			$oEventType	= Collection_Event_Type::getForId($oEvent->collection_event_type_id);
			
			switch ($oEventType->collection_event_type_implementation_id)
			{
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_CORRESPONDENCE:
					$oDetails = Collection_Event_Correspondence::getForCollectionEventId($iEventId)->toStdClass();
					
					// correspondence_template_id
					$oDetails->correspondence_template = Correspondence_Template::getForId($oDetails->correspondence_template_id)->toStdClass();
					
					// document_template_type_id
					if ($oDetails->document_template_type_id !== null)
					{
						$oQuery		= new Query();
						$mResult	= $oQuery->Execute("SELECT 	*
														FROM 	DocumentTemplateType
														WHERE	Id = {$oDetails->document_template_type_id}");
						if ($mResult === false)
						{
							throw new Exception("Failed to get DocumentTemplateType record for event {$iEventId}. ".$oQuery->Error());
						}
						$oDetails->document_template_type = $mResult->fetch_assoc();
					}
					break;
					
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_REPORT:
					$oDetails = Collection_Event_Report::getForCollectionEventId($iEventId)->toStdClass();
					
					// email_notification_id
					$oQuery		= new Query();
					$mResult	= $oQuery->Execute("SELECT 	*
													FROM 	email_notification
													WHERE	id = {$oDetails->email_notification_id}");
					if ($mResult === false)
					{
						throw new Exception("Failed to get email_notification record for event {$iEventId}. ".$oQuery->Error());
					}
					$oDetails->email_notification = $mResult->fetch_assoc();
					
					// collection_event_report_output_id
					$aCollectionEventReportOutput 				= $GLOBALS['*arrConstant']['collection_event_report_output'];
					$oDetails->collection_event_report_output 	= $aCollectionEventReportOutput[$oDetails->collection_event_report_output_id];
					break;
				
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_ACTION:
					$oDetails 				= Collection_Event_Action::getForCollectionEventId($iEventId)->toStdClass();
					$oDetails->action_type 	= Action_Type::getForId($oDetails->action_type_id)->toStdClass();
					break;
					
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_SEVERITY:
					$oDetails 						= Collection_Event_Severity::getForCollectionEventId($iEventId)->toStdClass();
					$oDetails->collection_severity	= Collection_Severity::getForId($oDetails->collection_severity_id)->toStdClass();
					break;
					
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_OCA:
					$oDetails 							= Collection_Event_OCA::getForCollectionEventId($iEventId)->toStdClass();
					$oDetails->legal_fee_charge_type	= Charge_Type::getForId($oDetails->legal_fee_charge_type_id)->toStdClass();
					break;
					
				case COLLECTION_EVENT_TYPE_IMPLEMENTATION_CHARGE:
					$oDetails 				= Collection_Event_Charge::getForCollectionEventId($iEventId)->toStdClass();
					$oDetails->charge_type	= Charge_Type::getForId($oDetails->charge_type_id)->toStdClass();
					break;
				
				default:
					$oDetails = new StdClass();
			}
			
			$oDetails->collection_event_type_implementation_id = $oEventType->collection_event_type_implementation_id;
			
			return array('bSuccess' => true, 'oDetails' => $oDetails);
		}
		catch (Exception $oException)
		{
			$sMessage = $bUserIsGod ? $oException->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	private static function _getStdClassEvent($oEvent)
	{
		$oStdEvent 			= $oEvent->toStdClass();
		$oStdEventType 		= Collection_Event_Type::getForId($oStdEvent->collection_event_type_id)->toStdClass();
		$oStdImplementation	= Collection_Event_Type_Implementation::getForId($oStdEventType->collection_event_type_implementation_id)->toStdClass();
		
		$oStdEvent->collection_event_type 										= $oStdEventType;
		$oStdEvent->collection_event_type->collection_event_type_implementation	= $oStdImplementation;
		
		return $oStdEvent;
	}
}

class JSON_Handler_Collection_Event_Exception extends Exception
{
	// No changes
}

?>