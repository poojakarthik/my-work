<?php

class JSON_Handler_Developer_Permissions extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function userHasPermission($intPermission, $intEmployeeId=null)
	{
		try
		{
			$bolHasPermission	= Operation::userHasPermission($intPermission, $intEmployeeId);
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"			=> true,
							"bolHasPermission"	=> $bolHasPermission,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : null
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : null
						);
		}
	}
	
	public function assertPermission($intPermission, $bolLogErrorsOnly=false, $intEmployeeId=null)
	{
		try
		{
			Log::getLog()->log(print_r(Operation::assertPermission($intPermission, $bolLogErrorsOnly, $intEmployeeId), true));
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"			=> true,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : null
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : null
						);
		}
	}
	
	public function getDetails()
	{
		try
		{
			$qryQuery	= new Query();
			
			// Get Employees
			$strEmployeesSQL	= "SELECT * FROM Employee WHERE Archived = 0";
			$resEmployees		= $qryQuery->Execute($strEmployeesSQL);
			if ($resEmployees === false)
			{
				throw new Exception($qryQuery->Error());
			}
			else
			{
				$arrEmployees	= array();
				while ($arrEmployee = $resEmployees->fetch_assoc())
				{
					$arrEmployees[]	= objectifyArray($arrEmployee);
				}
			}
			
			// Get Operations
			$arrOperations		= array();
			$arrORMOperations	= Operation::getAll();
			foreach ($arrORMOperations as $intOperationId=>$objORMOperation)
			{
				$arrOperations[]	= $objORMOperation->toStdClass();
			}
			
			// Get Operation Profiles
			$arrOperationProfiles		= array();
			$arrORMOperationProfiles	= Operation_Profile::getAll();
			foreach ($arrORMOperationProfiles as $intOperationProfileId=>$objORMOperationProfile)
			{
				$arrOperationProfiles[]	= $objORMOperationProfile->toStdClass();
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"				=> true,
							'intCurrentEmployeeId'	=> Flex::getUserId(),
							'arrEmployees'			=> $arrEmployees,
							'arrOperations'			=> $arrOperations,
							'arrOperationProfiles'	=> $arrOperationProfiles,
							"strDebug"				=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : null
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : null
						);
		}
	}
}
?>