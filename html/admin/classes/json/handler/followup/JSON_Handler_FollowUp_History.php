<?php

class JSON_Handler_FollowUp_History extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getForFollowUp($iFollowUpId)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_History_Exception('You do not have permission to view Follow-Up History.');
			}
			
			$aHistoryRecords	= FollowUp_History::getForFollowUpId($iFollowUpId);
			$aResult			= array();
			foreach ($aHistoryRecords as $oRecord)
			{
				// Convert to std class
				$oStdClassRecord							= $oRecord->toStdClass();
				$oStdClassRecord->aModifyReasons			= array();
				$oStdClassRecord->assigned_employee_name	= Employee::getForId($oStdClassRecord->assigned_employee_id)->getName(); 
				$oStdClassRecord->modified_employee_name	= Employee::getForId($oStdClassRecord->modified_employee_id)->getName();
				
				// Get modify reasons
				$aModifyReasons	= $oRecord->getModifyReasons();
				foreach ($aModifyReasons as $iHistoryReasonId => $oReason)
				{
					$oStdClassRecord->aModifyReasons[$iHistoryReasonId]	= $oReason->toStdClass();
				}
				
				// Get reassign reason
				$oReassignReason	= $oRecord->getReassignReason();
				if ($oReassignReason)
				{
					$oStdClassRecord->oReassignReason	= $oReassignReason->toStdClass();
				}
				
				// Store
				$aResult[$oRecord->id]				= $oStdClassRecord;  
			}
			
			return 	array(
						"Success"	=> true,
						"aResults"	=> $aResult
					);
		}
		catch (JSON_Handler_FollowUp_History_Exception $oException)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error getting the dataset'
					);
		}
	}
}

class JSON_Handler_FollowUp_History_Exception extends Exception
{
	// No changes
}

?>