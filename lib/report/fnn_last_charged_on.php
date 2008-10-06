<?php

// Framework
require_once("../../flex.require.php");

// Statements
$selLastChargedOn	= new StatementSelect("Service", "MIN(EarliestCDR) AS MinEarliestCDR, MAX(LatestCDR) AS MaxLatestCDR", "FNN = <FNN>");

// Source file
$strInputPath	= $argv[1];
if (!file_exists($strInputPath))
{
	throw new Exception("CSV file '{$strInputPath}' does not exist!");
}

$strOutputPath	= dirname($strInputPath).'/'.basename($strInputPath, '.csv').'.output.csv';

// Open the CSV files
$resInputFile	= fopen($strInputPath, 'r');
$resOutputFile	= fopen($strOutputPath, 'w');
if ($resInputFile && $resOutputFile)
{
	// Parse each line
	while ($arrLine = fgetcsv($resInputFile))
	{
		// Get each FNN from the line
		foreach ($arrLine as $strFNN)
		{			
			$arrOutputLine	= Array($strFNN);
			
			// Find the CDR Dates
			$strFNN	= trim($strFNN);
			if ($selLastChargedOn->Execute(Array('FNN'=>$strFNN)) === FALSE)
			{
				throw new Exception($selLastChargedOn->Error());
			}
			
			if (!($arrLastChargedOn = $selLastChargedOn->Fetch()))
			{
				$arrLastChargedOn	= Array();
			}
			
			$arrOutputLine[]	= $arrLastChargedOn['MinEarliestCDR'];
			$arrOutputLine[]	= $arrLastChargedOn['MaxLatestCDR'];
			
			fwrite($resOutputFile, implode(',', $arrOutputLine)."\n");
		}
	}
	
	// Close the files
	fclose($resOutputFile);
	fclose($resInputFile);
}
else
{
	throw new Exception("Unable to open file '{$strInputPath}' or '{$strOutputPath}'");
}


?>