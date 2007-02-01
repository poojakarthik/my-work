<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	
	
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	ETECH SYSTEM NOTE JANITOR : " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	
	
	// Open a Connection/Session to ETECH
	$cnnConnection = new Connection ();	
	$rptReport->AddMessage ("+	COMMUNICATION WITH ETECH ESTABLISHED\n");
	$rptReport->AddMessage (MSG_HORIZONTAL_RULE);
	
	
	// Write the Select to find Dodgy Records
	$selScrape = new StatementSelect ('ScrapeNoteSys', 'CustomerId, Attempt, TimeTaken', 'DataOriginal = 1', 'RAND()', '1');
	
	$arrScrapeSave = Array (
		'Attempt'		=> '',
		'TimeTaken'		=> '',
		'DataOriginal'	=> ''
	);
	
	// Setup the MySQLi Insert Query
	$updScrape = new StatementUpdate ('ScrapeNoteSys', 'CustomerId = <CustomerId>', $arrScrapeSave);
	
	$intCurrentRow = 1;
	
	// Loop through each of the Customers
	while (true)
	{
		$selScrape->Execute ();
		
		if ($selScrape->Count () <> 1)
		{
			break;
		}
		
		$arrScrape = $selScrape->Fetch ();
		
		// Start a Timer for this Request
		$fltStartTime = microtime (TRUE);
		
		// Pull the Information from ETECH
		$strResponse = $cnnConnection->Transmit (
			"GET",
			"https://sp.teleconsole.com.au/sp/customers/showsysnotes.php?customer_id=" . $arrScrape ['CustomerId']
		);
		
		// Count the Total Time
		$fltTotalTime = microtime (TRUE) - $fltStartTime;
		
		// Insert the Information into the Database
		$arrScrapeSave = Array (
			'DataOriginal'		=> $strResponse,
			'Attempt'			=> ($arrScrape ['Attempt'] + 1),
			'TimeTaken'			=> $fltTotalTime
		);
		
		$updScrape->Execute ($arrScrapeSave, Array ('CustomerId' => $arrScrape ['CustomerId']));
		
		$arrReport = Array (
			"<CurrentRow>"		=> sprintf ("%06d",	$intCurrentRow),
			"<TotalTime>"		=> sprintf ("%1.6f", $fltTotalTime),
			"<CustomerID>"		=> $arrScrape ['CustomerId'],
		);
		
		if ($strResponse <> "1")
		{
			// Add something to the Report
			$arrReport ['<Response>'] = "SYSTEM NOTE HAS BEEN CACHED";
		}
		else
		{
			// Add something to the Report
			$arrReport ['<Response>'] = "FAILED TO RETRIEVE SYSTEM NOTE PAGE";
		}
		
		// Add something to the Report
		$rptReport->AddMessageVariables (
			"+	<CurrentRow>		<TotalTime>	<CustomerID>\n" .
			"	<Response>\n",
			$arrReport
		);
		
		// Up the count
		++$intCurrentRow;
		
		// Flush the data out
		flush ();
		
		usleep (rand (1, 2) / 2);
	}
	
	echo "\n\n";
	
	$rptReport->Finish ();
	
?>
