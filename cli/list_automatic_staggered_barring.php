<?php

	require_once dirname(__FILE__) . "/../lib/cli/Cli.php";	

	$argv[] = '-r';
	Cli::execute("Cli_App_Staggered_Automatic_Barring");

?>
