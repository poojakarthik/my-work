<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	require ("decode_etech.php");
	
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	ETECH CUSTOMER ACCOUNT ADDITIONAL INFORMATION CACHE RUNNER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	
	
	// Open a Connection/Session to ETECH
	$cnnConnection = new Connection ();	
	$rptReport->AddMessage ("+	COMMUNICATION WITH ETECH ESTABLISHED\n");
	
	
	
	$rptReport->AddMessage (MSG_HORIZONTAL_RULE);
	
	// Start Counting each Record (from record #1)
	$intCurrentRow = 1;
	
	// Setup the MySQLi Insert Query
	$insScrape = new StatementInsert ('ScrapeServiceInbound');
	
	
	$objDecodeEtech = new VixenDecode (Array ());
	
	while ($arrCustomer = $objDecodeEtech->FetchCustomer ())
	{
		foreach ($arrCustomer ['DataArray']['sn'] as $arrService)
		{
			if (substr (trim ($arrService ['Number']), 0, 4) == "1300")
			{
				// Start a Timer for this Request
				$fltStartTime = microtime (TRUE);
				
				// Pull the Information from ETECH
				$strResponse = $cnnConnection->Transmit (
					"GET",
					"https://sp.teleconsole.com.au/sp/customers/viewdetails.php" .
					"?customer_id=" . $arrCustomer ['CustomerId'] . 
					"&id=" . $arrService ['Id'] . 
					"&editInbound"
				);
				
				// Count the Total Time
				$fltTotalTime = microtime (TRUE) - $fltStartTime;
				
				// Insert the Information into the Database
				$arrScrape = Array (
					'CustomerId'		=> $arrCustomer ['CustomerId'],
					'FNN'				=> $arrService ['AreaCode'] . $arrService ['Number'],
					'DataOriginal'		=> $strResponse
				);
				
				$insScrape->Execute ($arrScrape);
				
				// Add something to the Report
				$rptReport->AddMessageVariables (
					"+	<CurrentRow>		<TotalTime>	<Account>	<FNN>	<Response>",
					Array (
						"<CurrentRow>"		=> sprintf ("%06d",	$intCurrentRow),
						"<TotalTime>"		=> sprintf ("%1.6f", $fltTotalTime),
						"<Account>"			=> $arrCustomer ['CustomerId'],
						"<FNN>"				=> $arrService ['AreaCode'] . $arrService ['Number'],
						"<Response>"		=> "INBOUND HAS BEEN CACHED"
					)
				);
				
				// Up the count
				++$intCurrentRow;
				
				// Flush the data out
				flush ();
			}
		}
	}
	
	echo "\n\n";
	
	$rptReport->Finish ();
	
?>
