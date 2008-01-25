<?php

	require ("config/application_loader.php");
	
	// If they are not authenticated, forward them to somewhere else
	if (!$athAuthentication->getAuthentication ())
	{
		header ("Location: login.php");
		exit;
	}
	
	// If they are not a company contact, they only have 1 account 
	// So, direct them to that particular account to save time ...
	if (!$athAuthentication->getAuthenticatedUser ()->isCustomerContact ())
	{
		header ("Location: account.php");
		exit;
	}
	
	$Accounts = $athAuthentication->getAuthenticatedUser ()->getAccounts ();
	$Style->attachObject ($Accounts);
	
	$Style->Output ("xsl/content/accounts/accounts.xsl");
	
?>
