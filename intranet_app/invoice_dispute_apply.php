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
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_INVOICE | MODULE_CHARGE | MODULE_CDR | MODULE_SERVICE | MODULE_SERVICE_TOTAL;
	
	// call application
	require ('config/application.php');
	
	try
	{
		// Get the Invoice
		$invInvoice		= $Style->attachObject (new Invoice (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/invoice/notfound.xsl');
		exit;
	}
	
	// UI Values (Remember)
	$oblarrUIValues = $Style->attachObject (new dataArray ('ui-values'));
	$oblstrDisputed = $oblarrUIValues->Push (new dataFloat ('Disputed'));
	
	if ($_POST ['Disputed'])
	{
		$oblstrDisputed->setValue ($_POST ['Disputed']);
		
		// If we have set a dispute forward
		$oblfltDisputed = new dataFloat ('Disputed');
		
		// Check the Dispute is valid
		if ($oblfltDisputed->setValue ($_POST ['Disputed']))
		{
			$invInvoice->Dispute ($_POST ['Disputed']);
			
			// If it is valid, show a Confirmation
			$Style->Output ('xsl/content/invoice/dispute/applied.xsl');
			exit;
		}
	}
	else
	{
		// Get the current database value
		$oblstrDisputed->setValue ($invInvoice->Pull ('Disputed')->getValue ());
	}
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Invoice');
	
	// Output the Account View
	$Style->Output ('xsl/content/invoice/dispute/apply.xsl');
	
?>
