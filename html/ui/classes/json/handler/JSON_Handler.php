<?php

class JSON_Handler {
	const	LOG_COOKIE_NAME						= 'json_handler_log_enabled';
	const	LOG_NAME							= 'JSON_Handler_Debug_Log';
	const	LOG_STRING_RESPONSE_PROPERTY_NAME	= 'sDebug';
	const	GENERIC_EXCEPTION_MESSAGE			= 'There was an error accessing the database. Please contact YBS for more assistance.';
		
	protected	$_sJSONDebug = '';
	
	public function invokeHandlerMethod($sMethod, $aArgs) {
		// Determine criteria for logging & automatic exception handling
		$aInterfaces	= class_implements($this);
		$bIsLoggable 	= isset($aInterfaces['JSON_Handler_Loggable']);
		$bIsCatchable	= isset($aInterfaces['JSON_Handler_Catchable']);
		try {
			$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		} catch (Exception $oEx) {
			$bUserIsGod = false;
		}
		$bLogCookieSet	= (isset($_COOKIE[self::LOG_COOKIE_NAME]) && ((int)$_COOKIE[self::LOG_COOKIE_NAME] == 1));
		$bLogEnabled	= ($bIsLoggable && $bUserIsGod && $bLogCookieSet);
		
		if ($bLogEnabled) {
			// Setup a default log to be stored in the protected _sJSONDebug instance variable
			Log::registerLog(self::LOG_NAME, Log::LOG_TYPE_STRING, $this->_sJSONDebug);
			Log::setDefaultLog(self::LOG_NAME);
		}
		
		// Invoke the method and get the return value (the response)
		try {
			$mResponse 	= call_user_func_array(array(0 => $this, 1 => $sMethod), $aArgs);
			$aResponse	= (!$mResponse ? array() : $mResponse);
		} catch (Exception $oException) {
			if ($bIsCatchable) {
				// The handler implements Catchable, build an exception response
				$aResponse = self::_buildExceptionResponse($oException);
			} else {
				// Non-catchable exceptions should be passed through
				throw $oException;
			}
		}
		
		if ($bLogEnabled) {
			// Add the logging output to the response
			$aResponse[self::LOG_STRING_RESPONSE_PROPERTY_NAME] = $this->_sJSONDebug;
		} else if ($bUserIsGod && $bLogCookieSet && is_array($aResponse) && isset($aResponse['strDebug']) && ($aResponse['strDebug'] != '')) {
			// The JSON handler doesn't implement the logging interface however an old style of logging is used,
			// send it back as though it were a new piece of logging
			$aResponse[self::LOG_STRING_RESPONSE_PROPERTY_NAME] = $aResponse['strDebug'];
		}
		
		return $aResponse;
	}
	
	protected static function _buildExceptionResponse($oException) {
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		
		// Determine the exception message
		$aExceptionInterfaces 	= class_implements($oException);
		$mData					= null;
		if (isset($aExceptionInterfaces['JSON_Handler_Exception'])) {
			// JSON_Handler_Exception exceptions have a friendly (non-god users) and detailed (god users) message
			$sMessage 	= ($bUserIsGod ? $oException->getDetailedMessage() : $oException->getFriendlyMessage());
			$mData		= $oException->getData();
		} else {
			// Not a json handler exception, show the message if god
			$sMessage = ($bUserIsGod ? $oException->getMessage() : self::GENERIC_EXCEPTION_MESSAGE);
		}
		
		// Determine the inheritance hierarchy for the exception
		$aClasses	= array();
		$sClass 	= get_class($oException);
		while ($sClass !== false) {
			$aClasses[]	= $sClass;
			$sClass 	= get_parent_class($sClass);
		}
		
		return array(
			'oException' => array(
				'sMessage'		=> $sMessage,
				'aClasses'		=> $aClasses,
				'aStackTrace'	=> ($bUserIsGod ? $oException->getTrace() : null),
				'mData'			=> $mData
			)
		);
	}
}

?>
