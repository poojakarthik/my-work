<?php

	require ("config/application_loader.php");
	
	// If they are not authenticated, forward them to somewhere else
	if (!$athAuthentication->getAuthentication ())
	{
		header ("Location: login.php");
		exit;
	}
	
	$invInvoice		= $Style->attachObject ($athAuthentication->getAuthenticatedUser ()->getInvoice ($_GET ['Invoice']));
	$ivsService		= $Style->attachObject ($invInvoice->getService ($_GET ['Id']));
	
	$ivsService->getCalls (isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1);
	
	if (!isset ($_GET ['rangePage']) || $_GET ['rangePage'] == 1)
	{
		$ivsService->getCharges ();
	}
	
	$RecordTypes		= $Style->attachObject (new RecordTypes ());
	$RecordDisplayTypes	= $Style->attachObject (new RecordDisplayTypes ());
	
	$Style->Output ("xsl/content/invoices/invoice_service.xsl");
	
?>
