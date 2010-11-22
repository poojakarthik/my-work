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
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	public function getAll($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null)
	{
		$aOperationProfiles	= Operation_Profile::getAll();
		return self::getRecords($aOperationProfiles, $bCountOnly, $iLimit, $iOffset, $oFieldsToSort);
	}

	public function getActive($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null)
	{
		$aOperationProfiles	= Operation_Profile::getAllActive();
		return self::getRecords($aOperationProfiles, $bCountOnly, $iLimit, $iOffset, $oFieldsToSort);
	}
	
	public function getRecords($aOperationProfiles, $bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null)
	{
		//
		//	NOTE: 	Sorting & Filtering is not supported by this (Dataset_Ajax) method. rmctainsh 20100527
		//
		
		try
		{
			if ($bCountOnly)
			{
				// Count Only
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> self::_getRecordCount(),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
			}
			else
			{
				$iLimit		= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset	= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aResults	= array();
				$iCount		= 0;
				
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
						
						$oStdClass->status_label	= ($oOperationProfile->isActive() ? 'Active' : 'Inactive'); 
						
						// Add to Result Set
						$aResults[$iCount + $iOffset]	= $oStdClass;
					}
					
					$iCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResults,
							"iRecordCount"	=> ($iLimit === null) ? count($aResults) : self::_getRecordCount(),
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
			}
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function save($iOperationProfileId, $sName, $sDescription, $iStatusId, $aOperationProfileIds, $aOperationIds)
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
			if (is_null($iOperationProfileId))
			{
				// Create new operation profile
				$oOperationProfile	= new Operation_Profile();
				
				// Default value, not supplied by the interface
				$oOperationProfile->status_id	= 1;
			}
			else
			{
				// Get operation profile
				$oOperationProfile	= Operation_Profile::getForId($iOperationProfileId);
				
				// Delete existing operation_profile_children records for the operation profile
				$oOperationProfile->removeChildren();
				
				// Delete existing operation_profile_operation records for the operation profile
				$oOperationProfile->removeOperations();
				
				// Store new status
				$oOperationProfile->status_id	= $iStatusId;
			}
			
			$oOperationProfile->name		= $sName;
			$oOperationProfile->description	= $sDescription;
			$oOperationProfile->save();
			
			// Add new operation_profile_children records for the operation profile
			foreach ($aOperationProfileIds as $iChildProfileId)
			{
				$oOperationProfileChildren								= new Operation_Profile_Children();
				$oOperationProfileChildren->parent_operation_profile_id	= $oOperationProfile->id;
				$oOperationProfileChildren->child_operation_profile_id	= $iChildProfileId;
				$oOperationProfileChildren->save();
			}
			
			// Add new operation_profile_operation records for the operation profile
			foreach ($aOperationIds as $iOperationId)
			{
				$oOperationProfileOperation							= new Operation_Profile_Operation();
				$oOperationProfileOperation->operation_profile_id	= $oOperationProfile->id;
				$oOperationProfileOperation->operation_id			= $iOperationId;
				$oOperationProfileOperation->save();
			}
			
			// Commit transaction
			$oDataAccess->TransactionCommit();
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						"Success"	=> true,
						"iId"		=> $oOperationProfile->id,
						'test'		=> $oOperationProfile,
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			$oDataAccess->TransactionRollback();
			
			return array(
							"Success"	=> false,
							"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error accessing the database',
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
			throw new Exception_Database($qQuery->Error());
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
		$aOperationProfiles	= Operation_Profile::getAllActive();
		
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