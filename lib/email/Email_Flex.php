<?php

// Ensure that the Zend folder (lib) is in the incoude path
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/' . ".." . '/'));

require_once 'Zend/Mail.php';

class Email_Flex extends Zend_Mail
{
	const SEND_STATUS_NOT_SENT	= null;
	const SEND_STATUS_SENT		= true;
	const SEND_STATUS_FAILED	= false;
	
	private $_bSuccess	= null;
	
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
}

?>