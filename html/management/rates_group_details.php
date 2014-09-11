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
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE_GROUP | MODULE_RATE | MODULE_RECURRING_CHARGE | MODULE_BILLING | MODULE_RECORD_TYPE;
	
	// call application
	require ('config/application.php');
	
	// Get the Rate Plan being enquired for
	try
	{
		$rrgRateGroup = $Style->attachObject (new RateGroup ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/rates/groups/notfound.xsl');
		exit;
	}
	
	// Get a list of Rates associated
	$rgrRateGroupRate		= new RateGroupRate ($rrgRateGroup->Pull ('Id')->getValue ());
	$oblsamRateGroupRate	= $rgrRateGroupRate->Sample (
		($_GET ['rangePage'])	? $_GET ['rangePage']	: 1, 
		($_GET ['rangeLength'])	? $_GET ['rangeLength']	: 20
	);
	
	$Style->attachObject ($oblsamRateGroupRate);
	
	// Get the Documentation for Rate Plans
	$docDocumentation->Explain ("Rate Group");
	
	// Display the Output
	$Style->Output ("xsl/content/rates/groups/details.xsl");
	
?>
