<?php

	require ("config/application_loader.php");
	
	// If they are not authenticated, forward them to somewhere else
	if (!$athAuthentication->getAuthentication ())
	{
		header ("Location: login.php");
		exit;
	}
	
	if (!isset ($_POST ['Id']))
	{
		header ("Location: contact.php");
		exit;
	}
	
	// If they are not a company contact, then they do not have access to this page
	if (!$athAuthentication->getAuthenticatedUser ()->isCustomerContact ())
	{
		if ($athAuthentication->getAuthenticatedUser ()->Pull ("Id")->getValue () <> $_POST ['Id'])
		{
			header ("Location: console.php");
			exit;
		}
	}
	
	$Contact = $athAuthentication->getAuthenticatedUser ()->getContact ($_POST ['Id']);
	$Style->attachObject ($Contact);
	
	if (!$athAuthentication->getAuthenticatedUser ()->checkPassword ($_POST ['My_PassWord']))
	{
		$Style->Output ("xsl/content/contacts/contact_password_mymismatch.xsl");
		exit;
	}
	
	if ($_POST ['New_PassWord']['0'] <> $_POST ['New_PassWord']['1'])
	{
		$Style->Output ("xsl/content/contacts/contact_password_mismatch.xsl");
		exit;
	}
	
	if (strlen ($_POST ['New_PassWord']['0']) < 5)
	{
		$Style->Output ("xsl/content/contacts/contact_password_length.xsl");
		exit;
	}
	
	$Contact->setPassword ($_POST ['New_PassWord']['0']);
	$Style->Output ("xsl/content/contacts/contact_password_success.xsl");
	
?>
