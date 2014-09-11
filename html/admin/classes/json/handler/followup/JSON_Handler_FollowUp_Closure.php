<?php

class JSON_Handler_FollowUp_Closure extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getDataSet($bCountOnly=false, $iLimit=0, $iOffset=0, $oSort=null, $oFilter=null)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Closure_Exception('You do not have permission to view Follow-Up Closures.');
			}
			
			$aSort		= $oSort !== null ? get_object_vars($oSort) : array();
			$aFilter	= $oFilter !== null ? get_object_vars($oFilter) : array();
			
			if ($bCountOnly)
			{
				// Count Only
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> FollowUp_Closure::searchFor(null, null, $aSort, $aFilter, true)
						);
			}
			else
			{
				$iLimit			= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset		= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aCategories	= FollowUp_Closure::searchFor($iLimit, $iOffset, $aSort, $aFilter);
				$aResults		= array();
				$iCount			= 0;		
				foreach ($aCategories as $oClosure)
				{
					// Add to Result Set
					$aResults[$iCount+$iOffset]	= $oClosure->toStdClass();
					$iCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResults,
							"iRecordCount"	=> FollowUp_Closure::searchFor(null, null, $aSort, $aFilter, true)
						);
			}
		}
		catch (JSON_Handler_FollowUp_Closure_Exception $oException)
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
	
	public function getForId($iClosureId)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Closure_Exception('You do not have permission to view Follow-Up Closures.');
			}
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						"Success"	=> true,
						"oRecord"	=> FollowUp_Closure::getForId($iClosureId)->toStdClass()
					);
		}
		catch (JSON_Handler_FollowUp_Closure_Exception $oException)
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
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error getting the closure details'
					);
		}
	}
	
	public function deactivate($iClosureId)
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
				throw new JSON_Handler_FollowUp_Closure_Exception('You do not have permission to deactivate Follow-Up Closures.');
			}
			
			// Change status and save
			$oClosure				= FollowUp_Closure::getForId($iClosureId);
			$oClosure->status_id	= STATUS_INACTIVE;
			$oClosure->save();
			
			$oDataAccess->TransactionCommit();
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						"Success"	=> true
					);
		}
		catch (JSON_Handler_FollowUp_Closure_Exception $oException)
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
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error deactivating the closure'
					);
		}
	}
	
	public function save($iClosureId, $oDetails)
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
				throw new JSON_Handler_FollowUp_Closure_Exception('You do not have permission to view Follow-Up Closures.');
			}
			
			if ($iClosureId)
			{
				$oClosure	= FollowUp_Closure::getForId($iClosureId);
			}
			else
			{
				$oClosure	= new FollowUp_Closure();
			}
			
			// Validate input
			$iNameLength	= 128;
			$iDescLength	= 256;
			$aErrors		= array();
			if (is_null($oDetails->name) || strlen($oDetails->name) == 0)
			{
				$aErrors[]	= 'Name missing.';
			}
			else if (strlen($oDetails->description) > $iNameLength)
			{
				$aErrors[]	= "Name is too long, maximum {$iNameLength} characters.";
			}
			
			if (is_null($oDetails->description) || strlen($oDetails->description) == 0)
			{
				$aErrors[]	= 'Description missing.';
			}
			else if (strlen($oDetails->description) > $iDescLength)
			{
				$aErrors[]	= "Description is too long, maximum {$iDescLength} characters.";
			}
			
			if (($oDetails->status_id != STATUS_ACTIVE) && ($oDetails->status_id != STATUS_INACTIVE))
			{
				$aErrors[]	= 'Invalid status';
			}
			
			if (($oDetails->followup_closure_type_id != FOLLOWUP_CLOSURE_TYPE_COMPLETED) && ($oDetails->followup_closure_type_id != FOLLOWUP_CLOSURE_TYPE_DISMISSED))
			{
				$aErrors[]	= 'Invalid closure reason type';
			}
			
			$oClosure->name						= $oDetails->name;
			$oClosure->description				= $oDetails->description;
			$oClosure->status_id				= $oDetails->status_id;
			$oClosure->followup_closure_type_id	= $oDetails->followup_closure_type_id;
			$oClosure->save();
			
			$oDataAccess->TransactionCommit();
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						"Success"	=> true,
						"iRecordId"	=> $oClosure->id
					);
		}
		catch (JSON_Handler_FollowUp_Closure_Exception $oException)
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
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error getting the closure details'
					);
		}
	}
}

class JSON_Handler_FollowUp_Closure_Exception extends Exception
{
	// No changes
}

?>