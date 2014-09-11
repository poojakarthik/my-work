<?php

require_once 'Zend/Mail/Storage/Maildir.php';

class Ticketing_Import_MAILDIR extends Ticketing_Import_MailServer {
	public function __construct($oTicketingConfig, $bLoggingEnabled=false) {
		Flex::assert(false, "MAILDIR is not a supported ticketing email protocol, use IMAP instead");
		parent::__construct(
			$oTicketingConfig, 
			$bLoggingEnabled,
			new Zend_Mail_Storage_Maildir(array('dirname'=> $oTicketingConfig->host))
		);
	}
}

?>