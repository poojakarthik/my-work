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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT_GROUP | MODULE_ACCOUNT | MODULE_PAYMENT;
	
	// call application
	require ('config/application.php');
	
	
	
	// Payments can be made in one of two ways:
	// 1. Payments can be made against an Account Group. By doing this, the Invoice with
	//    the most outstanding balance in the Account Group will be Paid First.
	// 2. Payments can be made against an Account. By doing this, the Invoice with
	//    the most outstanding balance in the Account will be Paid First.
	
	
	// If an Account Group Id# exists, then we want to validate against
	// the Account Group. By doing this, we will assume that there is no
	// Account and the Account Group is the definitive answer to the
	// 
	
	if ($_GET ['Account'] || $_POST ['Account'])
	{
		try
		{
			$actAccount			= $Style->attachObject (new Account (($_GET ['Account']) ? $_GET ['Account'] : $_POST ['Account']));
			$acgAccountGroup	= $Style->attachObject ($actAccount->AccountGroup ());
			
			$Style->attachObject (new dataString ('Account', $actAccount->Pull ('Id')->getValue ()));
		}
		catch (Exception $e)
		{
			$Style->Output ('xsl/content/account/notfound.xsl');
			exit;
		}
	}
	
	if ($_GET ['Contact'] || $_POST ['Contact'])
	{
		try
		{
			$cntContact			= $Style->attachObject (new Contact (($_GET ['Contact']) ? $_GET ['Contact'] : $_POST ['Contact']));
			
			if (!isset ($acgAccountGroup))
			{
				$acgAccountGroup	= $Style->attachObject ($cntContact->AccountGroup ());
			}
		}
		catch (Exception $e)
		{
			$Style->Output ('xsl/content/account/notfound.xsl');
			exit;
		}
	}
	
	if (!$actAccount && !$cntContact)
	{
		header ('Location: console.php');
		exit;
	}
	
	// User Interface Values
	$oblarrUIValues = $Style->attachObject (new dataArray ('ui-values'));
	
	$oblbolAccount_Use		= $oblarrUIValues->Push (new dataBoolean('Account-Use',		$_POST ['Account-Use']));
	$oblstrAmount			= $oblarrUIValues->Push (new dataString ('Amount',			$_POST ['Amount']));
	$oblstrTXNReference		= $oblarrUIValues->Push (new dataString	('TXNReference',	$_POST ['TXNReference']));
	
	// Attach Payment Types
	$ptlPaymentTypes = $Style->attachObject (new PaymentTypes);
	
	// Error handler
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	if ($_POST ['PaymentType'])
	{
		// If an amount has been posted - then we're attempting to 
		// add the information into the database
		
		$oblfltAmount = new dataFloat ('Amount', $_POST ['Amount']);
		
		if (!$ptlPaymentTypes->setValue ($_POST ['PaymentType']))
		{
			$oblstrError->setValue ('PaymentType');
		}
		else if (!$oblfltAmount->setValue ($_POST ['Amount']))
		{
			$oblstrError->setValue ('Amount');
		}
		else if (!$_POST ['TXNReference'])
		{
			$oblstrError->setValue ('TXNReference');
		}
		else
		{
			$intPayment = Payments::Pay (
				Array (
					"AccountGroup"			=> ($acgAccountGroup) ? $acgAccountGroup->Pull ('Id')->getValue () : $actAccount->Pull ('AccountGroup')->getValue (),
					"Account"				=> ($oblbolAccount_Use->isTrue ()) ? $actAccount->Pull ('Id')->getValue () : null,
					"PaymentType"			=> $_POST ['PaymentType'],
					"Amount"				=> $_POST ['Amount'],
					"TXNReference"			=> $oblstrTXNReference->getValue (),
					"EnteredBy"				=> $athAuthentication->AuthenticatedEmployee ()->Pull ('Id')->getValue (),
					"Status"				=> PAYMENT_WAITING
				)
			);
			
			header ("Location: payment_added.php?Id=" . $intPayment . (($cntContact) ? "&Contact=" . $cntContact->Pull ('Id')->getValue () : ""));
			exit;
		}
	}
	
	// By this point, we should have an Account Group specified, and Possibly an
	// account. Therefore, we can assume that the $acgAccountGroup variable is set.
	$acsAccounts = $Style->attachObject ($acgAccountGroup->getAccounts ());
	$oblsamAccounts = $acsAccounts->Sample ();
	
	// Pull the required documentation information
	$docDocumentation->Explain ('AccountGroup');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Payment');
	
	$Style->Output (
		'xsl/content/payment/add.xsl',
		Array (
			'Contact'	=> ($cntContact)	? $cntContact->Pull ('Id')->getValue () : null,
			'Account'	=> ($actAccount)	? $actAccount->Pull ('Id')->getValue () : null
		)
	);
	
?>
