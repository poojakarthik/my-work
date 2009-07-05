<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= TRUE;
	$arrPage['Permission']	= PERMISSION_OPERATOR_VIEW | PERMISSION_OPERATOR_EXTERNAL;
	$arrPage['Modules']		= MODULE_BASE | MODULE_CDR | MODULE_CARRIER | MODULE_RECORD_TYPE | MODULE_RATE | MODULE_FILE | MODULE_SERVICE_TYPE;
	
	// call application
	require ('config/application.php');
	
	// Pull documentation information
	$docDocumentation->Explain ('CDR');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Carrier');
	
	// Get the CDR
	try
	{
		$cdrCDR = $Style->attachObject (new CDR ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/CDR/notfound.xsl');
		exit;
	}
	
	try
	{
		// Carrier Information
		$Style->attachObject (new Carriers);
		
		// Record Type Information
		$Style->attachObject (new RecordType ($cdrCDR->Pull ('RecordType')->getValue ()));
		
		// Rate Information
		$Style->attachObject (new Rate ($cdrCDR->Pull ('Rate')->getValue ()));
		
		// CDR Status Information
		$Style->attachObject (new CDR_Status ($cdrCDR->Pull ('Status')->getValue ()));
		
		// File Import Information
		$Style->attachObject (new FileImport ($cdrCDR->Pull ('File')->getValue ()));
	}
	catch (Exception $e)
	{
		// We only want to surpress errors here
	}
	
	// Output the Account View
	$Style->Output ('xsl/content/CDR/view.xsl');
	
?>
