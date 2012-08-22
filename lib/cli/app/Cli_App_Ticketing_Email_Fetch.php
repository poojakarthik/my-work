<?php

require_once dirname(__FILE__) . '/' . '../../classes/Flex.php';
Flex::load();

class Cli_App_Ticketing_Email_Fetch extends Cli {
	function run() {
		try {
			$aArgs = $this->getValidatedArguments();
			$aTicketingConfigs = Ticketing_Config::getAll();
			$oLog = Log::get();
			foreach ($aTicketingConfigs as $oRecord) {
				if ($oRecord->is_active == 1) {
					$oLog->log("[*] Ticketing Config #{$oRecord->id}");
					$oLog->log("[*] - - - - - - - - - -");
					Ticketing_Import::getInstance($oRecord, $aArgs[self::SWITCH_VERBOSE])->import();
					$oLog->log("[*] - - - - - - - - - -");
					$oLog->log("");
				}
			}
		} catch(Exception $oException) {
			$this->showUsage("ERROR: " . $oException->getMessage());
		}
	}

	function getCommandLineArguments() {
		return array();
	}
}