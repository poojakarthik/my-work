<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ('config/application_loader.php');
	
	$docDocumentation->Explain ('Account Group');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Contact');
	
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
	
	
	// Start a new Note Listing
	$nosNotes = new Notes ();
	
	
	// If we have an Account Group Specified, then 
	// pull information about the Account Group
	// and Constrain against the Id
	if ($_GET ['AccountGroup'])
	{
		try
		{
			// Pull:
			$acgAccountGroup = new AccountGroup ($_GET ['AccountGroup']);
			$Style->attachObject ($acgAccountGroup);
			
			// Constrain:
			$nosNotes->Constrain ('AccountGroup', '=', $acgAccountGroup->Pull ('Id')->getValue ());
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
			$actAccount = new Account ($_GET ['Account']);
			$Style->attachObject ($actAccount);
			
			// Constrain:
			$nosNotes->Constrain ('Account', '=', $actAccount->Pull ('Id')->getValue ());
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
			$cntContact = new Contact ($_GET ['Contact']);
			$Style->attachObject ($cntContact);
			
			// Constrain:
			$nosNotes->Constrain ('Contact', '=', $cntContact->Pull ('Id')->getValue ());
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
			$srvService = new Service ($_GET ['Service']);
			$Style->attachObject ($srvService);
			
			// Constrain:
			$nosNotes->Constrain ('Service', '=', $srvService->Pull ('Id')->getValue ());
		}
		catch (Exception $e)
		{
			header ('Location: index.php');
			exit;
		}
	}
	
	// Order by Newest First
	$nosNotes->Order ('Datetime', FALSE);
	
	// Pull a Sample
	$nosNotes->Sample (
		isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
		isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : null
	);
	
	// Attach the Notes
	$Style->attachObject ($nosNotes);
	
	// Attach the Note Type Inforamtion
	$Style->attachObject (new NoteTypes ());
	
	// Output to the Browser
	$Style->Output ('xsl/content/notes/list.xsl');
	
?>
