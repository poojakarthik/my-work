
<?php

class JSON_Handler_Account_TIO_Complaint extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getCurrentComplaintAndPromiseForAccount($iAccountId)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oCurrent 	= Account_TIO_Complaint::getCurrentForAccountId($iAccountId);
			$oPromise	= Collection_Promise::getCurrentForAccountId($iAccountId); 
			return	array(
						'bSuccess'		=> true,
						'oComplaint'	=> ($oCurrent !== null ? $oCurrent->toStdClass() : null),
						'oPromise'		=> ($oPromise !== null ? $oPromise->toStdClass() : null)
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
	
	public function getExtendedComplaintDetailsForAccount($iAccountId)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oComplaint = Account_TIO_Complaint::getCurrentForAccountId($iAccountId);
			if (!$oComplaint)
			{
				throw new JSON_Handler_Account_TIO_Complaint_Exception("There is no active TIO Complaint for the Account: {$iAccountId}.");
			}
			
			$oStdClass 			= $oComplaint->toStdClass();
			$oStdClass->account = Account::getForId($oComplaint->account_id)->toArray();
			
			$oSuspension 						= Collection_Suspension::getForId($oComplaint->collection_suspension_id)->toStdClass();
			$oSuspension->start_employee_name 	= Employee::getForId($oSuspension->start_employee_id)->getName();
			
			$oStdClass->collection_suspension	= $oSuspension;
			
			return	array(
						'bSuccess'		=> true,
						'oComplaint'	=> $oStdClass
					);
		}
		catch (JSON_Handler_Account_TIO_Complaint_Exception $oEx)
		{
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> $oEx->getMessage()
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
		
	public function createComplaint($oDetails, $iEffectiveSeconds)
	{
		$bUserIsGod 	= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess	= DataAccess::getDataAccess();
		try
		{
			if (!$oDataAccess->TransactionStart())
			{
				throw new JSON_Handler_Account_TIO_Complaint_Exception("Failed to start database transaction");
			}
			
			// Validation
			if ($oDetails->account_id === null)
			{
				$aErrors[] = 'No Account was supplied';
			}
			
			if ($oDetails->start_datetime === null)
			{
				$aErrors[] = 'No Start Date was supplied';
			}
			else if (strtotime($oDetails->start_datetime) < $iEffectiveSeconds)
			{
				$aErrors[] = 'Start Date must be in the future';
			}
			
			if ($oDetails->tio_reference_number === null)
			{
				$aErrors[] = 'No TIO Reference Number was supplied';
			}
			else if (!IsValidTIOReferenceNumber($oDetails->tio_reference_number))
			{
				$aErrors[] = 'TIO Reference Number is invalid';
			}
			
			if (count($aErrors) > 0)
			{
				return 	array(
							'bSuccess'	=> false,
							'aErrors'	=> $aErrors
						);
			}
			
			// Cancel any existing suspension
			$oExistingSuspension = Collection_Suspension::getActiveForAccount($oDetails->account_id);
			if ($oExistingSuspension !== null)
			{
				$oExistingSuspension->end(Collection_Suspension_End_Reason::getForSystemName('CANCELLED')->id);
			}
			
			// Cancel any existing promise to pay
			$oExistingPromise = Collection_Promise::getCurrentForAccountId($oDetails->account_id);
			if ($oExistingPromise !== null)
			{
				$oLogicPromise = new Logic_Collection_Promise($oExistingPromise);
				$oLogicPromise->complete(COLLECTION_PROMISE_COMPLETION_CANCELLED);
			}
			
			// Create suspension
			$oSuspension 									= new Collection_Suspension();
			$oSuspension->account_id 						= $oDetails->account_id;
			$oSuspension->start_datetime 					= $oDetails->start_datetime;
			$oSuspension->proposed_end_datetime 			= Data_Source_Time::END_OF_TIME;
			$oSuspension->start_employee_id 				= Flex::getUserId();
			$oSuspension->collection_suspension_reason_id	= Collection_Suspension_Reason::getForSystemName('TIO_COMPLAINT')->id;
			$oSuspension->save();
			
			// Create complaint
			$oComplaint 							= new Account_TIO_Complaint();
			$oComplaint->account_id 				= $oDetails->account_id;
			$oComplaint->collection_suspension_id	= $oSuspension->id;
			$oComplaint->tio_reference_number 		= $oDetails->tio_reference_number;
			$oComplaint->save();
			
			// Update the accounts tio reference number
			$oAccount 						= Account::getForId($oComplaint->account_id);
			$oAccount->tio_reference_number	= $oDetails->tio_reference_number;
			$oAccount->save();
			
			if (!$oDataAccess->TransactionCommit())
			{
				throw new JSON_Handler_Account_TIO_Complaint_Exception("Failed to commit database transaction");
			}
			
			return	array(
						'bSuccess' 		=> true,
						'iComplaintId'	=> $oComplaint->id
					);
		}
		catch (JSON_Handler_Account_TIO_Complaint_Exception $oEx)
		{
			// Custom exception
			$sMessage = $oEx->getMessage();
			if (!$oDataAccess->TransactionRollback())
			{
				$sMessage .= " (Failed to rollback database transaction)";
			}
			
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> $sMessage
					);
		}
		catch (Exception $oEx)
		{
			// General exception
			$sMessage = ($bUserIsGod ? $oEx->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.');
			if (!$oDataAccess->TransactionRollback())
			{
				$sMessage .= " (Failed to rollback database transaction)";
			}
			
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	
	public function endComplaint($iComplaintId, $iSuspensionEndReasonId)
	{
		$bUserIsGod 	= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess	= DataAccess::getDataAccess();
		try
		{
			if (!$oDataAccess->TransactionStart())
			{
				throw new JSON_Handler_Account_TIO_Complaint_Exception("Failed to start database transaction");
			}
			
			Account_TIO_Complaint::getForId($iComplaintId)->end($iSuspensionEndReasonId);
			
			if (!$oDataAccess->TransactionCommit())
			{
				throw new JSON_Handler_Account_TIO_Complaint_Exception("Failed to commit database transaction");
			}
			
			return array('bSuccess' => true);
		}
		catch (Exception $oEx)
		{
			// General exception
			$sMessage = ($bUserIsGod ? $oEx->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.');
			if (!$oDataAccess->TransactionRollback())
			{
				$sMessage .= " (Failed to rollback database transaction)";
			}
			
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
}

class JSON_Handler_Account_TIO_Complaint_Exception extends Exception
{
	// No changes
}

?>