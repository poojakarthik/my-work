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
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"				=> true,
							"aOperationIds"			=> array_keys($aEmployeeOperations),
							"aOperationProfileIds"	=> array_keys($aEmployeeOperationProfiles),
							"strDebug"				=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
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
			$bUserIsSelf		= FALSE;
	
			if ($iEmployeeId != AuthenticatedUser()->GetUserId())
			{
				if(!$bProperAdminUser)
				{
					throw new JSON_Handler_Employee_Exception('You do not have permission t edit employee details');
				}
			}
			else
			{
				$bUserIsSelf = TRUE;
			}
	
			$aValidationErrors 	= array();
			$bCreateNew 		= is_null($iEmployeeId);

			if ($bCreateNew)
			{
				// Create new employee object
				$oEmployee					= new Employee();
				$oEmployee->DOB 			= GetCurrentDateForMySQL();

				// Apply default values for non-nullable fields
				$oEmployee->SessionId 		= "";
				$oEmployee->SessionExpire 	= GetCurrentDateAndTimeForMySQL();
				$oEmployee->Session 		= "";
				$oEmployee->Karma 			= 0;
				$oEmployee->PabloSays 		= PABLO_TIP_POLITE;
				$oEmployee->Archived 		= 0;
				$oEmployee->is_god 			= 0;
				
				// Validate username
				if (isset($oDetails->sUserName) && $oDetails->sUserName != '')
				{
					$oDetails->sUserName = trim($oDetails->sUserName);
					
					if (Employee::getForUserName($oDetails->sUserName))
					{
						$aValidationErrors[] = "UserName {$oDetails->sUserName} is already in use.";
					}
				}
			}
			else
			{
				// Get existing employee object
				$oEmployee	= Employee::getForId($iEmployeeId);
			}
	
			if (!$bUserIsSelf && $bAdminUser)
			{
				$oDetails->sFirstName	= trim($oDetails->sFirstName);
				
				if (!Validation::IsNotEmptyString($oDetails->sFirstName))
				{
					$aValidationErrors[]	= 'First Name is missing';
				}
				
				$oDetails->sLastName		= trim($oDetails->sLastName);
				
				if (!Validation::IsNotEmptyString($oDetails->sLastName))
				{
					$aValidationErrors[]	= 'Last Name is missing';
				}
				
				$oDetails->sDOB 	= trim($oDetails->sDOB);
				$oDetails->sDOB 	= UnmaskShortDate($oDetails->sDOB);
				
				if (!Validation::IsValidDate($oDetails->sDOB))
				{
					$aValidationErrors[]	= 'DOB is an invalid date';
				}
				
				if (!Validation::IsValidDate($oDetails->sDOB))
				{
					$aValidationErrors[]	= 'DOB is an invalid date';
				}
				
				if (!Validation::IsValidDateInPast($oDetails->sDOB))
				{
					$aValidationErrors[]	= 'DOB needs to be in the past';
				}
			}

			$oDetails->sEmail 	= trim($oDetails->sEmail);
			
			if (!Validation::IsValidEmail($oDetails->sEmail))
			{
				$aValidationErrors[]	= 'Invalid email address';
			}

			$oDetails->sExtension	= ((isset($oDetails->sExtension) && $oDetails->sExtension != '') ? $oDetails->Extension : '');
			$oDetails->sPhone 		= trim($oDetails->sPhone);
			
			if (Validation::IsNotEmptyString($oDetails->sPhone) && !Validation::IsValidPhoneNumber($oDetails->sPhone))
			{
				$aValidationErrors[]	= 'Invalid phone number';
			}
			
			$oDetails->sMobile 	= trim($oDetails->sMobile);

			if (Validation::IsNotEmptyString($oDetails->sMobile) && !Validation::IsValidPhoneNumber($oDetails->sMobile))
			{
				$aValidationErrors[]	= 'Invalid mobile number';
			}

			// Check that the password has been entered and confirmed, as appropriate
			// Validate that the password has been submitted as a 2 value array
			if ($oDetails->bPassWordChange)
			{
				if (Validation::isNonEmptyString($oDetails->sPassword) && Validation::isNonEmptyString($oDetails->sPasswordConfirm))
				{
					$aValidationErrors[] = "Both Password and Password Confirmation are required.";
				}
				
				// Check that the values are the same
				else if ($oDetails->sPassword != $oDetails->sPasswordConfirm)
				{
					$aValidationErrors[] = "Password does not match Password Confirmation.";
				}
				else
				{
					// Set the validated password value into the password property
					$oDetails->sPassword	= sha1($oDetails->sPassword);
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

			if (!$bCreateNew)
			{
				// Restrict the fields that can be updated
				$oEmployee->Email		= $oDetails->sEmail;
				$oEmployee->Extension	= $oDetails->sExtension;
				$oEmployee->Phone		= $oDetails->sPhone;
				$oEmployee->Mobile		= $oDetails->sMobile;

				// Only change the following through the admin console, not when editing self
				if (!$bUserIsSelf && $bAdminUser)
				{
					$oEmployee->FirstName		= $oDetails->sFirstName;
					$oEmployee->LastName		= $oDetails->sLastName;
					$oEmployee->DOB				= $oDetails->sDOB;
					$oEmployee->Archived		= $oDetails->iArchived;
					$oEmployee->Privileges		= $oDetails->iPriviledges;
					$oEmployee->user_role_id	= $oDetails->iUserRoleId;
				}
				
				// Only set password if needed
				if ($oDetails->bPasswordChange)
				{
					$oEmployee->PassWord	= $oDetails->sPassword;
				}
			}
			
			return array('oEmployee' => $oEmployee);
			
			if ($oEmployee->save())
			{
				if (Flex_Module::isActive(FLEX_MODULE_TICKETING))
				{
					$currentUserTicketingPermission	= Ticketing_User::getPermissionForEmployeeId(AuthenticatedUser()->GetUserId());
					if ($bUserIsSelf)
					{
						$displayUserTicketingPermission	= $currentUserTicketingPermission;
					}
					else
					{
						$displayUserTicketingPermission	= Ticketing_User::getPermissionForEmployeeId($oEmployee->Id);
					}
					
					if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN) || (!$bUserIsSelf && $currentUserTicketingPermission == TICKETING_USER_PERMISSION_ADMIN))
					{
						Ticketing_User::setPermissionForEmployeeId($oEmployee->Id, intval($oDetails->iTicketingPermission));
					}
				}

				// If the user has the Sales privilege then create/update their dealer record
				$iEmployeePerms = $oEmployee->Privileges;
				
				$bModifiedDealerTable = FALSE;
				$oDealerConfig = Dealer_Config::getConfig();
				$oDefaultEmployeeManagerDealer = ($oDealerConfig->defaultEmployeeManagerDealerId !== NULL)? Dealer::getForId($oDealerConfig->defaultEmployeeManagerDealerId) : NULL;
				if (($iEmployeePerms & PERMISSION_SALES) == PERMISSION_SALES)
				{
					// The Employee has the Sales permission
					// Check if they already have a dealer record
					$oDealer = Dealer::getForEmployeeId($oEmployee->Id);

					if ($oDealer === NULL && $oEmployee->Archived == 0)
					{
						// A dealer record doesn't exist, but the employee is active, so create one
						$oDealer = new Dealer();

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
						$bModifiedDealerTable	= TRUE;
					}
				}
				else
				{
					// The employee doesn't have the sales permission
					// If they have a related dealer record, then de-activate it, but check that they are not currently set as the Default Manager for Employee Dealers
					$oDealer = Dealer::getForEmployeeId($oEmployee->Id);
					if ($oDealer !== NULL && $oDealer->dealerStatusId != Dealer_Status::INACTIVE)
					{
						// A dealer record exists and it isn't set to inactive, so set it
						// Check that the dealer isn't currently set as the Default Manager for Employee Dealers
						if ($oDefaultEmployeeManagerDealer !== NULL && $oDefaultEmployeeManagerDealer->id == $oDealer->id)
						{
							throw new Exception("This employee is currently set up as the Default Manager for employee dealers and therefore must keep the sales permission.  If you wish to remove this employee's sales permission, then please declare a different Default Manager for employee dealers.");
						}

						$oDealer->dealerStatusId = Dealer_Status::INACTIVE;
						$oDealer->save();
						$bModifiedDealerTable = TRUE;
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
						$sWarning = "Pushing the data from Flex to the Sales database, failed. Contact your system administrators to have them manually trigger the data push.  (Error message: ". htmlspecialchars($e->getMessage()) .")";
					}
				}

				return 	array(
					"Success"	=> true,
					"sDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
				);
			}
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