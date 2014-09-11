<?php
/*
Usage:
	php /path/to/flex/cli/calculate_customer_statuses.php ([-d DAYS] | [-i INVOICE_RUN_ID] | [-b BILLING_DATE]) [-l LOG_FILE]
where:
	DAYS			customer statuses will be calculated for each Invoice Run which has a BillingDate within the last DAYS (integer) days
	INVOICE_RUN_ID	customer statuses will be calculated for the Invoice Run with id = INVOICE_RUN_ID (integer)
	BILLING_DATE	customer statuses will be calculated for all Invoice Runs with BillingDate = BILLING_DATE (YYYY-MM-DD)
	LOG_FILE		record log to writeable file LOG_FILE [optional, default is no logging]
	
**/

	require_once dirname(__FILE__) . "/../lib/cli/Cli.php";	

	Cli::execute("Cli_App_Calculate_Customer_Statuses");

?>
