#!/usr/bin/php
<?php

// writes CSVs of IDD rates from the Etech scrape data

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
require_once(decode_etech.php);
$objDecoder = new VixenDecode($arrConfig);

// define the output directory
$strDirName = "/home/vixen/vixen_seed/Rate/IDD";

// create the output directory
$strCommand = "mkdir -p $strDirName";
system($strCommand);


// Get Rate details from the scrape
$sqlQuery = new Query();
$strQuery = "SELECT DataSerialised, AxisM FROM ScrapeRates";
$sqlResult = $sqlQuery->Execute($strQuery);
while ($row = $sqlResult->fetch_assoc())
{
	$arrScrapeRate = unserialize($row['DataSerialised']);
	$arrScrapeRate['Carrier'] = $row['AxisM'];
	$arrRates = objDecoder->DecodeIDDGroupRate($arrScrapeRate);
	
	if (is_array($arrRates))
	{
		// get the plan name
		$arrTitle 			= explode(': ',$arrScrapeRate['Title']);
		$strPlanName		= trim($arrTitle[1]);
		
		// set the file name
		$strFilename = "$strDirName/$strPlanName.csv";
		
		// open the file
		$resFile = fopen($strFileName, 'r');
		
		if ($resFile)
		{
			// write the CSV header row
			$strHeader = CSVHeader('Rate');
			if ($strHeader)
			{
				fwrite($strHeader);
			}
			else
			{
				echo "bad header row\n";
			}
			
			// write each rate to the file
			foreach ($arrRates AS $arrRate)
			{
				$strLine = CSVRow('Rate', $arrRate);
				if ($strLine)
				{
					fwrite($strLine);
				}
				else
				{
					echo "bad line\n";
				}
			}
			
			// close the file
			fclose($resFile);
		}
		else
		{
			echo "could not open file $strFileName\n";
		}
	}
	else
	{
		echo "bad record\n";
	}
}
?>
