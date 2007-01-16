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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT_GROUP | MODULE_DIRECT_DEBIT;
	
	// call application
	require ('config/application.php');
	
	
	
	// Start the Error Handler
	$oblstrError = $Style->attachObject (new dataString ('Error', ''));
	
	// Try getting the account
	try
	{
		$acgAccountGroup = $Style->attachObject (new AccountGroup ($_GET ['AccountGroup'] ? $_GET ['AccountGroup'] : $_POST ['AccountGroup']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/accountgroup/notfound.xsl');
	}
	
	// Start the User Interface Stored Values
	$oblarrUIValues			= $Style->attachObject (new dataArray ('ui-values'));
	
	$oblarrDirectDebit		= $oblarrUIValues->Push (new dataArray ('DirectDebit'));
	
	$oblstrBankName			= $oblarrDirectDebit->Push (new dataString ('BankName',			$_POST ['DirectDebit']['BankName']));
	$oblstrBSB				= $oblarrDirectDebit->Push (new dataString ('BSB',				$_POST ['DirectDebit']['BSB']));
	$oblstrAccountNumber	= $oblarrDirectDebit->Push (new dataString ('AccountNumber',	$_POST ['DirectDebit']['AccountNumber']));
	$oblstrAccountName		= $oblarrDirectDebit->Push (new dataString ('AccountName',		$_POST ['DirectDebit']['AccountName']));
	
	if ($_SERVER ['REQUEST_METHOD'] == "POST")
	{
		if (!$_POST ['DirectDebit']['BankName'])
		{
			$oblstrError->setValue ('BankName');
		}
		else if (!$_POST ['DirectDebit']['BSB'])
		{
			$oblstrError->setValue ('BSB');
		}
		else if (!$_POST ['DirectDebit']['AccountNumber'])
		{
			$oblstrError->setValue ('AccountNumber');
		}
		else if (!$_POST ['DirectDebit']['AccountName'])
		{
			$oblstrError->setValue ('AccountName');
		}
		else
		{
			$ddrDirectDebit = $acgAccountGroup->AddDirectDebit (
				Array (
					"BankName"			=> $_POST ['DirectDebit']['BankName'],
					"BSB"				=> $_POST ['DirectDebit']['BSB'],
					"AccountNumber"		=> $_POST ['DirectDebit']['AccountNumber'],
					"AccountName"		=> $_POST ['DirectDebit']['AccountName']
				)
			);
			
			header ("Location: billing_type_list.php?AccountGroup=" . $acgAccountGroup->Pull ('Id')->getValue ());
			exit;
		}
	}
	
	$docDocumentation->Explain ("AccountGroup");
	$docDocumentation->Explain ("Direct Debit");
	
	$Style->Output ('xsl/content/directdebit/add.xsl');
	
?>
