<?php

class JSON_Handler_Developer_Permissions extends JSON_Handler
{
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getData() {
		return self::getTicketData();
	}
	
	public static function getTicketData() {
		// Get Data
	}
}
?>