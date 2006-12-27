#!/usr/bin/php
<?=system ("clear");?>

	=====================================================================================================
	WELCOME TO THE ETECH DATA PARSER (version 1.0)
	=====================================================================================================
	
<?php
	
	// set up global defs
	
	// Record Types
	$GLOBALS['arrRecordTypes'] = Array
	(
		//TODO!!!!
		"localrate"				=> "",
		"natrate"				=> "",
		"mobrate"				=> "",
		"intrate"				=> "",
		"service_equip_rate"	=> "",
		
		"mobileunitel"			=> "",
		"mobiletelstra"			=> "",
		"mobileother"			=> "",
		"mobilenational"		=> "",
		"mobile1800"			=> "",
		"mobilevoicemail"		=> "",
		"mobilediverted"		=> "",
		"mobilesms"				=> "",
		"mobilemms"				=> "",
		"mobiledata"			=> "",
		"mobileinternational"	=> ""
	);
	
	// Rates
	//TODO!!!!
	$arrRates['x'] = intRateId;
	$GLOBALS['arrRates'] = $arrRates;
	
	
	set_time_limit (0);
	
	// Record the start time of the script
	$startTime = microtime (TRUE);
	
	// connect
	mysql_connect ("10.11.12.13", "bash", "bash");
	mysql_select_db ("bash");
	
	// How many scrape account records do we have?
	$sql = "SELECT count(*) AS records FROM ScrapeAccount ";
	$query = mysql_query ($sql);
	
	$row = mysql_fetch_assoc ($query);
	$records = $row ['records'];
	
	$records = 100;
	
	echo "Checking $records records\n";
	
	// Loop through each Scrape
	for ($start=0; $start < ceil ($records / 100); ++$start)
	{
		Run($start);
	}
	
	//finish
	Die ();
	
	
	//#########################################################################
	
	function Run($start)
	{
		echo "Checking $start - ".($start + 100)."\n";
		// Get acount details from the scrape
		$sql = "SELECT CustomerId, DataSerialized FROM ScrapeAccount ";
		$sql .= "LIMIT " . ($start * 100) . ", 100";
		$query = mysql_query ($sql);
		while ($row = mysql_fetch_assoc ($query))
		{
			$arrScrapeAccount = unserialize($row['DataSerialized']);
			Decode($arrScrapeAccount);
		}
	}
	
	function Decode($arrScrapeAccount)
	{
		//echo "Decoding\n";
		if (!is_array($arrScrapeAccount))
		{
			return FALSE;
		}
		
		$arrRates = Array();
		
		// for each RecordType
		foreach ($GLOBALS['arrRecordTypes'] AS $strName=>$intServiceType )
		{
			// if we have a rate for this RecordType
			if ($arrScrapeAccount[$strName])
			{
				//if we have a conversion name for this rate
				if ($GLOBALS['arrRates'][$arrScrapeAccount[$strName]])
				{
					$intRateGroup = $GLOBALS['arrRates'][$arrScrapeAccount[$strName]];
					
					// insert record
					//TODO!!!!
					// for each service of $intServiceType
						// insert into ServiceRateGroup
					
				}
				else
				{
					//error
					echo "No new rate found for : $intRecordType \t: {$arrScrapeAccount[$strName]}\n";
				}
			}
		}
		
		
		return TRUE;
	}
	
?>
