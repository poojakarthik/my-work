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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_RECURRING_CHARGE | MODULE_BILLING;
	
	// call application
	require ('config/application.php');
	
	try
	{
		$actAccount = $Style->attachObject (new Account ($_GET ['Account']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	$rclRecurringCharges = $Style->attachObject ($actAccount->RecurringCharges ());
	$rclRecurringCharges->Sample ();
	
	$Style->Output ("xsl/content/account/recurring_charge_list.xsl");
	
?>
