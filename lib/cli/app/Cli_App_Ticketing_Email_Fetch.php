<?php

require_once dirname(__FILE__) . '/' . '../../classes/Flex.php';
Flex::load();

class Cli_App_Ticketing_Email_Fetch extends Cli {
	function run() {
		try {
			$aArgs = $this->getValidatedArguments();
			Ticketing_Import::getInstance($aArgs[self::SWITCH_VERBOSE])->import();
		} catch(Exception $oException) {
			$this->showUsage("ERROR: " . $oException->getMessage());
		}
	}

	function getCommandLineArguments() {
		return array();
	}
}