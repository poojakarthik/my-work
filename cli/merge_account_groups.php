<?php
/*
Usage:
	php /path/to/flex/cli/merge_account_groups.php (([-d RECEIVING_ACCOUNT_GROUP] [-o DEPRECATED_ACCOUNT_GROUP]) | [-r REVERSAL_FILENAME]) [-l LOG_FILE] [-t]
where:
	RECEIVING_ACCOUNT_GROUP		Account Group that will be recieving all accounts belonging to the deprecated account group
	DEPRECATED_ACCOUNT_GROUP	Account Group that will have its accounts moved to the receiving account group
	LOG_FILE					record log to writeable file LOG_FILE [optional, default is no logging]
	REVERSAL_FILENAME			reverse changes (you will need to specify the Reversal filename)
	-t							switch to execute merge in test mode.  No records will be modified, but a reversal file script is still made
	
	When merging account groups, a reversal file will be generated with the sql required to reverse the merge
**/

	require_once dirname(__FILE__) . "/../lib/cli/Cli.php";	

	Cli::execute("Cli_App_Merge_Account_Groups");

?>
