<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	
	
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	ETECH CUSTOMER SYSTEM NOTE CACHE RUNNER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	
	
	
	
	
	
	
	
	
	// Start Counting each Record (from record #1)
	$intCurrentRow = 0;
	
	// Start the Pull from the Database
	$selScrape = new StatementSelect ('ScrapeRates', '*', '');
	$selScrape->Execute (Array ());
	
	// Start an Update MySQLi
	$updScrape = new StatementUpdate ('ScrapeRates', 'AxisN = <AxisN> AND AxisM = <AxisM>', Array ('DataSerialised' => ''));
	
	
	// Start Time
	$fltStartTime = microtime (TRUE);
	
	// Loop through each of the Customers
	foreach ($selScrape->FetchAll () as $arrScrape)
	{
		$strResponse = $arrScrape ['DataOriginal'];
		
		
		// Read the DOM Document
		$domDocument	= new DOMDocument ('1.0', 'utf-8');
		@$domDocument->LoadHTML ($strResponse);
		
		$dxpPath		= new DOMXPath ($domDocument);
		
		//-----------------------------------------------
		//	Ok - Freeze Frame.
		//-----------------------------------------------
		
		$arrRateData = Array (
			"Title"		=> $dxpPath->Query		("//table[1]/tr[1]/td[1]/strong[1]")->item (0)->nodeValue,
			"SetCap"	=> $dxpPath->Evaluate	("count(//table[1]/tr[8]/td[1]/input[@checked]) != 0"),
			"CapTime"	=> $dxpPath->Query		("//table[1]/tr[8]/td[3]/input[1]")->item (0)->getAttribute ("value"),
			"MaxCost"	=> $dxpPath->Query		("//table[1]/tr[8]/td[4]/input[1]")->item (0)->getAttribute ("value"),
			"StdFlag"	=> $dxpPath->Query		("//table[1]/tr[11]/td[5]/input[1]")->item (0)->getAttribute ("value"),
			"StdMin"	=> $dxpPath->Query		("//table[1]/tr[12]/td[5]/input[1]")->item (0)->getAttribute ("value"),
			"PostFlag"	=> $dxpPath->Query		("//table[1]/tr[11]/td[6]/input[1]")->item (0)->getAttribute ("value"),
			"PostMin"	=> $dxpPath->Query		("//table[1]/tr[12]/td[6]/input[1]")->item (0)->getAttribute ("value"),
			"Rates"		=> Array ()
		);
		
		$dncNotes = $dxpPath->Query ("//table[1]/tr[position() >= 13 and position() <= (last() - 2)]");
		
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
			
			$arrRateData ['Rates'] [] = Array (
				"Destination"	=> trim ($xpaRow->Query ("/tr/td[1]")->item (0)->nodeValue),
				"CapSet"		=> $xpaRow->Evaluate ("count(/tr/td[2]/input[@checked]) != 0"),
				"CapSeconds"	=> $xpaRow->Query ("/tr/td[3]/input")->item (0)->getAttribute ("value"),
				"CapCost"		=> $xpaRow->Query ("/tr/td[4]/input")->item (0)->getAttribute ("value"),
				"StdRate"		=> $xpaRow->Query ("/tr/td[5]/input")->item (0)->getAttribute ("value"),
				"PostCredit"	=> $xpaRow->Query ("/tr/td[6]/input")->item (0)->getAttribute ("value")
			);
		}
		
		// Update
		$updScrape->Execute (
			Array (
				'DataSerialised'	=>	serialize ($arrRateData),
			),
			
			Array (
				'AxisN'				=> $arrScrape ['AxisN'],
				'AxisM'				=> $arrScrape ['AxisM']
			)
		);
		
		// Add something to the Report
		$rptReport->AddMessageVariables (
			"+	<N>		<M>		<TotalTime>	<Response>",
			Array (
				"<N>"				=> sprintf ("%02d", $arrScrape ['AxisN']),
				"<M>"				=> sprintf ("%02d", $arrScrape ['AxisM']),
				"<TotalTime>"		=> sprintf ("%1.6f", microtime (TRUE) - $fltStartTime),
				"<Response>"		=> "DATA HAS BEEN NORMALISED"
			)
		);
		
		// Flush the data out
		flush ();
	}
	
	echo "\n\n";
	
	$rptReport->Finish ();
	
?>
