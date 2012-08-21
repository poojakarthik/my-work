<?php
/*
 * This Exception should be thrown when an assertion fails
 * That is to say, if something happens that should NEVER happen (indicating a bug in the system), this Exception should be used.
 * Instanciating an Exception_Assertion, will automatically send an EMAIL_NOTIFICATION_ALERT email to all predefined recipients of this email notification type
 */
class Exception_Assertion extends Exception {
	public function __construct($sMessage=null, $sExtraDetails=null, $sAssertionName=null)
	{
		parent::__construct($sMessage);
		
		// Send an EMAIL_NOTIFICATION_ALERT email
		if (!strlen($sMessage)) {
			$sMessage = "[ No Message ]";
		}
		
		$sDetails = $sMessage;
		if ($sExtraDetails) {
			$sDetails .= "\n\nFurther Details:\n"
						. (is_string($sExtraDetails) ? $sExtraDetails : print_r($sExtraDetails, true));
		}
		
		$sEmailSubject = "Assertion Failed";
		if (strlen($sAssertionName)) {
			$sEmailSubject .= " - {$sAssertionName}";
		} elseif () {
			$sEmailSubject .= " - {$sMessage}";
		}
		
		Flex::sendEmailNotificationAlert($sEmailSubject, $sDetails, false, true);
	}
}