<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	
	// The first thing we have to do is get the INVOICE.HTML file
	// This is what we will be using as an example so we can leave
	// the cache runner do what it needs to
	
	
	$arrInvoice = Array ();
	
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
	
	$dnlRows		= $dxpDocument->Query ("/table/tr[position() >= 6 and position() < last()]");
	
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
		
		$strMonthYear	= trim ($dxpRow->Query ("/tr/td[position() = 1]")->Item (0)->nodeValue);
		$strInvoiceId	= trim ($dxpRow->Query ("/tr/td[position() = 2]")->Item (0)->nodeValue);
		$strInvAmount	= trim ($dxpRow->Query ("/tr/td[position() = 3]")->Item (0)->nodeValue);
		$strInvApplied	= trim ($dxpRow->Query ("/tr/td[position() = 4]")->Item (0)->nodeValue);
		$strInvOwing	= trim ($dxpRow->Query ("/tr/td[position() = 5]")->Item (0)->nodeValue);
		
		list ($strMonth, $strYear) = explode ("_", $strMonthYear);
		
		if ($strInvoiceId)
		{
			$arrInvoice [] = Array (
				"Month"			=> $strMonth,
				"Year"			=> $strYear,
				"InvoiceId"		=> $strInvoiceId,
				"Amount"		=> $strInvAmount,
				"Applied"		=> $strInvApplied,
				"Owing"			=> $strInvOwing
			);
		}
	}
	
	print_r ($arrInvoice);
	
	echo "\n\n\n";
	echo "Done in " . (microtime (TRUE) - $fltStartTime) . " seconds\n\n\n\n";
	
	// Get the List of Invoices Table
	
//	echo $domTable->saveXML ();
	
?>
