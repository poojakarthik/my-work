<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	require ("decode_etech.php");
	
	$objDecode = new VixenDecode (Array ());
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	INVOICE DIVERSION RUNNER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	$intCurrentRow = 0;
	$intTotalCharges = 0;
	
	$arrInvoiceInsert = Array (
		"Id"				=> "",
		"AccountGroup"		=> "",
		"Account"			=> "",
		"CreatedOn"			=> "",
		"DueOn"				=> "",
		"SettledOn"			=> "",
		"Credits"			=> "",
		"Debits"			=> "",
		"Total"				=> "",
		"Tax"				=> "",
		"Balance"			=> "",
		"Disputed"			=> "",
		"AccountBalance"	=> "",
		"Status"			=> "",
		"InvoiceRun"		=> ""
	);
	
	$insInvoice = new StatementInsert ("Invoice_Bash", $arrInvoiceInsert, TRUE);
	
	while ($arrInvoices = $objDecode->FetchInvoiceDetail ())
	{
		++$intCurrentRow;
		
		foreach ($arrInvoices ['DataArray'] as $arrInvoice)
		{
			$intInvoiceDate = strtotime ($arrInvoice ['Year'] . "-" . $arrInvoice ['Month'] . "-01");
			$intInvoiceDate = strtotime ("+1 month", $intInvoiceDate);
			
			$arrInvoiceInsert = Array (
				"Id"				=> $arrInvoice ['InvoiceId'],
				"AccountGroup"		=> $arrInvoices ['CustomerId'],
				"Account"			=> $arrInvoices ['CustomerId'],
				"CreatedOn"			=> date ("Y-m-d", $intInvoiceDate),
				"DueOn"				=> "0000-00-00",
				"SettledOn"			=> "0000-00-00",
				"Credits"			=> "0",
				"Debits"			=> "0",
				"Total"				=> $arrInvoice ['Amount'],
				"Tax"				=> ($arrInvoice ['Amount'] * .1),
				"Balance"			=> $arrInvoice ['Owing'],
				"Disputed"			=> "",
				"AccountBalance"	=> "",
				"Status"			=> "",
				"InvoiceRun"		=> ""
			);
			
			$insInvoice->Execute ($arrInvoiceInsert);
			$intTotalCharges += 1;
		}
		
		$rptReport->AddMessageVariables (
			"+	<CurrentRow>		<CustomerID>	<Response>",
			Array (
				"<CurrentRow>"		=> $intCurrentRow,
				"<CustomerID>"		=> $arrInvoices ['CustomerId'],
				"<Response>"		=> "INVOICES ADDED: " . count ($arrInvoices ['DataArray'])
			)
		);
	}
	
	$rptReport->AddMessageVariables (
		"\n\n	Total number of Recurring Charges:	<Charges>\n",
		Array (
			"<Charges>"			=> $intTotalCharges
		)
	);
	
	$rptReport->Finish ();
	
?>
