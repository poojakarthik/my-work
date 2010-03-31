<?php

class JSON_Handler_Charge_Type extends JSON_Handler
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
	
	public function getChargeTypes($bCountOnly=false, $iLimit=0, $iOffset=0)
	{
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_permissions))
			{
				throw(new Exception('User does not have permission.'));
			}
			
			// Build filter data for the 'searchFor' function
			$aFilterData = 	array(
								array(
									'Type' 	=> 'ChargeType|Archived', 
									'Value'	=> 0
								)
							);
			
			if ($bCountOnly)
			{
				// Count Only
				return array(
							"Success"			=> true,
							"intRecordCount"	=> Charge_Type::searchFor($aFilterData, null, null, null, true),
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
				$aChargeTypes = Charge_Type::searchFor($aFilterData, null, $iLimit, $iOffset);
				$aStdClassChargeTypes = array();
				
				foreach ($aChargeTypes as $iId => $oChargeType)
				{
					$aStdClassChargeTypes[$iId]									= $oChargeType->toStdClass();
					$aStdClassChargeTypes[$iId]->charge_type_visibility_name	= Constant_Group::getConstantGroup('charge_type_visibility')->getConstantDescription($oChargeType->charge_type_visibility_id);
					$aStdClassChargeTypes[$iId]->archived_label					= ($oChargeType->Archived)			? 'Archived'	: 'Active';
					$aStdClassChargeTypes[$iId]->automatic_only_label			= ($oChargeType->automatic_only)	? 'System Only'	: 'Users';
				}
				
				$oPaginationDetails = Charge_Type::getLastSearchPaginationDetails();
				
				// If no exceptions were thrown, then everything worked
				return array(
							"Success"			=> true,
							"arrRecords"		=> $aStdClassChargeTypes,
							"intRecordCount"	=> ($oPaginationDetails !== null)? $oPaginationDetails->totalRecordCount : count($aChargeTypes),
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
		}
		catch (Exception $e)
		{
			return array(
						"Success"	=> false,
						"Message"	=> 'ERROR: '.$e->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function archive($iChargeTypeId)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return array(
						"Success"		=> false,
						"ErrorMessage"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? 'ERROR: Could not start database transaction.' : false,
						"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_permissions))
			{
				throw(new Exception('User does not have permission.'));
			}
			
			$oChargeType = Charge_Type::getForId((int)$iChargeTypeId);
			$oChargeType->Archived = 1;
			$oChargeType->save();
			
			$oDataAccess->TransactionCommit();
			
			return array(
						"Success"	=> true,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			// Rollback database transaction
			$oDataAccess->TransactionRollback();
			
			return array(
						"Success"	=> false,
						"Message"	=> 'ERROR: '.$e->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function save($oChargeTypeDetails)
	{
		// Start a new database transaction
		$oDataAccess	= DataAccess::getDataAccess();
		
		if (!$oDataAccess->TransactionStart())
		{
			// Failure!
			return array(
						"Success"		=> false,
						"ErrorMessage"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? 'ERROR: Could not start database transaction.' : false,
						"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_permissions))
			{
				throw(new Exception('User does not have permission.'));
			}
			
			// Create a charge type object
			if ($oChargeTypeDetails->iId)
			{
				// Details have id, must be an update
				$oChargeType = Charge_Type::getForId($oChargeTypeDetails->iId);
			}
			else
			{
				// No id given, must be a new object
				$oChargeType 								= new Charge_Type();
				
				// The following fields are not supplied by the interface, defaults are set here
				$oChargeType->Archived 						= 0;
				$oChargeType->charge_type_visibility_id 	= CHARGE_TYPE_VISIBILITY_VISIBLE;
				$oChargeType->automatic_only				= 0;
			}
			
			// Validate input
			$aValidationErrors = array();
			
			if (!isset($oChargeTypeDetails->sChargeType) || $oChargeTypeDetails->sChargeType == '')
			{
				$aValidationErrors[] = 'Charge Code missing';
			}
			else if($oExisting = Charge_Type::getByCode($oChargeTypeDetails->sChargeType))
			{
				$aValidationErrors[] = 'Charge Code already in use';
			}
			
			if (!isset($oChargeTypeDetails->sDescription) || $oChargeTypeDetails->sDescription == '')
			{
				$aValidationErrors[] = 'Description missing';
			}
			
			if (!isset($oChargeTypeDetails->sNature) || $oChargeTypeDetails->sNature == '')
			{
				$aValidationErrors[] = 'Nature missing';
			}
			
			if (!isset($oChargeTypeDetails->fAmount) || !is_numeric($oChargeTypeDetails->fAmount))
			{
				$aValidationErrors[] = "Invalid Amount '{$oChargeTypeDetails->fAmount}'";
			}
			
			if (count($aValidationErrors) > 0)
			{
				// Validation errors found, rollback transaction and return errors
				$oDataAccess->TransactionRollback();
				
				return array(
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
				$oChargeType->ChargeType 	= $oChargeTypeDetails->sChargeType;
				$oChargeType->Description 	= $oChargeTypeDetails->sDescription;
				$oChargeType->Nature 		= $oChargeTypeDetails->sNature;
				$oChargeType->Fixed 		= (int)$oChargeTypeDetails->bFixed;
				$oChargeType->Amount 		= $oChargeTypeDetails->fAmount;
				$oChargeType->save();
				
				return array(
							"sChargeType"	=> $oChargeType->ChargeType,
							"Success"		=> true,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
		}
		catch (Exception $e)
		{
			// Rollback database transaction
			$oDataAccess->TransactionRollback();
			
			return array(
						"Success"	=> false,
						"Message"	=> 'ERROR: '.$e->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
}

?>