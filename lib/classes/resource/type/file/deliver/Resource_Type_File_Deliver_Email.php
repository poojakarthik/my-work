<?php
class Resource_Type_File_Deliver_Email extends Resource_Type_File_Deliver
{
	const	RESOURCE_TYPE	= RESOURCE_TYPE_FILE_DELIVERER_EMAIL;

	protected	$_sWrapper;

	public function connect()
	{
		return $this;
	}

	protected function _deliver($sLocalPath, $mCarrierModule=null)
	{
		$oEmailFlex	= new Email_Flex();
		
		// Add recipients
		$aRecipients	= explode(',', $this->getConfig()->Recipients);
		foreach ($aRecipients as $sEmail) {
			$oEmailFlex->addTo(trim($sEmail));
		}
		$aCC	= ($this->getConfig()->CC) ? explode(',', $this->getConfig()->CC) : array();
		foreach ($aCC as $sEmail) {
			$oEmailFlex->addCc(trim($sEmail));
		}
		$aBCC	= ($this->getConfig()->BCC) ? explode(',', $this->getConfig()->BCC) : array();
		foreach ($aBCC as $sEmail) {
			$oEmailFlex->addBcc(trim($sEmail));
		}
		
		// Subject, body, sender
		$oEmailFlex->setSubject($this->getConfig()->EmailSubject);
		$oEmailFlex->setFrom($this->getConfig()->EmailFrom);
		$oEmailFlex->setBodyText($this->getConfig()->EmailBody);
		
		// Attachment (file to deliver)
		$oEmailFlex->createAttachment(
			file_get_contents($sLocalPath), 
			@mime_content_type($sLocalPath),
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

	static public function createCarrierModule($iCarrier, $iCustomerGroup, $sClass=__CLASS__) {
		parent::createCarrierModule($iCarrier, $iCustomerGroup, $sClass, self::RESOURCE_TYPE);
	}

	static public function defineCarrierModuleConfig()
	{
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'Recipients'	=> array('Description' => 'Email addresses to recieve the email (comma separated)'),
			'CC'			=> array('Description' => 'Email addresses to recieve a carbon copy of the email (comma separated)'),
			'BCC'			=> array('Description' => 'Email addresses to recieve a blind carbon copy of the email (comma separated)'),
			'EmailSubject'	=> array('Description' => 'Subject of the delivered email'),
			'EmailFrom'		=> array('Description' => 'The sender/reply-to address'),
			'EmailBody'		=> array('Description' => 'The body text of the delivered email')
		));
	}
}
?>