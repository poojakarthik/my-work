<?php

class JSON_Handler_Recurring_Charge_Type extends JSON_Handler
{
	protected	$_JSONDebug		= '';
	protected	$_permissions	= PERMISSION_CREDIT_MANAGEMENT;
	
	const MAX_LIMIT = 100;
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getAll($bCountOnly=false, $iLimit=0, $iOffset=0)
	{
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_permissions))
			{
				throw(new JSON_Handler_Recurring_Charge_Type_Exception('You do not have permission to view recurring charge types.'));
			}
			
			// Build filter data for the 'searchFor' function
			$aFilterData = 	array(
								array(
									'Type' 	=> 'RecurringChargeType|Archived', 
									'Value'	=> 0
								)
							);
			
			if ($bCountOnly)
			{
				// Count Only
				return array(
							"Success"			=> true,
							"intRecordCount"	=> Recurring_Charge_Type::searchFor($aFilterData, null, null, null, true),
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
			else
			{
				// Include Data
				$iLimit		= (max($iLimit, 0) == 0) 		? self::MAX_LIMIT 	: (int)$iLimit;
				$iLimit		= ($iLimit > self::MAX_LIMIT)	? self::MAX_LIMIT	: $iLimit;
				$iOffset	= ($iLimit === null) 			? 0 				: max((int)$iOffset, 0);
				
				// Retrieve the charges & convert response to std classes
				$aRecurringChargeTypes = Recurring_Charge_Type::searchFor($aFilterData, null, $iLimit, $iOffset);
				$aStdClassChargeTypes = array();
				
				$aTest	= array();
				
				foreach ($aRecurringChargeTypes as $iId => $oRecurringChargeType)
				{
					$aStdClassRecurringChargeTypes[$iId] = $oRecurringChargeType->toStdClass();
					
					// Add 'recursion' property, human readable recursion frequency
					$sFreqType = false;
					
					switch ($oRecurringChargeType->RecurringFreqType)
					{
						case BILLING_FREQ_MONTH:
							$sFreqType = 'Month';
							break;
					}
					
					if ($sFreqType)
					{
						$aStdClassRecurringChargeTypes[$iId]->recursion = "Every {$oRecurringChargeType->RecurringFreq} {$sFreqType}".($oRecurringChargeType->RecurringFreq != 1 ? 's' : '');
						
						if ($oRecurringChargeType->RecursionCharge > 0)
						{
							$iNumberOfCharges = ceil($oRecurringChargeType->MinCharge / $oRecurringChargeType->RecursionCharge);
						}
						else
						{
							$iNumberOfCharges = 0; 	
						}
						
						$iTotalMonths = ($iNumberOfCharges * $oRecurringChargeType->RecurringFreq);
						$aStdClassRecurringChargeTypes[$iId]->recursion_detail = "{$iNumberOfCharges} times over {$iTotalMonths} {$sFreqType}".($iTotalMonths != 1 ? 's' : '');
					}
					
					// Apply G.S.T to all amounts
					$sDate = date('d/m/y', time());
					$aStdClassRecurringChargeTypes[$iId]->MinCharge 		+= Invoice::calculateGlobalTaxComponent($aStdClassRecurringChargeTypes[$iId]->MinCharge, 		$sDate);
					$aStdClassRecurringChargeTypes[$iId]->CancellationFee 	+= Invoice::calculateGlobalTaxComponent($aStdClassRecurringChargeTypes[$iId]->CancellationFee, 	$sDate);
					$aStdClassRecurringChargeTypes[$iId]->RecursionCharge 	+= Invoice::calculateGlobalTaxComponent($aStdClassRecurringChargeTypes[$iId]->RecursionCharge, 	$sDate);
					
					// Add archived property (string)
					$aStdClassRecurringChargeTypes[$iId]->archived_label = ($oRecurringChargeType->Archived) ? 'Archived' : 'Active';
				}
				
				$oPaginationDetails = Recurring_Charge_Type::getLastSearchPaginationDetails();
				
				// If no exceptions were thrown, then everything worked
				return array(
							"Success"			=> true,
							"arrRecords"		=> $aStdClassRecurringChargeTypes,
							"intRecordCount"	=> ($oPaginationDetails !== null)? $oPaginationDetails->totalRecordCount : count($aStdClassRecurringChargeTypes)
						);
			}
		}
		catch (JSON_Handler_Recurring_Charge_Type_Exception $oException)
		{
			return array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function archive($iRecurringChargeTypeId)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? 'Could not start database transaction.' : false,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_permissions))
			{
				throw(new JSON_Handler_Recurring_Charge_Type_Exception('You do not have permission to archive a recurring charge type.'));
			}
			
			$oRecurringChargeType 			= Recurring_Charge_Type::getForId((int)$iRecurringChargeTypeId);
			$oRecurringChargeType->Archived	= 1;
			$oRecurringChargeType->save();
			
			// Commit transaction
			$oDataAccess->TransactionCommit();
			
			return array(
						"Success"	=> true,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (JSON_Handler_Recurring_Charge_Type_Exception $oException)
		{
			// Rollback database transaction
			$oDataAccess->TransactionRollback();
			
			return array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			// Rollback database transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function save($oDetails)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? 'Could not start database transaction.' : false,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_permissions))
			{
				throw(new JSON_Handler_Recurring_Charge_Type_Exception('You do not have permission to save recurring charge types.'));
			}
			
			// Create a recurring charge type object
			if ($oDetails->iId)
			{
				// Details have id, must be an update
				$oRecurringChargeType = Recurring_Charge_Type::getForId($oDetails->iId);
			}
			else
			{
				// No id given, must be a new object
				$oRecurringChargeType 				= new Recurring_Charge_Type();
				
				// The following fields are not supplied by the interface, defaults are set here
				$oRecurringChargeType->Archived 	= 0;
				$oRecurringChargeType->PlanCharge	= 0;
				$oRecurringChargeType->UniqueCharge	= 0;
			}
			
			// Validate the input
			$aValidationErrors = array();
			
			if (!isset($oDetails->sChargeType) || $oDetails->sChargeType == '')
			{
				$aValidationErrors[] = 'Charge Code missing';
			} 
			else if($oExisting = Recurring_Charge_Type::getByCode($oDetails->sChargeType))
			{
				$aValidationErrors[] = 'Charge Code already in use';
			}
						
			if (!isset($oDetails->sDescription) || $oDetails->sDescription == '')
			{
				$aValidationErrors[] = 'Description missing';
			}
			
			if (!isset($oDetails->sNature) || $oDetails->sNature == '')
			{
				$aValidationErrors[] = 'Nature missing';
			}
			
			if (!isset($oDetails->iRecurringFreqType) || !is_numeric($oDetails->iRecurringFreqType))
			{
				$aValidationErrors[] = "Invalid Recurring Frequency Type '{$oDetails->iRecurringFreqType}'";
			}
			
			if (!isset($oDetails->iRecurringFreq) || !is_numeric($oDetails->iRecurringFreq))
			{
				$aValidationErrors[] = "Invalid Recurring Frequency '{$oDetails->iRecurringFreq}'";
			}
			
			if (!isset($oDetails->fMinCharge) || !is_numeric($oDetails->fMinCharge))
			{
				$aValidationErrors[] = "Invalid Minimum Charge '{$oDetails->fMinCharge}'";
			}
			
			if (!isset($oDetails->fRecursionCharge) || !is_numeric($oDetails->fRecursionCharge))
			{
				$aValidationErrors[] = "Invalid Recursion Charge '{$oDetails->fRecursionCharge}'";
			}
			
			if (!isset($oDetails->fCancellationFee) || !is_numeric($oDetails->fCancellationFee))
			{
				$aValidationErrors[] = "Invalid Cancellation Fee '{$oDetails->fCancellationFee}'";
			}
			
			if (!isset($oDetails->iApprovalRequired) || !is_numeric($oDetails->iApprovalRequired))
			{
				$aValidationErrors[] = "Invalid Approval Required '{$oDetails->iApprovalRequired}'";
			}
			
			if (count($aValidationErrors) > 0)
			{
				// Validation errors found, rollback transaction and return errors
				$oDataAccess->TransactionRollback();
				
				return 	array(
							"Success"			=> false,
							"aValidationErrors"	=> $aValidationErrors,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
			else
			{
				// Everything looks OK -- Commit!
				$oDataAccess->TransactionCommit();
				
				// Validation passed, update the object and save
				$oRecurringChargeType->ChargeType 			= $oDetails->sChargeType;
				$oRecurringChargeType->Description 			= $oDetails->sDescription;
				$oRecurringChargeType->Nature 				= $oDetails->sNature;
				$oRecurringChargeType->Fixed 				= (int)$oDetails->bFixed;
				$oRecurringChargeType->RecurringFreqType	= $oDetails->iRecurringFreqType;
				$oRecurringChargeType->RecurringFreq		= $oDetails->iRecurringFreq;
				$oRecurringChargeType->MinCharge			= $oDetails->fMinCharge;
				$oRecurringChargeType->RecursionCharge		= $oDetails->fRecursionCharge;
				$oRecurringChargeType->CancellationFee		= $oDetails->fCancellationFee;
				$oRecurringChargeType->Continuable			= (int)$oDetails->bContinuable;
				//$oRecurringChargeType->UniqueCharge			= (int)$oDetails->bUniqueCharge;
				$oRecurringChargeType->approval_required	= $oDetails->iApprovalRequired;
				$oRecurringChargeType->save();
				
				return array(
							"sChargeType"	=> $oRecurringChargeType->ChargeType,
							"Success"		=> true,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
		}
		catch (JSON_Handler_Recurring_Charge_Type_Exception $oException)
		{
			// Rollback database transaction
			$oDataAccess->TransactionRollback();
			
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			// Rollback database transaction
			$oDataAccess->TransactionRollback();
			
			return	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
}

class JSON_Handler_Recurring_Charge_Type_Exception extends Exception
{
	// No changes...
}

?>