<?php

class JSON_Handler_Charge_Type extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	const MAX_LIMIT = 100;
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getChargeTypes($bCountOnly=false, $iLimit=0, $iOffset=0)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		try
		{
			if ($bCountOnly)
			{
				// Count Only
				return array(
							"Success"			=> true,
							"intRecordCount"	=> Charge_Type::searchFor(null, null, null, null, true),
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
						);
			}
			else
			{
				// Include Data
				$iLimit		= (max($iLimit, 0) == 0) 		? self::MAX_LIMIT 	: (int)$iLimit;
				$iLimit		= ($iLimit > self::MAX_LIMIT)	? self::MAX_LIMIT	: $iLimit;
				$iOffset	= ($iLimit === null) 			? 0 				: max((int)$iOffset, 0);
				
				//throw new Exception("iLimit:{$iLimit} iOffset:{$iOffset}");
				
				// Retrieve the charges & convert response to std classes
				$aChargeTypes = Charge_Type::searchFor(null, null, $iLimit, $iOffset);
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
		try
		{
			$oChargeType = Charge_Type::getForId((int)$iChargeTypeId);
			$oChargeType->Archived = 1;
			$oChargeType->save();
			
			return array(
						"Success"	=> true,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
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
	
	public function save($oChargeTypeDetails)
	{
		try
		{
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
				$oChargeType->Archived 						= 0;
				$oChargeType->charge_type_visibility_id 	= CHARGE_TYPE_VISIBILITY_VISIBLE;
				$oChargeType->automatic_only				= 0;
			}
			
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
		catch (Exception $e)
		{
			return array(
						"Success"	=> false,
						"Message"	=> 'ERROR: '.$e->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
}

?>