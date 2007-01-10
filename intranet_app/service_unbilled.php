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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_CDR;
	
	// call application
	require ('config/application.php');
	
	try
	{
		// Get the Service
		$srvService = $Style->attachObject (new Service ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	$cdrUnbilled = $srvService->UnbilledCharges ();
	$Style->attachObject (
		$cdrUnbilled->Sample (
			isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
			isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 30
		)
	);
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Service');
	
	// Output the Service Unbilled Charges
	$Style->Output ('xsl/content/service/unbilled.xsl');
	
?>
