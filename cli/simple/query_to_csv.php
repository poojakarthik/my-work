<?php

//----------------------------------------------------------------------------//
// QUERY
//----------------------------------------------------------------------------//
$strQuery	=	"SELECT Account, COUNT(Id) AS 'CDR Count', SUM(Cost) AS 'Cost to TB', SUM(Charge) AS 'Charge to Account'" .
				"FROM CDRInvoiced" .
				"WHERE InvoiceRun = '20080601105406'" .
				"AND 0 <" .
				"(" .
				"	SELECT COUNT(Id)" .
				"	FROM CDRInvoiced CDRI2" .
				"	WHERE Id != CDR.Id AND" .
				"	FNN = CDR.FNN AND" .
				"	Source = CDR.Source AND" .
				"	Destination = CDR.Destination AND" .
				"	StartDatetime = CDR.StartDatetime AND" .
				"	EndDatetime = CDR.EndDatetime AND" .
				"	Units = CDR.Units AND" .
				"	Cost = CDR.Cost AND" .
				"	RecordType = CDR.RecordType AND" .
				"	RecordType NOT IN (10, 15, 33) AND" .
				"	Credit = CDR.Credit" .
				")" .
				"GROUP BY Account";
$strQuery	=	"SELECT * FROM InvoiceRun";
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
			$arrHeaders[]	= $arrHeader['name'];
		}
		fwrite('"'.implode('","', $arrHeaders).'"'."\n");
		
		// Dump data to CSV
		while ($arrRow = $mixResult->fetch_assoc())
		{
			fwrite('"'.implode('","', $arrRow).'"'."\n");
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