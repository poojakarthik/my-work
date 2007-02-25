<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	
	
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	ETECH CUSTOMER USER NOTE CACHE RUNNER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	
	
	// Read the Customers CSV File
	$cstCustomers = new Parser_CSV ('data/customers.csv');
	$rptReport->AddMessage ("+	CUSTOMER CSV HAS BEEN PARSED");
	
	
	
	
	$rptReport->AddMessage (MSG_HORIZONTAL_RULE);
	
	// Start Counting each Record (from record #1)
	$intCurrentRow = 1;
	
	// Setup the MySQLi Insert Query
	$insScrape = new StatementInsert ('ScrapeNoteUser');
	
	
	
	// Loop through each of the Customers
	foreach ($cstCustomers->CustomerList () AS $intCustomerId)
	{
		// Insert the Information into the Database
		$arrScrape = Array (
			'CustomerId'		=> $intCustomerId,
			'DataOriginal'		=> "",
			'Attempt'			=> 0,
			'TimeTaken'			=> 0
		);
		
		$insScrape->Execute ($arrScrape);
		
		// Add something to the Report
		$rptReport->AddMessageVariables (
			"+	<CurrentRow>		<CustomerID>	<Response>\n",
			Array (
				"<CurrentRow>"		=> sprintf ("%06d",	$intCurrentRow),
				"<CustomerID>"		=> $intCustomerId,
				"<Response>"		=> "USER NOTE HAS BEEN CACHED"
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
