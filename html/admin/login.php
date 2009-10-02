<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require_once('../management/config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_PUBLIC;
	$arrPage['Modules']		= MODULE_BASE;
	
	// call application
	require_once('../management/config/application.php');
	
	if ($athAuthentication->isAuthenticated ())
	{
		//header ('Location: ../management/console.php'); exit;
		header ("Location: ../admin/reflex.php/Console/View/"); exit;
	}
	
	if (isset ($_POST ['UserName']) && isset ($_POST ['PassWord']))
	{
		// If the UserName and the PassWord fields match the informatiom in the database
		if ($athAuthentication->Login ($_POST ['UserName'], $_POST ['PassWord']))
		{
			// Foward to Console Interface
			//header ("Location: ../management/console.php?PabloSays=1"); exit;
			header ("Location: ../admin/reflex.php/Console/View/"); exit;
		}
		
		// If the UserName and the PassWord fields do not match the informatiom in the database
		// Continue to show the webpage, but with an Error Message
		$oblarrAuthenticationAttempt = new dataArray ("AuthenticationAttempt");
		$oblarrAuthenticationAttempt->Push (
			new dataString ("UserName", $_POST ['UserName'])
		);
		
		$Style->attachObject ($oblarrAuthenticationAttempt);
	}
	
	$Style->Output ("../management/xsl/content/login.xsl");
	
?>
