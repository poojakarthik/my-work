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
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE_PLAN | MODULE_RATE_GROUP | MODULE_RECURRING_CHARGE | MODULE_BILLING | MODULE_RECORD_TYPE;
	
	// call application
	require ('config/application.php');
	
	try
	{
		$rplRatePlans = new RatePlan ($_GET ['Id']);
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/rates/plans/notfound.xsl');
		exit;
	}
	
	// Create a Base Object
	$oblarrDetails	= $Style->attachObject (new dataArray ('RatePlanDetails'));
	
	// Add the details for the Rate Plan
	$rplRatePlans = $oblarrDetails->Push ($rplRatePlans);
	
	// Include the Recurring Charge Types that are Associated with this Rate Plan
	$oblarrRecurringCharges		= $oblarrDetails->Push ($rplRatePlans->RecurringChargeTypes ());
	
	// Include the associated Rate Groups for this Rate Plan
	$oblarrDetails->Push ($rplRatePlans->RateGroups ());
	
	$docDocumentation->Explain ("Rate Plan");
	$Style->Output ("xsl/content/rates/plans/view.xsl");
	
?>
