<?php

class Ticketing_Import_MailServer extends Ticketing_Import {
	protected $_oEmailStorage;

	public function __construct($oTicketingConfig, $bLoggingEnabled, $oEmailStorage) {
		parent::__construct($oTicketingConfig, $bLoggingEnabled);
		$this->_oEmailStorage = $oEmailStorage;
	}

	public function import() {
		$oDataAccess = DataAccess::getDataAccess();
		$oDataAccess->TransactionStart(false);
		try {
			// Find out how many emails there are
			Log::get()->logIf($this->_bLoggingEnabled, "[*] Getting list of emails");
			$aUniqueMessageIds = $this->_oEmailStorage->getUniqueId();
			$iMessages = count($aUniqueMessageIds);
			if (!$iMessages) {
				Log::get()->logIf($this->_bLoggingEnabled, "[*] No messages");
				return 0;
			} else {
				Log::get()->logIf($this->_bLoggingEnabled, "[*] Number of messages: {$iMessages}");
			}

			$aReadMessageIds = array();
			$aProcessedMessageIds = array();
			foreach($aUniqueMessageIds as $sUniqueId) {
				$iMessageNumber = $this->_oEmailStorage->getNumberByUniqueId($sUniqueId);
				$aReadMessageIds[] = $iMessageNumber;
				$oMessage = $this->_oEmailStorage->getMessage($iMessageNumber);
				Log::get()->logIf($this->_bLoggingEnabled, "[*] Message {$sUniqueId} (Message #: {$iMessageNumber})");
				if (!$oMessage->hasFlag(Zend_Mail_Storage::FLAG_SEEN)) {
					Log::get()->logIf($this->_bLoggingEnabled, "[*] Unread");

					// Process the email
					// Get the details of the email
					$aParts = array('sMessage' => null, 'aAttachments' => array());
					$this->_extractEmailParts($aParts, $oMessage);

					// Create a piece of ticketing correspondance
					$sNow = DataAccess::getDataAccess()->getNow();
					$aRecipients = $this->_extractRecipients($oMessage);
					$aFrom = $this->_extractAddressParts($oMessage->from);
					$aDetails = array(
						'subject' => $oMessage->subject,
						'message' => $aParts['sMessage'],
						'to' => array(),
						'cc' => array(),
						'bcc' => array(),
						'from' => array(
							'address' => $aFrom['sAddress'],
							'name' => $aFrom['sName']
						),
						'attachments' => array(),
						'delivery_status' => TICKETING_CORRESPONDANCE_DELIVERY_STATUS_RECEIVED, // Set delivery status to received (this is inbound)
						'source_id' => TICKETING_CORRESPONDANCE_SOURCE_EMAIL, // XML files originate from emails
						'creation_datetime' => $sNow,
						'delivery_datetime' => $sNow, // Set delivery time (to system) same as creation time (now)
						'user_id' => null // Not required for inbound correspondance
					);

					// Add recipient addresses
					foreach ($aRecipients as $sType => $aTypedRecipients) {
						foreach ($aTypedRecipients as $aRecipient) {
							$aDetails[$sType][] = $aRecipient['sAddress'];
						}
					}

					// Add attachments
					foreach ($aParts['aAttachments'] as $aAttachment) {
						$aDetails['attachments'][] = array(
							'name' => $aAttachment['sName'],
							'type' => $aAttachment['sContentType'],
							'data' => $aAttachment['sContent']
						);
					}

					//Log::get()->logIf($this->_bLoggingEnabled, "[*] Details: ".var_export($aDetails, true));
		
					// Load the details into the ticketing system
					$oCorrespondance = Ticketing_Correspondance::createForDetails($aDetails, $this->_bLoggingEnabled);

					// If a correspondence was created...
					if ($oCorrespondance) {
						// Acknowledge receipt of the correspondence
						Log::get()->logIf($this->_bLoggingEnabled, "[+] Correspondance #{$oCorrespondance->id} created");
						$oCorrespondance->acknowledgeReceipt();
					} else {
						throw new Exception("No Ticket Correspondance was created");
					}
				} else {
					Log::get()->logIf($this->_bLoggingEnabled, "[*] Already read");
				}

				$aProcessedMessageIds[] = $iMessageNumber;
				$this->_oEmailStorage->setFlags($iMessageNumber, array(Zend_Mail_Storage::FLAG_SEEN));
			}

			// Archive all processed messages
			$this->_archiveMessages($aProcessedMessageIds);
		} catch (Exception $oEx) {
			// Something went wrong, rollback all changes
			$oDataAccess->TransactionRollback(false);

			// Set all read messages as unseen
			foreach ($aReadMessageIds as $iMessageNumber) {
				$this->_oEmailStorage->setFlags($iMessageNumber, array(Zend_Mail_Storage::FLAG_RECENT));
			}

			throw $oEx;
		}

		$oDataAccess->TransactionCommit(false);
	}

	protected function _extractEmailParts(&$aParts, $oMessagePart) {
		// Find out if the message part is multipart
		if ($oMessagePart->isMultipart()) {
			Log::get()->logIf($this->_bLoggingEnabled, "[*] Multi part");
			foreach ($oMessagePart as $oChildMessagePart) {
				$this->_extractEmailParts($aParts, $oChildMessagePart);
			}
		} else {
			$aHeaders = $oMessagePart->getHeaders();
			$sContentType = strtok($oMessagePart->contentType, ';');
			$sContentTransferEncoding = (isset($aHeaders['content-transfer-encoding']) ? $oMessagePart->getHeader('content-transfer-encoding') : null);
			Log::get()->logIf($this->_bLoggingEnabled, "[*] Single part (ContentType: {$sContentType}; Encoding: {$sContentTransferEncoding})");
			if (isset($aHeaders['content-description'])) {
				Log::get()->logIf($this->_bLoggingEnabled, "[*] Attachment: Type 1");
				$aParts['aAttachments'][] = array(
					'sContentType' => $sContentType,
					'sName' => $oMessagePart->getHeader('content-description'), 
					'sContent' => $this->_encodePartContent($sContentTransferEncoding, $oMessagePart->getContent())
				);
			} else {
				switch ($sContentType) {
					case 'text/plain':
						$aParts['sMessage'] = htmlentities($this->_encodePartContent($sContentTransferEncoding, $oMessagePart->getContent()));
						Log::get()->logIf($this->_bLoggingEnabled, "[*] Text/plain ({$sContentTransferEncoding}): ".$aParts['sMessage']);
						break;
					case 'text/html':
						$aParts['sMessage'] = $this->_processHTMLContent($this->_encodePartContent($sContentTransferEncoding, $oMessagePart->getContent()));
						Log::get()->logIf($this->_bLoggingEnabled, "[*] Text/html ({$sContentTransferEncoding}): ".$aParts['sMessage']);
						break;
					default:
						Log::get()->logIf($this->_bLoggingEnabled, "[*] Attachment: Type 2, headers: ".var_export($aHeaders, true));

						// Determine the name of the attachment file (there is not content-description header so check content-type and content-disposition)
						preg_match('/; name="(.*)"$/', $oMessagePart->contentType, $aContentTypeMatch);
						$sFilename = null;
						if (isset($aContentTypeMatch[1])) {
							// The filename was in the content type
							$sFilename = $aContentTypeMatch[1];
						} else {
							// Check the content-disposition header
							if (isset($aHeaders['content-disposition'])) {
								preg_match('/; filename="(.*)"$/', $aHeaders['content-disposition'], $aContentDispositionMatch);
								if (isset($aContentDispositionMatch[1])) {
									// The filename was in the content disposition
									$sFilename = $aContentDispositionMatch[1];
								}
							}
						}

						if ($sFilename === null) {
							throw new Exception("Failed to determine filename of attachment. Headers: ".var_export($aHeaders, true));
						}

						$aParts['aAttachments'][] = array(
							'sContentType' => $sContentType,
							'sName' => $sFilename, 
							'sContent' => $this->_encodePartContent($sContentTransferEncoding, $oMessagePart->getContent())
						);
						break;
				}
			}
		}
	}

	protected function _archiveMessages($aMessageIdsToArchive) {
		$sArchiveFolder = $this->_oTicketingConfig->archive_folder_name;
		if ($sArchiveFolder === null) {
			throw new Exception("No archive folder name defined in ticketing_config, cannot use ".get_class($this));
		}
		try {
			Log::get()->logIf($this->_bLoggingEnabled, "[*] Changing to folder '{$sArchiveFolder}'");
			$this->_oEmailStorage->selectFolder($sArchiveFolder);
			Log::get()->logIf($this->_bLoggingEnabled, "[*] Successfully changed folder, changing back to the INBOX");
			$this->_oEmailStorage->selectFolder('INBOX');
		} catch(Zend_Mail_Storage_Exception $oException) {
			Log::get()->logIf($this->_bLoggingEnabled, "[!] No folder with name '{$sArchiveFolder}', creating it");
			$this->_oEmailStorage->createFolder($sArchiveFolder);
		}

		// Move all messages to the folder
		$aCopiedMessageIds = array();
		try {
			foreach ($aMessageIdsToArchive as $sMessageId) {
				Log::get()->logIf($this->_bLoggingEnabled, "[*] Copying message {$sMessageId}");
				$this->_oEmailStorage->copyMessage($sMessageId, $sArchiveFolder);
				$aCopiedMessageIds[] = $sMessageId;
			}
		} catch (Exception $oEx) {
			// Failed to copy a message, move all of the copied messages back to the INBOX
			foreach ($aCopiedMessageIds as $sMessageId) {
				Log::get()->logIf($this->_bLoggingEnabled, "[*] Copying message {$sMessageId} back to INBOX");
				$this->_oEmailStorage->copyMessage($sMessageId, 'INBOX');
			}

			throw $oEx;
		}

		// All messages copied to the archive folder, remove them from the inbox
		foreach ($aMessageIdsToArchive as $sMessageId) {
			Log::get()->logIf($this->_bLoggingEnabled, "[*] Removing message {$sMessageId}");
			$this->_oEmailStorage->removeMessage($sMessageId);
		}
	}

	protected function _encodePartContent($sEncoding, $sContent) {
		$sEncodedContent = '';
		switch ($sEncoding) {
			case 'quoted-printable':
				$sEncodedContent = quoted_printable_decode($sContent);
			break;
			case 'base64':
				$sEncodedContent = base64_decode($sContent);
			break;
			default:
				$sEncodedContent = $sContent;
			break;
		}
		return $sEncodedContent;
	}

	protected function _extractAddressParts($sAddress) {
		$sName = null;
		if (preg_match('/"?([^"]*)"?<([^>]*)>/', $sAddress, $aAddressParts)) {
			// The address contains the name of the owner
			$sName = $aAddressParts[1];
			$sAddress = $aAddressParts[2];
		}
		
		return array(
			'sName' => trim($sName), 
			'sAddress' => $sAddress
		);
	}

	protected function _processHTMLContent($sHTML) {
		return Email_Template_Logic::toText(utf8_encode($sHTML));
	}

	protected function _extractRecipients($oMessage) {
		$aHeaders = $oMessage->getHeaders();
		return array(
			'to' => $this->_parseAddressList($oMessage->to),
			'cc' => $this->_parseAddressList((isset($aHeaders['cc']) ? $oMessage->cc : '')),
			'bcc' => $this->_parseAddressList((isset($aHeaders['bcc']) ? $oMessage->bcc : '')),
		);
	}

	protected function _parseAddressList($sRecipients) {
		$aAddresses = array();
		if (!empty($sRecipients)) {
			Log::get()->log("[*] Parsing address list: {$sRecipients}");
			if (preg_match('/,/', $sRecipients)) {
				// Split by comma
				Log::get()->log("[*] Split by comma");
				$aRecipients = explode(',', $sRecipients);
			} else if (preg_match('/;/', $sRecipients)) {
				// Split by semicolon
				Log::get()->log("[*] Split by semicolon");
				$aRecipients = explode(';', $sRecipients);
			} else {
				// Must only be one address
				Log::get()->log("[*] Single address");
				$aRecipients = array($sRecipients);
			}

			foreach ($aRecipients as $sRecipient) {
				Log::get()->log("[*] Extracting parts from individual recipient: {$sRecipient}");
				$aAddresses[] = $this->_extractAddressParts(trim($sRecipient));
			}
		}
		return $aAddresses;
	}
}

?>