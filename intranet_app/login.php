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
	$arrPage['Permission']	= PERMISSION_PUBLIC;
	$arrPage['Modules']		= MODULE_BASE;
	
	// call application
	require ('config/application.php');
	
	if (isset ($_POST ['UserName']) && isset ($_POST ['PassWord']))
	{
		// If the UserName and the PassWord fields match the informatiom in the database
		if ($athAuthentication->Login ($_POST ['UserName'], $_POST ['PassWord']))
		{
			// Foward to Console Interface
			header ("Location: console.php"); exit;
		}
		
		// If the UserName and the PassWord fields do not match the informatiom in the database
		// Continue to show the webpage, but with an Error Message
		$oblarrAuthenticationAttempt = new dataArray ("AuthenticationAttempt");
		$oblarrAuthenticationAttempt->Push (
			new dataCDATA ("UserName", $_POST ['UserName'])
		);
		
		$Style->attachObject ($oblarrAuthenticationAttempt);
	}
	
	$Style->Output ("xsl/content/login.xsl");
	
?>
