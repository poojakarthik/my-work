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
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE_PLAN | MODULE_RATE_GROUP | MODULE_RATE | MODULE_RECURRING_CHARGE | MODULE_BILLING | MODULE_RECORD_TYPE;
	
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
	
	// Get the Record Types
	$rtyRecordType = $Style->attachObject (new RecordType ($rrgRateGroup->Pull ('RecordType')->getValue ()));
	
	
	// Get a list of Rates associated
	$rgrRateGroupRate = $Style->attachObject (new RateGroupRate ($rrgRateGroup->Pull ('Id')->getValue ()));
	$rgrRateGroupRate->Sample (1, 10);
	
	
	
	// Get the Documentation for Rate Plans
	$docDocumentation->Explain ("Rate Group");
	
	// Display the Output
	$Style->Output ("xsl/content/rates/groups/details.xsl");
	
?>
