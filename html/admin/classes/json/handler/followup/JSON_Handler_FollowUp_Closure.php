<?php

class JSON_Handler_FollowUp_Closure extends JSON_Handler
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
		// This dataset ajax method does not support sorting
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Category_Exception('You do not have permission to view Follow-Up Closures.');
			}
			
			$aSort		= get_object_vars($oSort);
			$aFilter	= get_object_vars($oFilter);
			
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
				foreach ($aCategories as $oCategory)
				{
					// Add to Result Set
					$aResults[$iCount+$iOffset]	= $oCategory->toStdClass();
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
						"Success"		=> true,
						"oClosure"		=> FollowUp_Closure::getForId($iClosureId)->toStdClass()
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
			if (is_null($oDetails->sName) || strlen($oDetails->sName) == 0)
			{
				$aErrors[]	= 'Name missing.';
			}
			else if (strlen($oDetails->sDescription) > $iNameLength)
			{
				$aErrors[]	= "Name is too long, maximum {$iNameLength} characters.";
			}
			
			if (is_null($oDetails->sDescription) || strlen($oDetails->sDescription) == 0)
			{
				$aErrors[]	= 'Description missing.';
			}
			else if (strlen($oDetails->sDescription) > $iDescLength)
			{
				$aErrors[]	= "Description is too long, maximum {$iDescLength} characters.";
			}
			
			if (($oDetails->iStatusId != STATUS_ACTIVE) && ($oDetails->iStatusId != STATUS_INACTIVE))
			{
				$aErrors[]	= 'Invalid status';
			}
			
			if (($oDetails->iFollowUpClosureTypeId != FOLLOWUP_CLOSURE_TYPE_COMPLETED) && ($oDetails->iFollowUpClosureTypeId != FOLLOWUP_CLOSURE_TYPE_DISMISSED))
			{
				$aErrors[]	= 'Invalid closure reason type';
			}
			
			$oClosure->name						= $oDetails->sName;
			$oClosure->description				= $oDetails->sDescription;
			$oClosure->status_id				= $oDetails->iStatusId;
			$oClosure->followup_closure_type_id	= $oDetails->iFollowUpClosureTypeId;
			$oClosure->save();
			
			$oDataAccess->TransactionCommit();
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						"Success"		=> true,
						"iClosureId"	=> $oClosure->id
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