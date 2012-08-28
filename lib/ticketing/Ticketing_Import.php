<?php

// Load other required classes
require_once dirname(__FILE__).'/Ticketing_Ticket.php';

abstract class Ticketing_Import {
	protected $_oTicketingConfig;
	protected $_bLoggingEnabled = false;

	public function __construct($oTicketingConfig, $bLoggingEnabled=false) {
		$this->_oTicketingConfig = $oTicketingConfig;
		$this->_bLoggingEnabled = $bLoggingEnabled;
	}

	abstract public function import();

	public static function getInstance($oTicketingConfig, $bLoggingEnabled) {
		// Verify that there an implementation for the recorded protocol
		$sClass = 'Ticketing_Import_'.strtoupper($oTicketingConfig->protocol);
		if (!class_exists($sClass)) {
			throw new Exception("Invalid protocol in the current ticketing_config: {$oTicketingConfig->protocol}. There is no implementation of this protocol ({$sClass})");
		}

		// Return an instance of the implementation class
		$oInstance = new $sClass($oTicketingConfig, $bLoggingEnabled);
		return $oInstance;
	}
}

?>