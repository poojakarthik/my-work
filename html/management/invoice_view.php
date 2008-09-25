<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR_VIEW;
	$arrPage['Modules']		= MODULE_BASE | MODULE_INVOICE | MODULE_CHARGE | MODULE_CDR | MODULE_SERVICE | MODULE_SERVICE_TOTAL | MODULE_RECORD_TYPE | MODULE_SERVICE_TYPE;
	
	// call application
	require ('config/application.php');
	
	try
	{
		// Get the Invoice + Associated Account
		$invInvoice		= $Style->attachObject (new Invoice ($_GET ['Invoice']));
		$actAccount		= $Style->attachObject ($invInvoice->Account ());
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/invoice/notfound.xsl');
		exit;
	}
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Invoice');
	
	// If no service is set, then ask for the service
	if (!$_GET ['ServiceTotal'])
	{
		$stlServiceTotals = $Style->attachObject ($invInvoice->ServiceTotals ());
		$stlServiceTotals->Sample ();
		
		$Style->Output (
			'xsl/content/invoice/view_service_select.xsl',
			Array (
				'Account'	=> $actAccount->Pull ('Id')->getValue ()
			)
		);
		exit;
	}
	
	// If the ServiceTotal is set, then filter for it
	try
	{
		// Get the ServiceTotal
		$sttServiceTotal = $Style->attachObject ($invInvoice->ServiceTotal ($_GET ['ServiceTotal']));		
		$srvService = $Style->attachObject ($sttServiceTotal->Service ());
	}
	catch (Exception $e)
	{
		// If the Service is not found, display an error
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	if (!isset ($_GET ['rangePage']) || $_GET ['rangePage'] == 1)
	{
		// Get the Charges the Invoice has
		$cgsCharges	= $Style->attachObject ($invInvoice->Charges ());
		$cgsCharges->Constrain ('Service', '=', $sttServiceTotal->Pull ('Service')->getValue ());
		$cgsCharges->Sample ();
	}
	
	// Get the CDRs the Invoice has
	// We may also want to Constrain this to only show a Certain Record Type
	// WIP = THIS WON'T WORK ON THE POSTGRES DB!!!
/*
	$cdrCDRs = $Style->attachObject ($invInvoice->CDRs ());
	$cdrCDRs->Constrain ('service',			'=',	$sttServiceTotal->Pull ('Service')->getValue ());
	$cdrCDRs->Constrain ('invoice_run_id',  '=',	$invInvoice->Pull ('invoice_run_id')->getValue ());
	
	if ($_GET ['RecordType'])
	{
		$cdrCDRs->Constrain ('record_type',	'=',	$_GET ['RecordType']);
	}
	
	$cdrCDRs->Sample (
		isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1,
		isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 30
	);
*/
	
	// Get a list of Record Types
	$rtsRecordTypes = $Style->attachObject (new RecordTypes);
	$rtsRecordTypes->Constrain ('ServiceType', '=', $srvService->Pull ('ServiceType')->getValue ());
	$rtsRecordTypes->Sample ();
	
	$docDocumentation->Explain ('CDR');
	
	// Output the Account View
	$Style->Output (
		'xsl/content/invoice/view.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue (),
			'Invoice'	=> $invInvoice->Pull ('Id')->getValue (),
			'Service'	=> $sttServiceTotal->Pull ('Service')->getValue ()
		)
	);
	
?>
