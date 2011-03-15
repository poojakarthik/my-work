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
	
	public function getAll($bActiveOnly=false)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aChargeTypes 	= Charge_Type::getAll();
			$aResults		= array();
			foreach ($aChargeTypes as $oChargeType)
			{
				if (!$bActiveOnly || !$oChargeType->Archived)
				{
					$aResults[$oChargeType->id] = $oChargeType->toStdClass();
				}
			}
			
			return	array(
						'bSuccess'	=> true,
						'aResults'	=> $aResults,
						'sDebug'	=> ($bUserIsGod ? $this->_JSONDebug : '')
					);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	// This function wraps around getTypes and supplies CHARGE_MODEL_CHARGE as the charge_model_id
	public function getChargeTypes($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null)
	{
		return self::getTypes($bCountOnly, $iLimit, $iOffset, $oFieldsToSort, CHARGE_MODEL_CHARGE);
	}
	
	// This function wraps around getTypes and supplies CHARGE_MODEL_ADJUSTMENT as the charge_model_id
	public function getAdjustmentTypes($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null)
	{
		return self::getTypes($bCountOnly, $iLimit, $iOffset, $oFieldsToSort, CHARGE_MODEL_ADJUSTMENT);
	}
	
	// This function wraps around getTypes and supplies nothing (all charge models) as the charge_model_id
	public function getAllTypes($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null)
	{
		return self::getTypes($bCountOnly, $iLimit, $iOffset, $oFieldsToSort);
	}
	
	public function getTypes($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $iChargeModel=false)
	{
		//
		//	NOTE: 	Sorting is not supported by this (Dataset_Ajax) method. rmctainsh 20100527
		//
		
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_permissions))
			{
				throw(new JSON_Handler_Charge_Type_Exception('You do not have permission to view charge types'));
			}
			
			// Build filter data for the 'searchFor' function
			$aFilterData = 	array(
								array('Type' => 'ChargeType|Archived', 'Value' => 0)
							);
			
			// Add charge model filter if necessary
			if ($iChargeModel !== false)
			{
				$aFilterData[]	= array('Type' => 'ChargeType|charge_model_id', 'Value' => $iChargeModel);
			}
			
			if ($bCountOnly)
			{
				// Count Only
				return array(
							"Success"		=> true,
							"iRecordCount"	=> Charge_Type::searchFor($aFilterData, null, null, null, true),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
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
							"Success"		=> true,
							"aRecords"		=> $aStdClassChargeTypes,
							"iRecordCount"	=> ($oPaginationDetails !== null)? $oPaginationDetails->totalRecordCount : count($aChargeTypes),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
		}
		catch (JSON_Handler_Charge_Type_Exception $oException)
		{
			return array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error accessing the database',
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
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_permissions))
			{
				throw(new JSON_Handler_Charge_Type_Exception('You do not have permission to archive a charge type.'));
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
		catch (JSON_Handler_Charge_Type_Exception $oException)
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
			
			return array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error accessing the database',
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
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $this->_JSONDebug : ''
					);
		}
		
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm($this->_permissions))
			{
				throw(new JSON_Handler_Charge_Type_Exception('You do not have permission to save charge types'));
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
				$oChargeType	= new Charge_Type();
				
				// The following fields are not supplied by the interface, defaults are set here
				$oChargeType->Archived 						= 0;
				$oChargeType->charge_type_visibility_id 	= CHARGE_TYPE_VISIBILITY_VISIBLE;
				$oChargeType->automatic_only				= 0;
			}
			
			$sChargeModel	= Constant_Group::getConstantGroup('charge_model')->getConstantName($oChargeTypeDetails->iChargeModel);
			
			// Validate input
			$aValidationErrors = array();
			
			if (!isset($oChargeTypeDetails->sChargeType) || $oChargeTypeDetails->sChargeType == '')
			{
				$aValidationErrors[] = "{$sChargeModel} Code missing";
			}
			else if($oExisting = Charge_Type::getByCode($oChargeTypeDetails->sChargeType))
			{
				$aValidationErrors[] = "{$sChargeModel} Code already in use";
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
				$oChargeType->ChargeType 		= $oChargeTypeDetails->sChargeType;
				$oChargeType->Description 		= $oChargeTypeDetails->sDescription;
				$oChargeType->Nature 			= $oChargeTypeDetails->sNature;
				$oChargeType->Fixed 			= (int)$oChargeTypeDetails->bFixed;
				$oChargeType->Amount 			= $oChargeTypeDetails->fAmount;
				$oChargeType->charge_model_id	= $oChargeTypeDetails->iChargeModel;
				$oChargeType->save();
				
				return array(
							"sChargeType"	=> $oChargeType->ChargeType,
							"Success"		=> true,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
		}
		catch (JSON_Handler_Charge_Type_Exception $oException)
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
			
			return array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
}

class JSON_Handler_Charge_Type_Exception extends Exception
{
	// No changes...
}

?>