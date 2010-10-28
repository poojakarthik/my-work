<?php

class Email_Flex_Queue
{
	private static $_aQueues	= array();
	
	const DEFAULT_QUEUE_NAME	= 'default';
	
	private $_bIsSent		= false;
	private $_aEmails		= array();
	private $_aEmailORMs	= array();
	private $_bCommited		= false;
	private $_sDebugAddress	= null;
	private	$_iAutoId		= 0;
	private $_bScheduled	= false;
	
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
		if ($this->_bScheduled)
		{
			throw new Exception("Cannot send this email queue, it has been scheduled for delivery.");
		}
		
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
	
	public function getEmailORMObjects()
	{
		return $this->_aEmailORMs;
	}	
	
	public function scheduleForDelivery($mDatetime=null, $sDescription='')
	{
		$oDA	= DataAccess::getDataAccess();
		if ($oDA->TransactionStart() === false)
		{
			throw new Exception("Failed to start database transaction.");
		}
		
		try
		{
			if ($this->_bScheduled)
			{
				throw new Exception("Cannot schedule this email queue, it has already been scheduled for delivery.");
			}
		
			// Validate date time input, convert to utc string
			$sDatetime	= null;
			if ($mDatetime === null)
			{
				$sDatetime	= date('Y-m-d H:i:s');
			}
			else if (is_numeric($mDatetime))
			{
				$sDatetime	= date('Y-m-d H:i:s', $mDatetime);
			}
			else if(strtotime($mDatetime) !== false)
			{
				$sDatetime	= $mDatetime;
			}
			
			$iLoggedInEmployee	= Flex::getUserId();
			$iLoggedInEmployee	= ($iLoggedInEmployee === null ? USER_ID : $iLoggedInEmployee);
			
			// Create a new Email_Queue
			$oEmailQueue						= new Email_Queue();
			$oEmailQueue->scheduled_datetime	= $sDatetime;
			$oEmailQueue->created_datetime		= date('Y-m-d H:i:s');
			$oEmailQueue->created_employee_id	= $iLoggedInEmployee;
			$oEmailQueue->description			= $sDescription;
			$oEmailQueue->save();
			
			// Create Email records
			$iCount	= 0;
			foreach ($this->_aEmails as $mId => $oEmailFlex)
			{
				// Create Email
				$oEmail				= new Email();
				$oEmail->recipients	= implode(',', $oEmailFlex->getRecipients());
				$oEmail->sender		= $oEmailFlex->getFrom();
				$oEmail->subject	= $oEmailFlex->getSubject();
				$oEmail->text		= $oEmailFlex->getDecodedBodyText();
				$sHTML				= $oEmailFlex->getDecodedBodyHTML();
				if ($sHTML !== false)
				{
					$oEmail->html	= $sHTML;
				}
				
				$oEmail->email_status_id		= EMAIL_STATUS_AWAITING_SEND;
				$oEmail->created_datetime		= date('Y-m-d H:i:s');
				$oEmail->created_employee_id	= $iLoggedInEmployee;
				
				// Link the email to the queue
				$oEmail->email_queue_id			= $oEmailQueue->id;
				
				$oEmail->save();
				$iCount++;
				
				// Cache the orm object, against the emails 'id'
				$this->_aEmailORMs[$mId]	= $oEmail;
				
				// Create any attachments that are in the Email
				foreach ($oEmailFlex->aAttachmentParts as $oPart)
				{
					$oEmailAttachment						= new Email_Attachment();
					$oEmailAttachment->content				= $oEmailFlex->getDecodedPartContent($oPart);
					$oEmailAttachment->mime_type			= $oPart->type;
					$oEmailAttachment->disposition			= $oPart->disposition;
					$oEmailAttachment->encoding				= $oPart->encoding;
					$oEmailAttachment->filename				= $oPart->filename;
					$oEmailAttachment->created_datetime		= date('Y-m-d H:i:s');
					$oEmailAttachment->created_employee_id	= $iLoggedInEmployee;
					
					// Check the size/length of the attachment
					if (strlen($oEmailAttachment->content) > Email_Attachment::MAX_CONTENT_LENGTH)
					{
						// Too big
						throw new Exception("Attachment '{$oPart->filename}' is to large. Part of the email addressed to '{$oEmail->recipients}'.");
					}
					
					// Link the attachment to the email
					$oEmailAttachment->email_id	= $oEmail->id;
					
					$oEmailAttachment->save();
				}
			}
			
			if ($iCount > 0)
			{
				$oDA->TransactionCommit();
				$this->_bScheduled	= true;
				return $oEmailQueue;
			}
			else
			{
				$oDA->TransactionRollback();
				return null;
			}
		}
		catch (Exception $oException)
		{
			$oDA->TransactionRollback();
			throw new Exception("Failed to schedule the queue. ".$oException->getMessage());
		}
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