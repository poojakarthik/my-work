<?php

// Ensure that the Zend folder (lib) is in the incoude path
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/' . ".." . '/'));

require_once 'Zend/Mail.php';

class Email_Flex extends Zend_Mail
{
	const SEND_STATUS_NOT_SENT	= null;
	const SEND_STATUS_SENT		= true;
	const SEND_STATUS_FAILED	= false;
	
	private	$_bSuccess			= null;
	
	public 	$aAttachmentParts	= array();
	
	public function clearRecipients()
	{
		$this->_to			= array();
		$this->_recipients	= array();
		unset($this->_headers['To']);
		unset($this->_headers['Cc']);
		unset($this->_headers['Bcc']);
	}
	
    public function send($transport = null)
    {
    	try
    	{
    		//Log::getLog()->log("\t ...sending to:\n".print_r($this->_recipients, true));
    		$mReturnVal			= parent::send($transport);
    		$this->_bSuccess	= true;
    		return $mReturnVal;
    	}
    	catch (Exception $oException)
    	{
    		$this->_bSuccess	= false;
    		throw $oException;
    	}
    }
    
    public function getSendStatus()
    {
    	return $this->_bSuccess;
    }
	
	// Override: Cache a reference to each attachment part
	public function addAttachment(Zend_Mime_Part $attachment)
    {
		$this->aAttachmentParts[]	= $attachment;
		return parent::addAttachment($attachment);
    }
    
    // getDecodedBodyText: Returns the body text of the Zend_Mail object, decoded from it's stored (encoded) state
    public function getDecodedBodyText()
    {
    	return self::getDecodedPartContent($this->getBodyText());
    }
    
    // getDecodedBodyHTML: Returns the body html of the Zend_Mail object, decoded from it's stored (encoded) state
    public function getDecodedBodyHTML()
    {
    	return self::getDecodedPartContent($this->getBodyHtml());
    }
    
    // getDecodedPartContent: Returns the content of the Zend_Mime_Part object, decoded from it's stored (encoded) state
    public static function getDecodedPartContent(Zend_Mime_Part $oPart)
    {
    	$sRawContent	= $oPart->getContent();
    	switch ($oPart->encoding)
		{
			case Zend_Mime::ENCODING_BASE64:
				$sContent	= base64_decode($sRawContent);
				break;
			case Zend_Mime::ENCODING_QUOTEDPRINTABLE:
				$sContent	= quoted_printable_decode($sRawContent);
				break;
			default:
				$sContent	= $sRawContent;
		}
		return $sContent;
    } 
}

?>