<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	
	
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	ETECH CUSTOMER SYSTEM NOTE CACHE RUNNER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	
	
	// Read the Customers CSV File
	$cstCustomers = new Parser_CSV ('data/customers_short.csv');
	$rptReport->AddMessage ("+	CUSTOMER CSV HAS BEEN PARSED");
	$rptReport->AddMessage (MSG_HORIZONTAL_RULE);
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	// Load all the Employees
	$selEmployees = new StatementSelect ('Employee', '*');
	$selEmployees->Execute (Array ());
	
	$arrEmployees = Array ();
	foreach ($selEmployees->FetchAll () as $arrEmployee)
	{
		$arrEmployees [$arrEmployee ['FirstName'] . " " . $arrEmployee ['LastName']] = $arrEmployee ['Id'];
	}
	
	
	
	
	
	
	
	
	// Start Counting each Record (from record #1)
	$intCurrentRow = 0;
	
	// Setup the MySQLi Insert Query
	$selScrape = new StatementSelect ('ScrapeNoteSys', 'DataOriginal', 'CustomerId = <CustomerId>', null, '1');
	
	// Also - set up the statement incase we have to add a new note type
	$insNote = new StatementInsert ('Note');
	
	
	// Start Time
	$fltStartTime = microtime (TRUE);
	
	// Loop through each of the Customers
	foreach ($cstCustomers->CustomerList () AS $intCustomerId)
	{
		// Pull the Data
		$selScrape->Execute (Array ('CustomerId' => $intCustomerId));
		$arrScrape = $selScrape->Fetch ();
		
		$strResponse = $arrScrape ['DataOriginal'];
		
		
		// Read the DOM Document
		$domDocument	= new DOMDocument ('1.0', 'utf-8');
		@$domDocument->LoadHTML ($strResponse);
		
		$dxpPath		= new DOMXPath ($domDocument);
		
		
		//-----------------------------------------------
		//	Ok - Freeze Frame.
		//-----------------------------------------------
		
		$dncNotes = $dxpPath->Query ("//table[1]/tr[position() >= 4 and position() mod 2 = 0]");
		
		// Check if we are told there are "No Results"
		if ($dncNotes->length == 1)
		{
			$domRow = new DOMDocument ('1.0', 'utf-8');
			$domRow->formatOutput = true;
			@$domRow->appendChild (
				$domRow->importNode (
					$dncNotes->item (0),
					TRUE
				)
			);
			
			$xpaRow = new DOMXPath ($domRow);
			
			if ($xpaRow->Evaluate ("count(/tr/td[1][@colspan='3']) = 1"))
			{
				continue;
			}
		}
		
		$arrNotes = Array ();
		
		// Loop through each of the Rows
		foreach ($dncNotes as $dnoRow)
		{
			// Up the count
			++$intCurrentRow;
			
			$domRow = new DOMDocument ('1.0', 'utf-8');
			$domRow->formatOutput = true;
			@$domRow->appendChild (
				$domRow->importNode (
					$dnoRow,
					TRUE
				)
			);
			
			$xpaRow = new DOMXPath ($domRow);
			
			
			// Employee
			$strEmployee = preg_replace ("/^\W+/", "", $xpaRow->Query ("/tr/td[3]")->item (0)->nodeValue);
			$intEmployee = null;
			
			if ($strEmployee == "System")
			{
				$intEmployee = null;
			}
			else
			{
				if (isset ($arrEmployees [$strEmployee]))
				{
					$intEmployee = $arrEmployees [$strEmployee];
				}
				else
				{
					$rptReport->AddMessageVariables (
						"-	<CurrentRow>		<TotalTime>	<CustomerID>	<Response>\n",
						Array (
							"<CurrentRow>"		=> $intCurrentRow,
							"<TotalTime>"		=> sprintf ("%1.6f", microtime (TRUE) - $fltStartTime),
							"<CustomerID>"		=> $intCustomerId,
							"<Response>"		=> "EMPLOYEE NOT FOUND: " . $strEmployee
						)
					);
					
					continue;
				}
			}
			
			// Insert
			$intNote = $insNote->Execute (
				Array (
					'AccountGroup'	=>	$intCustomerId,
					'Account'		=>	$intCustomerId,
					'Employee'		=>	($intEmployee == null) ? null : $intEmployee,
					'NoteType'		=>	7,
					'Note'			=>	$xpaRow->Query ("/tr/td[2]")->item (0)->nodeValue,
					'Datetime'		=>	$xpaRow->Query ("/tr/td[1]")->item (0)->nodeValue
				)
			);
			
			// Add something to the Report
			$rptReport->AddMessageVariables (
				"+	<CurrentRow>		<TotalTime>	<CustomerID>	<Response>\n",
				Array (
					"<CurrentRow>"		=> $intCurrentRow,
					"<TotalTime>"		=> sprintf ("%1.6f", microtime (TRUE) - $fltStartTime),
					"<CustomerID>"		=> $intCustomerId,
					"<Response>"		=> "SYSTEM NOTE HAS BEEN CACHED"
				)
			);
		}
		
		// Flush the data out
		flush ();
	}
	
	echo "\n\n";
	
	$rptReport->Finish ();
	
?>
