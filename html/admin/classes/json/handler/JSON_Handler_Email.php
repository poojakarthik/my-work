<?php

class JSON_Handler_Email extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	
	public function getDataset($bCountOnly, $iLimit, $iOffset, $oSort, $oFilter) {
		$iRecordCount = Email::searchFor(true, $iLimit, $iOffset, $oSort, $oFilter);
		if ($bCountOnly) {
			return array('iRecordCount' => $iRecordCount);
		}
		
		$iLimit		= ($iLimit === null ? 0 : $iLimit);
		$iOffset	= ($iOffset === null ? 0 : $iOffset);
		$aData	 	= Email::searchFor(false, $iLimit, $iOffset, $oSort, $oFilter);
		$aResults	= array();
		$i			= $iOffset;
		
		foreach ($aData as $aRecord) {
			$aResults[$i] = $aRecord;
			$i++;
		}
		
		return array(
			'aRecords'		=> $aResults,
			'iRecordCount'	=> $iRecordCount
		);
	}
	
	public function getDetailsForId($iEmailId) {
		$oEmail 				= Email::getForId($iEmailId);
		$aAttachments			= $oEmail->getAttachments();
		$oStdEmail 				= $oEmail->toStdClass();
		$oStdEmail->attachments	= array();
		foreach ($aAttachments as $oAttachment) {
			$oStdEmail->attachments[] = $oAttachment->toStdClass();
		}
		
		return array('oEmail' => $oStdEmail);
	}
	
	public function sendSampleEmail($aRecipients, $iEmailId) {
		// Get email data
		$oEmail 		= Email::getForId($iEmailId);
		$aAttachments	= $oEmail->getAttachments();
		
		// Create an Email_Flex instance to send it with
		$oEmailFlex = new Email_Flex();
		$oEmailFlex->setFrom($oEmail->sender);
		$oEmailFlex->setSubject("[Flex Sample Email] {$oEmail->subject}");
		$oEmailFlex->setBodyText($oEmail->text);
		$oEmailFlex->setBodyHTML($oEmail->html);
		
		// Attachments
		foreach ($aAttachments as $oAttachment) {
			$oEmailFlex->createAttachment($oAttachment->content, $oAttachment->mime_type, $oAttachment->disposition, $oAttachment->encoding, $oAttachment->filename);
		}
		
		// Recipients
		foreach ($aRecipients as $sAddress) {
			$oEmailFlex->addTo($sAddress);
		}
	
		// Send the email
		$oEmailFlex->send();
	}
}

?>