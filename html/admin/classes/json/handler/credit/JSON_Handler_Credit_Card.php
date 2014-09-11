<?php

class JSON_Handler_Credit_Card extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function archiveCreditCard($iId)
	{
		try
		{
			$oCreditCard	= Credit_Card::getForId($iId);
			$oCreditCard->Archived	= 1;
			$oCreditCard->save();
			
			return 	array(
						"Success"			=> true,
						"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : '',
					);
		}
		catch (JSON_Handler_Credit_Card_Exception $oException)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage(),
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database',
						"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function getAllTypes()
	{
		$bGod	= Employee::getForId(Flex::getUserId());
		try
		{
			$aTypes		= Credit_Card_Type::listAll();
			$aResults	= array();
			foreach ($aTypes as $oType)
			{
				$aResults[$oType->id]	= $oType->toStdClass();
			}
			
			return	array(
						'bSuccess'			=> true, 
						'aCreditCardTypes'	=> $aResults,
						'sDebug'			=> ($bGod ? $this->_JSONDebug : '')
					);
		}
		catch(Exception $oException) 
		{
			return	array(
						'bSuccess'	=> false, 
						'sMessage'	=> ($bGod ? $oException->getMessage() : 'There was an error accessing the server'),
						'sDebug'	=> ($bGod ? $this->_JSONDebug : '')
					);
		}
	}
}

class JSON_Handler_Credit_Card_Exception extends Exception
{
	// No changes
}

?>