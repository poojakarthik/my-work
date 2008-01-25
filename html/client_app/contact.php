<?php

	require ("config/application_loader.php");
	
	// If they are not authenticated, forward them to somewhere else
	if (!$athAuthentication->getAuthentication ())
	{
		header ("Location: login.php");
		exit;
	}
	
	// If they are not a company contact, then they do not have access to this page if they're trying to view someone else
	if (!$athAuthentication->getAuthenticatedUser ()->isCustomerContact ())
	{
		if (isset ($_GET ['Id']) && $_GET ['Id'] <> $athAuthentication->getAuthenticatedUser ()->Pull ("Id")->getValue ())
		{
			header ("Location: console.php");
			exit;
		}
	}
	
	$Id = (isset ($_GET ['Id']) ? $_GET ['Id'] : $athAuthentication->getAuthenticatedUser ()->Pull ("Id")->getValue ());
	
	$Contact = $athAuthentication->getAuthenticatedUser ()->getContact ($Id);
	$Style->attachObject ($Contact);
	
	$Style->Output ("xsl/content/contacts/contact.xsl");
	
?>
