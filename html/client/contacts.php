<?php

	require ("config/application_loader.php");
	
	// If they are not authenticated, forward them to somewhere else
	if (!$athAuthentication->getAuthentication ())
	{
		header ("Location: login.php");
		exit;
	}
	
	// If they are not a company contact, then they do not have access to this page
	if (!$athAuthentication->getAuthenticatedUser ()->isCustomerContact ())
	{
		header ("Location: account.php");
		exit;
	}
	
	$Contacts = $athAuthentication->getAuthenticatedUser ()->getContacts ();
	$Style->attachObject ($Contacts);
	
	$Style->Output ("xsl/content/contacts/contacts.xsl");
	
?>
