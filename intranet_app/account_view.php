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
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	
	
	
	// Get the Account
	$actAccount		= $Style->attachObject (new Account ($_GET ['Id']));	
	
	// Get Associated Services
	$svsServices	= $Style->attachObject (new Services ());
	$svsServices->Constrain ('Account', '=', $_GET ['Id']);
	$svsServices->Order ('FNN', TRUE);
	$svsServices->Sample ();
	
	// Get information about Note Types
	$ntsNoteTypes	= $Style->attachObject (new NoteTypes ());
	
	// Get Associated Notes
	$nosNotes		= $Style->attachObject (new Notes ());
	$nosNotes->Constrain ('Account', '=', $_GET ['Id']);
	$nosNotes->Sample ();
	
	// Record a request to view an Account in the Audit
	$athAuthentication->AuthenticatedEmployee ()->Audit ()->RecordAccount ($actAccount);
	
	// Output the Account View
	$Style->Output ('xsl/content/account/view.xsl');
	
?>
