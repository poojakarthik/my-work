<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	require ("decode_etech.php");
	
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	ETECH PAYMENT CACHE RUNNER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	
	
	// Open a Connection/Session to ETECH
	$cnnConnection = new Connection ();	
	$rptReport->AddMessage ("+	COMMUNICATION WITH ETECH ESTABLISHED\n");
	
	
	
	$arrMonths = Array (
		Array ("Month"	=> "06",		"Year"		=> "2006"),
		Array ("Month"	=> "07",		"Year"		=> "2006"),
		Array ("Month"	=> "08",		"Year"		=> "2006"),
		Array ("Month"	=> "09",		"Year"		=> "2006"),
		Array ("Month"	=> "10",		"Year"		=> "2006"),
		Array ("Month"	=> "11",		"Year"		=> "2006"),
		Array ("Month"	=> "12",		"Year"		=> "2006"),
		Array ("Month"	=> "01",		"Year"		=> "2007"),
		Array ("Month"	=> "02",		"Year"		=> "2007")
	);
	
	
	$rptReport->AddMessage (MSG_HORIZONTAL_RULE);
	
	// Start Counting each Record (from record #1)
	$intCurrentRow = 1;
	
	// Setup the MySQLi Insert Query
	$insScrape = new StatementInsert ('ScrapePayment');
	
	
	foreach ($arrMonths as $arrMonth)
	{
		// Start a Timer for this Request
		$fltStartTime = microtime (TRUE);
		
		// Pull the Information from ETECH
		$strResponse = $cnnConnection->Transmit (
			CONNECTION_TRANSMIT_METHOD_POST,
			"https://sp.teleconsole.com.au/sp/reporting/payment_for_month.php",
			Array (
				"payment_month"		=> $arrMonth ['Month'],
				"payment_year"		=> $arrMonth ['Year'],
				"show_dates"		=> "y",
				"Submit"			=> "Display Report"
			)
		);
		
		// Count the Total Time
		$fltTotalTime = microtime (TRUE) - $fltStartTime;
		
		// Insert the Information into the Database
		$arrScrape = Array (
			'Month'				=> $arrMonth ['Month'],
			'Year'				=> $arrMonth ['Year'],
			'DataOriginal'		=> $strResponse
		);
		
		$insScrape->Execute ($arrScrape);
		
		// Add something to the Report
		$rptReport->AddMessageVariables (
			"+	<CurrentRow>		<TotalTime>	<Month>	<Year>	<Response>",
			Array (
				"<CurrentRow>"		=> sprintf ("%06d",	$intCurrentRow),
				"<TotalTime>"		=> sprintf ("%1.6f", $fltTotalTime),
				'<Month>'			=> $arrMonth ['Month'],
				'<Year>'			=> $arrMonth ['Year'],
				"<Response>"		=> "PAYMENT HAS BEEN CACHED"
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
