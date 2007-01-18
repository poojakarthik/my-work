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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_CONTACT | MODULE_NOTE | MODULE_EMPLOYEE;
	
	// call application
	require ('config/application.php');
	
	
	
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
	$oblsamAccounts = $acsAccounts->Sample ();
	
	// Get the Overdue Amount for the Account
	foreach ($oblsamAccounts as $actAccount)
	{
		$actAccount->OverdueAmount ();
	}
	
	// Note Types
	$ntlNoteTypes = $Style->attachObject (new NoteTypes);
	
	// Notes
	$nosNotes = $Style->attachObject (new Notes);
	$nosNotes->Constrain ('Contact', 'EQUALS', $cntContact->Pull ('Id')->getValue ());
	$nosNotes->Sample ();
	
	// Output the Account View
	$Style->Output ('xsl/content/contact/view.xsl');
	
?>
