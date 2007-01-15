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
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_PAYMENT;
	
	// call application
	require ('config/application.php');
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	
	
	// Output the Account View
	$Style->Output ('xsl/content/payment/upload.xsl');
	
?>
