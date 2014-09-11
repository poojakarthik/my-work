<?php

	require_once dirname(__FILE__) . "/../lib/cli/Cli.php";	

	// Force this to run in test mode!
	$argv[] = '-r';
	Cli::execute("Cli_App_Automatic_Barring");

?>
