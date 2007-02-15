<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	require ("decode_etech.php");
	
	$objDecode = new VixenDecode (Array ());
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	PASSWORD ALLOCATION RUNNER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	$intCurrentRow = 0;
	$intTotalCharges = 0;
	
	$arrContactPassword = Array (
		"PassWord"	=> ""
	);
	
	$updContact = new StatementUpdate ("Contact", "Account = <Account>", $arrContactPassword);
	
	while ($arrPassword = $objDecode->FetchPassword ())
	{
		++$intCurrentRow;
		
		$updContact->Execute (
			Array (
				"PassWord"		=> sha1 ($arrPassword ['DataArray']['password'])
			),
			Array (
				"Account"		=> $arrPassword ['CustomerId']
			)
		);
		
		$rptReport->AddMessageVariables (
			"+	<CurrentRow>		<CustomerID>	<Response>",
			Array (
				"<CurrentRow>"		=> $intCurrentRow,
				"<CustomerID>"		=> $arrPassword ['CustomerId'],
				"<Response>"		=> "PASSWORD DELEGATED: " . $arrPassword ['DataArray']['password']
			)
		);
	}
	
	$rptReport->Finish ();
	
?>
