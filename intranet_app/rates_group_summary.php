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
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE_GROUP | MODULE_RATE | MODULE_RECORD_TYPE;
	
	// call application
	require ('config/application.php');
	
	
	// If there is at least one Selected Rate, build the table
	if (isset ($_POST ['SelectedRates']))
	{
		$rglRateGroups = new RateGroups ();
		$Style->attachObject ($rglRateGroups->RateAvailability ($_POST ['SelectedRates']));
	}
	
	$Style->Output ("xsl/content/rates/groups/summary.xsl");
	
?>
