<?php

class JSON_Handler_Customer_Group extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getEmailTemplateHistory($iEmailTemplateId)
	{
		try
		{

			$aTemplateDetails = Email_Template_Details::getForTemplateId($iEmailTemplateId);
			return	array(
							'Success'	=> true,
							'aResults'	=> $aTemplateDetails
						);

		}
		catch(Exception $e)
		{
			return	array(
							'Success'	=> false,
							'message'	=> $e->__toString()
						);
		}
	}

	public function getAll()
	{
		try
		{
			$aItems		= Customer_Group::listAll();
			$aResults	= array();
			foreach ($aItems as $oItem)
			{
				$aResults[$oItem->id]	= $oItem->toArray();
			}

			return	array(
						'Success'	=> true,
						'bSuccess'	=> true,
						'aResults'	=> $aResults
					);
		}
		catch (JSON_Handler_Customer_Group_Run_Exception $oException)
		{
			return 	array(
						'Success'	=> false,
						'bSuccess'	=> false,
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
	
	public function setDefaultAccountClasses($hCustomerGroupDefaultAccountClassIds)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			foreach ($hCustomerGroupDefaultAccountClassIds as $iCustomerGroupId => $iDefaultAccountClassId)
			{
				Customer_Group::getForId($iCustomerGroupId)->setDefaultAccountClassId($iDefaultAccountClassId);
			}
			return array('bSuccess' => true);
		}
		catch (Exception $oEx)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
}

class JSON_Handler_Customer_Group_Exception extends Exception
{
	// No changes
}

?>