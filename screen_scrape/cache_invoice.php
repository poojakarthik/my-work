<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	
	
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	ETECH CUSTOMER INVOICE MANAGEMENT DATABASE SETUP UTILITY: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	
	
	// Read the Customers CSV File
	$cstCustomers = new Parser_CSV ('data/customers.csv');
	$rptReport->AddMessage ("+	CUSTOMER CSV HAS BEEN PARSED");
	
	
	/*
	// No communication with ETECH is needed because we're going to be using the Janitor
	
	// Open a Connection/Session to ETECH
	$cnnConnection = new Connection ();	
	$rptReport->AddMessage ("+	COMMUNICATION WITH ETECH ESTABLISHED\n");
	*/
	
	
	
	
	$rptReport->AddMessage (MSG_HORIZONTAL_RULE);
	
	// Start Counting each Record (from record #1)
	$intCurrentRow = 1;
	
	// Setup the MySQLi Insert Query
	$insScrape = new StatementInsert ('ScrapeInvoice');
	
	
	
	// Loop through each of the Customers
	foreach ($cstCustomers->CustomerList () AS $intCustomerId)
	{
		/*
		// This is going to be uber fast - so we don't need a timer
		
		// Start a Timer for this Request
		$fltStartTime = microtime (TRUE);
		*/
		
		/*
		// Janitors Job Now ...
		
		// Pull the Information from ETECH
		$strResponse = $cnnConnection->Transmit (
			"GET",
			"https://sp.teleconsole.com.au/sp/customers/viewinvoice.php?customer_id=" . $intCustomerId
		);
		
		// Count the Total Time
		$fltTotalTime = microtime (TRUE) - $fltStartTime;
		*/
		
		// Insert the Information into the Database
		$arrScrape = Array (
			'CustomerId'		=> trim ($intCustomerId),
			'DataOriginal'		=> "1",
			'Attempt'			=> 0,
			'TimeTaken'			=> 0
		);
		
		$insScrape->Execute ($arrScrape);
		
		// Add something to the Report
		$rptReport->AddMessageVariables (
			"+	<CurrentRow>		<CustomerID>	<Response>",
			Array (
				"<CurrentRow>"		=> sprintf ("%06d",	$intCurrentRow),
				"<CustomerID>"		=> trim ($intCustomerId),
				"<Response>"		=> "MANAGE INVOICE PAGE HAS BEEN SET UP"
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
