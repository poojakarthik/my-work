<?php

class JSON_Handler_Email_Notification extends JSON_Handler
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
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aEmailNotifications = Email_Notification::getAll();
			return	array(
						'bSuccess'				=> true,
						'aEmailNotifications'	=> $aEmailNotifications,
						'sDebug'				=> ($bUserIsGod ? $this->_JSONDebug : '')
					);
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

class JSON_Handler_Email_Notification_Exception extends Exception
{
	// No changes
}

?>