<?php
/**
 * Email_Queue
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Email_Queue
 */
class Email_Queue extends ORM_Cached
{
	protected 			$_strTableName			= "email_queue";
	protected static	$_strStaticTableName	= "email_queue";

	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	protected static function getMaxCacheSize()
	{
		return 100;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}
	
	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	public function getEmails()
	{
		return Email::getForQueue($this->id);
	}
	
	public function deliver($oEmailQueueBatch, $bCommitQueue=false)
	{
		// Create a queue
		$oEmailFlexQueue	= new Email_Flex_Queue();
		
		// Create the Email_Flex objects
		$aEmails	= $this->getEmails();
		foreach ($aEmails as $oEmail)
		{
			$oEmailFlex	= new Email_Flex();
			
			$aTo	= split(',', $oEmail->recipients);
			foreach ($aTo as $sTo)
			{
				$oEmailFlex->addTo($sTo);
			}
			
			$oEmailFlex->setFrom($oEmail->sender);
			$oEmailFlex->setSubject($oEmail->subject);
			$oEmailFlex->setBodyText($oEmail->text);
			if ($oEmail->html !== null)
			{
				$oEmailFlex->setBodyHtml($oEmail->html);
			}
			
			// Add attachments
			$aAttachments	= $oEmail->getAttachments();
			foreach ($aAttachments as $oAttachment)
			{
				// Defaults for nullable fields
				$sDisposition	= ($oAttachment->disposition === null	? Zend_Mime::DISPOSITION_ATTACHMENT : $oAttachment->disposition);
				$sEncoding		= ($oAttachment->encoding === null 		? Zend_Mime::ENCODING_BASE64 		: $oAttachment->encoding);
				
				// Create/add the attachment
				$oEmailFlex->createAttachment($oAttachment->content, $oAttachment->mime_type, $sDisposition, $sEncoding, $oAttachment->filename);
			}
			
			// Add the email to the queue
			$oEmailFlexQueue->push($oEmailFlex, $oEmail->id);
		}
		
		if ($bCommitQueue)
		{
			// Commit the queue
			$oEmailFlexQueue->commit();
		}
		
		// Send the queue
		$oEmailFlexQueue->send();
		
		// Update the status of the queued emails
		$aEmailFlexes	= $oEmailFlexQueue->getEmails();
		foreach ($aEmailFlexes as $iEmailId => $oEmailFlex)
		{
			$oEmail			= $aEmails[$iEmailId];
			$mStatus		= $oEmailFlex->getSendStatus();
			$iEmailStatus	= Email_Status::EMAIL_STATUS_AWAITING_SEND;
			if ($mStatus === Email_Flex::SEND_STATUS_SENT)
			{
				// Email sent
				$iEmailStatus	= Email_Status::EMAIL_STATUS_AWAITING_SEND;
			}
			else if ($mStatus === Email_Flex::SEND_STATUS_FAILED)
			{
				// Email sending failed
				$iEmailStatus	= Email_Status::EMAIL_STATUS_SENDING_FAILED;
			}
			else if ($mStatus === Email_Flex::SEND_STATUS_NOT_SENT)
			{
				// Not sent
				$iEmailStatus	= Email_Status::EMAIL_STATUS_NOT_SENT;
			}
			$oEmail->setStatus($iEmailStatus);
		}
		
		// Update the queues delivery datetime and batch reference
		$oEmailFlexQueue->delivered_datetime	= date('Y-m-d H:i:s');
		$oEmailFlexQueue->email_queue_batch_id	= $oEmailQueueBatch->id;
		$oEmailFlexQueue->save();
	}
	
	public static function getWaitingQueues()
	{
		$oStmt	= self::_preparedStatement('selAllWaiting');
		$oStmt->Execute();
		$aQueues	= array();
		while($aRow = $oStmt->Fetch())
		{
			$aQueues[$aRow['id']]	= new self($aRow);
		}
		return $aQueues;
	}
	
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;
				case 'selAllWaiting':
					$arrPreparedStatements[$strStatement]	= 	new StatementSelect(
																	self::$_strStaticTableName, 
																	"*", 
																	"	email_queue_batch_id IS NULL 
																	AND	delivered_datetime IS NULL 
																	AND	scheduled_datetime <= NOW()", 
																	"created_datetime ASC"
																);
					break;
					
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>