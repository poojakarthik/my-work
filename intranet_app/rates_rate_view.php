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
	$oblarrDetails	= $Style->attachObject (new dataArray ('RateDetails'));
	
	// Add the details for the Rate Group
	$rrrRate		= $oblarrDetails->Push (new Rate ($_GET ['Id']));
	
	// Include a list of Rate Groups that use this Rate
	$oblarrDetails->Push ($rrrRate->RateGroups ());
	
	
	$docDocumentation->Explain ("Rate");
	
	$Style->Output ("xsl/content/rates/rates/view.xsl");
	
?>
