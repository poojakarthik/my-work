<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	
	
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	ETECH CUSTOMER SYSTEM NOTE CACHE RUNNER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	
	
	// Read the Customers CSV File
	$cstCustomers = new Parser_CSV ('data/customers.csv');
	$rptReport->AddMessage ("+	CUSTOMER CSV HAS BEEN PARSED");
	
	// Open a Connection/Session to ETECH
	$cnnConnection = new Connection ();	
	$rptReport->AddMessage ("+	COMMUNICATION WITH ETECH ESTABLISHED\n");
	
	
	
	
	
	$rptReport->AddMessage (MSG_HORIZONTAL_RULE);
	
	// Start Counting each Record (from record #1)
	$intCurrentRow = 1;
	
	// Setup the MySQLi Insert Query
	$insScrape = new StatementInsert ('ScrapeAccountViewDetail');
	
	
	
	// Loop through each of the Customers
	foreach ($cstCustomers->CustomerList () AS $intCustomerId)
	{
		
		// Start a Timer for this Request
		$fltStartTime = microtime (TRUE);
		
		// Pull the Information from ETECH
		$strResponse = $cnnConnection->Transmit (
			"GET",
			"https://sp.teleconsole.com.au/sp/customers/viewdetails.php?customer_id=" . $intCustomerId
		);
		
		// Count the Total Time
		$fltTotalTime = microtime (TRUE) - $fltStartTime;
		
		
		// Insert the Information into the Database
		$arrScrape = Array (
			'CustomerId'		=> $intCustomerId,
			'DataOriginal'		=> $strResponse
		);
		
		$insScrape->Execute ($arrScrape);
		
		// Add something to the Report
		$rptReport->AddMessageVariables (
			"+	<CurrentRow>		<TotalTime>	<CustomerID>	<Response>",
			Array (
				"<CurrentRow>"		=> sprintf ("%06d",	$intCurrentRow),
				"<TotalTime>"		=> sprintf ("%1.6f", $fltTotalTime),
				"<CustomerID>"		=> $intCustomerId,
				"<Response>"		=> "ACCOUNT VIEW DETAILS HAVE BEEN CACHED"
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
