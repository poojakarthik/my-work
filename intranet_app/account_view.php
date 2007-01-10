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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_SERVICE | MODULE_CONTACT | MODULE_NOTE;
	
	// call application
	require ('config/application.php');
	
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
	$nosNotes->Sample (1, 5);
	
	// Get all the Contacts
	$ctsContacts	= $Style->attachObject ($actAccount->Contacts ());
	
	// Record a request to view an Account in the Audit
	$athAuthentication->AuthenticatedEmployee ()->Audit ()->RecordAccount ($actAccount);
	
	
	// Output the Account View
	$Style->Output ('xsl/content/account/view.xsl');
	
?>
