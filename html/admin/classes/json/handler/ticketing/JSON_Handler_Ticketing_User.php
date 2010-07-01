<?php

class JSON_Handler_Ticketing_User extends JSON_Handler
{
	protected	$_JSONDebug		= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getDataset($bCountOnly=false, $iLimit=0, $iOffset=0, $oSort=null, $oFilter=null)
	{
		//
		//	NOTE: 	Limit, Offset, Sorting & Filtering is not supported by this (Dataset_Ajax) method
		//
		
		try
		{
			$aTicketingUsers	= Ticketing_User::listAllActive();
			if ($bCountOnly)
			{
				// Count Only
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> count($aTicketingUsers)
						);
			}
			else
			{
				$aResults	= array();
				foreach ($aTicketingUsers as $oTicketingUser)
				{
					$oStdClass					= new StdClass();
					$oStdClass->id				= $oTicketingUser->id;
					$oStdClass->employee_id		= $oTicketingUser->employeeId;
					$oStdClass->permission_id	= $oTicketingUser->permissionId;
					$oStdClass->name			= $oTicketingUser->getName();
					$oStdClass->email			= $oTicketingUser->getEmail();
					$aResults[]					= $oStdClass;
				}
				
				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResults,
							"iRecordCount"	=> count($aTicketingUsers)
						);
			}
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $e->getMessage() : 'There was an error getting the data.'
					);
		}
	}
}
?>