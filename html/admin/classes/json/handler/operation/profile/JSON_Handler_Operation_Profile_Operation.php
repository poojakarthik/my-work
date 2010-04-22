<?php

class JSON_Handler_Operation_Profile_Operation extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}

	public function getAll()
	{
		try
		{
			$aOperationProfiles	= Operation_Profile::getAllActive();
			$aResult			= array();
			
			foreach ($aOperationProfiles as $iId => $oOperationProfile)
			{
				// Get child operations for the operation profile
				$aOperations	= $oOperationProfile->getChildOperations();
				$aResult[$iId]	= array();
				
				foreach ($aOperations as $iOperationId => $oOperation)
				{
					$aResult[$iId][]	= $iOperationId;
				}
			}
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						"Success"		=> true,
						"aOperations"	=> $aResult,
						"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? 'ERROR: '.$e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? $this->_JSONDebug : ''
					);
		}
	}
}
?>