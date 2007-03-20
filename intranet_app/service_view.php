<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ('config/application_loader.php');
	// call application loader
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_RATE_PLAN | MODULE_NOTE | MODULE_EMPLOYEE | MODULE_RECURRING_CHARGE | MODULE_BILLING | MODULE_CHARGE_TYPE | MODULE_COST_CENTRE | MODULE_INBOUND;
	
	// call application
	require ('config/application.php');
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Archive');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Charge Type');
	$docDocumentation->Explain ('Recurring Charge Type');
	$docDocumentation->Explain ('Service Inbound');
	
	try
	{
		// Get the Service
		$srvService		= $Style->attachObject (new Service ($_GET ['Id']));
		$actAccount		= $Style->attachObject ($srvService->getAccount ());
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	//Get the details if an Inbound Service
	try
	{
		if ($srvService->Pull ('ServiceType')->getValue () == SERVICE_TYPE_INBOUND)
		{
			$inbInboundDetails = $Style->attachObject (new InboundDetail ($_GET ['Id']));
			
		}
	}
	catch (Exception $e)
	{
	
    }
	
	
	// Get the Amount of Unbilled Charges
	$srvService->UnbilledChargeCostCurrent ();
	
	// Get the Rate Plan this person is on
	$srvService->Plan ();
	
	// Get information about Note Types
	$ntsNoteTypes = $Style->attachObject (new NoteTypes);
	
	// Get Associated Notes
	$nosNotes = $Style->attachObject (new Notes);
	$nosNotes->Constrain ('Service', '=', $_GET ['Id']);
	$nosNotes->Sample ();
	
	
	// ChargeType and RecurringChargeType
	$tctCharges = $Style->attachObject (new dataArray ('TemplateChargeTypes'));
	
	// Get the Recurring ChargeTypes which can be put against this Account
	$rclRecurringChargeTypes	= $tctCharges->Push (new RecurringChargeTypes);
	$rclRecurringChargeTypes->Constrain ('Archived', '=', FALSE);
	$rclRecurringChargeTypes->Sample ();
	
	// Get the Charge Types which can be put against this Account
	$octChargeTypes	= $tctCharges->Push (new ChargeTypes);
	$octChargeTypes->Constrain ('Archived', '=', FALSE);
	$octChargeTypes->Sample ();
	
	// Get the Associated Cost Centre
	try
	{
		$intCostCentre = $srvService->Pull ('CostCentre')->getValue ();
		
		if ($intCostCentre)
		{
			$ccrCostCentre = $Style->AttachObject (new CostCentre ($intCostCentre));
		}
	}
	catch (Exception $e)
	{
	}
	

	$Style->Output (
		'xsl/content/service/view.xsl',
		Array (
			'Account'		=> $actAccount->Pull ('Id')->getValue (),
		)
	);
	
?>
