<?php

class JSON_Handler_FollowUp_Recurring_History extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getForRecurringFollowUp($iFollowUpRecurringId)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Recurring_History_Exception('You do not have permission to view Follow-Up History.');
			}
			
			$aHistoryRecords	= FollowUp_Recurring_History::getForFollowUpRecurring($iFollowUpRecurringId);
			$aResult			= array();
			foreach ($aHistoryRecords as $oRecord)
			{
				$aResult[$oRecord->id]	= $oRecord->toStdClass();
			}
			
			return 	array(
						"Success"	=> true,
						"aResults"	=> $aResult
					);
		}
		catch (JSON_Handler_FollowUp_Recurring_History_Exception $oException)
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
	/*
	public function getForId($iId)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Recurring_History_Exception('You do not have permission to view Follow-Up History.');
			}
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						"Success"	=> true,
						"oRecord"	=> FollowUp_Recurring_History::getForId($iId)->toStdClass()
					);
		}
		catch (JSON_Handler_FollowUp_Recurring_History_Exception $oException)
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
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error getting the history details'
					);
		}
	}
	*/
}

class JSON_Handler_FollowUp_Recurring_History_Exception extends Exception
{
	// No changes
}

?>