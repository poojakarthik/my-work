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
	$arrPage['Permission']	= PERMISSION_CREDIT_MANAGEMENT;
	$arrPage['Modules']		= MODULE_BASE | MODULE_CHARGE | MODULE_CHARGE_TYPE;
	
	// call application
	require ('config/application.php');
	
	
	// Get the Charge Type
	try
	{
		$rctChargeType = $Style->attachObject (new ChargeType ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/windowclose.xsl');
		exit;
	}
	
	// Output the Account View
	$Style->Output ('xsl/content/charges/charges/view.xsl');
	
?>
