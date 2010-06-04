<?php

class JSON_Handler_FollowUp extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getDataSet($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Exception('You do not have permission to view Follow-Ups.');
			}
			
			$aFilter	= get_object_vars($oFilter);
			
			// Convert the 'status' filter value to valid filters  
			if (isset($aFilter['status']))
			{
				$mStatus	= $aFilter['status']; 
				if (is_numeric($mStatus))
				{
					$aFilter['followup_closure_type_id']	= (int)$mStatus;
				}
				else
				{
					$oNewDueDateTimeConstraint	= false;
					switch ($mStatus)
					{
						case 'ACTIVE':
							$aFilter['followup_closure_id']			= 'NULL';
							break;
						case 'CURRENT':
							$aFilter['followup_closure_id']			= 'NULL';
							$oNewDueDateTimeConstraint				= new StdClass();
							$oNewDueDateTimeConstraint->mFrom		= date('Y-m-d H:i:s');
							break;
						case 'OVERDUE':
							$aFilter['followup_closure_id']			= 'NULL';
							$oNewDueDateTimeConstraint				= new StdClass();
							$oNewDueDateTimeConstraint->mTo			= date('Y-m-d H:i:s');
							break;
						case 'COMPLETED':
							$aFilter['followup_closure_type_id']	= FOLLOWUP_CLOSURE_TYPE_COMPLETED;
							break;
						case 'DISMISSED':
							$aFilter['followup_closure_type_id']	= FOLLOWUP_CLOSURE_TYPE_DISMISSED;
							break;
					}
					
					// Add a new due date time constraint if needed
					if ($oNewDueDateTimeConstraint)
					{
						if (isset($aFilter['due_datetime']))
						{
							// Existing due_datetime constraint, turn it into an 'AND' array constraint
							$aFilter['due_datetime']	= array($aFilter['due_datetime'], $oNewDueDateTimeConstraint);
						}
						else
						{
							// No exising, add the new one as the only due_datetime 
							$aFilter['due_datetime']	= $oNewDueDateTimeConstraint;
						}
					}
				}
				
				// Remove the status filter as it is not a valid followup field alias
				unset($aFilter['status']);
			}
			
			if ($bCountOnly)
			{
				// Count Only
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> FollowUp::searchFor(null, null, get_object_vars($oFieldsToSort), $aFilter, true)
						);
			}
			else
			{
				$iLimit		= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset	= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aFollowUps	= FollowUp::searchFor($iLimit, $iOffset, get_object_vars($oFieldsToSort), $aFilter);
				$aResults	= array();
				$iCount		= 0;		
				foreach ($aFollowUps as $aFollowUp)
				{
					if ($iLimit && $iCount >= $iOffset+$iLimit)
					{
						// Break out, as there's no point in continuing
						break;
					}
					elseif ($iCount >= $iOffset)
					{
						// Create ORM object
						$oFollowUp			= new FollowUp($aFollowUp);
						$oFollowUpStdClass	= $oFollowUp->toStdClass();
						
						// Add special 'followup_id' field (from temporary table 'followup_search')
						$oFollowUpStdClass->followup_id	= $aFollowUp['followup_id'];
						
						// Add other special fields
						$oFollowUpStdClass->followup_closure_type_id		= $aFollowUp['followup_closure_type_id'];
						$oFollowUpStdClass->followup_recurring_iteration	= $aFollowUp['followup_recurring_iteration'];
						$oFollowUpStdClass->assigned_employee_label			= Employee::getForId($oFollowUp->assigned_employee_id)->getName();
						$oFollowUpStdClass->followup_category_label			= FollowUp_Category::getForId($oFollowUp->followup_category_id)->name;
						$oFollowUpStdClass->status							= FollowUp::getStatus($oFollowUp->followup_closure_id, $oFollowUp->due_datetime);
						
						if ($oFollowUp->followup_recurring_id)
						{
							// Get the followup_recurring orm object to get the details
							$oFollowUpRecurring			= FollowUp_Recurring::getForId($oFollowUp->followup_recurring_id);
							$oFollowUpStdClass->details	= $oFollowUpRecurring->getDetails();
							$oFollowUpStdClass->summary	= $oFollowUpRecurring->getSummary();
						}
						else
						{
							// Get the actual followup orm object to get details
							$oFollowUpTemp				= FollowUp::getForId($oFollowUpStdClass->followup_id);
							$oFollowUpStdClass->details	= $oFollowUpTemp->getDetails();
							$oFollowUpStdClass->summary	= $oFollowUpTemp->getSummary();
						}
						
						// Add to Result Set
						$aResults[$iCount+$iOffset]	= $oFollowUpStdClass;
					} 
					
					$iCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResults,
							"iRecordCount"	=> $iCount
						);
			}
		}
		catch (JSON_Handler_FollowUp_Exception $oException)
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
	
	public function closeFollowUp($iFollowUpId, $iFollowUpClosureId)
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
			// Validate the closure id
			if (FollowUp_Closure::getForId($iFollowUpClosureId))
			{
				$oFollowUp						= FollowUp::getForId($iFollowUpId);
				$oFollowUp->followup_closure_id	= $iFollowUpClosureId;
				$oFollowUp->closed_datetime		= date('Y-m-d H:i:s');
				$oFollowUp->save();
			}
			else
			{
				throw new JSON_Handler_FollowUp_Exception('Could not close the Follow-Up. Invalid closure reason given.');
			}
			
			// Commit db transaction
			$oDataAccess->TransactionCommit();
			
			return array("Success" => true);
		}
		catch (JSON_Handler_FollowUp_Exception $oException)
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod ? $e->getMessage() : 'There was an error closing the follow-up'
					);
		}
	}
	
	public function closeRecurringFollowUpIteration($iFollowUpRecurringId, $iFollowUpClosureId, $iIteration)
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
			// Validate the closure id
			if (FollowUp_Closure::getForId($iFollowUpClosureId))
			{
				// Get the recurring followup (parent of the iteration)
				$oFollowUpRecurring	= FollowUp_Recurring::getForId($iFollowUpRecurringId);
				
				// Create a once off (closed) followup for the iteration
				$oFollowUp	= new FollowUp();
				
				// Default values
				$sNowDateTime						= date('Y-m-d H:i:s');
				
				// Inherit fields from the recurring followup
				$oFollowUp->assigned_employee_id	= $oFollowUpRecurring->assigned_employee_id;
				$oFollowUp->created_datetime		= $oFollowUpRecurring->created_datetime;
				$oFollowUp->due_datetime			= date('Y-m-d H:i:s', $oFollowUpRecurring->getProjectedDueDate($iIteration));
				$oFollowUp->followup_type_id		= $oFollowUpRecurring->followup_type_id;
				$oFollowUp->followup_category_id	= $oFollowUpRecurring->followup_category_id;
				
				// Closure specific values
				$oFollowUp->followup_closure_id		= $iFollowUpClosureId;
				$oFollowUp->closed_datetime			= $sNowDateTime;
				$oFollowUp->followup_recurring_id	= $oFollowUpRecurring->id;
				
				// Save new record
				$oFollowUp->save();
				
				// Commit db transaction
				$oDataAccess->TransactionCommit();
				
				return array("Success" => true);
			}
			else
			{
				throw new JSON_Handler_FollowUp_Exception('Could not close the Follow-Up. Invalid closure reason given.');
			}
		}
		catch (JSON_Handler_FollowUp_Exception $oException)
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod ? $e->getMessage() : 'There was an error closing the follow-up'
					);
		}
	}
	
	public function reassignFollowUp($iFollowUpId, $iToEmployeeId)
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
			// Update assigned_employee_id
			$oFollowUp							= FollowUp::getForId($iFollowUpId);
			$oFollowUp->assigned_employee_id	= $iToEmployeeId;
			$oFollowUp->save();
			
			// Commit db transaction
			$oDataAccess->TransactionCommit();
			
			return array("Success" => true);
		}
		catch (JSON_Handler_FollowUp_Exception $oException)
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod ? $e->getMessage() : 'There was an error updating the follow-up'
					);
		}
	}
	
	public function reassignRecurringFollowUp($iFollowUpRecurringId, $iToEmployeeId)
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
			// Update assigned_employee_id
			$oFollowUpRecurring							= FollowUp_Recurring::getForId($iFollowUpRecurringId);
			$oFollowUpRecurring->assigned_employee_id	= $iToEmployeeId;
			$oFollowUpRecurring->save();
			
			// Commit db transaction
			$oDataAccess->TransactionCommit();
			
			return array("Success" => true);
		}
		catch (JSON_Handler_FollowUp_Exception $oException)
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod ? $e->getMessage() : 'There was an error updating the recurring follow-up'
					);
		}
	}
	
	public function updateFollowUpDueDate($iFollowUpId, $sDueDateTime)
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
			// Update assigned_employee_id
			$oFollowUp					= FollowUp::getForId($iFollowUpId);
			$oFollowUp->due_datetime	= $sDueDateTime;
			$oFollowUp->save();
			
			// Commit db transaction
			$oDataAccess->TransactionCommit();
			
			return array("Success" => true, 'sDueDateTime' => $sDueDateTime);
		}
		catch (Exception $e)
		{
			// Rollback db transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod ? $e->getMessage() : 'There was an error updating the recurring follow-up'
					);
		}
	}
}

class JSON_Handler_FollowUp_Exception extends Exception
{
	// No changes
}

?>