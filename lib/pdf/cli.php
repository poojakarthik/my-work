<?php

// CULL:: Check that this isn't being used by scheduled tasks
/*

DEPRECATED - Use /flex/cli/pdf.php instead

*/

	require_once dirname(__FILE__) . "/../cli/Cli.php";	

	Cli::execute("Cli_App_Pdf");

?>
