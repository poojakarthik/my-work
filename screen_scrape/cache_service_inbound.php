<?php
	
	system ('clear;');
	
	require ('config/application_loader.php');
	
	
	
	// Create a new Report Object
	$rptReport = new Report (
		'+	ETECH CUSTOMER INBOUND SERVICE CACHE RUNNER: ' . date ('Y-m-d h:i:s A'),
		'bash@voiptelsystems.com.au'
	);
	
	
	// Open a Connection/Session to ETECH
	$cnnConnection = new Connection ();	
	$rptReport->AddMessage ('+	COMMUNICATION WITH ETECH ESTABLISHED\n');
	
	
	// Read the Services
	$selCustomers = new StatementSelect ('Service', 'Id, Account, FNN', 'ServiceType = <ServiceType>');
	$selCustomers->Execute (Array ('ServiceType' => SERVICE_TYPE_INBOUND));
	$rptReport->AddMessage ('+	SERVICE QUERY HAS BEEN EXECUTED');
	
	
	
	
	$rptReport->AddMessage (MSG_HORIZONTAL_RULE);
	
	// Start Counting each Record (from record #1)
	$intCurrentRow = 1;
	
	// Setup the MySQLi Insert Query
	$insScrape = new StatementInsert ('ScrapeServiceInbound');
	
	
	
	// Loop through each of the Customers
	foreach ($selCustomers->FetchAll () AS $arrService)
	{
		// Start a Timer for this Request
		$fltStartTime = microtime (TRUE);
		
		// Pull the Information from ETECH
		$strResponse = $cnnConnection->Transmit (
			'GET',
			'https://sp.teleconsole.com.au/sp/customers/viewdetails.php?customer_id=' . $arrService ['Account'] . '&id=' . $arrService ['Id'] . '&editInbound'
		);
		
		// Count the Total Time
		$fltTotalTime = microtime (TRUE) - $fltStartTime;
		
		// Insert the Information into the Database
		$arrScrape = Array (
			'ServiceId'			=> $intServiceId,
			'DataOriginal'		=> $strResponse,
			'DataSerialized'	=> ''
		);
		
		$insScrape->Execute ($arrScrape);
		
		// Add something to the Report
		$rptReport->AddMessageVariables (
			'+	<CurrentRow>		<TotalTime>	<ServiceId>	<Response>',
			Array (
				'<CurrentRow>'		=> sprintf ('%06d',	$intCurrentRow),
				'<TotalTime>'		=> sprintf ('%1.6f', $fltTotalTime),
				'<ServiceId>'		=> $intServiceId,
				'<Response>'		=> $insScrape->Error ()
			)
		);
		
		// Up the count
		++$intCurrentRow;
		
		// Flush the data out
		flush ();
	}
	
	echo '\n\n';
	
	$rptReport->Finish ();
	
?>
