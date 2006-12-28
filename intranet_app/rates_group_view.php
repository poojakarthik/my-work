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
	
	
	
	// Create a Base Object
	$oblarrDetails	= $Style->attachObject (new dataArray ('RateGroupDetails'));
	
	// Add the details for the Rate Group
	$rrgRateGroup	= $oblarrDetails->Push (new RateGroup ($_GET ['Id']));
	
	
	
	// Include the associated Rates for this Rate Group
	$oblarrDetails->Push ($rrgRateGroup->Rates ());
	
	// Include the Rate Plans that use this Rate Group
	$oblarrDetails->Push ($rrgRateGroup->RatePlans ());
	
	
	// Documentation for a Rate Group
	$docDocumentation->Explain ("Rate Group");
	
	$Style->Output ("xsl/content/rates/groups/view.xsl");
	
?>
