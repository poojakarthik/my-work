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
	//TODO!!!! - finish this
	$arrPage['Modules']		= MODULE_BASE;
	
	// call application
	require ('config/application.php');	
	
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ("Location: login.php"); exit;
	}
	
	// If the Employee is Authenticated, show the Console
	$Style->Output ("xsl/content/console.xsl");
	
?>
