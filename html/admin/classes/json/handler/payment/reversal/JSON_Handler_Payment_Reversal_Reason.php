<?php

class JSON_Handler_Payment_Reversal_Reason extends JSON_Handler
{
	protected	$_JSONDebug	= '';

	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getAll($bActiveOnly)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aRecords = Payment_Reversal_Reason::getAll();
			$aReasons = array();
			foreach ($aRecords as $oRecord)
			{
				if (!$bActiveOnly || ($oRecord->status_id == STATUS_ACTIVE))
				{
					$aReasons[$oRecord->id] = $oRecord->toStdClass();
				}
			}
			return array('bSuccess' => true, 'aReasons' => $aReasons);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
}

class JSON_Handler_Payment_Reversal_Reason_Exception extends Exception
{
	// No changes
}

?>