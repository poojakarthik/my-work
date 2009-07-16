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
				// Include Data
				$iLimit		= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset	= ($iLimit === null) ? null : max((int)$iOffset, 0);
				
				$qQuery	= new Query();
				
				// Retrieve list of Employees
				$sGetAllSQL	= "SELECT * FROM operation_profile WHERE 1";
				$sGetAllSQL	.= ($iLimit !== null) ? " LIMIT {$iLimit} OFFSET {$iOffset}" : '';
				$rGetAll	= $qQuery->Execute($sGetAllSQL);
				if ($rGetAll === false)
				{
					throw new Exception($qQuery->Error());
				}
				$aResults	= array();
				$iCount		= 0;
				while ($aResult = $rGetAll->fetch_assoc())
				{
					$aResults[$iCount+$iOffset]	= $aResult;
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
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
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
	
	public static function getOperationProfiles($bIncludeChildReferences=false)
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
			
			if ($bIncludeChildReferences)
			{
				// Get list of child Profiles
				$aChildren						= $oOperationProfile->getChildOperationProfiles();
				$oStdClass->aOperationProfiles	= array();
				
				foreach ($aChildren as $oOperationProfile)
				{
					$oStdClass->aOperationProfiles[]	= $oOperationProfile->id;
				}
				
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