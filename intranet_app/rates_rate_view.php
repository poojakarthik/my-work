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
	$arrPage['Permission']	= PERMISSION_ADMIN;
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE | MODULE_RATE_GROUP | MODULE_RECORD_TYPE;
	
	// call application
	require ('config/application.php');
	
	try
	{
		$rrrRate = new Rate ($_GET ['Id']);
	}
	catch (Exception $e)
	{
		$Style->Output ("xsl/content/rates/rates/notfound.xsl");
		exit;
	}
	
	// Create a Base Object
	$oblarrDetails	= $Style->attachObject (new dataArray ('RateDetails'));
	
	// Add the details for the Rate
	$rrrRate = $oblarrDetails->Push ($rrrRate);
	
	// Include a list of Rate Groups that use this Rate
	$oblarrDetails->Push ($rrrRate->RateGroups ());
	
	
	$docDocumentation->Explain ("Rate");
	
	$Style->Output ("xsl/content/rates/rates/view.xsl");
	
?>
