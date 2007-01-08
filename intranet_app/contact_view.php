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
	
	// Pull documentation information
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	$docDocumentation->Explain ('Contact');
	$docDocumentation->Explain ('Service');
	
	// Make sure we have a Contact being Requested
	if (!$_GET ['Id'])
	{
		header ('Location: console.php'); exit;
	}
	
	// Pull the Information about the Contact
	$cntContact = $Style->attachObject (new Contact ($_GET ['Id']));
	
	// Pull the Accounts that the Contact has Access to
	$acsAccounts = $Style->attachObject ($cntContact->getAccounts ());
	$acsAccounts->Sample ();
	
	// Note Types
	$ntlNoteTypes = $Style->attachObject (new NoteTypes ());
	
	// Notes
	$nosNotes = $Style->attachObject (new Notes ());
	$nosNotes->Constrain ('Contact', 'EQUALS', $cntContact->Pull ('Id')->getValue ());
	$nosNotes->Sample ();
	
	// Output the Account View
	$Style->Output ('xsl/content/contact/view.xsl');
	
?>
