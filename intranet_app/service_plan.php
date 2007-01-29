<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	//TODO!bash! Assigning a new plan does not work, no error, just doesn't work.... I thought you told me the UI was finished ????
	//TODO!bash! View Plan Details still shows the old view plan details pages, make it show the new one
	
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE_PLAN | MODULE_SERVICE;

	// call application
	require ('config/application.php');
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Rate Plan');
	$docDocumentation->Explain ('Service Rate Plan');
	$docDocumentation->Explain ('Account');
	
	// Get the Service
	try
	{
		if ($_GET ['Service'])
		{
			// Try the GET method
			$srvService		= $Style->attachObject (new Service ($_GET ['Service']));
		}
		else if ($_POST ['Service'])
		{
			// Try the POST method
			$srvService		= $Style->attachObject (new Service ($_POST ['Service']));
		}
		// get the account
		$actAccount		= $Style->attachObject ($srvService->getAccount ());
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
	}
	
	// If we wish to update the Rate Plan ...
	if (isset ($_POST ['RatePlan']))
	{
		// Try getting the requested Rate Plan
		try 
		{
			$rplRatePlan = new RatePlan ($_POST ['RatePlan']);
			
			// Make sure the Plan is not Archived
			if ($rplRatePlan->Pull ('Archived')->isTrue ())
			{
				throw new Exception ('Archived');
			}
			
			$srvService->PlanSelect ($athAuthentication->AuthenticatedEmployee (), $rplRatePlan);
		}
		catch (Exception $e)
		{
			// We're doing nothing in here ... because it's going to show up in the page properly
		}
		
		header ('Location: service_plan.php?Service=' . $_POST ['Service']);
		exit;
	}
	
	$srvService->Plan ();
	
	$rplRatePlans = $Style->attachObject (new RatePlans ());
	$rplRatePlans->Constrain ('ServiceType', 'EQUALS', $srvService->Pull ('ServiceType')->getValue ());
	$rplRatePlans->Constrain ('Archived', 'EQUALS', 0);
	$rplRatePlans->Sample ();
	
	// Output the Account View
	$Style->Output ('xsl/content/service/plan_view.xsl');
	
?>
