<?php

require_once('../../flex.require.php');

//----------------------------------------------------------------------------//
// Truncates vixenworking.CDR then copies unbilled CDRs from vixen.CDR
//----------------------------------------------------------------------------//

$GLOBALS['fwkFramework']->StartWatch();

Debug("[ CDR UNBILLED UPDATE ]\n");

// Truncate CDR table
CliEcho(" * Truncating vixenworking.CDR...\t\t\t\t", FALSE);
$qryTruncate		= new QueryTruncate();
$qryTruncate->Execute("CDR");
CliEcho("[   OK   ]");

// Copy Unbilled CDRs across
CliEcho(" * Copying Unbilled CDRs from vixen.CDR to vixenworking.CDR...\t", FALSE);
$strStatus			= "100, 101, 150";
$qryCopyUnbilled	= new Query();
$qryCopyUnbilled->Execute("INSERT INTO CDR SELECT * FROM vixen.CDR USE INDEX (Status) WHERE Status IN ($strStatus)");
CliEcho("[   OK   ]");

$intTime	= (int)$GLOBALS['fwkFramework']->LapWatch();

CliEcho("\nExiting: Script Successful! ($intTime seconds)\n");
?>