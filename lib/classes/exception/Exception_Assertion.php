<?php
/*
 * This Exception should be thrown when an assertion fails
 * That is to say, if something happens that should NEVER happen (indicating a bug in the system), this Exception should be used.
 * Instanciating an Exception_Assertion, will automatically send an EMAIL_NOTIFICATION_ALERT email to all predefined recipients of this email notification type
 */
class Exception_Assertion extends Exception {
	protected $_mDebug;

	public function __construct($sMessage=null, $mDebugData=null, $sAssertionName=null) {
		parent::__construct($sMessage);

		// Send an EMAIL_NOTIFICATION_ALERT email
		$sDetails = strlen($sMessage) ? $sMessage : "[ No Message ]";

		$this->_mDebug = $mDebugData;
		if ($this->getDebug() !== null) {
			$sDetails .= "\n\nFurther Details:\n" . $this->getDebugAsString();
		}

		$sEmailSubject = "Assertion Failed";
		if (strlen($sAssertionName)) {
			$sEmailSubject .= " - {$sAssertionName}";
		} elseif (strlen($sMessage)) {
			$sEmailSubject .= " - {$sMessage}";
		}

		Flex::sendEmailNotificationAlert(
			$sEmailSubject,
			$sDetails,
			false,
			true
		);
	}

	public function getDebug() {
		return $this->_mDebug;
	}

	public function getDebugAsString() {
		if ($this->_mDebug === null) {
			return null;
		}
		if (is_string($this->_mDebug)) {
			return $this->_mDebug;
		}
		return print_r($this->_mDebug, true);
	}

	public function __toString() {
		$sStringValue = parent::__toString();
		if ($this->getDebug() !== null) {
			$sStringValue .= "\nFurther Details:\n" . $this->getDebugAsString();
		}
		return $sStringValue;
	}
}