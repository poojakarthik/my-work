<?php

class JSON_Handler_User_Role extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getDataset($bolCountOnly=false, $intLimit=0, $intOffset=0)
	{
		try
		{
			if ($bolCountOnly)
			{
				// Count Only
				return array(
								"Success"			=> true,
								"intRecordCount"	=> self::_getDatasetLength(),
								"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
							);
			}
			else
			{
				// Include Data
				$intLimit	= (max($intLimit, 0) == 0) ? null : (int)$intLimit;
				$intOffset	= ($intLimit === null) ? null : max((int)$intOffset, 0);
				
				$qryQuery	= new Query();
				
				// Retrieve list of User Roles
				$strSelectSQL	= "SELECT * FROM user_role WHERE 1";
				$strSelectSQL	.= ($intLimit !== null) ? " LIMIT {$intLimit} OFFSET {$intOffset}" : '';
				$resEmployees	= $qryQuery->Execute($strSelectSQL);
				if ($resEmployees === false)
				{
					throw new Exception($qryQuery->Error());
				}
				$arrResultSet	= array();
				$intCount		= 0;
				while ($arrRecord = $resEmployees->fetch_assoc())
				{
					$arrResultSet[$intCount+$intOffset]	= $arrRecord;
					$intCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return array(
								"Success"			=> true,
								"arrRecords"		=> $arrResultSet,
								"intRecordCount"	=> ($intLimit === null) ? count($arrResultSet) : self::_getDatasetLength(),
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
	
	private static function _getDatasetLength()
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
}
?>