<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	require ("decode_etech.php");
	
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	ETECH SERVICE MOBILE CACHE RUNNER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	
	
	// Open a Connection/Session to ETECH
	$cnnConnection = new Connection ();	
	$rptReport->AddMessage ("+	COMMUNICATION WITH ETECH ESTABLISHED\n");
	
	
	
	
	
	$rptReport->AddMessage (MSG_HORIZONTAL_RULE);
	
	// Start Counting each Record (from record #1)
	$intCurrentRow = 1;
	
	// Setup the MySQLi Insert Query
	$insScrape = new StatementInsert ('ScrapeServiceMobile');
	
	
	$objDecodeEtech = new VixenDecode (Array ());
	
	while ($arrService = $objDecodeEtech->FetchServiceByType (SERVICE_TYPE_MOBILE))
	{
		// Start a Timer for this Request
		$fltStartTime = microtime (TRUE);
		
		// Pull the Information from ETECH
		$strResponse = $cnnConnection->Transmit (
			"GET",
			"https://sp.teleconsole.com.au/sp/customers/viewdetails.php" .
			"?customer_id=" . $arrService ['Account'] . 
			"&MBnumber=" . $arrService ['FNN'] . 
			"&editMb"
		);
		
		// Count the Total Time
		$fltTotalTime = microtime (TRUE) - $fltStartTime;
		
		// Insert the Information into the Database
		$arrScrape = Array (
			'CustomerId'		=> $arrService ['Account'],
			'FNN'				=> $arrService ['FNN'],
			'DataOriginal'		=> $strResponse,
			'DataSerialized'	=> ''
		);
		
		$insScrape->Execute ($arrScrape);
		
		// Add something to the Report
		$rptReport->AddMessageVariables (
			"+	<CurrentRow>		<TotalTime>	<Account>	<FNN>	<Response>",
			Array (
				"<CurrentRow>"		=> sprintf ("%06d",	$intCurrentRow),
				"<TotalTime>"		=> sprintf ("%1.6f", $fltTotalTime),
				"<Account>"			=> $arrService ['Account'],
				"<FNN>"				=> $arrService ['FNN'],
				"<Response>"		=> "MOBILE HAS BEEN CACHED"
			)
		);
		
		// Up the count
		++$intCurrentRow;
		
		// Flush the data out
		flush ();
	}
	
	echo "\n\n";
	
	$rptReport->Finish ();
	
?>
