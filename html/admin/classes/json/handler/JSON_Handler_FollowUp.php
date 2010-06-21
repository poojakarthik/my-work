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
	
	public function getDataSetForAuthenticatedEmployee($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null, $iSummaryCharacterLimit=null)
	{
		if (is_null($oFilter))
		{
			$oFilter	= new StdClass();
		}
		
		$oFilter->assigned_employee_id	= Flex::getUserId();
		return self::getDataSet($bCountOnly, $iLimit, $iOffset, $oFieldsToSort, $oFilter, $iSummaryCharacterLimit);
	}
	
	public function getDataSet($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null, $iSummaryCharacterLimit=30)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Exception('You do not have permission to view Follow-Ups.');
			}
			
			$aFilter	= get_object_vars($oFilter);
			
			// Retrieve and remove the 'now' filter value. It's only purpose is to tell this method what the client time is
			$iNowSeconds	= time();
			if (isset($aFilter['now']))
			{
				$iNowSeconds	= (int)$aFilter['now'];
				unset($aFilter['now']);
			}	
			
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
							$oNewDueDateTimeConstraint->mFrom		= date('Y-m-d H:i:s', $iNowSeconds);
							break;
						case 'OVERDUE':
							$aFilter['followup_closure_id']			= 'NULL';
							$oNewDueDateTimeConstraint				= new StdClass();
							$oNewDueDateTimeConstraint->mTo			= date('Y-m-d H:i:s', $iNowSeconds);
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
					$oFollowUpStdClass->status							= FollowUp::getStatus($oFollowUp->followup_closure_id, $oFollowUp->due_datetime, $iNowSeconds);
					
					if ($oFollowUp->followup_recurring_id)
					{
						// Get the followup_recurring orm object to get the details
						$oFollowUpRecurring			= FollowUp_Recurring::getForId($oFollowUp->followup_recurring_id);
						$oFollowUpStdClass->details	= $oFollowUpRecurring->getDetails();
						$oFollowUpStdClass->summary	= $oFollowUpRecurring->getSummary($iSummaryCharacterLimit);
					}
					else
					{
						// Get the actual followup orm object to get details
						$oFollowUpTemp				= FollowUp::getForId($oFollowUpStdClass->followup_id);
						$oFollowUpStdClass->details	= $oFollowUpTemp->getDetails();
						$oFollowUpStdClass->summary	= $oFollowUpTemp->getSummary($iSummaryCharacterLimit);
					}
					
					// Add to Result Set
					$aResults[$iCount+$iOffset]	= $oFollowUpStdClass;
					$iCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResults,
							"iRecordCount"	=> FollowUp::searchFor(null, null, get_object_vars($oFieldsToSort), $aFilter, true)
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
				$oFollowUp->due_datetime			= date('Y-m-d H:i:s', $oFollowUpRecurring->getProjectedDueDateSeconds($iIteration));
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
	
	public function reassignFollowUp($iFollowUpId, $iToEmployeeId, $iReassignReasonId)
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
			$oFollowUp->save(null, $iReassignReasonId);
			
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
	
	public function reassignRecurringFollowUp($iFollowUpRecurringId, $iToEmployeeId, $iReassignReasonId)
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
			$oFollowUpRecurring->save(null, $iReassignReasonId);
			
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
	
	public function updateFollowUpDueDate($iFollowUpId, $sDueDateTime, $iModifyReasonId)
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
			$oFollowUp->save($iModifyReasonId);
			
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
	
	public function getOverdueCountForLoggedInEmployee($iNowSeconds)
	{
		try
		{
			$oEmployee	= Employee::getForId(Flex::getUserId());
			$iCount		= $oEmployee->getOverdueFollowUpCount($iNowSeconds);
			
			return 	array(
						"Success"	=> true,
						"iCount"	=> $iCount
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod ? $e->getMessage() : 'There was an error updating the recurring follow-up'
					);
		}
	}
	
	public function getFollowUpContextDetails($iFollowUpTypeId, $iContextId)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Exception('You do not have permission to view Follow-Ups.');
			}
			
			$aDetails	= array();
			$oAccount	= null;
			$oService	= null;
			$oContact	= null;
			switch ($iFollowUpTypeId)
			{
				case FOLLOWUP_TYPE_NOTE:
					// Context is note, get account,service and contact details
					$oNote	= Note::getForId($iContextId);
					if ($oNote)
					{
						if ($oNote->Account)
						{
							$oAccount	= Account::getForId($oNote->Account);
						}
						
						if ($oNote->Service)
						{
							$oService	= Service::getForId($oNote->Service);
						}
						
						if ($oNote->Contact)
						{
							$oContact	= Contact::getForId($oNote->Contact);
						}
					}
					break;
				case FOLLOWUP_TYPE_ACTION:
					// Context is action, get account,service and contact details
					$oAction	= Action::getForId($iContextId);
					if ($oAction)
					{
						$aAccounts	= $oAction->getAssociatedAccounts();
						foreach ($aAccounts as $iAccountId => $oAssocAccount)
						{
							$oAccount	= $oAssocAccount;
						}
						
						$aServices	= $oAction->getAssociatedServices();
						foreach ($aServices as $iServiceId => $oAssocService)
						{
							$oService	= $oAssocService;
						}
						
						$aContacts	= $oAction->getAssociatedContacts();
						foreach ($aContacts as $iContactId => $oAssocContact)
						{
							$oContact	= $oAssocContact;
						}
					}
					break;
				case FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
					// Context is ticket, get account and ticketing contact details
					$oTicketingCorrespondence	= Ticketing_Correspondance::getForId($iContextId);
					if ($oTicketingCorrespondence)
					{
						$oTicket	= $oTicketingCorrespondence->getTicket();
						if ($oTicket)
						{
							$aDetails['ticket_id']	= $oTicket->id;
							
							if ($oTicket->account_id)
							{
								$oAccount	= Account::getForId($oTicket->account_id);
							}
						}
						
						$oTicketContact	= $oTicketingCorrespondence->getContact();
						if ($oTicketContact)
						{
							$aDetails['ticket_contact_name']	= $oTicketContact->getName();
						}
					}
					break;
			}
			
			if ($oAccount)
			{
				$aDetails['account_id']		= $oAccount->Id;
				$aDetails['account_name']	= $oAccount->BusinessName;
				$aDetails['customer_group']	= $oAccount->getCustomerGroup()->internalName;
			}
			
			if ($oService)
			{
				$aDetails['service_id']		= $iServiceId;
				$aDetails['service_fnn']	= $oService->FNN;
			}
			
			if ($oContact)
			{
				$aDetails['contact_id']		= $iContactId;
				$aDetails['contact_name']	= $oContact->FirstName.' '.$oContact->LastName;
			}
			
			return	array(
						"Success"	=> true,
						"aDetails"	=> $aDetails
					);
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod ? $e->getMessage() : 'There was an error getting the follow-up context details'
					);
		}
	}
	
	public function createNew($iType, $iTypeDetail, $oDetails)
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
				throw new JSON_Handler_FollowUp_Exception('You do not have permission to create Follow-Ups.');
			}
			
			$bIsOnceOff	= isset($oDetails->sDueDateTime);
			$aErrors	= array();
			
			// Validate input
			if (!is_numeric($oDetails->iCategory))
			{
				$aErrors[]	= 'Invalid Category.';
			}
			
			if ($bIsOnceOff)
			{
				// Once off
				if (!is_numeric(strtotime($oDetails->sDueDateTime)))
				{
					$aErrors[]	= 'Invalid Due Date.';
				}
			}
			else
			{
				// Recurring
				if (!is_numeric(strtotime($oDetails->sStartDateTime)))
				{
					$aErrors[]	= 'Invalid Start Date.';
				}
				
				if (!is_null($oDetails->sEndDateTime) && !is_numeric(strtotime($oDetails->sEndDateTime)))
				{
					$aErrors[]	= 'Invalid End Date.';
				}
				
				if (!is_numeric($oDetails->iRecurrenceMultiplier))
				{
					$aErrors[]	= 'Invalid Recurrence Multiplier.';
				}
				
				if (!is_numeric($oDetails->iRecurrencePeriod))
				{
					$aErrors[]	= 'Invalid Recurrence Period.';
				}
			}
			
			// Validate the type detail
			switch ($iType)
			{
				case FOLLOWUP_TYPE_NOTE:
					if (!Note::getForId($iTypeDetail))
					{
						$aErrors[]	= 'Invalid Note identifier supplied.';
					}
					break;
				case FOLLOWUP_TYPE_ACTION:
					if (!Action::getForId($iTypeDetail))
					{
						$aErrors[]	= 'Invalid Action identifier supplied.';
					}
					break;
				case FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
					if (!Ticketing_Correspondance::getForId($iTypeDetail))
					{
						$aErrors[]	= 'Invalid Ticketing Correspondance identifier supplied.';
					}
					break;
			}
			
			if (count($aErrors) > 0)
			{
				// Return validation errors
				return 	array(
							"Success"			=> false,
							"aValidationErrors"	=> $aErrors
						);
			}
			else
			{
				// Create fup/recurring fup
				if ($bIsOnceOff)
				{
					// Once off/single follow-up
					$oFollowUp							= new FollowUp();
					$oFollowUp->assigned_employee_id	= Flex::getUserId();
					$oFollowUp->created_datetime		= date('Y-m-d H:i:s');
					$oFollowUp->due_datetime			= $oDetails->sDueDateTime;
					$oFollowUp->followup_type_id		= $iType;
					$oFollowUp->followup_category_id	= $oDetails->iCategory;
					$oFollowUp->save();
					
					// Create link to type detail
					switch ($iType)
					{
						case FOLLOWUP_TYPE_NOTE:
							$oFollowUpNote				= new FollowUp_Note();
							$oFollowUpNote->followup_id	= $oFollowUp->id;
							$oFollowUpNote->note_id		= $iTypeDetail;
							$oFollowUpNote->save();
							break;
						case FOLLOWUP_TYPE_ACTION:
							$oFollowUpAction				= new FollowUp_Action();
							$oFollowUpAction->followup_id	= $oFollowUp->id;
							$oFollowUpAction->action_id		= $iTypeDetail;
							$oFollowUpAction->save();
							break;
						case FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
							$oFollowUpTicketingCorrespondence								= new FollowUp_Ticketing_Correspondence();
							$oFollowUpTicketingCorrespondence->followup_id					= $oFollowUp->id;
							$oFollowUpTicketingCorrespondence->ticketing_correspondence_id	= $iTypeDetail;
							$oFollowUpTicketingCorrespondence->save();
							break;
					}
				}
				else
				{
					// Recurring followup
					$oFollowUpRecurring									= new FollowUp_Recurring();
					$oFollowUpRecurring->assigned_employee_id			= Flex::getUserId();
					$oFollowUpRecurring->created_datetime				= date('Y-m-d H:i:s');
					$oFollowUpRecurring->start_datetime					= $oDetails->sStartDateTime;
					
					if (is_null($oDetails->sEndDateTime))
					{
						// No end date given, set to 9999-12-31 23:59:59 (the end of time)
						$oFollowUpRecurring->end_datetime	= END_OF_TIME;
					}
					else
					{
						// Use end date given
						$oFollowUpRecurring->end_datetime	= $oDetails->sEndDateTime;
					}
					
					$oFollowUpRecurring->followup_type_id				= $iType;
					$oFollowUpRecurring->followup_category_id			= $oDetails->iCategory;
					$oFollowUpRecurring->recurrence_multiplier			= $oDetails->iRecurrenceMultiplier;
					$oFollowUpRecurring->followup_recurrence_period_id	= $oDetails->iRecurrencePeriod;
					$oFollowUpRecurring->save();
					
					// Create link to type detail
					switch ($iType)
					{
						case FOLLOWUP_TYPE_NOTE:
							$oFollowUpRecurringNote							= new FollowUp_Recurring_Note();
							$oFollowUpRecurringNote->followup_recurring_id	= $oFollowUpRecurring->id;
							$oFollowUpRecurringNote->note_id				= $iTypeDetail;
							$oFollowUpRecurringNote->save();
							break;
						case FOLLOWUP_TYPE_ACTION:
							$oFollowUpRecurringAction							= new FollowUp_Recurring_Action();
							$oFollowUpRecurringAction->followup_recurring_id	= $oFollowUpRecurring->id;
							$oFollowUpRecurringAction->action_id				= $iTypeDetail;
							$oFollowUpRecurringAction->save();
							break;
						case FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
							$oFollowUpRecurringTicketingCorrespondence								= new FollowUp_Recurring_Ticketing_Correspondence();
							$oFollowUpRecurringTicketingCorrespondence->followup_recurring_id		= $oFollowUpRecurring->id;
							$oFollowUpRecurringTicketingCorrespondence->ticketing_correspondence_id	= $iTypeDetail;
							$oFollowUpRecurringTicketingCorrespondence->save();
							break;
					}
				}
				
				// Commit db transaction
				$oDataAccess->TransactionCommit();
				
				return	array("Success"	=> true);
			}
		}
		catch (JSON_Handler_FollowUp_Exception $oException)
		{
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod ? $e->getMessage() : 'There was an error creating the new follow-up'
					);
		}
	}
	
	public function getFollowUpsFromContext($iFollowUpType, $iTypeDetail)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Exception('You do not have permission to retrieve Follow-Ups.');
			}
			
			$aData	= $this->_getFollowUpsFromContext($iFollowUpType, $iTypeDetail);
			
			return	array(
						"Success"				=> true,
						"aFollowUps"			=> $aData['aFollowUps'],
						"aFollowUpRecurrings"	=> $aData['aFollowUpRecurrings']
					);
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod ? $e->getMessage() : 'There was an error creating the new follow-up'
					);
		}
	}
	
	public function getFollowUpsFromMultipleContexts($aFollowUpContexts)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Exception('You do not have permission to retrieve Follow-Ups.');
			}
			
			$aResults	= array();
			
			foreach ($aFollowUpContexts as $oContext)
			{
				$aResults[$oContext->iType][$oContext->iTypeDetail]	=	$this->_getFollowUpsFromContext(
																			$oContext->iType, 
																			$oContext->iTypeDetail
																		);
			}
			
			return	array(
						"Success"	=> true,
						"aResults"	=> $aResults
					);
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod ? $e->getMessage() : 'There was an error creating the new follow-up'
					);
		}
	}
	
	private function _getFollowUpsFromContext($iFollowUpType, $iTypeDetail)
	{
		switch ($iFollowUpType)
		{
			case FOLLOWUP_TYPE_NOTE:
				$aFollowUps				= FollowUp_Note::getFollowUpsForNote($iTypeDetail);
				$aFollowUpRecurrings	= FollowUp_Recurring_Note::getFollowUpRecurringsForNote($iTypeDetail);
				break;
			case FOLLOWUP_TYPE_ACTION:
				$aFollowUps				= FollowUp_Action::getFollowUpsForAction($iTypeDetail);
				$aFollowUpRecurrings	= FollowUp_Recurring_Action::getFollowUpRecurringsForAction($iTypeDetail);
				break;
			case FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
				$aFollowUps				= FollowUp_Ticketing_Correspondence::getFollowUpsForCorrespondence($iTypeDetail);
				$aFollowUpRecurrings	= FollowUp_Recurring_Ticketing_Correspondence::getFollowUpRecurringsForCorrespondence($iTypeDetail);
				break;
		}
		
		// Convert result to StdClasses and return
		$aStdClassFollowUps				= array();
		$aStdClassFollowUpRecurrings	= array();
		$iLoggedInEmployee				= Flex::getUserId();
		
		if (isset($aFollowUps))
		{
			foreach ($aFollowUps as $oFollowUp)
			{
				// Only return the followup if it is assigned to the current employee
				if ($oFollowUp->assigned_employee_id == $iLoggedInEmployee)
				{
					$aStdClassFollowUps[]	= $oFollowUp->toStdClass();
				}
			}
		}
		
		if (isset($aFollowUpRecurrings))
		{
			foreach ($aFollowUpRecurrings as $oFollowUpRecurring)
			{
				// Only return the followup if it is assigned to the current employee
				if ($oFollowUpRecurring->assigned_employee_id == $iLoggedInEmployee)
				{
					$aStdClassFollowUpRecurrings[]	= $oFollowUpRecurring->toStdClass();
				}
			}
		}
		
		return	array(
					'aFollowUps'			=> $aStdClassFollowUps,
					'aFollowUpRecurrings'	=> $aStdClassFollowUpRecurrings
				);
	}
}

class JSON_Handler_FollowUp_Exception extends Exception
{
	// No changes
}

?>