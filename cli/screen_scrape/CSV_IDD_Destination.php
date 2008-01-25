#!/usr/bin/php
<?php

// writes CSVs of IDD Destination Codes from the Etech scrape data

// load framework
$strFrameworkDir = "../framework/";
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."database_define.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");

// instanciate the etech decoder
require_once('decode_etech.php');
$objDecoder = new VixenDecode($arrConfig);

// define the output directory
$strDirName = "/home/vixen/vixen_seed/Destination/IDD";

// create the output directory
$strCommand = "mkdir -p $strDirName";
system($strCommand);

// clean the output array
$arrOutput = Array();

// Get Rate details from the scrape
while ($arrRow = $objDecoder->FetchIDDGroupRate())
{
	$arrScrapeRate = $arrRow['DataArray'];
	$arrScrapeRate['Carrier'] = $arrRow['AxisM'];
	$arrDestination = $objDecoder->DecodeIDDDestination($arrScrapeRate);

	if (is_array($arrDestination))
	{
		// add each destination to the array
		foreach ($arrDestination AS $strDestination)
		{
			$arrOutput[$strDestination] = TRUE;
		}
	}
	else
	{
		echo "bad record\n";
	}
}

// set the file name
$strFileName = "$strDirName/Destination.csv";
		
echo "Exporting Destinations\n";
		
// open the file
$resFile = fopen($strFileName, 'w');

if ($resFile)
{
	// write the CSV header row
	$strHeader = CSVHeader('Destination');
	if ($strHeader)
	{
		fwrite($resFile, $strHeader);
	}
	else
	{
		echo "bad header row\n";
		Die();
	}
	
	// set up destination array
	$arrDestination = Array();
	$arrDestination['Context'] = 1;
	$arrDestination['Code'] = 10000;
	
	// write each Destination to the file
	foreach ($arrOutput AS $strDestination=>$bolVoid)
	{
		$arrDestination['Description'] = $strDestination;
		$strLine = CSVRow('Destination', $arrDestination);
		if ($strLine)
		{
			fwrite($resFile, $strLine);
		}
		else
		{
			echo "bad line\n";
		}
		
		// incrament the destination code
		$arrDestination['Code']++;
	}
	
	// close the file
	fclose($resFile);
}
else
{
	echo "could not open file $strFileName\n";
	Die;
}
echo "Done\n";
?>
