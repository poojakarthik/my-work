<?php
/**
 * Exception_Assertion
 *
 * This Exception should be thrown when an assertion fails
 * 
 * This Exception should be thrown when an assertion fails
 * That is to say, if something happens that should NEVER happen (indicating a bug in the system), this Exception should be used.
 * Instanciating an Exception_Assertion, will automatically send an EMAIL_NOTIFICATION_ALERT email to all predefined recipients of this email notification type
 *
 * @class	Exception_Assertion
 */
class Exception_Assertion extends Exception
{
	/**
	 * __construct()
	 *
	 * Constructor
	 * 
	 * Constructor
	 * This will automatically send an EMAIL_NOTIFICATION_ALERT email to all predefined recipients of this email notification type
	 *
	 * @param	string	[ $strMessage ]			Exception Message. This will be include in the body of the EMAIL_NOTIFICATION_ALERT email, Defaults to NULL
	 * @param	string	[ $strExtraDetails ]	Defaults to NULL.  If defined then these will also be included in the body of the EMAIL_NOTIFICATION_ALERT email
	 * @param	string	[ $strAssertionName ]	Defaults to NULL.  If included, then this will form part of the subject for the EMAIL_NOTIFICATION_ALERT email
	 * 
	 * @return	void
	 * 
	 * @method
	 */
	public function __construct($strMessage=NULL, $strExtraDetails=NULL, $strAssertionName=NULL)
	{
		parent::__construct($strMessage);
		
		// Send an EMAIL_NOTIFICATION_ALERT email
		if (strlen($strMessage) == 0)
		{
			$strMessage = "[ No Message ]";
		}
		
		$strDetails = $strMessage;
		if (strlen($strExtraDetails))
		{
			$strDetails .= "\n\nFurther Details:\n$strExtraDetails";
		}
		
		$strEmailSubject = "Assertion Failed";
		if (strlen($strAssertionName))
		{
			$strEmailSubject .= " - $strAssertionName";
		}
		
		Flex::sendEmailNotificationAlert($strEmailSubject, $strDetails, FALSE, TRUE);
	}
}
?>