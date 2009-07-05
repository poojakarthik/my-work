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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT_GROUP | MODULE_NOTE | MODULE_SERVICE | MODULE_CONTACT | MODULE_EMPLOYEE;
	
	// call application
	require ('config/application.php');
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ('Location: login.php'); exit;
	}
	
	// If there is no search Criteria
	if (!$_GET ['AccountGroup'] && !$_GET ['Account'] && !$_GET ['Service'] && !$_GET ['Contact'])
	{
		// Redirect Somewhere Else
		header ('Location: index.php'); exit;
	}
	

	// Attach the Note Type Inforamtion
	$Style->attachObject (new NoteTypes);
	
	// Start a new Note Listing
	$nosNotes = $Style->attachObject (new Notes);
	
	
	// If we have an Account Group Specified, then 
	// pull information about the Account Group
	// and Constrain against the Id
	if ($_GET ['AccountGroup'])
	{
		try
		{
			// Pull:
			$acgAccountGroup = $Style->attachObject (new AccountGroup ($_GET ['AccountGroup']));
			
			// Constrain:
			$nosNotes->Constrain ('AccountGroup', '=', $acgAccountGroup->Pull ('Id')->getValue ());
			
			// Documentation:
			$docDocumentation->Explain ('Account Group');
		}
		catch (Exception $e)
		{
			header ('Location: index.php');
			exit;
		}
	}
	
	
	// If we have an Account Specified, then 
	// pull information about the Account
	// and Constrain against the Id
	if ($_GET ['Account'])
	{
		try
		{
			// Pull:
			$actAccount = $Style->attachObject (new Account ($_GET ['Account']));
			
			// Constrain:
			$nosNotes->Constrain ('Account', '=', $actAccount->Pull ('Id')->getValue ());
			
			// Documentation:
			$docDocumentation->Explain ('Account');
		}
		catch (Exception $e)
		{
			header ('Location: index.php');
			exit;
		}
	}
	
	
	// If we have a Contact Specified, then 
	// pull information about the Contact
	// and Constrain against the Id
	if ($_GET ['Contact'])
	{
		try
		{
			// Pull:
			$cntContact = $Style->attachObject (new Contact ($_GET ['Contact']));
			
			// Constrain:
			$nosNotes->Constrain ('Contact', '=', $cntContact->Pull ('Id')->getValue ());
			
			// Documentation:
			$docDocumentation->Explain ('Contact');
		}
		catch (Exception $e)
		{
			header ('Location: index.php');
			exit;
		}
	}
	
	// If we have a Service Specified, then 
	// pull information about the Service
	// and Constrain against the Id
	if ($_GET ['Service'])
	{
		try
		{
			// Pull:
			$srvService = $Style->attachObject (new Service ($_GET ['Service']));
			
			// Constrain:
			$nosNotes->Constrain ('Service', '=', $srvService->Pull ('Id')->getValue ());
			
			// Documentation:
			$docDocumentation->Explain ('Service');
		}
		catch (Exception $e)
		{
			header ('Location: index.php');
			exit;
		}
	}
	
	if ($_GET ['NoteType'])
	{
		$nosNotes->Constrain ('NoteType', '=', $_GET ['NoteType']);
	}
	
	// Order by Newest First
	$nosNotes->Order ('Datetime', FALSE);
	
	// Pull a Sample (all notes)
	$nosNotes->Sample (
		isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
		isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : null
	);
	
	$docDocumentation->Explain ('Note');
	
//	echo $Style;exit;
	
	// Output to the Browser
	$Style->Output ('xsl/content/notes/list.xsl');
	
?>
