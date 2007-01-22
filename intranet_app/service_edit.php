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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE;
	
	// call application
	require ('config/application.php');
	
	try
	{
		if ($_GET ['Id'])
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
	
	// Error String
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	
	// If we're wishing to save the details, we can identify this by
	// whether or not we're using GET or POST
	if ($_POST ['FNN'])
	{
		// Check the Line Numbers Match
		if ($_POST ['FNN']['1'] <> $_POST ['FNN']['2'])
		{
			$oblstrError->setValue ('Mismatch');
		}
		else
		{
			$srvService->Update (
				Array (
					"FNN"				=> $_POST ['FNN']['1']
				)
			);
			
			if (isset ($_POST ['Archived']))
			{
				$srvService->ArchiveStatus ($_POST ['Archived']);
				
				echo "[ END ]";
				exit;
			}
			
			header ("Location: service_view.php?Id=" . $srvService->Pull ('Id')->getValue ());
			exit;
		}
	}
	
	// Pull documentation information for Service
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Archive');
	
	$Style->Output ('xsl/content/service/edit.xsl');
	
?>
