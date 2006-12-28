<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ("config/application_loader.php");
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ("Location: login.php"); exit;
	}
	
	// Check that there is an Id we're wishing to retrieve
	if (!isset ($_GET ['Id']))
	{
		// If there aren't any - go away
		header ("Location: console.php"); exit;
	}
	
	$docDocumentation->Explain ("Rate Plan");
	
	
	
	
	// Create a Base Object
	$oblarrDetails	= $Style->attachObject (new dataArray ('RatePlanDetails'));
	
	// Add the details for the Rate Plan
	$rplRatePlans	= $oblarrDetails->Push (new RatePlan ($_GET ['Id']));
	
	// Include the Recurring Charge Types that are Associated with this Rate Plan
	$oblarrRecurringCharges		= $oblarrDetails->Push ($rplRatePlans->RecurringChargeTypes ());
	
	
	// Include the associated Rate Groups for this Rate Plan
	$oblarrDetails->Push ($rplRatePlans->RateGroups ());
	
	
	$Style->Output ("xsl/content/rates/plans/view.xsl");
	
?>
