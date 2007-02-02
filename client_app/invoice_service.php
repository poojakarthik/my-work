<?php

	require ("config/application_loader.php");
	
	// If they are not authenticated, forward them to somewhere else
	if (!$athAuthentication->getAuthentication ())
	{
		header ("Location: login.php");
		exit;
	}
	
	$invInvoice = $Style->attachObject ($athAuthentication->getAuthenticatedUser ()->getInvoice ($_GET ['Invoice']));
	$ivsService = $Style->attachObject ($invInvoice->getService ($_GET ['Id']));
	
	if (!isset ($_GET ['rangePage']) || $_GET ['rangePage'] == 1)
	{
		$ivsService->getCharges ();
	}
	
	$ivsService->getCalls (isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1);
	
	$Style->Output ("xsl/content/invoices/invoice_service.xsl");
	
?>
