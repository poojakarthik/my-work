
<?php

class JSON_Handler_Collection_Suspension extends JSON_Handler
{
	protected	$_JSONDebug	= '';

	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getForId($iSuspensionId)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			return	array(
						'bSuccess'		=> true,
						'oSuspension'	=> Collection_Suspension::getForId($iSuspensionId)->toStdClass()
					);
		}
		catch (Exception $oEx)
		{
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function getExtendedDetailsForId($iSuspensionId)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oSuspension 								= Collection_Suspension::getForId($iSuspensionId);
			$oStdClass									= $oSuspension->toStdClass();
			$oStdClass->account 						= Account::getForId($oSuspension->account_id)->toArray();
			$oStdClass->start_employee 					= Employee::getForId($oSuspension->start_employee_id)->toStdClass();
			$oStdClass->collection_suspension_reason	= Collection_Suspension_Reason::getForId($oSuspension->collection_suspension_reason_id)->toStdClass();
			if ($oSuspension->effective_end_datetime !== null)
			{
				$oStdClass->collection_suspension_end_reason	= Collection_Suspension_End_Reason::getForId($oSuspension->collection_suspension_end_reason_id)->toStdClass();
				$oStdClass->end_employee 						= Employee::getForId($oSuspension->end_employee_id)->toStdClass();
			}
			return	array(
						'bSuccess'		=> true,
						'oSuspension'	=> $oStdClass
					);
		}
		catch (Exception $oEx)
		{
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function getSuspensionAvailabilityInfo($iAccountId)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oSuspension			= Collection_Suspension::getActiveForAccount($iAccountId);
			$oPromise				= Collection_Promise::getCurrentForAccountId($iAccountId);
			$iNumberOfSuspensions	= Logic_Collection_Suspension::getNumberOfSuspensionsInCurrentCollectionsPeriod($iAccountId);
			$aConfig				= Collection_Permissions_Config::getOptimalConfigValuesForEmployee(Flex::getUserId());
			
			return	array(
						'bSuccess' 					=> true, 
						'oSuspension'				=> ($oSuspension !== null ? $oSuspension->toStdClass() : null), 
						'oReason'					=> ($oSuspension !== null ? Collection_Suspension_Reason::getForId($oSuspension->collection_suspension_reason_id)->toStdClass() : null),
						'oPromise'					=> ($oPromise !== null ? $oPromise->toStdClass() : null),
						'bSuspensionLimitExceeded'	=> ($iNumberOfSuspensions == $aConfig['suspension_maximum_suspensions_per_collections_period']),
						'iNumberOfSuspensions'		=> $iNumberOfSuspensions,
						'iMaxSuspensions'			=> $aConfig['suspension_maximum_suspensions_per_collections_period']
					);
		}
		catch (Exception $oEx)
		{
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function getSuspensionConfig()
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			return	array(
						'bSuccess'	=> true,
						'aConfig'	=> Collection_Permissions_Config::getOptimalConfigValuesForEmployee(Flex::getUserId())
					);
		}
		catch (Exception $oEx)
		{
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function createSuspension($oDetails, $iEffectiveSeconds, $oTIOComplaintDetails=null)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Validation
			if ($oDetails->account_id === null)
			{
				$aErrors[] = 'No Account was supplied';
			}
						
			if ($oDetails->proposed_end_datetime === null)
			{
				$aErrors[] = 'No Proposed End Date was supplied';
			}
			else if (strtotime($oDetails->proposed_end_datetime) <= strtotime($oDetails->start_datetime))
			{
				$aErrors[] = 'Proposed End Date must be after the Start Date';
			}
			
			if ($oDetails->collection_suspension_reason_id === null)
			{
				$aErrors[] = 'No reason was supplied';
			}
			
			if (count($aErrors) > 0)
			{
				return 	array(
							'bSuccess'	=> false,
							'aErrors'	=> $aErrors
						);
			}
			
			// Start transaction
			$oDataAccess = DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false)
			{
				throw new Exception_Database("Failed to start db transaction.");
			}
			
			try
			{
				if ($oTIOComplaintDetails !== null)
				{
					// Close the tio complaint using the given details (id and reason)
					Account_TIO_Complaint::getForId($oTIOComplaintDetails->iTIOComplaintId)->end($oTIOComplaintDetails->iEndReasonId);
				}
				
				$oSuspension 									= new Collection_Suspension();
				$oSuspension->account_id 						= $oDetails->account_id;
				$oSuspension->proposed_end_datetime 			= $oDetails->proposed_end_datetime;
				$oSuspension->start_employee_id 				= Flex::getUserId();
				$oSuspension->collection_suspension_reason_id	= $oDetails->collection_suspension_reason_id;
				$oSuspension->calculateStartDatetime(date('Y-m-d H:i:s', $iEffectiveSeconds));
				$oSuspension->save();
			}
			catch (Exception $oEx)
			{
				// Rollback transaction
				if ($oDataAccess->TransactionRollback() === false)
				{
					throw new Exception_Database("Failed to rollback db transaction.");
				}
				throw $oEx;
			}
			
			// Commit transaction
			if ($oDataAccess->TransactionCommit() === false)
			{
				throw new Exception_Database("Failed to commit db transaction.");
			}
			
			return	array(
						'bSuccess' 		=> true,
						'iSuspensionId'	=> $oSuspension->id
					);
		}
		catch (Exception $oEx)
		{
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	
	public function endSuspension($iSuspensionId, $iSuspensionEndReasonId)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			Collection_Suspension::getForId($iSuspensionId)->end($iSuspensionEndReasonId);
			
			return array('bSuccess' => true);
		}
		catch (Exception $oEx)
		{
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.')
					);
		}
	}
}

class JSON_Handler_Collection_Suspension_Exception extends Exception
{
	// No changes
}

?>