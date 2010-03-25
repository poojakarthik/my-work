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
	
	public function getChargeTypes($bolCountOnly=false, $intLimit=0, $intOffset=0)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CREDIT_MANAGEMENT);
		
		try
		{
			if ($bolCountOnly)
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
				$intLimit	= (max($intLimit, 0) == 0) ? self::MAX_LIMIT : (int)$intLimit;
				$intLimit	= ($intLimit > self::MAX_LIMIT)? self::MAX_LIMIT : $intLimit;
				$intOffset	= ($intLimit === null) ? 0 : max((int)$intOffset, 0);
				
				// Retrieve the charges & convert response to std classes
				$aChargeTypes = Charge_Type::searchFor(null, null, $intLimit, $intOffset);
				$aStdClassChargeTypes = array();
				
				foreach ($aChargeTypes as $iId => $oChargeType)
				{
					$aStdClassChargeTypes[$iId] = $oChargeType->toStdClass();
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
}

?>