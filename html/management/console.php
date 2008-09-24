<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//

	header("Location: ../admin/reflex.php/Console/View/");
	
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR_VIEW;
	$arrPage['Modules']		= MODULE_BASE | MODULE_TIP;
	
	// call application
	require ('config/application.php');
	
	// If we're loading this page after a Login, then we will
	// have a "PabloSays" request
	if (array_key_exists('PabloSays', $_GET) && $_GET ['PabloSays'])
	{
		// Try and Load a tip from PabloSays. If no tip
		// is found, then don't error, just continue.
		try
		{
			$tipTip = $Style->attachObject (Tips::FindRandom ($athAuthentication->AuthenticatedEmployee ()->Pull ('PabloSays')->getValue ()));
		}
		catch (Exception $e)
		{
		}
	}
	
	// If the Employee is Authenticated, show the Console
	$Style->Output ("xsl/content/console.xsl");
	
?>
