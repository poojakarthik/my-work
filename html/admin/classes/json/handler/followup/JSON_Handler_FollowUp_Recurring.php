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
			
			if ($bCountOnly)
			{
				// Count Only
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> FollowUp_Recurring::searchFor(null, null, get_object_vars($oSort), get_object_vars($oFilter), true)
						);
			}
			else
			{
				$iLimit					= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset				= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aFollowUpRecurrings	= FollowUp_Recurring::searchFor($iLimit, $iOffset, get_object_vars($oSort), get_object_vars($oFilter));
				$aResults				= array();
				$iCount					= 0;		
				foreach ($aFollowUpRecurrings as $oFollowUpRecurring)
				{
					if ($iLimit && $iCount >= $iOffset+$iLimit)
					{
						// Break out, as there's no point in continuing
						break;
					}
					elseif ($iCount >= $iOffset)
					{
						// Create ORM object
						$oFollowUpStdClass	= $oFollowUpRecurring->toStdClass();
						
						// Add other special fields
						$oFollowUpStdClass->assigned_employee_label			= Employee::getForId($oFollowUpRecurring->assigned_employee_id)->getName();
						$oFollowUpStdClass->followup_category_label			= FollowUp_Category::getForId($oFollowUpRecurring->followup_category_id)->name;
						
						// Get the followup_recurring orm object to get the details
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
	
	public function updateEndDate($iFollowUpRecurringId, $sEndDate)
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
			$oFollowUpRecurring->save();
			
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
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : 'There was an error getting the dataset'
					);
		}
	}
}

class JSON_Handler_FollowUp_Recurring_Exception extends Exception
{
	// No changes
}

?>