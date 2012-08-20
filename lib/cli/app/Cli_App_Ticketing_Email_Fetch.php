<?php

require_once dirname(__FILE__) . '/' . '../../classes/Flex.php';
Flex::load();

class Cli_App_Ticketing_Email_Fetch extends Cli {
	function run() {
		try {
			// Load the ticketing configuration
			Ticketing_Service::loadEmails();
		} catch(Exception $oException) {
			$this->showUsage("ERROR: " . $oException->getMessage());
		}
	}

	function getCommandLineArguments() {
		return array();
	}
}