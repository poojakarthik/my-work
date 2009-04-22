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
	$arrPage['Modules']		= MODULE_BASE | MODULE_NOTE | MODULE_ACCOUNT_GROUP | MODULE_ACCOUNT | MODULE_SERVICE | MODULE_CONTACT | MODULE_EMPLOYEE;
	
	// call application
	require ('config/application.php');
	
	
	
	
	// Authenticate the Note Type
	$ntyNoteType = new NoteType ($_POST ['NoteType']);
	
	try
	{
		// You MUST have an Account Group
		$agrAccountGroup = $Style->attachObject (new AccountGroup ($_POST ['AccountGroup']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/accountgroup/notfound.xsl');
		exit;
	}
	
	// If we have an Account, Authenticate it
	if ($_POST ['Account'])
	{
		try
		{
			$actAccount = $Style->attachObject ($agrAccountGroup->getAccount ($_POST ['Account']));
		}
		catch (Exception $e)
		{
			$Style->Output ('xsl/content/account/notfound.xsl');
			exit;
		}
	}
	
	// If we have a Contact, Authenticate it
	if ($_POST ['Contact'])
	{
		try
		{
			$cntContact = $Style->attachObject (new Contact ($_POST ['Contact']));
		}
		catch (Exception $e)
		{
			$Style->Output ('xsl/content/contact/notfound.xsl');
			exit;
		}
	}
	
	// If we have a Service, Authenticate it
	if ($_POST ['Service'])
	{
		try
		{
			$srvService = $Style->attachObject (new Service ($_POST ['Service']));
		}
		catch (Exception $e)
		{
			$Style->Output ('xsl/content/service/notfound.xsl');
			exit;
		}
	}
	
	if (!$_POST ['Note'])
	{
		$Style->Output ('xsl/content/notes/add_empty.xsl');
		exit;
	}
	
	// Get the Notes Controller
	$nosNotes = new Notes;
	$nosNotes->Add (
		Array (
			'Note'			=> $_POST ['Note'],
			'NoteType'		=> $ntyNoteType->Pull ('Id')->getValue (),
			
			'AccountGroup'	=> $agrAccountGroup->Pull ('Id')->getValue (),
			'Account'		=> ($_POST ['Account']) ? $actAccount->Pull ('Id')->getValue () : null,
			'Service'		=> ($_POST ['Service']) ? $srvService->Pull ('Id')->getValue () : null,
			'Contact'		=> ($_POST ['Contact']) ? $cntContact->Pull ('Id')->getValue () : null,
			
			'Datetime'		=> date ('Y-m-d h:i:s'),
			
			'Employee'		=> $athAuthentication->AuthenticatedEmployee ()->Pull ('Id')->getValue ()
		)
	);
	
	if ($srvService)
	{
		if ($_POST ['ServiceAddress'])
		{
			header ('Location: service_address.php?Service=' . $_POST ['Service']);
		}
		else
		{
			header ('Location: ../admin/flex.php/Service/View/?Service.Id=' . $_POST ['Service']);
			exit;
		}
	}
	else if ($cntContact)
	{
		header ('Location: contact_view.php?Id=' . $_POST ['Contact']);
		exit;
	}
	else if ($actAccount)
	{
		header ('Location: ../admin/flex.php/Account/Overview/?Account.Id=' . $_POST ['Account']);
		exit;
	}
	
?>
