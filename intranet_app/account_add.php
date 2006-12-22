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
	
	
	try
	{
		if ($_GET ['AccountGroup'])
		{
			$acgAccountGroup = $Style->attachObject (new AccountGroup ($_GET ['AccountGroup']));
		}
		else if ($_POST ['AccountGroup'])
		{
			$acgAccountGroup = $Style->attachObject (new AccountGroup ($_POST ['AccountGroup']));
		}
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/accountgroup/notfound.xsl');
		exit;
	}
	
	if ($acgAccountGroup)
	{
		$ctsContacts = $Style->attachObject ($acgAccountGroup->getContacts ());
	}
	
	$Style->attachObject (new CustomerGroups ());
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('AccountGroup');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	$docDocumentation->Explain ('Contact');
	$docDocumentation->Explain ('CustomerGroup');
	$docDocumentation->Explain ('Billing');
	$docDocumentation->Explain ('Payment');
	$docDocumentation->Explain ('Direct Debit');
	$docDocumentation->Explain ('Credit Card');
	
	// Setup the BillingMethod
	$bmeBillingMethod = $Style->attachObject (new BillingMethods ());
		
	
	// If we're wishing to save the details, we can identify this by
	// whether or not we're using GET or POST
	if ($_SERVER ['REQUEST_METHOD'] == "POST")
	{
		$acsAccounts = new Accounts ();
		$acsAccounts->Add (
			null,
			Array (
				"BusinessName"		=> $_POST ['BusinessName'],
				"TradingName"		=> $_POST ['TradingName'],
				"ABN"				=> $_POST ['ABN'],
				"ACN"				=> $_POST ['ACN'],
				"Address1"			=> $_POST ['Address1'],
				"Address2"			=> $_POST ['Address2'],
				"Suburb"			=> $_POST ['Suburb'],
				"Postcode"			=> $_POST ['Postcode'],
				"State"				=> $_POST ['State']
			)
		);
		
		header ("Location: account_view.php?Id=" . $actAccount->Pull ('Id')->getValue ());
		exit;
	}
	
	$Style->Output ('xsl/content/account/add.xsl');
	
?>
