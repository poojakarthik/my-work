<?php

//----------------------------------------------------------------------------//
// CONFIG
//----------------------------------------------------------------------------//

$strPath	= "/home/richdavis/Desktop/writeoff_20070906.csv";

//----------------------------------------------------------------------------//
// PROGRAM CODE
//----------------------------------------------------------------------------//

// Load Framework
require_once('../framework/require.php');

// Open and parse file
$ptrFile		= fopen($strPath, 'r');
$fltGrandTotal	= 0;
$intAccounts	= 0;
while ($strLine = trim(fgets($ptrFile)))
{
	// Check for header
	if (substr($strLine, 0, 1) == "Account")
	{
		continue;
	}
	
	// Split line
	$arrLine = explode(',', $strLine);
	if (count($arrLine) == 3)
	{
		$intAccount	= (int)trim($arrLine[0], '"');
		
		CliEcho("Writing off #$intAccount... ", FALSE);
		//$fltTotal = WriteOffAccount($intAccount);
		CliEcho("\${$fltTotal}\t\t\t[   OK   ]");
		
		// Add to Grand Total
		$fltGrandTotal += $fltTotal;
		$intAccounts++;
	}
}
fclose($ptrFile);

Debug(" * Wrote off $intAccounts at the value of \${$fltGrandTotal}\n");
?>