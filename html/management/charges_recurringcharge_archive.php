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
	$arrPage['Permission']	= PERMISSION_CREDIT_MANAGEMENT;
	$arrPage['Modules']		= MODULE_BASE | MODULE_RECURRING_CHARGE | MODULE_BILLING;
	
	// call application
	require ('config/application.php');
	
	try
	{
		$rctRecurringCharge = $Style->attachObject (new RecurringChargeType (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/charges/recurringcharges/notfound.xsl');
		exit;
	}
	
	if (isset ($_POST ['Confirm']))
	{
		if ($_POST ['Confirm'])
		{
			$rctRecurringCharge->Archive (TRUE);
		}
		
		$Style->Output ('xsl/content/charges/recurringcharges/archive_confirmed.xsl');
		exit;
	}
	
	$Style->Output ('xsl/content/charges/recurringcharges/archive_confirm.xsl');
	
?>
