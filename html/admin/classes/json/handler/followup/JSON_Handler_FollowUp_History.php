<?php

class JSON_Handler_FollowUp_History extends JSON_Handler implements JSON_Handler_Loggable
{
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
			$iCount				= 0;
			foreach ($aHistoryRecords as $oRecord)
			{
				// Convert to std class
				$oStdClassRecord							= $oRecord->toStdClass();
				$oStdClassRecord->aModifyReasons			= array();
				$oStdClassRecord->assigned_employee_name	= Employee::getForId($oStdClassRecord->assigned_employee_id)->getName(); 
				$oStdClassRecord->modified_employee_name	= Employee::getForId($oStdClassRecord->modified_employee_id)->getName();
				
				// Get modify reasons
				$bHaveAReason	= false;
				$aModifyReasons	= $oRecord->getModifyReasons();
				foreach ($aModifyReasons as $iHistoryReasonId => $oReason)
				{
					$bHaveAReason	= true;
					$oStdClassRecord->aModifyReasons[$iHistoryReasonId]	= $oReason->toStdClass();
				}
				
				// Get reassign reason
				$oReassignReason	= $oRecord->getReassignReason();
				if ($oReassignReason)
				{
					$bHaveAReason	= true;
					$oStdClassRecord->oReassignReason	= $oReassignReason->toStdClass();
				}
				
				// Check, no reason found & (this is not the first record OR there is only one record and it is the first)
				if (!$bHaveAReason && (($iCount > 0) || (count($aHistoryRecords) == 1)))
				{
					// Closed, get the followups closure reason
					$oFollowUp	= FollowUp::getForId($iFollowUpId);
					$oClosure	= FollowUp_Closure::getForId($oFollowUp->followup_closure_id);
					if ($oClosure->id)
					{
						$oStdClassRecord->oFollowUpClosure	= $oClosure->toStdClass();
					}
				}
				
				// Store
				$aResult[$oRecord->id]	= $oStdClassRecord; 
				$iCount++;
			}
			
			return 	array(
						"Success"			=> true,
						"aHistoryDetails"	=> $aResult
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