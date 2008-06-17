<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= TRUE;
	$arrPage['Permission']	= PERMISSION_OPERATOR_VIEW;
	$arrPage['Modules']		= MODULE_BASE | MODULE_CHARGE | MODULE_SERVICE;
	
	// call application
	require ('config/application.php');
	
	// Pull documentation information
	$docDocumentation->Explain ('Charge Type');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Invoice');
	
	// Get the CDR
	try
	{
		$crgCharge = $Style->attachObject (new Charge ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/charges/charges/notfound.xsl');
		exit;
	}
	
	// Output the Account View
	$Style->Output ('xsl/content/charges/charges/details.xsl');
	
?>
