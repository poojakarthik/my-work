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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT_GROUP | MODULE_CREDIT_CARD | MODULE_DIRECT_DEBIT;
	
	// call application
	require ('config/application.php');
	
	
	// Get Account
	try
	{
		// Try to pull the Account
		$acgAccountGroup = $Style->attachObject (new AccountGroup ($_GET ['AccountGroup']));	
	}
	catch (Exception $e)
	{
		// Output the Account View
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	// Retrieve the DDRs
	$oblarrDirectDebits = $Style->attachObject ($acgAccountGroup->getDirectDebits ());
	
	// Retrieve the CCs
	$oblarrCreditCards = $Style->attachObject ($acgAccountGroup->getCreditCards ());
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('AccountGroup');
	
	// Output the Account View
	$Style->Output ('xsl/content/payment/billing_type/list.xsl');
	
?>
