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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT_GROUP | MODULE_DIRECT_DEBIT | MODULE_BILLING;
	
	// call application
	require ('config/application.php');
	
    // Start the Error Handler
    $oblstrError = $Style->attachObject (new dataString ('Error', ''));
    
    try
    {
	    // Try getting the account + account group
	    $actAccount			= $Style->attachObject (new Account (($_GET ['Account']) ? $_GET ['Account'] : $_POST ['Account']));
        $acgAccountGroup	= $Style->attachObject ($actAccount->AccountGroup ());
    }
    catch (Exception $e)
    {
        $Style->Output ('xsl/content/account/notfound.xsl');
        exit;
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
		else if (!BSBValid ($_POST ['DirectDebit']['BSB']))
		{
			$oblstrError->setValue ('BSB');
		}
		else if (!$_POST ['DirectDebit']['AccountNumber'])
		{
			$oblstrError->setValue ('AccountNumber');
		}
		else if (!BankAccountValid ($_POST ['DirectDebit']['AccountNumber']))
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
					"AccountName"		=> $_POST ['DirectDebit']['AccountName'],
					"employee_id"		=> $athAuthentication->AuthenticatedEmployee()->Pull('Id')->getValue()
				)
			);
			
            // TODO!bash! [  DONE  ]		what if we came from account payment.php ?
            header ('Location: account_payment.php?Id=' . $actAccount->Pull ('Id')->getValue ());
            exit;
		}
	}
	
	$docDocumentation->Explain ("Account");
	$docDocumentation->Explain ("Direct Debit");
	
	$Style->Output (
		'xsl/content/directdebit/add.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue ()
		)
	);
	
?>
