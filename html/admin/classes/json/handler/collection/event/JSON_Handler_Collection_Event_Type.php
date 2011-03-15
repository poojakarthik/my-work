<?php

class JSON_Handler_Collection_Event_Type extends JSON_Handler
{
	protected	$_JSONDebug	= '';

	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
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
			
			$iRecordCount	= Logic_Collection_Event_Type::searchFor(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly)
			{
				return	array(
							'bSuccess'		=> true,
							'iRecordCount'	=> $iRecordCount
						);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aTypes 	= Logic_Collection_Event_Type::searchFor(false, $iLimit, $iOffset, $oSort, $oFilter);
			$aResults	= array();
			$i			= $iOffset;
			foreach ($aTypes as $aEventType)
			{
				$aResults[$i] = $aEventType;
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
	
	public function getAllEventTypeImplementations()
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aImplementations 	= Collection_Event_Type_Implementation::getAll();
			$aResults 			= array();
			foreach ($aImplementations as $oImplementation)
			{
				$aResults[$oImplementation->id] = $oImplementation->toStdClass();
			}
			
			return	array(
						'bSuccess'					=> true,
						'aEventTypeImplementations'	=> $aResults,
						'sDebug'					=> ($bUserIsGod ? $this->_JSONDebug : '')
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
	
	public function getAll($bActiveOnly=false)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aTypes 	= Collection_Event_Type::getAll();
			$aResults 	= array();
			foreach ($aTypes as $oType)
			{
				if (!$bActiveOnly || ($oType->isActive()))
				{
					$oStdType 										= $oType->toStdClass();
					$oStdType->collection_event_type_implementation	= Collection_Event_Type_Implementation::getForId($oStdType->collection_event_type_implementation_id)->toStdClass();
					$aResults[$oType->id] 							= $oStdType;
				}
			}
			
			return	array(
						'bSuccess'		=> true,
						'aEventTypes'	=> $aResults,
						'sDebug'		=> ($bUserIsGod ? $this->_JSONDebug : '')
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
	
	public function createEventType($oDetails)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Validation
			$aErrors = array();
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
			
			if ($oDetails->collection_event_type_implementation_id === null)
			{
				$aErrors[] = 'No implementation was supplied';
			}
			
			if (count($aErrors) > 0)
			{
				return 	array(
							'bSuccess'	=> false,
							'aErrors'	=> $aErrors
						);
			}
			
			// Save
			$oEventType 											= new Collection_Event_Type();
			$oEventType->name 										= $oDetails->name;
			$oEventType->description 								= $oDetails->description;
			$oEventType->collection_event_type_implementation_id 	= $oDetails->collection_event_type_implementation_id;
			$oEventType->collection_event_invocation_id 			= $oDetails->collection_event_invocation_id;
			$oEventType->status_id 									= STATUS_ACTIVE;
			$oEventType->save();
			
			return	array(
						'bSuccess'		=> true,
						'iEventTypeId'	=> $oEventType->id,
						'sDebug'		=> ($bUserIsGod ? $this->_JSONDebug : '')
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
	
	public function setStatus($iEventTypeId, $iStatusId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oEventType 			= Collection_Event_Type::getForId($iEventTypeId);
			$oEventType->status_id	= $iStatusId;
			$oEventType->save();
			
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

class JSON_Handler_Collection_Event_Type_Exception extends Exception
{
	// No changes
}

?>