<?php

require_once 'Zend/Mail/Storage/Pop3.php';

class Ticketing_Import_POP3 extends Ticketing_Import_MailServer {
	public function __construct($oTicketingConfig, $bLoggingEnabled=false) {
		Flex::assert(false, "POP3 is not a supported ticketing email protocol, use IMAP instead");
		parent::__construct(
			$oTicketingConfig, 
			$bLoggingEnabled,
			new Zend_Mail_Storage_Pop3(array(
				'host' => $oTicketingConfig->host,
				'user' => $oTicketingConfig->username,
				'password' => $oTicketingConfig->password,
				'port' => $oTicketingConfig->port
			))
		);
	}
}

?>