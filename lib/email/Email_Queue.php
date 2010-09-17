<?php

class Email_Queue
{
	private static $_aQueues	= array();
	
	const DEFAULT_QUEUE_NAME	= 'default';
	
	private $_bIsSent		= false;
	private $_aEmails		= array();
	private $_bCommited		= false;
	private $_sDebugAddress	= null;
	private	$_iAutoId		= 0;
	
	public function push(Email_Flex $oEmail, $mId=null)
	{
		if ($mId === null)
		{
			while(isset($this->_aEmails[$this->_iAutoId]))
			{
				$this->_iAutoId++;
			}
			$mId	= $this->_iAutoId;
			$this->_iAutoId++;
		}
		$this->_aEmails[$mId]	= $oEmail;
	}
	
	public function commit()
	{
		$this->_bCommited	= true;
	}
	
	public function send()
	{
		$iFailed	= 0;
		foreach ($this->_aEmails as $oEmail)
		{
			if (!$this->_bCommited)
			{
				$oEmail->clearRecipients();
				if ($this->_sDebugAddress !== null)
				{
					$oEmail->addTo($this->_sDebugAddress);
				}
			}
			
			if (count($oEmail->getRecipients()))
			{
				try
				{
					$oEmail->send();
				}
				catch (Exception $oEx)
				{
					$iFailed++;
				}
			}
		}
		
		return $iFailed;
	}
	
	public function setDebugAddress($sAddress)
	{
		if (!EmailAddressValid($sAddress))
		{
			throw new Exception("Invalid email adddress: '{$sAddress}'");
		}
		$this->_sDebugAddress	= $sAddress;
	}
	
	public function getEmails()
	{
		return $this->_aEmails;
	}
	
	public static function get($sQueueName=self::DEFAULT_QUEUE_NAME)
	{
		if (!isset(self::$_aQueues[$sQueueName]))
		{
			self::$_aQueues[$sQueueName]	= new self();
		}
		return self::$_aQueues[$sQueueName];
	}
}

?>