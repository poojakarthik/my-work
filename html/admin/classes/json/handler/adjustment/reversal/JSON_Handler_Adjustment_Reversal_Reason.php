<?php

class JSON_Handler_Adjustment_Reversal_Reason extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getAll($bActiveOnly)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aRecords = Adjustment_Reversal_Reason::getAll();
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

class JSON_Handler_Adjustment_Reversal_Reason_Exception extends Exception
{
	// No changes
}

?>