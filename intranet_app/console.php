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
	$arrPage['Modules']		= MODULE_BASE | MODULE_TIP;
	
	// call application
	require ('config/application.php');
	
	if ($_GET ['PabloSays'])
	{
		$tipTip = $Style->attachObject (Tips::FindRandom ($athAuthentication->AuthenticatedEmployee ()->Pull ('PabloSays')->getValue ()));
	}
	
	// If the Employee is Authenticated, show the Console
	$Style->Output ("xsl/content/console.xsl");
?>
