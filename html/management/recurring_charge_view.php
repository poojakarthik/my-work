<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= TRUE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_SERVICE | MODULE_RECURRING_CHARGE | MODULE_BILLING;
	
	// call application
	require ('config/application.php');
	
	
	try
	{
		// Get the Recurring Charge
		$rciRecurringCharge = $Style->attachObject (new RecurringCharge ($_GET ['Id']));
		$actAccount = $rciRecurringCharge->Account ();
		$srvService = $rciRecurringCharge->Service ();
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/recurringcharge/notfound.xsl');
		exit;
	}
	
	// Explain an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Recurring Charge Type');
	$docDocumentation->Explain ('Archived');
	
	$Style->Output ("xsl/content/recurringcharge/view.xsl");
	
?>
