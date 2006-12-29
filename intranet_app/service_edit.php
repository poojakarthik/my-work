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
	
	try
	{
		if ($_SERVER ['REQUEST_METHOD'] == "GET")
		{
			// Using GET
			$srvService = $Style->attachObject (new Service ($_GET ['Id']));
		}
		else
		{
			// Using POST
			$srvService = $Style->attachObject (new Service ($_POST ['Id']));
		}
	}
	catch (Exception $e)
	{
		// If the service does not exist, an exception will be thrown
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	// If we're wishing to save the details, we can identify this by
	// whether or not we're using GET or POST
	if ($_SERVER ['REQUEST_METHOD'] == "POST")
	{
		$srvService->Update (
			Array (
				"FNN"				=> $_POST ['FNN']
			)
		);
		
		if (isset ($_POST ['Archived']))
		{
			$srvService->ArchiveStatus ($_POST ['Archived']);
		}
		
		header ("Location: service_view.php?Id=" . $srvService->Pull ('Id')->getValue ());
		exit;
	}
	
	// Pull documentation information for Service
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Archive');
	
	$Style->Output ('xsl/content/service/edit.xsl');
	
?>
