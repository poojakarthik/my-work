<?php

//----------------------------------------------------------------------------//
// CONFIG
//----------------------------------------------------------------------------//

$strPath	= "/home/richdavis/Desktop/writeoff_20070921.csv";

//----------------------------------------------------------------------------//
// PROGRAM CODE
//----------------------------------------------------------------------------//

// Load Framework
require_once('../../flex.require.php');

// Open and parse file
$ptrFile		= fopen($strPath, 'r');
$fltGrandTotal	= 0;
$intAccounts	= 0;
while ($strLine = trim(fgets($ptrFile)))
{	
	// Split line
	$arrLine = explode(',', $strLine);
	if (count($arrLine) >= 3)
	{
		$intAccount	= (int)trim($arrLine[0], '"');

		// Check for header/footer
		if ($intAccount < 1000000000)
		{
			CliEcho("Line '$strLine' has an invalid Account #!");
			continue;
		}
		
		CliEcho("Writing off #$intAccount... ", FALSE);
		$fltTotal = WriteOffAccount($intAccount);
		CliEcho("\${$fltTotal}");
		
		// Add to Grand Total
		$fltGrandTotal += $fltTotal;
		$intAccounts++;
	}
	else
	{
		CliEcho("Line '$strLine' has less than 3 elements!");
	}
}
fclose($ptrFile);

Debug(" * Wrote off $intAccounts at the value of \${$fltGrandTotal}\n");
?>