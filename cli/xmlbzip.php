<?php

	require_once dirname(__FILE__) . "/../lib/cli/Cli.php";

	// TEMPORARY
	set_error_handler('Flex::errorHandlerLog');

	Cli::execute("Cli_App_XMLBZIP");

?>
