<?php

class JSON_Handler_Recurring_Charge_Type extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	const MAX_LIMIT = 100;
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getAll($bCountOnly=false, $iLimit=0, $iOffset=0)
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
							"intRecordCount"	=> Recurring_Charge_Type::searchFor(null, null, null, null, true),
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
				$aRecurringChargeTypes = Recurring_Charge_Type::searchFor(null, null, $iLimit, $iOffset);
				$aStdClassChargeTypes = array();
				
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
						$aStdClassRecurringChargeTypes[$iId]->recursion = "Every {$oRecurringChargeType->RecurringFreq} {$sFreqType}".($oRecurringChargeType->RecurringFreq > 1 ? 's' : '');
					}
					
					// Add archived property (string)
					$aStdClassRecurringChargeTypes[$iId]->archived_label = ($oRecurringChargeType->Archived) ? 'Archived' : 'Active';
				}
				
				$oPaginationDetails = Recurring_Charge_Type::getLastSearchPaginationDetails();
				
				// If no exceptions were thrown, then everything worked
				return array(
							"Success"			=> true,
							"arrRecords"		=> $aStdClassRecurringChargeTypes,
							"intRecordCount"	=> ($oPaginationDetails !== null)? $oPaginationDetails->totalRecordCount : count($aStdClassRecurringChargeTypes),
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
	
	public function archive($iRecurringChargeTypeId)
	{
		try
		{
			$oRecurringChargeType 			= Recurring_Charge_Type::getForId((int)$iRecurringChargeTypeId);
			$oRecurringChargeType->Archived	= 1;
			$oRecurringChargeType->save();
			
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
	
	public function save($oRecurringChargeTypeDetails)
	{
		try
		{
			// Create a recurring charge type object
			if ($oRecurringChargeTypeDetails->iId)
			{
				// Details have id, must be an update
				$oRecurringChargeType = Recurring_Charge_Type::getForId($oRecurringChargeTypeDetails->iId);
			}
			else
			{
				// No id given, must be a new object
				$oRecurringChargeType 				= new Recurring_Charge_Type();
				$oRecurringChargeType->Archived 	= 0;
				$oRecurringChargeType->PlanCharge	= 0;
			}
			
			$oRecurringChargeType->ChargeType 			= $oRecurringChargeTypeDetails->sChargeType;
			$oRecurringChargeType->Description 			= $oRecurringChargeTypeDetails->sDescription;
			$oRecurringChargeType->Nature 				= $oRecurringChargeTypeDetails->sNature;
			$oRecurringChargeType->Fixed 				= (int)$oRecurringChargeTypeDetails->bFixed;
			$oRecurringChargeType->RecurringFreqType	= $oRecurringChargeTypeDetails->iRecurringFreqType;
			$oRecurringChargeType->RecurringFreq		= $oRecurringChargeTypeDetails->iRecurringFreq;
			$oRecurringChargeType->MinCharge			= $oRecurringChargeTypeDetails->fMinCharge;
			$oRecurringChargeType->RecursionCharge		= $oRecurringChargeTypeDetails->fRecursionCharge;
			$oRecurringChargeType->CancellationFee		= $oRecurringChargeTypeDetails->fCancellationFee;
			$oRecurringChargeType->Continuable			= (int)$oRecurringChargeTypeDetails->bContinuable;
			$oRecurringChargeType->UniqueCharge			= (int)$oRecurringChargeTypeDetails->bUniqueCharge;
			$oRecurringChargeType->approval_required	= $oRecurringChargeTypeDetails->iApprovalRequired;
			$oRecurringChargeType->save();
			
			return array(
						"sChargeType"	=> $oRecurringChargeType->ChargeType,
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