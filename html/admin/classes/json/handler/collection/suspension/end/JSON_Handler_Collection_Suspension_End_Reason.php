<?php

class JSON_Handler_Collection_Suspension_End_Reason extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getAllForSuspension($iSuspensionId)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oSuspension 	= Collection_Suspension::getForId($iSuspensionId);
			$aRecords 		= Collection_Suspension_End_Reason::getForStartReason($oSuspension->collection_suspension_reason_id);
			$aReasons 		= array();
			foreach ($aRecords as $oRecord)
			{
				if ($oRecord->status_id == STATUS_ACTIVE)
				{
					$aReasons[$oRecord->id] = $oRecord->toStdClass();
				}
			}
			
			return	array(
						'bSuccess' 	=> true,
						'aReasons'	=> $aReasons
					);
		}
		catch (Exception $oEx)
		{
			return	array(
						'bSuccess' 	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.')
					);
		}
	}
}

class JSON_Handler_Collection_Suspension_End_Reason_Exception extends Exception
{
	// No changes
}

?>