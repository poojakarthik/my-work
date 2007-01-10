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
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_CDR | MODULE_CARRIER;
	
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
		$Style->Output ('xsl/content/windowclose.xsl');
		exit;
	}
	
	$Style->attachObject (new Carriers);
	
	// Output the Account View
	$Style->Output ('xsl/content/CDR/view.xsl');
	
?>
