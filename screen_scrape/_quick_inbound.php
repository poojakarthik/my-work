<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	require ("decode_etech.php");
	
	$objDecode = new VixenDecode (Array ());
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	INBOUND SERVICE DETAILS: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	$intCurrentRow = 0;
	
	$selService = new StatementSelect ("Service", "Id", "Account = <Account> AND FNN = <FNN>", NULL, 1);
	$insInbound = new StatementInsert ("ServiceInboundDetail");
	
	while ($arrInbound = $objDecode->FetchInboundDetail ())
	{
		++$intCurrentRow;
		
		$selService->Execute (
			Array (
				"Account"		=> $arrInbound ['CustomerId'],
				"FNN"			=> $arrInbound ['FNN']
			)
		);
		
		$arrService = $selService->Fetch ();
		
		$arrInbound ['DataArray']['Service'] = $arrService ['Id'];
		
		$insInbound->Execute ($arrInbound ['DataArray']);
		
		$rptReport->AddMessageVariables (
			"+	<CurrentRow>		<CustomerID>	<Response>",
			Array (
				"<CurrentRow>"		=> $intCurrentRow,
				"<CustomerID>"		=> $arrInbound ['CustomerId'],
				"<Response>"		=> "INBOUND ADDED: " . $arrInbound ['FNN']
			)
		);
	}
	
	$rptReport->Finish ();
	
?>
