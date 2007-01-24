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
	$arrPage['Permission']	= PERMISSION_OPERATOR | PERMISSION_ADMIN;
	$arrPage['Modules']		= MODULE_BASE | MODULE_DATA_REPORT;
	
	// call application
	require ('config/application.php');
	
	$rpsReports = new Reports ();
	
	// Explain the Fundamentals
	$docDocumentation->Explain ('Report');
	$docDocumentation->Explain ('Archive');
	
	$Style->Output ('xsl/content/account/list.xsl');
	
?>
