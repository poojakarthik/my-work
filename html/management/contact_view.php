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
	$arrPage['Permission']	= PERMISSION_OPERATOR_VIEW | PERMISSION_OPERATOR_EXTERNAL;
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_CONTACT | MODULE_NOTE | MODULE_EMPLOYEE | MODULE_TITLE;
	
	// call application
	require ('config/application.php');
	
	
	
	// Pull documentation information
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	$docDocumentation->Explain ('Contact');
	$docDocumentation->Explain ('Service');
	
	// Pull the Information about the Contact
	try
	{
		$cntContact = $Style->attachObject (new Contact ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/contact/notfound.xsl');
		exit;
	}
	
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
	
	// Titles (Mr, Mrs, Ms, Master ...)
	$ttyTitles = $Style->attachObject (new TitleTypes);
	
	// Output the Account View
	$Style->Output (
		'xsl/content/contact/view.xsl',
		Array (
			'Account'		=> $cntContact->Pull ('Account')->getValue ()
		)
	);
	
?>
