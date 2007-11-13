<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR_VIEW;
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_INVOICE;
	
	// call application
	require ('config/application.php');
	
	
	// Get Invoice PDF
	try
	{
		// Try to pull the Invoice PDF
		$strInvoice = getPDF (
			$_GET ['Account'],
			$_GET ['Year'],
			$_GET ['Month']
		);
		
		if ($strInvoice == "")
		{
			throw new Exception ("Not Found");
		}
		
		$strInvoiceFilename = GetPdfFilename($_GET['Account'], $_GET['Year'], $_GET['Month']);
		
		header ("Content-Type: application/pdf");
		header("Content-Disposition: attachment; filename=\"$strInvoiceFilename\"");
		echo $strInvoice;
		exit;
	}
	catch (Exception $e)
	{
		// Dispaly Error
		$Style->Output (
			'xsl/content/invoice/pdf_notfound.xsl',
			Array (
				'Account'	=> $_GET ['Account']
			)
		);
		exit;
	}
	
?>
