<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	
	
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	ETECH CUSTOMER USER NOTE CACHE RUNNER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	
	
	// Read the Customers CSV File
	$cstCustomers = new Parser_CSV ('data/customers_short.csv');
	$rptReport->AddMessage ("+	CUSTOMER CSV HAS BEEN PARSED");
	$rptReport->AddMessage (MSG_HORIZONTAL_RULE);
	
	
	
	
	
	// Start Counting each Record (from record #1)
	$intCurrentRow = 1;
	
	// Setup the MySQLi Insert Query
	$selScrape = new StatementSelect ('ScrapeNoteUser', 'DataOriginal', 'CustomerId = <CustomerId>', null, '1');
	
	
	
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
		//	We want to do the following
		//	
		//	1.	Get the second table in the page (because that's where the data resides)
		//	2.	Get the Third Row in the table and make sure it doesn't state that the
		//		table is empty
		//	3.	If the Third Row does not State that there are no rows
		//		1.	Get Each Row After the Third Row EXCEPT the last row
		//-----------------------------------------------
		
		// 1.	Get the second table in the page
		//	2.	Get the Third Row in the table and make sure it doesn't state that the
		//		table is empty
		
		$dncNotes = $dxpPath->Query ("//table[2]/tr[position() >= 3 and position() mod 2 = 1]");
		
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
			$domRow = new DOMDocument ('1.0', 'utf-8');
			$domRow->formatOutput = true;
			@$domRow->appendChild (
				$domRow->importNode (
					$dnoRow,
					TRUE
				)
			);
			
			$xpaRow = new DOMXPath ($domRow);
			
			$strDatetime = $xpaRow->Query ("/tr/td[1]")->item (0)->nodeValue;
			$arrDatetime = preg_split ("/\s+/", $strDatetime);
			
			$arrMonths = Array (
				"January"		=> 1,
				"February"		=> 2,
				"March"			=> 3,
				"April"			=> 4,
				"May"			=> 5,
				"June"			=> 6,
				"July"			=> 7,
				"August"		=> 8,
				"September"	=> 9,
				"October"		=> 10,
				"November"		=> 11,
				"December"		=> 12
			);
			
			$arrTime = preg_split ("/\:/", $arrDatetime [4]);
			
			$intDatetime = mktime (
				$arrTime [0],
				$arrTime [1],
				0,
				$arrMonths [$arrDatetime [0]],
				substr ($arrDatetime [1], 0, -1),
				$arrDatetime [2]
			);
			
			$arrNotes [] = Array (
				'AccountGroup'	=>	$intCustomerId,
				'Account'		=>	$intCustomerId,
				'Employee'		=>	$xpaRow->Query ("/tr/td[4]")->item (0)->nodeValue,
				'NoteType'		=>	$xpaRow->Query ("/tr/td[3]")->item (0)->nodeValue,
				'Note'			=>	$xpaRow->Query ("/tr/td[2]")->item (0)->nodeValue,
				'Datetime'		=>	date ("Y-m-d H:i:s", $intDatetime)
			);
		}
		
		print_r ($arrNotes);
		
		// Up the count
		++$intCurrentRow;
		
		// Flush the data out
		flush ();
	}
	
	echo "\n\n";
	
	$rptReport->Finish ();
	
?>
