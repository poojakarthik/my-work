<?php

// Framework
require_once("../../flex.require.php");

// Get list of CDRs which have duplicates
$strQuery	=	"SELECT Account, Service, FNN, Source, Destination, StartDatetime, EndDatetime, Units, Cost, Charge, RecordType, Credit, COUNT(Id) AS Copies " .
				"FROM CDRInvoiced " .
				"WHERE InvoiceRun = '20080601105406' " .
				"AND 0 < " .
				"(" .
				"	SELECT COUNT(Id)" .
				"	FROM CDRInvoiced CDRI2" .
				"	WHERE Id != CDRInvoiced.Id AND" .
				"	FNN = CDRInvoiced.FNN AND" .
				"	Source = CDRInvoiced.Source AND" .
				"	Destination = CDRInvoiced.Destination AND" .
				"	StartDatetime = CDRInvoiced.StartDatetime AND" .
				"	EndDatetime = CDRInvoiced.EndDatetime AND" .
				"	Units = CDRInvoiced.Units AND" .
				"	Cost = CDRInvoiced.Cost AND" .
				"	RecordType = CDRInvoiced.RecordType AND" .
				"	RecordType NOT IN (10, 15, 33) AND" .
				"	Credit = CDRInvoiced.Credit" .
				") " .
				"GROUP BY Account, Service, FNN, Source, Destination, StartDatetime, EndDatetime, Units, Cost, RecordType, Credit " .
				"HAVING Copies > 1 " .
				"ORDER BY Account";

// Get dump path
CliEcho('');
$strFilename	= trim($argv[1]);
if (!$strFilename)
{
	CliEcho("Please specify a file name to dump to as the first and only parameter\n");
	die;
}

// Run query
$qryQuery	= new Query();
if (($mixResult	= $qryQuery->Execute($strQuery)) === FALSE)
{
	CliEcho("There was an error with the query: ".$qryQuery->Error());
}
elseif (!$mixResult || !$mixResult->num_rows)
{
	CliEcho("There were no results for your query.");
}
else
{	
	// Open file
	$ptrCSVFile	= fopen($strFilename, 'w');
	if ($ptrCSVFile)
	{
		// Write Headers
		$arrHeaders	= Array("Account", "Duplicates", "Cost", "Charge");
		fwrite($ptrCSVFile, '"'.implode('","', $arrHeaders).'"'."\n");
		
		// Calculate Totals
		$arrAccounts	= Array();
		while ($arrRow = $mixResult->fetch_assoc())
		{
			$intDuplicates	= $arrRow['Copies'] - 1;
			$fltCost		= $arrRow['Cost'] * $intDuplicates;
			$fltCharge		= $arrRow['Charge'] * $intDuplicates;
			
			$arrAccounts[$arrRow['Account']]['Account']		= $arrRow['Account'];
			$arrAccounts[$arrRow['Account']]['Duplicates']	+= $intDuplicates;
			$arrAccounts[$arrRow['Account']]['Cost']		+= $fltCost;
			$arrAccounts[$arrRow['Account']]['Charge']		+= $fltCharge;
		}
		
		// Dump data to CSV
		foreach ($arrAccounts as $intAccount=>$arrAccount)
		{
			foreach ($arrAccount as &$strValue)
			{
				// Escape each value
				$strValue	= addslashes($strValue);
			}
			
			fwrite($ptrCSVFile, '"'.implode('","', $arrAccount).'"'."\n");
		}
		
		// Close the file
		fclose($ptrCSVFile);
		CliEcho("Query returned {$mixResult->num_rows} rows, and was successfully dumped to '$strFilename'");
	}
	else
	{
		CliEcho("Could not open file '$strFilename' for writing!");
	}
}
CliEcho('');


?>