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
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE_GROUP | MODULE_RATE | MODULE_RATE_PLAN | MODULE_RECORD_TYPE;
	
	// call application
	require ('config/application.php');
	
	try
	{
		$rrgRateGroup = new RateGroup ($_GET ['Id']);
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/rates/groups/notfound.xsl');
		exit;
	}
	
	// Create a Base Object
	$oblarrDetails	= $Style->attachObject (new dataArray ('RateGroupDetails'));
	
	// Add the details for the Rate Group
	$rrgRateGroup	= $oblarrDetails->Push ($rrgRateGroup);
	
	
	$rgrRateGroupRate = $oblarrDetails->Push (new RateGroupRate ($_GET ['Id']));
	$rgrRateGroupRate->Sample (1, 10);
	
	/*
	// Include the Rate Plans that use this Rate Group
	$oblarrDetails->Push ($rrgRateGroup->RatePlans ());
	
	// Documentation for a Rate Group
	$docDocumentation->Explain ("Rate Group");
	*/
	
	$Style->Output ("xsl/content/rates/groups/view.xsl");
	
?>
