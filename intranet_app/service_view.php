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
	$docDocumentation->Explain ('Archive');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Account');
	
	
	// Get the Service
	$srvService		= $Style->attachObject (new Service ($_GET ['Id']));
	
	// Get the Account
	$srvService		= $Style->attachObject (new Account ($srvService->Pull ('Account')->getValue ()));
	
	// Get information about Note Types
	$ntsNoteTypes	= $Style->attachObject (new NoteTypes ());
	
	// Get Associated Notes
	$nosNotes		= $Style->attachObject (new Notes ());
	$nosNotes->Constrain ('Service', '=', $_GET ['Id']);
	$nosNotes->Order ('Datetime', FALSE);
	$nosNotes->Sample ();
	
	// Output the Account View
	$Style->Output ('xsl/content/service/view.xsl');
	
?>
