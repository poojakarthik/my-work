<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ('config/application_loader.php');
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ('Location: login.php'); exit;
	}
	
	$arrDocDetails = $Style->attachObject (new dataArray ('DocumentationDetails'));
	$arrDocDetails->Push (new DocumentationField ($_GET ['Entity'], $_GET ['Field']));
	
	// Output the Account View
	$Style->Output ('xsl/content/documentation/view.xsl');
	
?>
