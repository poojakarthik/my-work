<?php
/*
 * This modifies all records referencing the deprecated contact record, so that they now reference the receiving contact record
Usage:
	php /path/to/flex/cli/merge_contacts.php (([-d RECEIVING_CONTACT] [-o DEPRECATED_CONTACT]) | [-r REVERSAL_FILENAME]) [-l LOG_FILE] [-t]
where:
	RECEIVING_CONTACT			Id of the Contact record that will now be referenced by all records that used to reference the Id of the deprecated contact
	DEPRECATED_CONTACT			Id of the contact record to deprecate
	LOG_FILE					record log to writeable file LOG_FILE [optional, default is no logging]
	REVERSAL_FILENAME			reverse changes (you will need to specify the Reversal filename)
	-t							switch to execute merge in test mode.  No records will be modified, but a reversal file script is still made
	
	When merging contacts, a reversal file will be generated with the sql required to reverse the merge
	
	before using this you should run /lib/data/model/type/grep -i -R "contact" .
	and make sure all the correct fields are being updated
**/

	require_once dirname(__FILE__) . "/../lib/cli/Cli.php";	

	Cli::execute("Cli_App_Merge_Contacts");

?>
