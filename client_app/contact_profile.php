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
	
	// If they are not a company contact, then they do not have access to this page for other people
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
	
	if (
	!isset ($_POST ['Title']) ||
	!isset ($_POST ['FirstName']) ||
	!isset ($_POST ['LastName']) ||
	!isset ($_POST ['DOB_day']) ||
	!isset ($_POST ['DOB_month']) ||
	!isset ($_POST ['DOB_year']) ||
	!isset ($_POST ['JobTitle']) ||
	!isset ($_POST ['Email']) ||
	!isset ($_POST ['Phone']) ||
	!isset ($_POST ['Mobile']) ||
	!isset ($_POST ['Fax']) ||

	empty ($_POST ['Title']) ||
	empty ($_POST ['FirstName']) ||
	empty ($_POST ['LastName']) ||
	empty ($_POST ['DOB_day']) ||
	empty ($_POST ['DOB_month']) ||
	empty ($_POST ['DOB_year']) ||
	empty ($_POST ['JobTitle']) ||
	empty ($_POST ['Email']) ||
	// empty ($_POST ['Fax']) ||	// Fax can be empty
	!checkdate ($_POST ['DOB_month'], $_POST ['DOB_day'], $_POST ['DOB_year']) ||
	$_POST ['DOB_year'] < (date ("Y") - 100) ||
	$_POST ['DOB_year'] > (date ("Y") - 18)
	) {
		header ("Location: contact.php");
		exit;
	}
	
	$Contact->setProfile (
		$_POST ['Title'],
		$_POST ['FirstName'],
		$_POST ['LastName'],
		$_POST ['DOB_year'],
		$_POST ['DOB_month'],
		$_POST ['DOB_day'],
		$_POST ['JobTitle'],
		$_POST ['Email'],
		$_POST ['Phone'],
		$_POST ['Mobile'],
		$_POST ['Fax']
	);
	
	$Style->Output ("xsl/content/contacts/contact_profile_success.xsl");
	
?>
