<?php

class JSON_Handler_ConstantGroup extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getConstantGroups($arrConstantGroups)
	{
		$arrResponse = array();
		
		try 
		{
			$arrResponse['arrConstantGroups']	= array();
			foreach ($arrConstantGroups as $strConstantGroup)
			{
				if (array_key_exists($strConstantGroup, $GLOBALS['*arrConstant']))
				{
					$arrResponse['arrConstantGroups'][$strConstantGroup]	= $GLOBALS['*arrConstant'][$strConstantGroup];
				}
				else
				{
					throw new Exception("Constant Group '{$strConstantGroup}' does not exist!");
				}
			}
			
			$arrResponse['Success']		= true;
			$arrResponse['strDebug']	= (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : '';
		}
		catch (Exception $eException)
		{
			// This is likely to be a user data validation error. Should not throw the exception.
			$arrResponse['Success'] 	= false;
			$arrResponse['Message'] 	= $eException->getMessage();
			$arrResponse['strDebug']	= (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : '';
		}
		
		return $arrResponse;
	}

}

?>
