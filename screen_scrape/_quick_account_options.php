<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	require ("decode_etech.php");
	
	$objDecode = new VixenDecode (Array ());
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	OPTION DIVERSION SPECIFIER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	$intCurrentRow = 0;
	$intTotalCharges = 0;
	
	$arrOptions = Array (
		"DisableDDR"			=> "",
		"DisableLatePayment"	=> ""
	);
	
	$updAccount = new StatementUpdate ("Account", "Id = <Id>", $arrOptions);
	
	while ($arrOptions = $objDecode->FetchAccountOptions ())
	{
		++$intCurrentRow;
		
		$updAccount->Execute (
			$arrOptions ['DataArray'],
			Array (
				"Id"		=> $arrOptions ['CustomerId']
			)
		);
		
		$rptReport->AddMessageVariables (
			"+	<CurrentRow>		<CustomerID>	<Response>",
			Array (
				"<CurrentRow>"		=> $intCurrentRow,
				"<CustomerID>"		=> $arrOptions ['CustomerId'],
				"<Response>"		=> "DONE"
			)
		);
	}
	
	$rptReport->Finish ();
	
?>
