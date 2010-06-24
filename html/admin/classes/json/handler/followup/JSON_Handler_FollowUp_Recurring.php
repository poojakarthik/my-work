<?php

class JSON_Handler_FollowUp_Recurring extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getDataSet($bCountOnly=false, $iLimit=0, $iOffset=0, $oSort=null, $oFilter=null)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Recurring_Exception('You do not have permission to view Recurring Follow-Ups.');
			}
			
			// Check for special sorting fields and convert them to actual fields
			$aSort	= get_object_vars($oSort);
			if (isset($aSort['recurrence_period']))
			{
				// Recurrence period (e.g. 3 Weeks, 1 Month)
				// Sort by days: 7 (week) or 28 (month) times the recurrence multiplier for each. 
				$aSort[
					'(
						CASE 
							WHEN	followup_recurrence_period_id = '.FOLLOWUP_RECURRENCE_PERIOD_WEEK.' 
							THEN 	(7 * recurrence_multiplier) 
							ELSE 	(28 * recurrence_multiplier) 
						END
					)'
				] = $aSort['recurrence_period'];
				
				// Also by period if there's a matching number of days (week=1, month=2)
				$aSort['followup_recurrence_period_id']	= $aSort['recurrence_period'];
				
				// Remove the invalid field
				unset($aSort['recurrence_period']);
			}
			
			if ($bCountOnly)
			{
				// Count Only
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> FollowUp_Recurring::searchFor(null, null, $aSort, get_object_vars($oFilter), true)
						);
			}
			else
			{
				$iLimit					= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset				= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aFollowUpRecurrings	= FollowUp_Recurring::searchFor($iLimit, $iOffset, $aSort, get_object_vars($oFilter), false, true);
				$aResults				= array();
				$iCount					= 0;		
				foreach ($aFollowUpRecurrings as $oFollowUpStdClass)
				{
					if ($iLimit && $iCount >= $iOffset+$iLimit)
					{
						// Break out, as there's no point in continuing
						break;
					}
					elseif ($iCount >= $iOffset)
					{
						// Add other special fields
						$oFollowUpStdClass->assigned_employee_label			= Employee::getForId($oFollowUpStdClass->assigned_employee_id)->getName();
						$oFollowUpStdClass->followup_category_label			= FollowUp_Category::getForId($oFollowUpStdClass->followup_category_id)->name;
						
						// Get the followup_recurring orm object to get the details
						$oFollowUpRecurring	= FollowUp_Recurring::getForId($oFollowUpStdClass->id);
						$oFollowUpStdClass->details	= $oFollowUpRecurring->getDetails();
						$oFollowUpStdClass->summary	= $oFollowUpRecurring->getSummary();
						
						// Add to Result Set
						$aResults[$iCount+$iOffset]	= $oFollowUpStdClass;
					} 
					
					$iCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResults,
							"iRecordCount"	=> FollowUp_Recurring::searchFor(null, null, get_object_vars($oSort), get_object_vars($oFilter), true)
						);
			}
		}
		catch (JSON_Handler_FollowUp_Recurring_Exception $oException)
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : 'There was an error getting the dataset'
					);
		}
	}
	
	public function endNow($iFollowUpRecurringId, $iModifyReasonId)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? 'There was an error accessing the database' : ''
					);
		}
		
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Recurring_Exception('You do not have permission to edit Recurring Follow-Ups.');
			}
			
			$oFollowUpRecurring					= FollowUp_Recurring::getForId($iFollowUpRecurringId);
			$oFollowUpRecurring->end_datetime	= date('Y-m-d H:i:s');
			$oFollowUpRecurring->save($iModifyReasonId);
			
			// Commit db transaction
			$oDataAccess->TransactionCommit();
			
			return array("Success" => true);
		}
		catch (JSON_Handler_FollowUp_Recurring_Exception $oException)
		{
			// Rollback db transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			// Rollback db transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : 'There was an error ending the follow-up.'
					);
		}
	}
	
	public function updateEndDate($iFollowUpRecurringId, $sEndDate, $iModifyReasonId)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? 'There was an error accessing the database' : ''
					);
		}
		
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Recurring_Exception('You do not have permission to edit Recurring Follow-Ups.');
			}
			
			$oFollowUpRecurring					= FollowUp_Recurring::getForId($iFollowUpRecurringId);
			$oFollowUpRecurring->end_datetime	= $sEndDate;
			$oFollowUpRecurring->save($iModifyReasonId);
			
			// Commit db transaction
			$oDataAccess->TransactionCommit();
			
			return array("Success" => true);
		}
		catch (JSON_Handler_FollowUp_Recurring_Exception $oException)
		{
			// Rollback db transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			// Rollback db transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : 'There was an error updating the end date'
					);
		}
	}
	
	public function getNextDueDate($iFollowUpRecurringId)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Recurring_Exception('You do not have permission to view Recurring Follow-Ups.');
			}
			
			$oFollowUpRecurring	= FollowUp_Recurring::getForId($iFollowUpRecurringId);
			$iProjectedDate		= strtotime($oFollowUpRecurring->start_datetime);
			$iEndDate			= strtotime($oRecurringFollowUp->end_datetime);
			$sDueDateTime		= date('Y-m-d H:i:s', $iProjectedDate);
			$iNow				= time();
			$i					= 0;
			while((($iProjectedDate <= $iNow) && ($iProjectedDate <= $iEndDate)) || 
				FollowUp::getForDateAndRecurringId($sDueDateTime, $iFollowUpRecurringId))
			{
				$i++;
				$iProjectedDate	= $oFollowUpRecurring->getProjectedDueDateSeconds($i);
				$sDueDateTime	= date('Y-m-d H:i:s', $iProjectedDate);
			}
			
			return	array(
						"Success"		=> true,
						"sDueDateTime"	=> $sDueDateTime
					);
		}
		catch (JSON_Handler_FollowUp_Recurring_Exception $oException)
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : 'There was an error getting the dataset'
					);
		}
	}
	
	public function getFollowUpDetails($iFollowUpId)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Exception('You do not have permission to view Recurring Follow-Ups.');
			}
			
			$oFollowUpRecurring		= FollowUp_Recurring::getForId($iFollowUpId);
			$aDetails				= $oFollowUpRecurring->getDetails();
			$aDetails['sContent']	= $oFollowUpRecurring->getSummary(null, false);
			
			return	array(
						"Success"	=> true,
						"oFollowUp"	=> $oFollowUpRecurring->toStdClass(),
						"aDetails"	=> $aDetails
					);
		}
		catch (JSON_Handler_FollowUp_Recurring_Exception $oException)
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod ? $e->getMessage() : 'There was an error getting the follow-up context details'
					);
		}
	}
	
	public function getOccurrences($iFollowUpRecurringId, $iNowSeconds=false)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Exception('You do not have permission to view Recurring Follow-Up occurrences.');
			}
			
			$oFollowUpRecurring	= FollowUp_Recurring::getForId($iFollowUpRecurringId);
			$aOccurrences		= $oFollowUpRecurring->getOccurrenceDetails(false, false, $iNowSeconds);
			return	array(
						"Success"		=> true,
						"aOccurrences"	=> $aOccurrences
					);
		}
		catch (JSON_Handler_FollowUp_Recurring_Exception $oException)
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod ? $e->getMessage() : 'There was an error getting the follow-up occurrences'
					);
		}
	}
}

class JSON_Handler_FollowUp_Recurring_Exception extends Exception
{
	// No changes
}

?>