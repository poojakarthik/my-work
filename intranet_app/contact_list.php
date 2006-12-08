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
	
	// If the account is set then we want to look for a contact
	if (isset ($_POST ['Id']))
	{
		$actAccount = $Style->attachObject (new Account ($_POST ['Id']));
		
		$Style->Output ("xsl/content/contact/list_account.xsl");
	}
	// If we have at least one of the following fields:
	// Account ID, Business Name, Trading Name, ABN, ACN
	else if ($_POST ['BusinessName'] || $_POST ['TradingName'] || $_POST ['ABN'] && $_POST ['ACN'])
	{
		// Start a new Account Search
		$acsAccounts = $Style->attachObject (new Accounts ());
		$acsAccounts->Order ('BusinessName', FALSE);
		
		if ($_POST ['Id'])				{ $acsAccounts->Constrain ('Id',			'EQUALS',	$_POST ['Id']); }
		if ($_POST ['BusinessName'])	{ $acsAccounts->Constrain ('BusinessName',	'LIKE',		$_POST ['BusinessName']); }
		if ($_POST ['TradingName'])	{ $acsAccounts->Constrain ('TradingName',	'LIKE',		$_POST ['TradingName']); }
		if ($_POST ['ABN'])				{ $acsAccounts->Constrain ('ABN',			'EQUALS',	$_POST ['ABN']); }
		if ($_POST ['ACN'])				{ $acsAccounts->Constrain ('ACN',			'EQUALS',	$_POST ['ACN']); }
		
		$acsAccounts->Sample ();
		
		$Style->Output ("xsl/content/contact/list_accounts.xsl");
	}
	else
	{
		$Style->Output ("xsl/content/contact/list_criteria.xsl");
	}
	
?>
