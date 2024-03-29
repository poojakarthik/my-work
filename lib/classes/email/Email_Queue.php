<?php
class Email_Queue extends ORM_Cached {
	protected $_strTableName = "email_queue";
	protected static $_strStaticTableName = "email_queue";

	protected static function getCacheName() {
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName)) {
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	protected static function getMaxCacheSize() {
		return 100;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	// START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache() {
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects() {
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mixObjects) {
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false) {
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}
	
	public static function getAll($bolForceReload=false) {
		return parent::getAll($bolForceReload, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	// END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	public function getEmails() {
		return Email::getForQueue($this->id);
	}
	
	public function deliver($bTestMode=true, $mDebugAddress=null) {
		// Check if this queue has already been delivered
		if ($this->delivered_datetime !== null) {
			throw new Exception("This queue, '{$this->id}', has already been delivered."); 
		}
		
		$bCommitQueue = $bTestMode === false;
		$sDescription = ($this->description !== '' ? $this->description : '[no description]');
		
		Log::getLog()->log("----------------------------");
		Log::getLog()->log("Delivering queue {$this->id} '{$sDescription}', committing = ".($bCommitQueue ? 'YES' : 'NO'));
		
		// Create a queue
		$oEmailFlexQueue = new Email_Flex_Queue();
		if ($bTestMode && ($mDebugAddress !== null) && EmailAddressValid($mDebugAddress)) {
			$oEmailFlexQueue->setDebugAddress($mDebugAddress);
		}
		
		// Create the Email_Flex objects
		$aEmails = $this->getEmails();
		foreach ($aEmails as $oEmail) {
			Log::getLog()->log("Email: {$oEmail->id}");
			
			$oEmailFlex = new Email_Flex();
			
			$aTo = explode(',', $oEmail->recipients);
			foreach ($aTo as $sTo) {
				Log::getLog()->log("\trecipient: $sTo");
				$oEmailFlex->addTo($sTo);
			}
			
			Log::getLog()->log("\tsender: $oEmail->sender");
			$oEmailFlex->setFrom($oEmail->sender);
			$oEmailFlex->setSubject($oEmail->subject);
			$oEmailFlex->setBodyText($oEmail->text);
			if ($oEmail->html !== null) {
				$oEmailFlex->setBodyHtml($oEmail->html);
			}
			
			// Add attachments
			$aAttachments = $oEmail->getAttachments();
			foreach ($aAttachments as $oAttachment) {
				// Defaults for nullable fields
				$sDisposition = ($oAttachment->disposition === null ? Zend_Mime::DISPOSITION_ATTACHMENT : $oAttachment->disposition);
				$sEncoding = ($oAttachment->encoding === null ? Zend_Mime::ENCODING_BASE64 : $oAttachment->encoding);
				
				Log::getLog()->log("\tattachment: $oAttachment->filename ({$oAttachment->mime_type})");
				
				// Create/add the attachment
				$oEmailFlex->createAttachment($oAttachment->content, $oAttachment->mime_type, $sDisposition, $sEncoding, $oAttachment->filename);
			}
			
			Log::getLog()->log("added to queue\n");
			
			// Add the email to the queue
			$oEmailFlexQueue->push($oEmailFlex, $oEmail->id);
			
			if ($bTestMode) {
				// Break out of the loop, only send a single email from this queue in test mode
				Log::getLog()->log("Only one email is processed per queue when testing\n");
				break;
			}
		}
		
		if ($bCommitQueue) {
			Log::getLog()->log("COMMITTING QUEUE");
			
			// Commit the queue
			$oEmailFlexQueue->commit();
		}
		
		Log::getLog()->log("SENDING QUEUE");
		
		// Send the queue
		$oEmailFlexQueue->send();
		
		Log::getLog()->log("\nSend Status");
		
		// Update the status of the queued emails
		$aEmailFlexes = $oEmailFlexQueue->getEmails();
		foreach ($aEmailFlexes as $iEmailId => $oEmailFlex) {
			$oEmail = $aEmails[$iEmailId];
			$mStatus = $oEmailFlex->getSendStatus();
			$iEmailStatus = EMAIL_STATUS_AWAITING_SEND;
			$sRecipient = implode(', ', $oEmailFlex->getRecipients());
			if ($mStatus === Email_Flex::SEND_STATUS_SENT) {
				// Email sent
				$iEmailStatus = EMAIL_STATUS_SENT;
				Log::getLog()->log("\t{$iEmailId}: Sent to ($sRecipient)");
			}
			else if ($mStatus === Email_Flex::SEND_STATUS_FAILED) {
				// Email sending failed
				$iEmailStatus = EMAIL_STATUS_SENDING_FAILED;
				Log::getLog()->log("\t{$iEmailId}: Failed sending to ($sRecipient)");
			}
			else if ($mStatus === Email_Flex::SEND_STATUS_NOT_SENT) {
				// Not sent
				$iEmailStatus = EMAIL_STATUS_NOT_SENT;
				Log::getLog()->log("\t{$iEmailId}: Not sent");
			}
			
			// Only update the status if NOT in test mode
			$oEmail->setStatus($iEmailStatus); 
		}
		
		// Update the queues delivery datetime
		$this->delivered_datetime = DataAccess::getDataAccess()->getNow();
		$this->email_queue_status_id = EMAIL_QUEUE_STATUS_DELIVERED;
		$this->save();
	}
	
	public function cancel() {
		$this->email_queue_status_id = EMAIL_QUEUE_STATUS_CANCELLED;
		$this->save();
	}
	
	public static function getWaitingQueues() {
		$oStmt = self::_preparedStatement('selAllWaiting');
		$oStmt->Execute();
		$aQueues = array();
		while($aRow = $oStmt->Fetch()) {
			$aQueues[$aRow['id']] = new self($aRow);
		}
		return $aQueues;
	}
	
	public static function searchFor($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null) {
		$aAliases = array(
			'id' => "eq.id",
			'scheduled_datetime' => "eq.scheduled_datetime",
			'delivered_datetime' => "eq.delivered_datetime",
			'created_datetime' => "eq.created_datetime",
			'created_employee_id' => "eq.created_employee_id",
			'created_employee_name' => "CONCAT(e_created.FirstName, ' ', e_created.LastName)",
			'email_queue_status_id' => "eq.email_queue_status_id",
			'email_queue_status_name' => "eqs.name",
			'description' => "eq.description"
		);
		
		$sFrom = " email_queue eq
					JOIN email_queue_status eqs ON (eqs.id = eq.email_queue_status_id)
					JOIN Employee e_created ON (e_created.Id = eq.created_employee_id)";
		if ($bCountOnly) {
			$sSelect = "COUNT(eq.id) AS count";
			$sOrderBy = "";
			$sLimit = "";
		} else {
			$aSelectLines = array();
			foreach ($aAliases as $sAlias => $sClause) {
				$aSelectLines[] = "{$sClause} AS {$sAlias}";
			}
			$sSelect = implode(', ', $aSelectLines);
			$sOrderBy = Statement::generateOrderBy($aAliases, get_object_vars($oSort));
			$sLimit = Statement::generateLimit($iLimit, $iOffset);
		}
		
		$aWhere = Statement::generateWhere($aAliases, get_object_vars($oFilter));
		$sWhere = $aWhere['sClause'];
		
		$oSelect = new StatementSelect($sFrom, $sSelect, $sWhere, $sOrderBy, $sLimit);
		if ($oSelect->Execute($aWhere['aValues']) === false) {
			throw new Exception_Database("Failed to get search results. ".$oSelect->Error());
		}
		
		if ($bCountOnly) {
			$aRow = $oSelect->Fetch();
			return $aRow['count'];
		}
		
		return $oSelect->FetchAll();
	}
	
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param string $strStatement Name of the statement
	 * 
	 * @return Statement The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement) {
		static $arrPreparedStatements = array();
		if (isset($arrPreparedStatements[$strStatement])) {
			return $arrPreparedStatements[$strStatement];
		}
		else {
			switch ($strStatement) {
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", null, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;
				case 'selAllWaiting':
					$arrPreparedStatements[$strStatement] = new StatementSelect(
																	self::$_strStaticTableName, 
																	"*", 
																	"delivered_datetime IS NULL 
																	AND scheduled_datetime <= NOW()
																	AND email_queue_status_id = ".EMAIL_QUEUE_STATUS_SCHEDULED, 
																	"created_datetime ASC"
																);
					break;
					
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement] = new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement] = new StatementUpdateById(self::$_strStaticTableName);
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