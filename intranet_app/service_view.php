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
	//TODO!!!! - finish this
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_NOTE | MODULE_CARRIER | 
							  MODULE_PROVISIONING | MODULE_RECURRING_CHARGE_TYPE | MODULE_BILLING | MODULE_CHARGE_TYPE;
	
	// call application
	require ('config/application.php');
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Archive');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Service Address');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Carrier');
	$docDocumentation->Explain ('Provisioning');
	
	try
	{
		// Get the Service
		$srvService		= $Style->attachObject (new Service ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	$srvService->UnbilledChargeCostCurrent ();
	
	// Get the Service Address Information
	$srvService->ServiceAddress ();
	
	// Get the Account
	$actAccount		= $Style->attachObject (new Account ($srvService->Pull ('Account')->getValue ()));
	
	// Get information about Note Types
	$ntsNoteTypes	= $Style->attachObject (new NoteTypes ());
	
	// Get Associated Notes
	$nosNotes		= $Style->attachObject (new Notes ());
	$nosNotes->Constrain ('Service', '=', $_GET ['Id']);
	$nosNotes->Sample ();
	
	// Load the List of Carrier Objects
	$calCarriers	= $Style->attachObject (new Carriers ());
	
	// Load the List of Provisioning Request Type Objects
	$prtPRQTypes	= $Style->attachObject (new ProvisioningRequestTypes ());
	
	// ChargeType and RecurringChargeType
	$tctCharges = $Style->attachObject (new dataArray ('TemplateChargeTypes'));
	
	$rclRecurringChargeTypes	= $tctCharges->Push (new RecurringChargeTypes ());
	$rclRecurringChargeTypes->Sample ();
	
	$octChargeTypes				= $tctCharges->Push (new ChargeTypes ());
	$octChargeTypes->Sample ();
	
	// Output the Account View
	$Style->Output ('xsl/content/service/view.xsl');
	
?>
