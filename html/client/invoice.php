<?php

	require ("config/application_loader.php");
	
	// If they are not authenticated, forward them to somewhere else
	if (!$athAuthentication->getAuthentication ())
	{
		header ("Location: login.php");
		exit;
	}
	
	$Invoice = $athAuthentication->getAuthenticatedUser ()->getInvoice ($_GET ['Id']);
	$Invoice->getServices ();
	$Style->attachObject ($Invoice);
	
	$Style->Output ("xsl/content/invoices/invoice.xsl");
	
?>
