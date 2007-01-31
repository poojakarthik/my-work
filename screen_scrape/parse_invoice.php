<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	
	// The first thing we have to do is get the INVOICE.HTML file
	// This is what we will be using as an example so we can leave
	// the cache runner do what it needs to
	
	
	$arrInvoices = Array ();
	
	// Start Time
	$fltStartTime = microtime (TRUE);
	
	$strFile = file_get_contents ("data/invoice.html");
	
	$domDocument	= new DOMDocument;
	@$domDocument->LoadHTML ($strFile);
	$dxpDocument	= new DOMXPath ($domDocument);	
	
	
	// Get the Table
	$dnlTable = $dxpDocument->Query ("//table");
	
	$domTable = new DOMDocument;
	$domTable->appendChild (
		$domTable->importNode (
			$dnlTable->Item (5),
			TRUE
		)
	);
	
	$dxpDocument	= new DOMXPath ($domTable);	
	
	
	// Get Each Possible Invoice Row
	$dnlRows		= $dxpDocument->Query ("/table/tr[position() >= 6 and position() < last()]");
	
	// Loop Through all the Invoice Rows
	foreach ($dnlRows as $dnoRow)
	{
		$domRow = new DOMDocument;
		$domRow->appendChild (
			$domRow->importNode (
				$dnoRow,
				TRUE
			)
		);
		
		$dxpRow		= new DOMXPath ($domRow);	
		
		// Pull the Invoice Information from the Rows
		$strMonthYear	= trim ($dxpRow->Query ("/tr/td[position() = 1]")->Item (0)->nodeValue);
		$strInvoiceId	= trim ($dxpRow->Query ("/tr/td[position() = 2]")->Item (0)->nodeValue);
		$strInvAmount	= trim ($dxpRow->Query ("/tr/td[position() = 3]")->Item (0)->nodeValue);
		$strInvApplied	= trim ($dxpRow->Query ("/tr/td[position() = 4]")->Item (0)->nodeValue);
		$strInvOwing	= trim ($dxpRow->Query ("/tr/td[position() = 5]")->Item (0)->nodeValue);
		$bolInvSent		= trim ($dxpRow->Query ("/tr/td[position() = 6]")->Item (0)->nodeValue) == "Yes";
		
		list ($strMonth, $strYear) = explode ("_", $strMonthYear);
		
		// To make sure we're dealing with an Invoice and not a pure PDF, 
		// check that the Invoice Number exists
		if ($strInvoiceId)
		{
			// Write up the Invoice add it to the Invoices array
			$arrInvoices [] = Array (
				"Month"			=> $strMonth,
				"Year"			=> $strYear,
				"InvoiceId"		=> $strInvoiceId,
				"Amount"		=> $strInvAmount,
				"Applied"		=> $strInvApplied,
				"Owing"			=> $strInvOwing,
				"Sent"			=> $bolInvSent
			);
		}
	}
	
	print_r ($arrInvoices);
	
?>
