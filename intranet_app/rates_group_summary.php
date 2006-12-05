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
	
	
	// If there is at least one Selected Rate, build the table
	if (isset ($_POST ['SelectedRates']))
	{
		$rglRateGroups = new RateGroups ();
		$Style->attachObject ($rglRateGroups->RateAvailability ($_POST ['SelectedRates']));
	}
	
	$Style->Output ("xsl/content/rates/groups/summary.xsl");
	
?>
