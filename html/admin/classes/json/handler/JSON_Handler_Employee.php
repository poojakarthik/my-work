<?php

class JSON_Handler_Employee extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getForId($iEmployeeId)
	{
		try
		{
			// Get the Employee
			$oEmployee							= Employee::getForId($iEmployeeId);
			$aEmployee							= $oEmployee->toArray();
			$aEmployee['ticketing_permission']	= Ticketing_User::getPermissionForEmployeeId($iEmployeeId);
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"			=> true,
							"objEmployee"		=> $aEmployee,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			return array(
							"Success"	=> false,
							"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error getting the employee.'),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	public function getRecords($bolCountOnly=false, $intLimit=0, $intOffset=0)
	{
		try
		{
			if ($bolCountOnly)
			{
				// Count Only
				return array(
								"Success"			=> true,
								"intRecordCount"	=> self::_getRecordCount(),
								"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
							);
			}
			else
			{
				// Include Data
				$intLimit	= (max($intLimit, 0) == 0) ? null : (int)$intLimit;
				$intOffset	= ($intLimit === null) ? null : max((int)$intOffset, 0);
				
				$qryQuery	= new Query();
				
				// Retrieve list of Employees
				$strEmployeeSQL	= "SELECT * FROM Employee";
				$strEmployeeSQL	.= ($intLimit !== null) ? " LIMIT {$intLimit} OFFSET {$intOffset}" : '';
				$resEmployees	= $qryQuery->Execute($strEmployeeSQL);
				if ($resEmployees === false)
				{
					throw new Exception($qryQuery->Error());
				}
				$arrEmployees	= array();
				$intCount		= 0;
				while ($arrEmployee = $resEmployees->fetch_assoc())
				{
					$arrEmployees[$intCount+$intOffset]	= $arrEmployee;
					$intCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return array(
								"Success"			=> true,
								"arrRecords"		=> $arrEmployees,
								"intRecordCount"	=> ($intLimit === null) ? count($arrEmployees) : self::_getRecordCount(),
								"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
							);
			}
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	private static function _getRecordCount()
	{
		$qryQuery	= new Query();
		
		// Retrieve COUNT() of Employees
		$strCountSQL	= "SELECT COUNT(Id) AS employee_count FROM Employee WHERE 1";
		$resCount		= $qryQuery->Execute($strCountSQL);
		if ($resCount === false)
		{
			throw new Exception($qryQuery->Error());
		}
		if ($arrCount = $resCount->fetch_assoc())
		{
			return $arrCount['employee_count'];
		}
	}
	
	public function getPermissions($iEmployeeId)
	{
		try
		{
			if ($iEmployeeId)
			{
				// Get the Employee
				$oEmployee					= Employee::getForId($iEmployeeId);
				$aEmployeeOperations		= $oEmployee->getOperations();
				$aEmployeeOperationProfiles	= $oEmployee->getOperationProfiles();
			}
			else
			{
				$aEmployeeOperations		= array();
				$aEmployeeOperationProfiles	= array();
			}
			
			// Return full operation profile details, convert all to std classes
			$aStdClassOperationProfiles	= array();
			
			foreach ($aEmployeeOperationProfiles as $iId => $oOperationProfile)
			{
				$oStdClass	= $oOperationProfile->toStdClass();
				
				// Get list of children profiles
				$aChildren				= $oOperationProfile->getChildOperationProfiles();
				$oStdClass->aChildren	= array();
				
				foreach ($aChildren as $oChild)
				{
					$oStdClass->aChildren[]	= $oChild->id;
				}
				
				$aStdClassOperationProfiles[$iId]	= $oStdClass;
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"				=> true,
							"aOperationIds"			=> array_keys($aEmployeeOperations),
							"aOperationProfiles"	=> $aStdClassOperationProfiles,
							"strDebug"				=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> 'ERROR: '.$e->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function setPermissions($iEmployeeId, $aOperationProfileIds, $aOperationIds)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? 'There was an error accessing the database' : '',
						"strDebug"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			// Get current data
			$aOperationProfiles	= Employee_Operation_Profile::getForEmployeeId($iEmployeeId);
			$aOperations		= Employee_Operation::getForEmployeeId($iEmployeeId);
			$sNowDate			= date('Y-m-d H:i:s');
			$sNowDateForInsert	= date('Y-m-d H:i:s', time() + 1);
			
			// Terminate the existing operation profiles
			foreach ($aOperationProfiles as $iId => $oOperationProfile)
			{
				$oOperationProfile->end_datetime	= $sNowDate;
				$oOperationProfile->save();
			}
			
			// Terminate the existing operations
			foreach ($aOperations as $iId => $oOperation)
			{
				$oOperation->end_datetime	= $sNowDate;
				$oOperation->save();
			}
			
			// Default end date
			$sEndDate	= '9999-12-31 23:59:59';
			
			// Add the new operation profiles
			foreach ($aOperationProfileIds as $iOperationProfileId)
			{
				$oOperationProfile							= new Employee_Operation_Profile();
				$oOperationProfile->employee_id				= $iEmployeeId;
				$oOperationProfile->operation_profile_id	= $iOperationProfileId;
				$oOperationProfile->start_datetime			= $sNowDateForInsert;
				$oOperationProfile->end_datetime			= $sEndDate;
				$oOperationProfile->assigned_timestamp		= $sNowDateForInsert;
				$oOperationProfile->assigned_employee_id	= Flex::getUserId();
				$oOperationProfile->save();
			}
			
			// Add the new operations
			foreach ($aOperationIds as $iOperationId)
			{
				$oOperation							= new Employee_Operation();
				$oOperation->employee_id			= $iEmployeeId;
				$oOperation->operation_id			= $iOperationId;
				$oOperation->start_datetime			= $sNowDateForInsert;
				$oOperation->end_datetime			= $sEndDate;
				$oOperation->assigned_timestamp		= $sNowDateForInsert;
				$oOperation->assigned_employee_id	= Flex::getUserId();
				$oOperation->save();
			}
			
			// If no exceptions were thrown, then everything worked. 
			// Commit transaction and return success
			$oDataAccess->TransactionCommit();
			
			return 	array(
						"Success"	=> true,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			// Rollback db transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error saving the permissions',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function save($iEmployeeId, $oDetails)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? 'There was an error accessing the database' : '',
						"sDebug"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $this->_JSONDebug : ''
					);
		}
		
		try 
		{
			$bAdminUser			= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
			$bProperAdminUser	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
			
			if ($iEmployeeId != AuthenticatedUser()->GetUserId())
			{
				if(!$bProperAdminUser)
				{
					throw new JSON_Handler_Employee_Exception('You do not have permission to edit employee details');
				}
			}
			else
			{
				throw new JSON_Handler_Employee_Exception('You can not edit your own details');
			}
	
			$aValidationErrors 	= array();
			$bCreateNew 		= is_null($iEmployeeId);

			if ($bCreateNew)
			{
				// Create new employee object
				$oEmployee		= new Employee();
				$oEmployee->DOB = GetCurrentDateForMySQL();

				// Apply default values for non-nullable fields
				$oEmployee->SessionId 		= "";
				$oEmployee->SessionExpire 	= GetCurrentDateAndTimeForMySQL();
				$oEmployee->Session 		= "";
				$oEmployee->Karma 			= 0;
				$oEmployee->PabloSays 		= PABLO_TIP_POLITE;
				$oEmployee->Archived 		= 0;
				$oEmployee->is_god 			= 0;
				$oEmployee->Privileges		= PERMISSION_PUBLIC; // TODO REMOVE ME
				
				// Validate username
				if (Validation::IsNotEmptyString($oDetails->mUserName))
				{
					// Given, check uniqueness
					$oDetails->mUserName = trim($oDetails->mUserName);
					
					if (Employee::getForUserName($oDetails->mUserName))
					{
						$aValidationErrors[] = "Username {$oDetails->mUserName} is already in use";
					}
					else
					{
						// Store the new username
						$oEmployee->UserName	= $oDetails->mUserName;
					}
				}
			}
			else
			{
				// Get existing employee object
				$oEmployee	= Employee::getForId($iEmployeeId);
			}
	
			if ($bAdminUser)
			{
				$oDetails->mFirstName	= trim($oDetails->mFirstName);
				
				if (!Validation::IsNotEmptyString($oDetails->mFirstName))
				{
					$aValidationErrors[]	= 'First Name is missing';
				}
				
				$oDetails->mLastName		= trim($oDetails->mLastName);
				
				if (!Validation::IsNotEmptyString($oDetails->mLastName))
				{
					$aValidationErrors[]	= 'Last Name is missing';
				}
				
				$oDetails->mDOB 	= trim($oDetails->mDOB);
				$oDetails->mDOB 	= UnmaskShortDate($oDetails->mDOB);
				
				if (!Validation::IsValidDate($oDetails->mDOB))
				{
					$aValidationErrors[]	= 'DOB is an invalid date';
				}
				
				if (!Validation::IsValidDate($oDetails->mDOB))
				{
					$aValidationErrors[]	= 'DOB is an invalid date';
				}
				
				if (!Validation::IsValidDateInPast($oDetails->mDOB))
				{
					$aValidationErrors[]	= 'DOB needs to be in the past';
				}
			}

			$oDetails->mEmail 	= trim($oDetails->mEmail);
			
			if (!Validation::IsValidEmail($oDetails->mEmail))
			{
				$aValidationErrors[]	= 'Invalid email address';
			}

			$oDetails->mExtension	= (Validation::IsNotEmptyString($oDetails->mExtension) ? $oDetails->mExtension : '');
			$oDetails->mPhone 		= trim($oDetails->mPhone);
			
			if (Validation::IsNotEmptyString($oDetails->mPhone) && !Validation::IsValidPhoneNumber($oDetails->mPhone))
			{
				$aValidationErrors[]	= 'Invalid phone number';
			}
			
			$oDetails->mMobile 	= trim($oDetails->mMobile);

			if (Validation::IsNotEmptyString($oDetails->mMobile) && !Validation::IsValidPhoneNumber($oDetails->mMobile))
			{
				$aValidationErrors[]	= 'Invalid mobile number';
			}

			// Check that the password has been entered and confirmed, as appropriate
			// Validate that the password has been submitted as a 2 value array
			$bPasswordSet			= Validation::IsNotEmptyString($oDetails->mPassWord);
			$bPasswordConfirmSet	= Validation::IsNotEmptyString($oDetails->mPassWordConfirm);
			
			if ($bPasswordSet || $bPasswordConfirmSet)
			{
				$oDetails->bPassWordChange	= true;
				
				if (!$bPasswordSet || !$bPasswordConfirmSet)
				{
					$aValidationErrors[] = "Both Password and Password Confirmation are required.";
				}
				
				// Check that the values are the same
				else if ($oDetails->mPassWord != $oDetails->mPassWordConfirm)
				{
					$aValidationErrors[] = "Password does not match Password Confirmation.";
				}
				else
				{
					// Set the validated password value into the password property
					$oDetails->mPassWord	= sha1($oDetails->mPassWord);
				}
			}
			
			if (count($aValidationErrors) > 0)
			{
				// There were validation errors, rollback db transaction and return the errors
				$oDataAccess->TransactionRollback();
				
				return 	array(
							"Success"			=> false,
							"aValidationErrors"	=> $aValidationErrors
						);
			}
			
			// Restrict the fields that can be updated
			$oEmployee->Email		= $oDetails->mEmail;
			$oEmployee->Extension	= $oDetails->mExtension;
			$oEmployee->Phone		= $oDetails->mPhone;
			$oEmployee->Mobile		= $oDetails->mMobile;

			// Only change the following through the admin console, not when editing self
			if ($bAdminUser)
			{
				$oEmployee->FirstName		= $oDetails->mFirstName;
				$oEmployee->LastName		= $oDetails->mLastName;
				$oEmployee->DOB				= $oDetails->mDOB;
				$oEmployee->Archived		= $oDetails->mArchived;
				$oEmployee->user_role_id	= $oDetails->muser_role_id;
				
				// TODO REMOVE ME
				if (Validation::IsNotEmptyString($oDetails->mPriviledges))
				{
					$oEmployee->Privileges	= $oDetails->mPriviledges;
				}
			}
			
			// Only set password if needed
			if ($oDetails->bPassWordChange)
			{
				$oEmployee->PassWord	= $oDetails->mPassWord;
			}
			
			$oEmployee->save();
			
			if (Flex_Module::isActive(FLEX_MODULE_TICKETING))
			{
				$currentUserTicketingPermission	= Ticketing_User::getPermissionForEmployeeId(AuthenticatedUser()->GetUserId());
				
				if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN) || ($currentUserTicketingPermission == TICKETING_USER_PERMISSION_ADMIN))
				{
					Ticketing_User::setPermissionForEmployeeId($oEmployee->Id, intval($oDetails->mticketing_permission));
				}
			}

			// If the user has the Sales privilege then create/update their dealer record
			$iEmployeePerms 				= $oEmployee->Privileges;
			$bModifiedDealerTable 			= FALSE;
			$oDealerConfig 					= Dealer_Config::getConfig();
			$oDefaultEmployeeManagerDealer 	= ($oDealerConfig->defaultEmployeeManagerDealerId !== NULL) 
												? Dealer::getForId($oDealerConfig->defaultEmployeeManagerDealerId) 
												: NULL;
			
			if (($iEmployeePerms & PERMISSION_SALES) == PERMISSION_SALES)
			{
				// The Employee has the Sales permission
				// Check if they already have a dealer record
				$oDealer	= Dealer::getForEmployeeId($oEmployee->Id);

				if ($oDealer === NULL && $oEmployee->Archived == 0)
				{
					// A dealer record doesn't exist, but the employee is active, so create one
					$oDealer	= new Dealer();

					// All employees get the "can verify" flag, initially
					$oDealer->canVerify				= TRUE;
					$oDealer->gstRegistered			= FALSE;
					$oDealer->syncSaleConstraints	= TRUE;
					$oDealer->clawbackPeriod		= 0;
					$oDealer->createdOn				= GetCurrentISODateTime();
					$oDealer->upLineId				= $oDealerConfig->defaultEmployeeManagerDealerId;
				}

				if ($oDealer !== NULL)
				{
					if ($oDealer->id !== NULL && $oEmployee->Archived)
					{
						// The dealer is already established, but has been archived
						// Make sure they are not currently set as the default Employee Manager
						if ($oDefaultEmployeeManagerDealer !== NULL && $oDealer->id === $oDefaultEmployeeManagerDealer->id)
						{
							throw new Exception("This employee is currently set up as the Default Manager for employee dealers and therefore can not be archived.  If you wish to archive this employee, then please declare a different Default Manager for employee dealers.");
						}
					}

					// Update the record
					$oDealer->firstName			= $oEmployee->FirstName;
					$oDealer->lastName			= $oEmployee->LastName;
					$oDealer->username			= $oEmployee->UserName;
					$oDealer->password			= $oEmployee->PassWord;
					$oDealer->phone				= $oEmployee->Phone;
					$oDealer->mobile			= $oEmployee->Mobile;
					$oDealer->email				= $oEmployee->Email;
					$oDealer->dealerStatusId	= ($oEmployee->Archived == 0)? Dealer_Status::ACTIVE : Dealer_Status::INACTIVE;
					$oDealer->employeeId		= $oEmployee->Id;
					$oDealer->save();
					$bModifiedDealerTable		= TRUE;
				}
			}
			else
			{
				// The employee doesn't have the sales permission
				// If they have a related dealer record, then de-activate it, but check that they are not currently set as the Default Manager for Employee Dealers
				$oDealer	= Dealer::getForEmployeeId($oEmployee->Id);
				
				if ($oDealer !== NULL && $oDealer->dealerStatusId != Dealer_Status::INACTIVE)
				{
					// A dealer record exists and it isn't set to inactive, so set it
					// Check that the dealer isn't currently set as the Default Manager for Employee Dealers
					if ($oDefaultEmployeeManagerDealer !== NULL && $oDefaultEmployeeManagerDealer->id == $oDealer->id)
					{
						throw new Exception("This employee is currently set up as the Default Manager for employee dealers and therefore must keep the sales permission.  If you wish to remove this employee's sales permission, then please declare a different Default Manager for employee dealers.");
					}

					$oDealer->dealerStatusId	= Dealer_Status::INACTIVE;
					$oDealer->save();
					$bModifiedDealerTable		= TRUE;
				}
			}

			// If no exceptions were thrown, then everything worked, commit transaction 
			$oDataAccess->TransactionCommit();

			if (isset($bModifiedDealerTable) && $bModifiedDealerTable && Flex_Module::isActive(FLEX_MODULE_SALES_PORTAL))
			{
				// Dealer table has been modified, trigger the sync operation
				try
				{
					Cli_App_Sales::pushAll();
				}
				catch (Exception $e)
				{
					// Pushing the data failed
					throw new Exception("Pushing the data from Flex to the Sales database, failed. Contact your system administrators to have them manually trigger the data push.  (Error message: ". htmlspecialchars($e->getMessage()) .")");
				}
			}

			return 	array(
						"Success"		=> true,
						"iEmployeeId"	=> $oEmployee->Id,
						"sDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (JSON_Handler_Employee_Exception $oException)
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
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error saving the permissions',
						"sDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}	
	
	public function setPassword($iEmployeeId, $sPassword, $sPasswordConfirm)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? 'There was an error accessing the database' : '',
						"sDebug"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			// Ensure that the user isn't trying to edit another users password illegally
			$bAdminUser			= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
			$bProperAdminUser	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
			
			if ($iEmployeeId != AuthenticatedUser()->GetUserId())
			{
				if(!$bProperAdminUser)
				{
					throw new JSON_Handler_Employee_Exception('You do not have permission to edit employee details');
				}
			}
			
			$oEmployee	= Employee::getForId($iEmployeeId);
			
			// Check for: both present, both matching
			if (!Validation::IsNotEmptyString($sPassword) || !Validation::IsNotEmptyString($sPasswordConfirm))
			{
				$aValidationErrors[] = "Both Password and Password Confirmation are required.";
			}
			else if ($sPassword != $sPasswordConfirm)
			{
				$aValidationErrors[] = "Password does not match Password Confirmation.";
			}
			
			if (count($aValidationErrors) > 0)
			{
				// There were validation errors, rollback db transaction and return the errors
				$oDataAccess->TransactionRollback();
				
				return 	array(
							"Success"			=> false,
							"aValidationErrors"	=> $aValidationErrors
						);
			}
			
			// Set the validated password value into the password property
			$oEmployee->PassWord	= sha1($sPassword);
			$oEmployee->save();
			
			// All good, commit and return
			$oDataAccess->TransactionCommit();
			return array("Success" => true);
		}
		catch (JSON_Handler_Employee_Exception $oException)
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
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error saving the permissions',
						"sDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}	
}

class JSON_Handler_Employee_Exception extends Exception
{
	// ...
}

?>