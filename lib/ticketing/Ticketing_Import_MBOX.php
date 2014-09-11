<?php

require_once 'Zend/Mail/Storage/Mbox.php';

class Ticketing_Import_MBOX extends Ticketing_Import_MailServer {
	public function __construct($oTicketingConfig, $bLoggingEnabled=false) {
		Flex::assert(false, "MBOX is not a supported ticketing email protocol, use IMAP instead");
		parent::__construct(
			$oTicketingConfig, 
			$bLoggingEnabled,
			new Zend_Mail_Storage_Mbox(array('dirname' => $oTicketingConfig->host))
		);
	}
}

?>