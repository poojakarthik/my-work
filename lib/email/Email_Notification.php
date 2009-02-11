<?php

// Ensure that the Zend folder (lib) is in the incoude path
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR));

require_once 'Zend/Mail.php';

class Email_Notification extends Zend_Mail
{
	private $intEmailNotification = NULL;
	private $intCustomerGroupId = NULL;
	private $strSubject = NULL;
	private $strTextMessage = NULL;
	private $strHTMLMessage = NULL;
	private $arrAttachments = array();
	private $arrTo = array();
	private $arrAddresses = array();
	private $_cc = array();
	private $_bcc = array();

	public function __set($property, $value)
	{
		switch(strtolower($property))
		{
			case 'subject':
				$this->setSubject($value);
				break;
			case 'html':
				$this->setBodyHtml($value);
				break;
			case 'text':
				$this->setBodyText($value);
				break;
			case 'from':
				$this->setFrom($value);
				break;
			case 'to':
				$this->addTo($value);
			case 'cc':
				$this->addCc($value);
			case 'bcc':
				$this->addBcc($value);
				break;
		}
	}

	public function __get($property)
	{
		switch(strtolower($property))
		{
			case 'subject':
				return $this->getSubject();
			case 'html':
				return $this->getBodyHtml();
			case 'text':
				return $this->getBodyText();
			case 'from':
				return $this->getFrom();
		}
	}

	public function __construct($intEmailNotification=0, $intCustomerGroupId=NULL, $charset='iso-8859-1')
	{
		$this->setEmailNotification($intEmailNotification);
		$this->setCustomerGroup($intCustomerGroupId);
		$this->arrAddresses = self::getEmailAddresses($this->intEmailNotification, $this->intCustomerGroupId);

		foreach($this->arrAddresses[EMAIL_ADDRESS_USAGE_FROM] as $strEmailAddress)
		{
			$this->setFrom($strEmailAddress);
		}

		foreach($this->arrAddresses[EMAIL_ADDRESS_USAGE_TO] as $strEmailAddress)
		{
			$this->addTo($strEmailAddress);
		}

		foreach($this->arrAddresses[EMAIL_ADDRESS_USAGE_CC] as $strEmailAddress)
		{
			$this->addCc($strEmailAddress);
		}

		foreach($this->arrAddresses[EMAIL_ADDRESS_USAGE_BCC] as $strEmailAddress)
		{
			$this->addBcc($strEmailAddress);
		}

		parent::__construct($charset);
	}

	private function setEmailNotification($intEmailNotification)
	{
		$this->intEmailNotification = $intEmailNotification;
	}

	private function setCustomerGroup($intCustomerGroupId)
	{
		$this->intCustomerGroupId = $intCustomerGroupId;
	}

	public function addAttachment($mxdContent, $strName, $strMimeType, $strDisposition=Zend_Mime::DISPOSITION_ATTACHMENT, $strEncoding=Zend_Mime::ENCODING_BASE64)
	{
		$nrArgs = func_num_args();
		if ($nrArgs === 3)
		{
			$at = new Zend_Mime_Part($mxdContent);
			$at->type			= $strMimeType;
			$at->disposition	= $strDisposition;
			$at->encoding		= $strEncoding;
			$at->filename		= $strName;

			parent::addAttachment($at);
		}
		else if ($nrArgs)
		{
			return parent::addAttachment($mxdContent);
		}
	}

	public function send($transport=NULL)
	{
		if (!count($this->_to))
		{
			$this->addTo('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		}
		if (!$this->_from)
		{
			$this->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		}
		parent::send($transport);
	}

	public static function getEmailAddresses($intEmailNotification, $intCustGroupId=NULL)
	{
		$addresses = array(EMAIL_ADDRESS_USAGE_FROM => array(), EMAIL_ADDRESS_USAGE_TO => array(), EMAIL_ADDRESS_USAGE_CC => array(), EMAIL_ADDRESS_USAGE_BCC => array());

		if (!$intEmailNotification)
		{
			return $addresses;
		}

		$intEmailNotification = intval($intEmailNotification);
	
		$arrColumns = array(
			'EmailUsage' => 'email_address_usage_id',
			'EmailAddress' => 'LCASE(email_address)',
		);
		$strTables = " email_notification_address"; 
		$custWhere = '';
		if (is_int($intCustGroupId))
		{
			$custWhere = ' OR customer_group_id = ' . $intCustGroupId . ' ';
		}
		$strWhere = "email_notification_id = $intEmailNotification AND (customer_group_id IS NULL$custWhere)";
	
		/*
		// DEBUG: Output the query that gets run
		$select = array();
		foreach($arrColumns as $alias => $column) $select[] = "$column '$alias'";
		echo "\n\nSELECT " . implode(",\n       ", $select) . "\nFROM $strTables\nWHERE $strWhere\n\n";
		//*/
	
		$selEmails = new StatementSelect($strTables, $arrColumns, $strWhere);
		$result = $selEmails->Execute();
		if ($result === FALSE)
		{
			throw new Exception('Failed to find email addresses for email notification type: '. $intEmailNotification);
		}
		$emailAddresses = $selEmails->FetchAll();
		foreach ($emailAddresses as $address)
		{
			$addresses[$address['EmailUsage']][] = $address['EmailAddress'];
		}
		foreach ($addresses as $usage => $emails)
		{
			$addresses[$usage] = array_unique($emails);
		}
		foreach ($addresses[EMAIL_ADDRESS_USAGE_TO] as $email)
		{
			if (($index = array_search($email, $addresses[EMAIL_ADDRESS_USAGE_CC])) !== FALSE)
			{
				unset($addresses[EMAIL_ADDRESS_USAGE_CC][$index]);
			}
			if (($index = array_search($email, $addresses[EMAIL_ADDRESS_USAGE_BCC])) !== FALSE)
			{
				unset($addresses[EMAIL_ADDRESS_USAGE_BCC][$index]);
			}
		}
		foreach ($addresses[EMAIL_ADDRESS_USAGE_CC] as $email)
		{
			if (($index = array_search($email, $addresses[EMAIL_ADDRESS_USAGE_BCC])) !== FALSE)
			{
				unset($addresses[EMAIL_ADDRESS_USAGE_BCC][$index]);
			}
		}
	
		return $addresses;
	}


	// This is for use by the Cli applications only!
	const EMAIL_ATTACHMENT_NAME = 'content_type';
	const EMAIL_ATTACHMENT_MIME_TYPE = 'dfilename';
	const EMAIL_ATTACHMENT_CONTENT = 'CONTENT';
	/**
	 * @deprecated IMMEDIATELY - 16/07/2008
	 */
	public static function sendEmailNotification($intEmailNotification, $intCustomerGroupId, $strToEmail, $strSubject, $strHTMLMessage, $strTextMessage=NULL, $arrAttachments=NULL)
	{
		$emailNotification = new Email_Notification($intEmailNotification, $intCustomerGroupId);
		$emailNotification->addTo($strToEmail);
		$emailNotification->setSubject($strSubject);
		if ($strHTMLMessage)
		{
			$emailNotification->setBodyHtml($strHTMLMessage);
		}
		if ($strTextMessage)
		{
			$emailNotification->setBodyText($strTextMessage);
		}
		if ($arrAttachments)
		{
			foreach($arrAttachments as $attchment)
			{
				$emailNotification->addAttachment($attchment[self::EMAIL_ATTACHMENT_CONTENT], $attchment[self::EMAIL_ATTACHMENT_NAME], $attchment[self::EMAIL_ATTACHMENT_MIME_TYPE]);
			}
		}
		try
		{
			$emailNotification->send();
			return TRUE;
		}
		catch(Exception $exception)
		{
			return $exception->getMessage();
		}
	}

}

?>
