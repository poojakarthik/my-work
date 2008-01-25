<?php

	require ("config/application_loader.php");
	
	// If they are not authenticated, forward them to somewhere else
	if (!$athAuthentication->getAuthentication ())
	{
		header ("Location: login.php");
		exit;
	}
	
	$Account = $athAuthentication->getAuthenticatedUser ()->getAccount (
		isset ($_GET ['Id']) ? $_GET ['Id'] : null
	);
	
	$Style->attachObject ($Account);
	
	$Invoices = $Account->getInvoices ();
	$Style->attachObject ($Invoices);
	
	$Style->Output ("xsl/content/accounts/account.xsl");
	
?>
