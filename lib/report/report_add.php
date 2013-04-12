<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Add a new Data Report to viXen
//----------------------------------------------------------------------------//

// Get the Flex class...
require_once '../../lib/classes/Flex.php';
require_once dirname(__FILE__).'/report_permissions.php';
Flex::load();

//----------------------------------------------------------------------------//
// TODO: Specify the DataReport here!  See report_skeleton.php for tut
//----------------------------------------------------------------------------//

function showUsage() {
	echo "\nUsage:\n";
	echo "\tphp report_add.php REPORT_SOURCE_FILE [-i ID]\n";
	echo "Where:";
	echo "\tREPORT_SOURCE_FILE is a php file defining the report to add (include the php file extension)\n";
	echo "\tID is the id of a data report record to update (will create a new one if not supplied)\n\n";
}

// The file that defines the report, must stick all the details for the DataReport record, in this array
$arrDataReport = array();
if ($argc > 4) {
	showUsage();
	exit(1);
}

$strFilename = $argv[1];
if (substr($strFilename, -4) != '.php') {
	echo "\nERROR: The report file must have the php extension\n";
	showUsage();
	exit(1);
}

if (!file_exists($strFilename)) {
	echo "\nERROR: Cannot find file: {$strFilename}\n";
	showUsage();
	exit(1);
}

// Validate the id (-i ID) parameter
$iDataReportId = null;
if (isset($argv[2]) && isset($argv[3])) {
	if ($argv[2] != '-i') {
		echo "\nERROR: Invalid flag {$argv[2]}\n";
		showUsage();
		exit(1);
	} else if (!is_numeric($argv[3])) {
		echo "\nERROR: Invalid data report id supplied {$argv[3]}\n";
		showUsage();
		exit(1);
	} else {
		$iDataReportId = (int)$argv[3];
		$aDataReport = Query::run("	SELECT	id
									FROM	DataReport
									WHERE	Id = <id>",
									array('id' => $iDataReportId))->fetch_assoc();
		if (!$aDataReport) {
			echo "\nERROR: Could not find a data report for the id supplied {$iDataReportId}\n";
			showUsage();
			exit(1);
		}
	}
}

// parse the report file
require $strFilename;

// Insert it into the database
TransactionStart();

if ($iDataReportId !== null) {
	// Update
	$oReport = DataReport::getForId($iDataReportId);
	echo "\nUpdating report: '{$oReport->Name}' from {$strFilename}... ";

	// Check status
	switch ($oReport->data_report_status_id) {
		case DATA_REPORT_STATUS_ACTIVE:
			if ($arrDataReport['data_report_status_id'] != DATA_REPORT_STATUS_DRAFT) {
				echo "\nSetting the report status to: ".strtoupper(Constant_Group::getConstantGroup('data_report_status')->getConstantName($arrDataReport['data_report_status_id']));
				$arrDataReport = array(
					'data_report_status_id' => $arrDataReport['data_report_status_id']
				);
			} else {
				echo "\nCannot set the report to DRAFT again, it is currently ACTIVE\n\n";
				TransactionRollback();
				exit(1);
			}
			break;
		case DATA_REPORT_STATUS_INACTIVE:
			echo "\nCannot edit this report, it is INACTIVE\n\n";
			TransactionRollback();
			exit(1);
			break;
		case DATA_REPORT_STATUS_DRAFT:
			echo "\nThis report is a DRAFT, updating it's definition\n";
			break;
	}

	$arrDataReport['Id'] = $iDataReportId;
	$oStmt = new StatementUpdateById("DataReport", $arrDataReport);
} else {
	// Insert
	echo "\nCreating new report: '{$arrDataReport['Name']}' from {$strFilename}... ";
	$oStmt = new StatementInsert("DataReport");
}

if ($oStmt->Execute($arrDataReport) === false) {
	TransactionRollback();
	echo "FAIL!!!\n";
	Debug($oStmt->Error());
	exit(1);
}

TransactionCommit();

echo "OK!\n";
echo("\n\n-- End of Report Generation --\n");

?>