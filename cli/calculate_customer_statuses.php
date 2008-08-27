<?php
/*
Usage:
	php /path/to/flex/cli/calculate_customer_statuses.php
**/

	require_once dirname(__FILE__) . "/../lib/cli/Cli.php";	

	Cli::execute("Cli_App_Calculate_Customer_Statuses");

?>
