<?php

class JSON_Handler_Collection_Severity extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$iRecordCount	= Logic_Collection_Severity::searchFor(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly)
			{
				return	array(
							'bSuccess'		=> true,
							'iRecordCount'	=> $iRecordCount
						);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aTypes 	= Logic_Collection_Severity::searchFor(false, $iLimit, $iOffset, $oSort, $oFilter);
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
	
	public function createSeverity($oDetails)
	{
		$bUserIsGod		= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess	= DataAccess::getDataAccess();
		try
		{
			if (!$oDataAccess->TransactionStart())
			{
				throw new Exception("Failed to start transaction.");
			}
			
			// Validation
			$aErrors = array();
			if ($oDetails->name === '')
			{
				$aErrors[] = 'No Name was supplied';
			}
			
			if (strlen($oDetails->name) > 256)
			{
				$aErrors[] = 'Name is too long (maximum 256 chars)';
			}
			
			if ($oDetails->description === '')
			{
				$aErrors[] = 'No Description was supplied';
			}
			
			if (strlen($oDetails->description) > 256)
			{
				$aErrors[] = 'Description is too long (maximum 256 chars)';
			}
			
			if ($oDetails->severity_level === '')
			{
				$aErrors[] = 'No Severity Level was supplied';
			}
			
			if (Collection_Severity::isSeverityLevelTaken($oDetails->severity_level, $oDetails->id))
			{
				$aErrors[] = 'The Severity Level is already applied to another Severity';
			}
			
			if (count($aErrors) > 0)
			{
				return 	array(
							'bSuccess'	=> false,
							'aErrors'	=> $aErrors
						);
			}
			
			// Create collection_severity
			if ($oDetails->id !== null)
			{
				$oSeverity = Collection_Severity::getForId($oDetails->id);
				
				// Clear all warning and restriction linkage records
				Collection_Severity_Warning::removeForSeverityId($oSeverity->id);
				Collection_Severity_Restriction::removeForSeverityId($oSeverity->id);
			}
			else
			{
				$oSeverity = new Collection_Severity();
			}
			
			// Save
			$oSeverity->name 		 		= $oDetails->name;
			$oSeverity->description			= $oDetails->description;
			$oSeverity->working_status_id 	= $oDetails->working_status_id;
			$oSeverity->severity_level		= $oDetails->severity_level;
			$oSeverity->save();
			
			// Link to warnings
			foreach ($oDetails->collection_warning_ids as $iWarningId)
			{
				$oSeverityWarning 							= new Collection_Severity_Warning();
				$oSeverityWarning->collection_severity_id 	= $oSeverity->id;
				$oSeverityWarning->collection_warning_id	= $iWarningId;
				$oSeverityWarning->save();
			}
			
			// Link to restrictions
			foreach ($oDetails->collection_restriction_ids as $iRestrictionId)
			{
				$oSeverityRestriction 								= new Collection_Severity_Restriction();
				$oSeverityRestriction->collection_severity_id 		= $oSeverity->id;
				$oSeverityRestriction->collection_restriction_id	= $iRestrictionId;
				$oSeverityRestriction->save();
			}
			
			if (!$oDataAccess->TransactionCommit())
			{
				throw new Exception("Failed to start transaction.");
			}
			
			return	array(
						'bSuccess'		=> true,
						'iSeverityId'	=> $oSeverity->id
					);
		}
		catch (Exception $e)
		{
			$oDataAccess->TransactionRollback();
			$sMessage	= $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function setStatus($iSeverityId, $iWorkingStatusId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oSeverity						= Collection_Severity::getForId($iSeverityId);
			$oSeverity->working_status_id	= $iWorkingStatusId;
			$oSeverity->save();
			
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
	
	public function getAll($bActiveOnly=false, $bSelectableOnly=false, $aMustIncludeIds=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			if ($bActiveOnly)
			{
				$aWorkingStatuses = array(WORKING_STATUS_ACTIVE);
			}
			else if ($bSelectableOnly)
			{
				$aWorkingStatuses = array(WORKING_STATUS_ACTIVE, WORKING_STATUS_INACTIVE);
			}
			else
			{
				$aWorkingStatuses = array(WORKING_STATUS_ACTIVE, WORKING_STATUS_INACTIVE, WORKING_STATUS_DRAFT);
			}
			
			// Add all given the status limits
			$aSeverities	= Collection_Severity::getForWorkingStatus($aWorkingStatuses);
			$aResults		= array();
			foreach ($aSeverities as $oSeverity)
			{
				$aResults[$oSeverity->id] = $oSeverity->toStdClass();
			}
			
			// Add the 'must-include' records
			if ($aMustIncludeIds !== null)
			{
				foreach ($aMustIncludeIds as $iId)
				{
					if (!isset($aResults[$iId]))
					{
						$oSeverity = Collection_Severity::getForId($iId);
						if ($oSeverity->id)
						{
							$aResults[$iId] = $oSeverity->toStdClass();
						}
					}
				}
			}
			
			return	array(
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
	
	public function isSeverityLevelTaken($iSeverityLevel, $iIgnoreBySeverityId=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			return	array(
						'bSuccess'	=> true,
						'bTaken'	=> Collection_Severity::isSeverityLevelTaken($iSeverityLevel, $iIgnoreBySeverityId)
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
	
	public function getExtendedDetailsForId($iSeverityId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oSeverity 	= Collection_Severity::getForId($iSeverityId);
			$oStdClass	= $oSeverity->toStdClass();
			
			$aWarningIds 		= array();
			$aSeverityWarnings 	= Collection_Severity_Warning::getForSeverityId($iSeverityId);
			foreach ($aSeverityWarnings as $oSeverityWarning)
			{
				$aWarningIds[] = $oSeverityWarning->collection_warning_id;
			}
			
			$aRestrictionIds = array();
			$aSeverityRestrictions 	= Collection_Severity_Restriction::getForSeverityId($iSeverityId);
			foreach ($aSeverityRestrictions as $oSeverityRestriction)
			{
				$aRestrictionIds[] = $oSeverityRestriction->collection_restriction_id;
			}
			
			$oStdClass->collection_warning_ids 		= $aWarningIds;
			$oStdClass->collection_restriction_ids 	= $aRestrictionIds;
			
			return	array(
						'bSuccess'	=> true,
						'oSeverity'	=> $oStdClass
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

class JSON_Handler_Collection_Severity_Exception extends Exception
{
	// No changes
}

?>