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
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	
	
	
	try
	{
		// Get the Account
		$actAccount		= $Style->attachObject (new Account ($_GET ['Id']));	
	}
	catch (Exception $e)
	{
		// Output the Account View
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	$ivlInvoices = $Style->attachObject ($actAccount->Invoices ());
	$ivlInvoices->Sample ();
	
	// Output the Account View
	$Style->Output ('xsl/content/account/ledger.xsl');
	
?>
