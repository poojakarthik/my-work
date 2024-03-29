<?php

class JSON_Handler_Ticketing_User_Permission extends JSON_Handler
{
	protected	$_JSONDebug		= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getDataset($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null)
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
							"iRecordCount"	=> self::_getDatasetLength(),
							"strDebug"		=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $this->_JSONDebug : ''
						);
			}
			else
			{
				// Include Data
				$iLimit		= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset	= ($iLimit === null) ? null : max((int)$iOffset, 0);
				
				// Retrieve list of User Roles
				$oQuery		= new Query();
				$sSelectSQL	= "SELECT id, description as name FROM ticketing_user_permission";
				$sSelectSQL	.= ($iLimit !== null) ? " LIMIT {$iLimit} OFFSET {$iOffset}" : '';
				$oEmployees	= $oQuery->Execute($sSelectSQL);
				
				if ($oEmployees === false)
				{
					throw new Exception_Database($oQuery->Error());
				}
				
				$aResultSet	= array();
				$iCount		= 0;
				
				while ($aRecord = $oEmployees->fetch_assoc())
				{
					$aResultSet[$iCount+$iOffset]	= $aRecord;
					$iCount++;
				}
				
				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResultSet,
							"iRecordCount"	=> ($iLimit === null) ? count($aResultSet) : self::_getDatasetLength(),
							"strDebug"		=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $this->_JSONDebug : ''
						);
			}
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error getting the data.',
						"strDebug"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $this->_JSONDebug : ''
					);
		}
	}
	
	private static function _getDatasetLength()
	{
		try
		{
			$oQuery	= new Query();
			
			// Retrieve COUNT() of Employees
			$sCountSQL	= "SELECT COUNT(id) AS ticketing_user_permission_count FROM ticketing_user_permission";
			$oCount		= $oQuery->Execute($sCountSQL);
			if ($oCount === false)
			{
				throw new Exception_Database($oQuery->Error());
			}
			if ($aCount = $oCount->fetch_assoc())
			{
				return $aCount["ticketing_user_permission_count"];
			}
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error getting the data.',
						"strDebug"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $this->_JSONDebug : ''
					);
		}
		
	}
}
?>