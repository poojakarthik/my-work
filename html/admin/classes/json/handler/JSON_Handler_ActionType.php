<?php

class JSON_Handler_ActionType extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getForId($intActionTypeId)
	{
		try
		{
			$objActionType			= new Action_Type(array('id'=>$intActionTypeId), true);
			$objActionTypeStdClass	= $objActionType->toStdClass();
			
			$objActionTypeStdClass->arrAssociationTypes	= array_keys($objActionType->getAllowableActionAssociationTypes());
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"		=> true,
							"objActionType"	=> $objActionTypeStdClass,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
}
?>