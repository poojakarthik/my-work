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
	$arrPage['Permission']	= PERMISSION_CREDIT_MANAGEMENT;
	$arrPage['Modules']		= MODULE_BASE;
	
	// call application
	require ('config/application.php');
	
	$Style->Output ("xsl/content/charges/charges/confirm.xsl");
	
?>
