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
	
	// Pull documentation information
	$docDocumentation->Explain ('CDR');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Carrier');
	
	// Get the CDR
	try
	{
		$cdrCDR = $Style->attachObject (new CDR ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/windowclose.xsl');
		exit;
	}
	
	$Style->attachObject (new Carriers);
	
	// Output the Account View
	$Style->Output ('xsl/content/CDR/view.xsl');
	
?>
