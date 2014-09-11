<?php

class JSON_Handler_FollowUp_Recurring extends JSON_Handler implements JSON_Handler_Loggable
{
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
			$aSort		= get_object_vars($oSort);
			$aFilter	= get_object_vars($oFilter);
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
							"iRecordCount"	=> FollowUp_Recurring::searchFor(null, null, $aSort, $aFilter, true)
						);
			}
			else
			{
				$iLimit					= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset				= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aFollowUpRecurrings	= FollowUp_Recurring::searchFor($iLimit, $iOffset, $aSort, $aFilter, false, true);
				$aResults				= array();
				$iCount					= 0;
				foreach ($aFollowUpRecurrings as $oFollowUpStdClass)
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
				
					$iCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResults,
							"iRecordCount"	=> FollowUp_Recurring::searchFor(null, null, $aSort, $aFilter, true)
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
			$oFollowUpRecurring->end_datetime	= DataAccess::getDataAccess()->getNow();
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
	
	public function getOverdueOccurrences($iFollowUpRecurringId)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Recurring_Exception('You do not have permission to get Recurring Follow-Up information.');
			}
			
			$oFollowUpRecurring	= FollowUp_Recurring::getForId($iFollowUpRecurringId);
			$aOccurrenceDetails	= $oFollowUpRecurring->getOccurrenceDetails(false, true);
			$aResult			= array();
			$i					= 0;
			
			foreach ($aOccurrenceDetails['aOccurrences'] as $aOccurrence)
			{
				if (is_null($aOccurrence['sClosedDatetime']))
				{
					$aResult[$i]	= $aOccurrence;
				}
				
				$i++;
			}
			
			return	array(
						"Success"		=> true,
						"aOccurrences"	=> $aResult,
						"iCount"		=> count($aResult)
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
			$aNextDueDate		= $oFollowUpRecurring->getNextDueDateInformation();
			$iNextDueDate		= strtotime($aNextDueDate["sDueDateTime"]);
			$bOverdue			= $iNextDueDate < time();
			
			return	array(
						"Success"		=> true,
						"sDueDateTime"	=> $aNextDueDate["sDueDateTime"],
						"iIteration"	=> $aNextDueDate["iIteration"],
						"bOverdue"		=> $bOverdue,
						"bNoMore"		=> $aNextDueDate["bNoMore"]
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
	
	public function getFollowUpDetails($iFollowUpId, $iRecurringIteration=false)
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
			
			if (is_numeric($iRecurringIteration))
			{
				$sDueDateTime	= date('Y-m-d H:i:s', $oFollowUpRecurring->getProjectedDueDateSeconds($iRecurringIteration));
				$oFollowUp		= FollowUp::getForDateAndRecurringId($sDueDateTime, $oFollowUpRecurring->id);
				
				if ($oFollowUp)
				{
					// There is a closed occurrences for that iteration, return it
					$oStdClass	= $oFollowUp->toStdClass();
					
					if ($oFollowUp->isClosed())
					{
						// Add the closure object because it is closed
						$oStdClass->followup_closure	= FollowUp_Closure::getForId($oStdClass->followup_closure_id)->toStdClass();
					}
				}
				else
				{
					// There is no closed occurrences for that iteration, return a simulated followup
					$oStdClass							= new StdClass();
					$oStdClass->followup_type_id		= $oFollowUpRecurring->followup_type_id;
					$oStdClass->followup_category_id	= $oFollowUpRecurring->followup_category_id;
					$oStdClass->followup_closure_id		= null;
					$oStdClass->closed_datetime			= null;
					$oStdClass->due_datetime			= $sDueDateTime;
				}
				
				$oStdClass->status	=	FollowUp::getStatus(
											$oStdClass->followup_closure_id, 
											$oStdClass->due_datetime
										);
			}
			else
			{
				$oStdClass	= $oFollowUpRecurring->toStdClass();
			}
			
			return	array(
						"Success"	=> true,
						"oFollowUp"	=> $oStdClass,
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
	
	public function getOccurrences($iFollowUpRecurringId)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Exception('You do not have permission to view Recurring Follow-Up occurrences.');
			}
			
			$oFollowUpRecurring	= FollowUp_Recurring::getForId($iFollowUpRecurringId);
			$aOccurrences		= $oFollowUpRecurring->getOccurrenceDetails(false, false);
			
			return	array(
						"Success"	=> true,
						"aDetails"	=> $aOccurrences
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