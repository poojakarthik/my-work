<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	require ("decode_etech.php");
	
	$objDecode = new VixenDecode (Array ());
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	CHARGES DIVERSION RUNNER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	$intCurrentRow = 0;
	$intTotalCharges = 0;
	
	$insCharge = new StatementInsert ("Charge");
	
	while ($arrCharges = $objDecode->FetchCharges ())
	{
		++$intCurrentRow;
		
		foreach ($arrCharges ['DataArray'] as $arrCharge)
		{
			$insCharge->Execute ($arrCharge);
			$intTotalCharges += 1;
		}
		
		$rptReport->AddMessageVariables (
			"+	<CurrentRow>		<CustomerID>	<Response>",
			Array (
				"<CurrentRow>"		=> $intCurrentRow,
				"<CustomerID>"		=> $arrCharges ['CustomerId'],
				"<Response>"		=> "CHARGES ADDED: " . count ($arrCharges ['DataArray'])
			)
		);
	}
	
	$rptReport->AddMessageVariables (
		"\n\n	Total number of Charges:	<Charges>\n",
		Array (
			"<Charges>"			=> $intTotalCharges
		)
	);
	
	$rptReport->Finish ();
	
?>
