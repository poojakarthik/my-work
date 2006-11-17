<?php
	
	require ("config/application_loader.php");
	
	// Check if the person is currently logged in. If they are logged in, 
	// we want to redirect them to the "Console" page.
	if ($athAuthentication->getAuthentication ())
	{
		header ("Location: console.php");
		exit;
	}
	
	// If the person has already been to this page and they
	// wish to authenticate, ensure that the values are not
	// blank and push the information to the auth class. 
	// we want to redirect them to the "Console" page.
	
	if (isset ($_POST ['UserName']) && isset ($_POST ['PassWord']) &&
	!empty ($_POST ['UserName']) && !empty ($_POST ['PassWord']))
	{
		// Check Crudentials
		if ($athAuthentication->Login ($_POST ['UserName'], $_POST ['PassWord']))
		{
			// If the person is logged in successfully, we want to
			// redirect them to the "Console" page.
			header ("Location: console.php");
			exit;
		}
	}
	
	// If there was an attempt to login that failed
	// Return the UserName to the screen. The presence
	// of /Response/AuthenticationAttempt flags that
	// the previous login attempt had failed.
	if (isset ($_POST ['UserName']))
	{
		$oblarrAuthenticationAttempt = new dataArray ("AuthenticationAttempt");
		$oblarrAuthenticationAttempt->Push (
			new dataString ("UserName", $_POST ['UserName'])
		);
		
		$Style->attachObject ($oblarrAuthenticationAttempt);
	}
	
	// If we're up to here, we are not logged in.
	// :. we want to sure the login XSLT
	
	$Style->Output ("xsl/content/login.xsl");
	
?>
