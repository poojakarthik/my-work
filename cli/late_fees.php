<?php

	require_once dirname(__FILE__) . "/../lib/cli/Cli.php";	

	// Force this to run in test mode!
	Cli::execute("Cli_App_ApplyLateFeesToAccounts");

?>
