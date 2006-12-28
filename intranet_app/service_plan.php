<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ('config/application_loader.php');
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ('Location: login.php'); exit;
	}
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Rate Plan');
	$docDocumentation->Explain ('Service Rate Plan');
	
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
		else
		{
			// Die
			throw new Exception ('Service Not Found');
		}
	}
	catch (Exception $e)
	{
		header ('Location: console.php'); exit;
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
