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
	$arrPage['Permission'] 	= Array(PERMISSION_ADMIN, PERMISSION_ACCOUNTS);
	$arrPage['Modules']		= MODULE_BASE;
	
	// call application
	require ('config/application.php');
	
	// If the Employee is Authenticated, show the Console
	$Style->Output ("xsl/content/console_admin.xsl");
	
?>
