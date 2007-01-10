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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_ACCOUNT | MODULE_CONTACT;
	
	// call application
	require ('config/application.php');
	
	$oblarrUI		= $Style->attachObject (new dataArray ('ui-values'));
	$oblarrAnswers	= $Style->attachObject (new dataArray ('ui-answers'));
	
	$oblstrBusinessName		= $oblarrUI->Push (new dataString  ('BusinessName',	(isset ($_POST ['ui-BusinessName'])	? $_POST ['ui-BusinessName']	: '')));
	$oblstrContactName		= $oblarrUI->Push (new dataString  ('ContactName',	(isset ($_POST ['ui-ContactName'])	? $_POST ['ui-ContactName']		: '')));
	$oblstrAccount			= $oblarrUI->Push (new dataString  ('Account',		(isset ($_POST ['ui-Account'])		? $_POST ['ui-Account']			: '')));
	$oblintAccountSel		= $oblarrUI->Push (new dataInteger ('Account-Sel',	(isset ($_POST ['ui-Account-Sel'])	? $_POST ['ui-Account-Sel']		: '')));
	$oblbolContactUse		= $oblarrUI->Push (new dataBoolean ('Contact-Use',	(isset ($_POST ['ui-Contact-Use'])	? $_POST ['ui-Contact-Use']		: '')));
	$oblintContactSel		= $oblarrUI->Push (new dataString  ('Contact-Sel',	(isset ($_POST ['ui-Contact-Sel'])	? $_POST ['ui-Contact-Sel']		: '')));
	$oblstrABN				= $oblarrUI->Push (new ABN         ('ABN',			(isset ($_POST ['ui-ABN'])			? $_POST ['ui-ABN']				: '')));
	$oblstrACN				= $oblarrUI->Push (new ACN         ('ACN',			(isset ($_POST ['ui-ACN'])			? $_POST ['ui-ACN']				: '')));
	$oblstrInvoice			= $oblarrUI->Push (new dataString  ('Invoice',		(isset ($_POST ['ui-Invoice'])		? $_POST ['ui-Invoice']			: '')));
	$oblstrFNN				= $oblarrUI->Push (new dataString  ('FNN',			(isset ($_POST ['ui-FNN'])			? $_POST ['ui-FNN']				: '')));
	
	$docDocumentation->Explain ("Account");
	$docDocumentation->Explain ("Service");
	$docDocumentation->Explain ("Invoice");
	$docDocumentation->Explain ("Contact");
	$docDocumentation->Explain ("Credit Card");
	$docDocumentation->Explain ("Direct Debit");
	
	if ($_POST ['ui-Account'])
	{
		// If we're matching against an Account#, we need to pull the Account
		// from the Database for further use
		
		$actAccount = $oblarrAnswers->Push (new Account ($_POST ['ui-Account']));
	}
	else if ($_POST ['ui-ABN'])
	{
		// If we're matching against an ABN#, we need to attempt to pull an 
		// unarchived Account with a matching ABN# from the database
		
	}
	else if ($_POST ['ui-ACN'])
	{
		// If we're matching against an ACN#, we need to attempt to pull an 
		// unarchived Account with a matching ACN# from the database
		
	}
	else if ($_POST ['ui-Invoice'])
	{
		// If we're matching against an Invoice#, we need to pull the Invoice
		// from the Database for further use
		
	}
	else if ($_POST ['ui-FNN'])
	{
		// If we're matching against a FNN#, we need to attempt to pull an 
		// unarchived Service with a matching FNN# from the database
		
	}
	else if ($_POST ['ui-BusinessName'])
	{
		// If we're matching against a Business Name, we need to display 
		// a list of Accounts which possibly match
		
	}
	else if ($_POST ['ui-ContactName'])
	{
		// If we're matching against a Contact Name, we need to display 
		// a list of Contacts which possibly match
		
	}
	
	//------------------------------------------------------
	// Dealing with Business Names
	//------------------------------------------------------
	
	if ($_POST ['ui-BusinessName'] && !$_POST ['ui-Account-Sel'])
	{
		// If we have a Business Name, but we don't have an Account Number, 
		// we have to show a screen which will allow the employee to 
		// select the right account
		
		$acsAccounts = $oblarrAnswers->Push (new Accounts ());
		$acsAccounts->Constrain ('BusinessName', 'LIKE', $oblstrBusinessName->getValue ());
		$acsAccounts->Order ('BusinessName', TRUE);
		$acsAccounts->Sample ();
		
		$Style->Output ('xsl/content/contact/list_1-account.xsl');
		exit;
	}
	else if ($_POST ['ui-BusinessName'] && $_POST ['ui-Account-Sel'])
	{
		try
		{
			$actAccount = $oblarrAnswers->Push (new Account ($oblintAccountSel->getValue ()));
		}
		catch (Exception $e)
		{
			// If we try to get an Account that doesn't exist - start the process again
			// because there's obvious an error occurring or hacking attempt
			header ('Location: contact_list.php'); exit;
		}
	}
	
	//------------------------------------------------------
	// Contact Management
	//------------------------------------------------------
	
	if ($actAccount)
	{
		// If we've successfully (somehow) identified an account,
		// then we need to start checking for a contact
		
		if (!isset ($_POST ['ui-Contact-Use']))
		{
			// If we have an Account, but we don't have a Contact, 
			// we have to show a screen which will allow the employee to 
			// select the person they are talking to on the phone. Alternatively
			// it is possible to dictate that this particular contact is
			// not on the account list, but may be able to process through
			
			$ctsContacts = $oblarrAnswers->Push ($actAccount->Contacts ());
			
			$Style->Output ('xsl/content/contact/list_2.xsl');
			exit;
		}
		else if ($_POST ['ui-Contact-Use'] && $_POST ['ui-Contact-Sel'])
		{
			try
			{
				// This method specifically makes sure that the contact
				// has access to the identified account - to prevent against hacking
				$cntContact = $oblarrAnswers->Push ($actAccount->Contact ($_POST ['ui-Contact-Sel']));
			}
			catch (Exception $e)
			{
				// If we try to get a Contact that doesn't exist in the account - 
				// start the process again because there's obvious an error 
				// occurring or hacking attempt
				header ('Location: contact_list.php'); exit;
			}
		}
	}
	
	if ($actAccount && isset ($_POST ['ui-Contact-Use']))
	{
		$Style->Output ('xsl/content/contact/list_3.xsl');
		exit;
	}
	
	$Style->Output ('xsl/content/contact/list_1.xsl');
	
?>
