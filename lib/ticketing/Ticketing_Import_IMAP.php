<?php

require_once 'Zend/Mail/Storage/Imap.php';

class Ticketing_Import_IMAP extends Ticketing_Import_MailServer {
	public function __construct($oTicketingConfig, $bLoggingEnabled=false) {
		parent::__construct(
			$oTicketingConfig, 
			$bLoggingEnabled,
			new Zend_Mail_Storage_Imap(array(
				'host'		=> $oTicketingConfig->host,
				'user'		=> $oTicketingConfig->username,
				'password'	=> $oTicketingConfig->password,
				'port'		=> $oTicketingConfig->port,
				'ssl'		=> ($oTicketingConfig->use_ssl == 1)
			))
		);
	}
}

?>