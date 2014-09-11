<?php

class JSON_Handler_Correspondence_Delivery_Method extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getAll()
	{
		try
		{
			$aItems		= Correspondence_Delivery_Method::getAll();
			$aResults	= array();
			foreach ($aItems as $oItem)
			{
				$aResults[$oItem->id]	= $oItem->toStdClass();
			}
			
			return	array(
						'Success'	=> true,
						'aResults'	=> $aResults
					);
		}
		catch (JSON_Handler_Correspondence_Delivery_Method_Run_Exception $oException)
		{
			return 	array(
						'Success'	=> false,
						'sMessage'	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
					);
		}
	}
}

class JSON_Handler_Correspondence_Delivery_Method_Exception extends Exception
{
	// No changes
}

?>