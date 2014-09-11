<?php

class JSON_Handler_Collection_Warning extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getAll()
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aRecords 	= Collection_Warning::getAll();
			$aResults	= array();
			foreach ($aRecords as $oRecord)
			{
				$aResults[$oRecord->id] = $oRecord->toStdClass();
			}
			
			return	array(
						'bSuccess'	=> true,
						'aResults'	=> $aResults
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
	
	public function createWarning($oDetails)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Validation
			$aErrors = array();
			if ($oDetails->name === '')
			{
				$aErrors[] = 'No Name was supplied';
			}
			
			if (strlen($oDetails->name) > 256)
			{
				$aErrors[] = 'Name is too long (maximum 256 chars)';
			}
			
			if ($oDetails->message === '')
			{
				$aErrors[] = 'No Message was supplied';
			}
			
			if (strlen($oDetails->message) > 1024)
			{
				$aErrors[] = 'Message is too long (maximum 1024 chars)';
			}
			
			if (count($aErrors) > 0)
			{
				return 	array(
							'bSuccess'	=> false,
							'aErrors'	=> $aErrors
						);
			}
			
			$oWarning 				= new Collection_Warning();
			$oWarning->name			= $oDetails->name;
			$oWarning->message 		= $oDetails->message;
			$oWarning->status_id	= STATUS_ACTIVE;
			$oWarning->save();
			
			return	array(
						'bSuccess'		=> true,
						'iWarningId'	=> $oWarning->id
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

class JSON_Handler_Collection_Warning_Exception extends Exception
{
	// No changes
}

?>