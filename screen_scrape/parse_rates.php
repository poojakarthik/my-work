#!/usr/bin/php
<?=system ("clear");?>

	=====================================================================================================
	WELCOME TO THE ETECH DATA PARSER (version 1.0)
	=====================================================================================================
	
<?php
	
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

	
	
	// set up global defs
	
	// rate report
	$GLOBALS['arrRateReport'] = Array();
	
	set_time_limit (0);
	
	// Record the start time of the script
	$startTime = microtime (TRUE);
	
	// connect
	mysql_connect ("10.11.12.13", "bash", "bash");
	mysql_select_db ("bash");

	$records = 135;
	
	echo "Checking $records records\n";
	
	Run();
	
	// output report
	/*echo "\n\n";
	foreach($GLOBALS['arrRateReport'] AS $strRecordType=>$arrValue)
	{
		foreach($arrValue AS $strRateName=>$intValue)
		{
			echo "\$arrRates['$strRecordType']['$strRateName'] = intRateId;\n";
		}
	}*/
	
	//finish
	Die ();
	
	
	//#########################################################################
	
	function Run()
	{
		// Get acount details from the scrape
		$sql = "SELECT DataSerialised, AxisM FROM ScrapeRates ";
		$query = mysql_query ($sql);
		while ($row = mysql_fetch_assoc ($query))
		{
			$arrScrapeAccount = unserialize($row['DataSerialised']);
			$arrScrapeAccount['Carrier'] = $row['AxisM'];
			Decode($arrScrapeAccount);
		}
	}
	
	function Decode($arrScrapeRate)
	{
		//echo "Decoding\n";
		if (!is_array($arrScrapeRate))
		{
			return FALSE;
		}
		
		foreach ($arrScrapeRate['Rates'] as $arrRate)
		{
			$strTitle = explode(': ',$arrScrapeRate['Title']);
			echo trim($strTitle[1]).";";
			echo $arrScrapeRate['SetCap'].";";
			echo $arrScrapeRate['CapTime'].";";
			echo $arrScrapeRate['MaxCost'].";";
			echo $arrScrapeRate['StdFlag'].";";
			echo $arrScrapeRate['PostFlag'].";";
			echo $arrScrapeRate['StdMin'].";";
			echo $arrScrapeRate['PostMin'].";";
			echo $arrScrapeRate['Carrier'].";";
			foreach ($arrRate as $strRate)
			{
				echo $strRate.";";
			}
			echo "\n";
		}
		
		
		return TRUE;
	}
	
?>
