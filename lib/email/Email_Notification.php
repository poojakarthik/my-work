<?php

class Email_Notification extends Email_Flex
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
				break;
			case 'cc':
				$this->addCc($value);
				break;
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
	
	public function addTo($email, $name='')
	{
		return parent::addTo(trim($email), $name);
	}
	
	public function addCc($email, $name='')
	{
		return parent::addCc(trim($email), $name);
	}
	
	public function addBcc($email)
	{
		return parent::addBcc(trim($email));
	}
	
	public function setFrom($email, $name='')
	{
		return parent::setFrom(trim($email), $name);
	}

	// If $inCustomerGroupId is specified, then the address retreived from email_notification_address will be the ones relevent to the customer group, or any with customer_group == NULL
	// This is mostly usefull for specifying an appropriate 'from' address specific to the customer group that the email is allegedly from
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
			Log::getLog()->log("Email_Notification::send - Added ybs admin as 'to'");
			$this->addTo('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		}
		else
		{
			Log::getLog()->log("Email_Notification::send - Added ybs admin as 'bcc'");
			$this->addBcc('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		}
		
		if (!$this->_from)
		{
			Log::getLog()->log("Email_Notification::send - Added ybs admin as 'from'");
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
	// Why only Cli apps?
	const EMAIL_ATTACHMENT_NAME = 'content_type';
	const EMAIL_ATTACHMENT_MIME_TYPE = 'dfilename';
	const EMAIL_ATTACHMENT_CONTENT = 'CONTENT';

	// factory: Create and return an email notitifcation from the given parameters
	public static function factory($intEmailNotificationId, $intCustomerGroupId, $mixToEmail, $strSubject, $strBodyHTML, $strBodyText=NULL, $arrAttachments=NULL, $bolSilentFail=FALSE)
	{
		$email	= null;
		try
		{
			if (!$intEmailNotificationId)
			{
				$intEmailNotificationId = 0;
			}
			$email = new Email_Notification($intEmailNotificationId, $intCustomerGroupId);
			
			$arrToEmails = array();
			switch (gettype($mixToEmail))
			{
				case 'string':
					$arrToEmails[] = $mixToEmail;
					break;
					
				case 'array':
					$arrToEmails = $mixToEmail;
					break;
					
				default:
					break;
			}
			
			foreach ($arrToEmails as $strEmailAdress)
			{
				$email->addTo($strEmailAdress);
			}
			
			$email->setSubject($strSubject);
			if ($strBodyHTML !== NULL)
			{
				$email->setBodyHtml($strBodyHTML);
			}
			elseif ($strBodyText !== NULL)
			{
				$email->setBodyText($strBodyText);
			}
			else
			{
				$email->setBodyText("[ No content ]");
			}
			
			if ($arrAttachments)
			{
				foreach($arrAttachments as $attchment)
				{
					$email->addAttachment($attchment[self::EMAIL_ATTACHMENT_CONTENT], $attchment[self::EMAIL_ATTACHMENT_NAME], $attchment[self::EMAIL_ATTACHMENT_MIME_TYPE]);
				}
			}
		}
		catch (Exception $e)
		{
			if ($bolSilentFail)
			{
				return null;
			}
			else
			{
				throw $e;
			}
		}
		
		return $email;
	}

	/**
	 * sendEmailNotification()
	 *
	 * Wrapper function for sending an email notification
	 *
	 * Wrapper function for sending an email notification
	 *
	 * @param	int		$intEmailNotificationId		Email Notification constant, defining the type of notification to send.  This can be set to NULL to send an email with no
	 * 												predefined to's, cc's, bcc's or from addresses. Although it will use ybs-admin@ybs.net.au as the from address
	 * @param	int		$intCustomerGroupId			if specified, it will use the address from email_notification_address specific to this customer group.  Only really useful for
	 * 												chosing a CustomerGroup specific 'from' address
	 * @param	mixed	$mixToEmail					string				: single email address for the 'to' address
	 * 												array				: multiple address' for the 'to' address
	 * 												NULL or empty array	: Only the predefined 'to' address' will be used
	 * 												Note that any predefined 'to' email address' in the email_notification_address table, will also be used, even if you explicitly
	 * 												specify email address' using $mixToEmail
	 * @param	string	$strSubject					Subject for the email
	 * @param	string	$strBodyHTML				The body of the email as HTML.  If you want to use text instead of html, then set this to NULL, and use $strBodyText
	 * @param	string	[ $strBodyText ]			The body of the email as Text.  if $strBodyHTML is also set, then it will be used instead of this
	 * @param	array	[ $arrAttachments ]			defaults to NULL.  Declare attachements here, using the format
	 *												$arrAttachments[] = array(	self::EMAIL_ATTACHMENT_CONTENT		=> file content,
	 *																			self::EMAIL_ATTACHMENT_NAME			=> filename,
	 *																			self::EMAIL_ATTACHMENT_MIME_TYPE	=> mime type
	 *																		)
	 * @param	bool	[ $bolSilentFail ]			Defaults to False. If TRUE then it will return FALSE on failure. If FALSE, then it will throw an exception on failure.
	 *
	 * @return	bool								TRUE on success, FALSE on failure (so long as $bolSilentFail == TRUE)
	 *
	 * @static
	 * @method
	 */
	public static function sendEmailNotification($intEmailNotificationId, $intCustomerGroupId, $mixToEmail, $strSubject, $strBodyHTML, $strBodyText=NULL, $arrAttachments=NULL, $bolSilentFail=FALSE)
	{
		try
		{
			$oEmailNotification	= self::factory($intEmailNotificationId, $intCustomerGroupId, $mixToEmail, $strSubject, $strBodyHTML, $strBodyText, $arrAttachments, $bolSilentFail);
			if ($oEmailNotification === null)
			{
				return false;
			}
			$oEmailNotification->send();
		}
		catch (Exception $e)
		{
			if ($bolSilentFail)
			{
				return FALSE;
			}
			else
			{
				throw $e;
			}
		}
		return true;
	}
}

?>