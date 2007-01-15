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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT_GROUP | MODULE_ACCOUNT | MODULE_PAYMENT;
	
	// call application
	require ('config/application.php');
	
	// Get the Service we just added the recurring charge to
	try
	{
		$payPayment = $Style->attachObject (new Payment ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		// If the service is not found, error
		$Style->Output ('xsl/content/payment/notfound.xsl');
		exit;
	}
	
	// Display the happy message
	$Style->Output ("xsl/content/payment/added.xsl");
	
?>
