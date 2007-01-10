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
	$arrPage['Modules']		= MODULE_BASE | MODULE_NOTE | MODULE_ACCOUNT_GROUP | MODULE_ACCOUNT | MODULE_SERVICE | MODULE_CONTACT;
	
	// call application
	require ('config/application.php');
	
	
	
	
	// Authenticate the Note Type
	$ntyNoteType = new NoteType ($_POST ['NoteType']);
	
	if (!$_POST ['Note'])
	{
		header ("Location: index.php");
		exit;
	}
	
	
	try
	{
		// You MUST have an Account Group
		$agrAccountGroup = new AccountGroup ($_POST ['AccountGroup']);
	}
	catch (Exception $e)
	{
		header ("Location: index.php");
		exit;
	}
	
	// If we have an Account, Authenticate it
	if ($_POST ['Account'])
	{
		try
		{
			$actAccount = $agrAccountGroup->getAccount ($_POST ['Account']);
		}
		catch (Exception $e)
		{
			header ("Location: index.php");
			exit;
		}
	}
	
	// If we have a Contact, Authenticate it
	if ($_POST ['Contact'])
	{
		try
		{
			$cntContact = new Contact ($_POST ['Contact']);
		}
		catch (Exception $e)
		{
			header ("Location: index.php");
			exit;
		}
	}
	
	// If we have a Service, Authenticate it
	if ($_POST ['Service'])
	{
		try
		{
			$srvService = new Service ($_POST ['Service']);
		}
		catch (Exception $e)
		{
			header ("Location: index.php");
			exit;
		}
	}
	
	// Get the Notes Controller
	$nosNotes = new Notes ();
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
	
	header ('Location: note_added.php');
	exit;
	
?>
