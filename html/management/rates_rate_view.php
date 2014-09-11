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
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE | MODULE_RATE_GROUP | MODULE_RECORD_TYPE;
	
	// call application
	require ('config/application.php');
	
	// Create a Base Object
	$oblarrDetails	= $Style->attachObject (new dataArray ('RateDetails'));
	
	try
	{
		// Add the details for the Rate
		$rrrRate = $oblarrDetails->Push (new Rate ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ("xsl/content/rates/rates/notfound.xsl");
		exit;
	}
	
	$docDocumentation->Explain ("Rate");
	
	$Style->Output ("xsl/content/rates/rates/view.xsl");
	
?>
