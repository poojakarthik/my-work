<?php

class JSON_Handler_File_Type extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getAll()
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aRecords = File_Type::getAll();
			foreach ($aRecords as $oRecord)
			{
				$aFileTypes[$oRecord->id] = $oRecord->toStdClass();
			}
			return	array('bSuccess' => true, 'aFileTypes' => $aFileTypes);
		}
		catch (Exception $e)
		{
			$sMessage	= $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array('bSuccess' => false, 'sMessage' => $sMessage);
		}
	}
}

class JSON_Handler_File_Type_Exception extends Exception
{
	// No changes
}

?>