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
	
	public function getForId($intEmployeeId, $bolIncludePermissions=false)
	{
		try
		{
			// Get the Employee
			$objEmployee	= Employee::getForId($intEmployeeId);
			$arrEmployee	= $objEmployee->toArray();
			
			// Get the Permissions
			if ($bolIncludePermissions)
			{
				
			}
			else
			{
				$arrPermissions	= null;
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"			=> true,
							"objEmployee"		=> $arrEmployee,
							"objPermissions"	=> $arrPermissions,
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
				$strEmployeeSQL	= "SELECT * FROM Employee WHERE 1";
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
	
	public function getPermissions($iEmployeeId, $bGetForEditing=false)
	{
		try
		{
			if ($iEmployeeId)
			{
				// Get the Employee
				$objEmployee				= Employee::getForId($iEmployeeId);
				$aEmployeeOperations		= $oEmployee->getOperations();
				$aEmployeeOperationProfiles	= $oEmployee->getOperationProfiles();
			}
			else
			{
				$aEmployeeOperations		= array();
				$aEmployeeOperationProfiles	= array();
			}
			
			$aOperations		= Operation::getAll();
			$aOperationProfiles	= Operation_Profile::getAll();
			
			$aPermissionsTree	= array();
			
			$aPermissionsTree['oOperations']	= array();
			foreach ($aOperations as $iOperationId=>$oOperation)
			{
				$aPermissionsTree['oOperations'][$iOperationId]							= $oOperation->toStdClass();
				$aPermissionsTree['oOperations'][$iOperationId]->bEmployeeHasPermission	= array_key_exists($iOperationId, $aEmployeeOperations);
			}
			
			$aPermissionsTree['oOperationProfiles']	= array();
			foreach ($aOperationProfiles as $iOperationProfileId=>$oOperationProfile)
			{
				$aPermissionsTree['oOperationProfiles'][$iOperationProfileId]							= self::_buildOperationProfileTreeNode($oOperationProfile);
				$aPermissionsTree['oOperationProfiles'][$iOperationProfileId]->bEmployeeHasPermission	= array_key_exists($iOperationProfileId, $aEmployeeOperationProfiles);
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"			=> true,
							"oPermissions"		=> $aPermissionsTree,
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
	
	private static function _buildOperationProfileTreeNode(Operation_Profile $oOperationProfile)
	{
		// Prepare this node
		$oNode	= $oOperationProfile->toStdClass();
		
		$oNode->oOperations	= array();
		$aChildOperations	= $oOperationProfile->getChildOperations();
		foreach ($aChildOperations as $iOperationId=>$oOperation)
		{
			$oNode->oOperations[$iOperationId]		= $oOperation->toStdClass();
		}
		
		$oNode->oOperationProfiles	= array();
		$aChildOperationProfiles	= $oOperationProfile->getChildOperationProfiles();
		foreach ($aChildOperationProfiles as $iOperationProfileId=>$oOperationProfile)
		{
			$oNode->oOperationProfiles[$iOperationProfileId]	= self::_buildOperationProfileTreeNode($oOperationProfile);
		}
		
		return $oNode;
	}
}
?>