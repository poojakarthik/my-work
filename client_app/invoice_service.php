<?php

	require ("config/application_loader.php");
	
	// If they are not authenticated, forward them to somewhere else
	if (!$athAuthentication->getAuthentication ())
	{
		header ("Location: login.php");
		exit;
	}
	
	$invInvoice = $athAuthentication->getAuthenticatedUser ()->getInvoice ($_GET ['Invoice']);
	$Style->attachObject ($invInvoice);
	
	$srvService = $invInvoice->getService ($_GET ['Id']);
	$Style->attachObject ($srvService);
	
	$srvService->getCalls (isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1);
	((!isset ($_GET ['rangePage']) || $_GET ['rangePage'] == 1) ? $srvService->getCharges () : null);
	
	$Style->Output ("xsl/content/invoices/invoice_service.xsl");
	
?>
