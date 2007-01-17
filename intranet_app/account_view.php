<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	//TODO!bash! fix error : Fatal error: Class 'RatePlan' not found in /home/flame/vixen/intranet_app/classes/service/service.php on line 355
	
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_SERVICE | MODULE_CONTACT | MODULE_NOTE | MODULE_EMPLOYEE;
	
	// call application
	require ('config/application.php');
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	
	try
	{
		// Get the Account
		$actAccount		= $Style->attachObject (new Account ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	// Get Associated Services
	$svsServices	= $Style->attachObject (new Services);
	$svsServices->Constrain ('Account', '=', $_GET ['Id']);
	$svsServices->Order ('FNN', TRUE);
	$oblsamServices = $svsServices->Sample ();
	
	foreach ($oblsamServices as $srvService)
	{
		$srvService->Plan ();
	}
	
	// Get information about Note Types
	$ntsNoteTypes	= $Style->attachObject (new NoteTypes);
	
	// Get Associated Notes
	$nosNotes = $Style->attachObject (new Notes);
	$nosNotes->Constrain ('Account', '=', $_GET ['Id']);
	$nosNotes->Sample (1, 5);
	
	// Get all the Contacts
	$ctsContacts	= $Style->attachObject ($actAccount->Contacts ());
	
	// Output the Account View
	$Style->Output ('xsl/content/account/view.xsl');
	
?>
