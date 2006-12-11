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
	$docDocumentation->Explain ("Contact");
	
	// Stage Two: Validate the Account or Service and Choose a Contact
	if ($_POST ['Account'] || $_POST ['FNN'])
	{
		try
		{
			// Try the Account first
			if ($_POST ['Account'])
			{
				$actAccount = new Account ($_POST ['Account']);
			}
			// Then try the Service FNN
			else if ($_POST ['FNN'])
			{
				$srvService = Service::UnarchivedFNN ($_POST ['FNN']);
				$actAccount = $srvService->getAccount ();
			}
			else
			{
				header ('Location: contact_list.php');
				exit;
			}
			
			$cnsContacts = $Style->attachObject (new Contacts ());
			$cnsContacts->Constrain ('Account', '=', $actAccount->Pull ('Id')->getValue ());
			$cnsContacts->Order ('LastName', FALSE);
			$cnsContacts->Sample ();
			
			$Style->Output ('xsl/content/contact/2_contact.xsl');
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
