<?php

class JSON_Handler_File_Type extends JSON_Handler
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