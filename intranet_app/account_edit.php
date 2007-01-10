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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT;
	
	// call application
	require ('config/application.php');
	
	try
	{
		if ($_SERVER ['REQUEST_METHOD'] == "GET")
		{
			// Using GET
			$actAccount = $Style->attachObject (new Account ($_GET ['Id']));
		}
		else
		{
			// Using POST
			$actAccount = $Style->attachObject (new Account ($_POST ['Id']));
		}
	}
	catch (Exception $e)
	{
		// If the account does not exist, an exception will be thrown
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	
	// If we're wishing to save the details, we can identify this by
	// whether or not we're using GET or POST
	if ($_SERVER ['REQUEST_METHOD'] == "POST")
	{
		$actAccount->Update (
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
		
		if (isset ($_POST ['Archived']))
		{
			$actAccount->ArchiveStatus ($_POST ['Archived']);
		}
		
		header ("Location: account_view.php?Id=" . $actAccount->Pull ('Id')->getValue ());
		exit;
	}
	
	$Style->Output ('xsl/content/account/edit.xsl');
	
?>
