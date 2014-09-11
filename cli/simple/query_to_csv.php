<?php

//----------------------------------------------------------------------------//
// QUERY
//----------------------------------------------------------------------------//
$strQuery	=	"SELECT Account, COUNT(Id) AS 'CDR Count', SUM(Cost) AS 'Cost to TB', SUM(Charge) AS 'Charge to Account' " .
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
				"GROUP BY Account " .
				"ORDER BY Account";
//$strQuery	=	"SELECT * FROM InvoiceRun";
//----------------------------------------------------------------------------//

// Load Framework
require_once("../../flex.require.php");

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
		// Dump headers
		$arrHeaders	= Array();
		while ($arrHeader = $mixResult->fetch_field())
		{
			$arrHeaders[]	= addslashes($arrHeader->name);
		}
		fwrite($ptrCSVFile, '"'.implode('","', $arrHeaders).'"'."\n");
		
		// Dump data to CSV
		while ($arrRow = $mixResult->fetch_assoc())
		{
			foreach ($arrRow as &$strValue)
			{
				// Escape each value
				$strValue	= addslashes($strValue);
			}
			
			fwrite($ptrCSVFile, '"'.implode('","', $arrRow).'"'."\n");
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