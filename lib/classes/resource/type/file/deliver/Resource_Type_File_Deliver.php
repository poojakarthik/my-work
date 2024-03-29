<?php
abstract class Resource_Type_File_Deliver extends Resource_Type_Base
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_FILE_DELIVER;
	
	protected static $_bTestMode	= false;	
	
	abstract public function connect();
	
	abstract public function disconnect();
	
	public final function deliver($sLocalPath, $mCarrierModule=null)
	{
		Log::getLog()->log("Delivery using class ".get_class($this));
		if (Resource_Type_File_Deliver::isTestModeEnabled())
		{
			$this->_testDeliver($sLocalPath, $mCarrierModule);
		}
		else
		{
			$this->_deliver($sLocalPath, $mCarrierModule);
		}
		return $this;
	}
	
	public function getDebugEmailContent($sLocalPath, $mCarrierModule=null)
	{
		return 'File Delivery is in Test Mode. The delivered file is attached.';
	}
	
	abstract protected function _deliver($sLocalPath, $mCarrierModule=null);
	
	protected function _testDeliver($sLocalPath, $mCarrierModule=null)
	{
		if (!self::isTestModeEnabled())
		{
			throw new Exception("Cannot test file delivery if not in test mode");
		}
		
		// Send an email to ybs-admin@ybs.net.au
		$oEmailFlex	= new Email_Flex();
		$oEmailFlex->setSubject('File Delivery Test Email');
		$oEmailFlex->addTo('ybs-admin@ybs.net.au');
		$oEmailFlex->setFrom('ybs-admin@ybs.net.au');
		$oEmailFlex->setBodyText($this->getDebugEmailContent($sLocalPath, $mCarrierModule=null));
		
		// Attachment (file to deliver)
		$sMimeType	= mime_content_type($sLocalPath);
		$oEmailFlex->createAttachment(
			file_get_contents($sLocalPath), 
			$sMimeType, 
			Zend_Mime::DISPOSITION_ATTACHMENT, 
			Zend_Mime::ENCODING_BASE64, 
			basename($sLocalPath)
		);
		
		// Send the email
		$oEmailFlex->send();
	}
	
	static public function createCarrierModule($iCarrier, $iCustomerGroup, $sClassName, $iResourceType) {
		if ($iCustomerGroup !== null) {
			throw new Exception(GetConstantName(self::CARRIER_MODULE_TYPE, 'carrier_module_type')." Carrier Modules cannot be Customer Group specific");
		}
		parent::createCarrierModule($iCarrier, $iCustomerGroup, $sClassName, $iResourceType, self::CARRIER_MODULE_TYPE);
	}
	
	public static function enableTestMode()
	{
		self::$_bTestMode	= true;
	}
	
	public static function isTestModeEnabled()
	{
		return self::$_bTestMode;
	}
}
?>