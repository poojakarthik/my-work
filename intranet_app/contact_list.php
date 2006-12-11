<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ("config/application_loader.php");
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ("Location: login.php"); exit;
	}
	
	$docDocumentation->Explain ("Account");
	$docDocumentation->Explain ("Service");
	$docDocumentation->Explain ("Contact");
	
	// Stage Two: Validate the Account or Service and Choose a Contact
	if ($_POST ['Account'] || $_POST ['FNN'])
	{
		try
		{
			// The main aim in the following IF ... (ELSE IF ... ) ELSE ... statement 
			// is to get a Contact that we can use to Authenticate against. If you
			// gracefully exit out of this block, it can be assumed that the value of
			// the variable $actAccount is the Account that you wish to edit.
			
			// Also - we only want to attach the Account to $Style if it's what we searched by
			
			// Try the Account first
			if ($_POST ['Account'])
			{
				$actAccount = new Account ($_POST ['Account']);
				$Style->attachObject ($actAccount);
			}
			// Then try the Service FNN
			else if ($_POST ['FNN'])
			{
				$srvService = Services::UnarchivedFNN ($_POST ['FNN']);
				$actAccount = $srvService->getAccount ();
				
				$Style->attachObject ($srvService);
			}
			else
			{
				header ('Location: contact_list.php');
				exit;
			}
			
			// BRANCH
			
			// If there is a Contact Specified, try Authenticating the follow:
			
			if ($_POST ['Contact'])
			{
				// We've reached the point where we want to do stage 3: verification
				
				
			}
			else
			{
				// If we have reached this Branch, then we
				// have not selected a Contact from the List
				// Therefore, we have to pull Basic Information
				// about each Contact so we can select a Contact
				// based on their Name
				
				// Pull information about Contacts
				$cnsContacts = $Style->attachObject (new Contacts ());
				$cnsContacts->Constrain ('Account', '=', $actAccount->Pull ('Id')->getValue ());
				$cnsContacts->Order ('LastName', FALSE);
				$cnsContacts->Sample ();
				
				$Style->Output ('xsl/content/contact/2_contact.xsl');
			}
		}
		catch (Exception $e)
		{
			header ('Location: contact_list.php');
			exit;
		}
		
		exit;
	}
	
	// Stage One: Identify an Account or a Service
	else
	{
		$Style->Output ('xsl/content/contact/1_account.xsl');
	}
	
?>
