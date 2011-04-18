<?php

class JSON_Handler_Email_Notification extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getAll()
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aEmailNotifications = Email_Notification::getAll();
			return	array(
						'bSuccess'				=> true,
						'aEmailNotifications'	=> $aEmailNotifications
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