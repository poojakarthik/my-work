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
	
	$docDocumentation->Explain ("Rate Group");
	
	// Create a Base Object
	$oblarrDetails	= $Style->attachObject (new dataArray ('RateGroupDetails'));
	
	// Add the details for the Rate Group
	$rrgRateGroup	= $oblarrDetails->Push (new RateGroup ($_GET ['Id']));
	
	// Include the associated Rates for this Rate Group
	$selRates = new StatementSelect ('RateGroupRate', 'Rate', 'RateGroup = <RateGroup>');
	$selRates->Execute (Array ('RateGroup' => $_GET ['Id']));
	
	$oblarrRates 	= $oblarrDetails->Push (new dataArray ('Rates'));
	
	foreach ($selRates->FetchAll () as $arrRate)
	{
		$oblarrRates->Push (new Rate ($arrRate ['Rate']));
	}
	
	// Include the Rate Plans that use this Rate Group
	$selPlans = new StatementSelect ('RatePlanRateGroup', 'RatePlan', 'RateGroup = <RateGroup>');
	$selPlans->Execute (Array ('RateGroup' => $_GET ['Id']));
	
	$oblarrPlans 	= $oblarrDetails->Push (new dataArray ('RatePlans'));
	
	foreach ($selPlans->FetchAll () as $arrPlan)
	{
		$oblarrPlans->Push (new RatePlan ($arrPlan ['RatePlan']));
	}
	
	$Style->Output ("xsl/content/rates/groups/view.xsl");
	
?>
