<?php

class JSON_Handler_Operation_Profile extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getForId($iOperationProfileId, $bolIncludePermissions=false)
	{
		try
		{
			// Get the Employee
			$oOperationProfile	= Operation_Profile::getForId($iOperationProfileId);
			$aOperationProfile	= $oOperationProfile->toArray();
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"			=> true,
							"oOperationProfile"	=> $aOperationProfile,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
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
	
	public function getRecords($bCountOnly=false, $iLimit=0, $iOffset=0)
	{
		try
		{
			if ($bCountOnly)
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
				$iLimit				= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset			= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aOperationProfiles	= Operation_Profile::getAll();
				$aResults			= array();
				$iCount				= 0;
				
				foreach ($aOperationProfiles as $iId => $oOperationProfile)
				{
					if ($iLimit && $iCount >= $iOffset + $iLimit)
					{
						// Break out, as there's no point in continuing
						break;
					}
					elseif ($iCount >= $iOffset)
					{
						$oStdClass	= $oOperationProfile->toStdClass();
						
						// Get list of Dependants
						$aDependants			= $oOperationProfile->getChildOperationProfiles();
						$oStdClass->aDependants	= array();
						
						foreach ($aDependants as $oDependant)
						{
							$oStdClass->aDependants[]	= $oDependant->id;
						}
						
						// Add to Result Set
						$aResults[$iCount + $iOffset]	= $oStdClass;
					}
					
					$iCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return array(
								"Success"			=> true,
								"arrRecords"		=> $aResults,
								"intRecordCount"	=> ($iLimit === null) ? count($aResults) : self::_getRecordCount(),
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
							"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? 'ERROR: '.$e->getMessage() : 'There was an error accessing the database',
							"strDebug"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $this->_JSONDebug : ''
						);
		}
	}
	
	private static function _getRecordCount()
	{
		$qQuery	= new Query();
		
		// Retrieve COUNT() of Employees
		$sCountSQL	= "SELECT COUNT(id) AS record_count FROM operation_profile WHERE 1";
		$rCount		= $qQuery->Execute($sCountSQL);
		if ($rCount === false)
		{
			throw new Exception($qQuery->Error());
		}
		if ($aCount = $rCount->fetch_assoc())
		{
			return $aCount['record_count'];
		}
	}
	
	public static function getOperationProfiles($bIncludeChildProfileReferences=false, $bIncludeOperationReferences=false)
	{
		static	$qQuery;
		$qQuery	= ($qQuery) ? $qQuery : new Query();
		
		// Get full list of Operation Profiles
		$aOperationProfiles	= Operation_Profile::getAll();
		
		// Convert to stdClasses
		$aReturn	= array();
		foreach ($aOperationProfiles as $iOperationProfileId=>$oOperationProfile)
		{
			$oStdClass	= $oOperationProfile->toStdClass();
			
			if ($bIncludeChildProfileReferences)
			{
				// Get list of child Profiles
				$aChildren						= $oOperationProfile->getChildOperationProfiles();
				$oStdClass->aOperationProfiles	= array();
				
				foreach ($aChildren as $oOperationProfile)
				{
					$oStdClass->aOperationProfiles[]	= $oOperationProfile->id;
				}
			}
			
			if ($bIncludeOperationReferences)
			{
				// Get list of Operations
				$aOperations			= $oOperationProfile->getChildOperations();
				$oStdClass->aOperations	= array();
				
				foreach ($aOperations as $oOperation)
				{
					$oStdClass->aOperations[]	= $oOperation->id;
				}
			}
			
			$aReturn[$oStdClass->id]	= $oStdClass;
		}
		
		return $aReturn;
	}
}
?>