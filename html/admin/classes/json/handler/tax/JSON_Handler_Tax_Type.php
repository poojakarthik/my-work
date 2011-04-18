<?php

class JSON_Handler_Tax_Type extends JSON_Handler
{
	public function getGlobalTaxType()
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oTaxType = Tax_Type::getGlobalTaxType();
			return array('bSuccess' => true, 'oTaxType' => ($oTaxType ? $oTaxType->toStdClass() : null));
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

class JSON_Handler_Tax_Type_Exception extends Exception
{
	// No changes
}

?>