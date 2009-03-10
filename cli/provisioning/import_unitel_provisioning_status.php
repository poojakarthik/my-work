<?php

// Framework
require_once("../../flex.require.php");

// Statements
$insProvisioningTranslation	= new StatementInsert("ProvisioningTranslation");

// Filename
$strFileName	= "/home/rdavis/unitel_preselection_status.csv";

CliEcho("\n[ IMPORTING UNITEL PRESELECTION STATUSES ]\n");

// Open the File & Read each Line
$resFile	= fopen($strFileName, 'r');
while ($arrLine = fgetcsv($resFile))
{
	if (!(int)$arrLine[0])
	{
		// Header or Unused Status
		continue;
	}
	
	CliEcho(" + Inserting '".trim($arrLine[2])."'...\t\t\t");
	
	// Parse Line
	$arrTranslation					= Array();
	$arrTranslation['Context']		= CARRIER_TRANSLATION_CONTEXT_UNITEL_PRESELECTION_STATUS;
	$arrTranslation['flex_code']	= Array('provisioning_type' => (int)$arrLine[0], 'provisioning_request_status' => (int)$arrLine[1]);
	$arrTranslation['CarrierCode']	= trim($arrLine[2]);
	if ($insProvisioningTranslation->Execute($arrTranslation))
	{
		CliEcho("[   OK   ]");
	}
	else
	{
		CliEcho("[ FAILED ]");
		CliEcho("\t -- ERROR: DB Error in \$insProvisioningTranslation: ".$insProvisioningTranslation->Error());
		exit(1);
	}
}

?>