<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	
	
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	ETECH ACCOUNT JANITOR : " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	
	
	// Open a Connection/Session to ETECH
	$cnnConnection = new Connection ();	
	$rptReport->AddMessage ("+	COMMUNICATION WITH ETECH ESTABLISHED\n");
	$rptReport->AddMessage (MSG_HORIZONTAL_RULE);
	
	$strWhere = 'Id > <Id> AND DataOriginal NOT LIKE "%html%"';
	
	// Write the Select to find Dodgy Records
	//$selScrape = new StatementSelect ('ScrapeAccount', 'CustomerId, Attempt, TimeTaken', 'DataOriginal NOT LIKE "%html%"', 'RAND()', '1');
	$selScrape = new StatementSelect ('ScrapeAccount', 'Id, CustomerId, Attempt, TimeTaken', $strWhere, NULL, '1');
	
	$arrScrapeSave = Array (
		'DataOriginal'	=> '',
		'DataSerialized'=> '',
		'Attempt'		=> '',
		'TimeTaken'		=> ''
	);
	
	
	// Setup the MySQLi Insert Query
	$updScrape = new StatementUpdate ('ScrapeAccount', 'CustomerId = <CustomerId>', $arrScrapeSave);
	
	$intCurrentRow = 1;
	
	$arrWhere = Array();
	$arrWhere['Id'] = 12349; 
	
	// Loop through each of the Customers
	while (true)
	{
		$selScrape->Execute ($arrWhere);
		
		if ($selScrape->Count () <> 1)
		{
			break;
		}
		
		$arrScrape = $selScrape->Fetch ();
		$arrWhere['Id'] = $arrScrape['Id']; 
		
		// Start a Timer for this Request
		$fltStartTime = microtime (TRUE);
		
		// Pull the Information from ETECH
		$strResponse = $cnnConnection->Transmit (
			"GET",
			"https://sp.teleconsole.com.au/sp/customers/editdetails.php?customer_id=" . $arrScrape ['CustomerId']
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
			$arrReport ['<Response>'] = "ACCOUNT HAS BEEN CACHED";
		}
		else
		{
			// Add something to the Report
			$arrReport ['<Response>'] = "FAILED TO RETRIEVE ACCOUNT";
		}
		
		// Add something to the Report
		$rptReport->AddMessageVariables (
			"+	<CurrentRow>		<TotalTime>	<CustomerID>	<Response>",
			$arrReport
		);
		
		// Up the count
		++$intCurrentRow;
		
		// Flush the data out
		flush ();
	}
	
	echo "\n\n";
	
	$rptReport->Finish ();
	
?>
