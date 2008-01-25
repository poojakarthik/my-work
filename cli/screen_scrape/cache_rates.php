<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	
	
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	ETECH CUSTOMER ACCOUNT CACHE RUNNER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	
	
	// Open a Connection/Session to ETECH
	$cnnConnection = new Connection ();	
	$rptReport->AddMessage ("+	COMMUNICATION WITH ETECH ESTABLISHED\n");
	
	
	$arrN = Array (
		57, 51, 2, 58, 88, 84, 75, 77, 76, 92, 52
	);
	
	$arrM = Array (
		19, 24, 26
	);
	
	
	$rptReport->AddMessage (MSG_HORIZONTAL_RULE);
	
	// Start Counting each Record (from record #1)
	$intCurrentRow = 1;
	
	// Setup the MySQLi Insert Query
	$insScrape = new StatementInsert ('ScrapeRates');
	
	
	
	// Loop through each of the N values
	foreach ($arrN as $intN)
	{
		// Loop through each of the M values
		foreach ($arrM as $intM)
		{
			// Start a Timer for this Request
			$fltStartTime = microtime (TRUE);
			
			// Pull the Information from ETECH
			$strResponse = $cnnConnection->Transmit (
				"GET",
				"https://sp.teleconsole.com.au/sp/configuration/editintrate_DB.php?id=" . $intN . "&intGroup=" . $intM
			);
			
			// Count the Total Time
			$fltTotalTime = microtime (TRUE) - $fltStartTime;
			
			
			// Insert the Information into the Database
			$arrScrape = Array (
				'AxisN'				=> $intN,
				'AxisM'				=> $intM,
				'DataOriginal'		=> $strResponse
			);
			
			$insScrape->Execute ($arrScrape);
			
			// Add something to the Report
			$rptReport->AddMessageVariables (
				"+	<N> 	<M>		<TotalTime>	<Response>",
				Array (
					"<N>"				=> sprintf ("%02d",	$intN),
					"<M>"				=> sprintf ("%02d",	$intM),
					"<TotalTime>"		=> sprintf ("%1.6f", $fltTotalTime),
					"<Response>"		=> "PAGE HAS BEEN CACHED"
				)
			);
			
			// Up the count
			++$intCurrentRow;
			
			// Flush the data out
			flush ();
		}
	}
	
	echo "\n\n";
	
	$rptReport->Finish ();
	
?>
