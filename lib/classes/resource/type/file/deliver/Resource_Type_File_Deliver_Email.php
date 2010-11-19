<?php
class Resource_Type_File_Deliver_Email extends Resource_Type_File_Deliver
{
	const	RESOURCE_TYPE	= RESOURCE_TYPE_FILE_DELIVERER_EMAIL;

	protected	$_sWrapper;

	public function connect()
	{
		return $this;
	}

	protected function _deliver($sLocalPath)
	{
		$oEmailFlex	= new Email_Flex();
		
		// Add recipients
		$aRecipients	= explode(',', $this->getConfig()->Recipients);
		foreach ($aRecipients as $sEmail)
		{
			$oEmailFlex->addTo(trim($sEmail));
		}
		
		// Subject, body, sender
		$oEmailFlex->setSubject($this->getConfig()->EmailSubject);
		$oEmailFlex->setFrom($this->getConfig()->EmailFrom);
		$oEmailFlex->setBodyText($this->getConfig()->EmailBody);
		
		// Attachment (file to deliver)
		$rFInfo		= finfo_open(FILEINFO_MIME);
		$sMimeType	= finfo_file($rFInfo, $sLocalPath);
		finfo_close($rFInfo);
		$oEmailFlex->createAttachment(
			file_get_contents($sLocalPath), 
			$sMimeType, 
			Zend_Mime::DISPOSITION_ATTACHMENT, 
			Zend_Mime::ENCODING_BASE64, 
			basename($sLocalPath)
		);
		
		// Send the email
		$oEmailFlex->send();
		
		return $this;
	}
	
	public function getDebugEmailContent($sLocalPath)
	{
		return $this->getConfig()->EmailBody;
	}

	public function disconnect()
	{
		return $this;
	}

	static public function createCarrierModule($iCarrier, $sClass=__CLASS__)
	{
		parent::createCarrierModule($iCarrier, $sClass, self::RESOURCE_TYPE);
	}

	static public function defineCarrierModuleConfig()
	{
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'Recipients'	=> array('Description' => 'Email addresses to recieve the email (comma separated)'),
			'EmailSubject'	=> array('Description' => 'Subject of the delivered email'),
			'EmailFrom'		=> array('Description' => 'The sender/reply-to address'),
			'EmailBody'		=> array('Description' => 'The body text of the delivered email')
		));
	}
}
?>