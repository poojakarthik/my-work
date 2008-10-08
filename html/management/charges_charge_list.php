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
	$arrPage['Modules']		= MODULE_BASE | MODULE_CHARGE | MODULE_CHARGE_TYPE;
	
	// call application
	require ('config/application.php');
	
	
	// Explain the Fundamentals
	$docDocumentation->Explain ('Charge Type');
	
	// Start a new Account Search
	$rclChargeTypes = $Style->attachObject (new ChargeTypes ());
	$rclChargeTypes->Constrain('Archived', '=', FALSE);
	$rclChargeTypes->Constrain('automatic_only', '=', 0);
	
	$rclChargeTypes->Order('ChargeType', TRUE);
	$rclChargeTypes->Sample ();
	
	$Style->Output ('xsl/content/charges/charges/list.xsl');
	
?>
