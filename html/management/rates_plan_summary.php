<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	header("Location: ../admin/flex.php/Plan/View/?RatePlan.Id=". $_GET['Id']);

	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR_VIEW;
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE_PLAN | MODULE_RATE_GROUP | MODULE_RATE | MODULE_RECURRING_CHARGE | MODULE_BILLING | MODULE_RECORD_TYPE;
	
	// call application
	require ('config/application.php');
	
	// Get the Rate Plan being enquired for
	try
	{
		$rplRatePlan = $Style->attachObject (new RatePlan ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/rates/plans/notfound.xsl');
		exit;
	}
	
	// Get the Record Types associated with this Rate Group's Service Type
	$rtsRecordTypes = $Style->attachObject (new RecordTypes ());
	$rtsRecordTypes->Constrain ('ServiceType', '=', $rplRatePlan->Pull ('ServiceType')->getValue ());
	$rtsRecordTypes->Sample ();
	
	$Style->attachObject (new RecordDisplayTypes);
	
	// Get all the Rate Groups associated with this Rate Plan
	$rglRateGroups = $Style->attachObject ($rplRatePlan->RateGroups ());
	
	// Get 5 of the Rates associated with the Rate Group
	// and also Store the Rates that are being pulled (in a normal Array)
	$oblarrRateGroupRates = $Style->attachObject (new dataArray ('RateGroupRates'));
	$arrRates = Array ();
	
	foreach ($rglRateGroups as $rrgRateGroup)
	{
		$oblarrRateGroupRate = $oblarrRateGroupRates->Push (new dataArray ('RateGroupRate'));
		
		$oblstrRateGroup	= $oblarrRateGroupRate->Push (new dataString ('RateGroup', $rrgRateGroup->Pull ('Id')->getValue ()));
		$oblarrRates		= $oblarrRateGroupRate->Push ($rrgRateGroup->RatesListing (5));
		
		foreach ($oblarrRates as $oblstrRate)
		{
			$arrRates [$oblstrRate->getValue ()] = "";
		}
	}
	
	$Style->attachObject (Rates::getRates ($arrRates));
	
	// Include the Recurring Charge Types that are Associated with this Rate Plan
	$oblarrRecurringCharges		= $Style->attachObject ($rplRatePlan->RecurringChargeTypes ());
	
	// Get the Documentation for Rate Plans
	$docDocumentation->Explain ("Rate Plan");
	
	// Display the Output
	$Style->Output ("xsl/content/rates/plans/summary.xsl");
	
?>
