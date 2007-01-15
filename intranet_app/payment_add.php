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
	
	if (!$_POST ['AccountGroup'] && !$_POST ['Account'] && !$_GET ['AccountGroup'] && !$_GET ['Account'])
	{
		header ('Location: console.php');
		exit;
	}
	
	if ($_POST ['AccountGroup'] || $_GET ['AccountGroup'])
	{
		try
		{
			$acgAccountGroup = $Style->attachObject (
				new AccountGroup (
					($_GET ['AccountGroup']) ? $_GET ['AccountGroup'] : $_POST ['AccountGroup']
				)
			);
		}
		catch (Exception $e)
		{
			$Style->Output ('xsl/content/account/notfound.xsl');
			exit;
		}
	}
	
	if ($_POST ['Account'] || $_GET ['Account'])
	{
		try
		{
			$actAccount = $Style->attachObject (
				new Account (
					($_GET ['Account']) ? $_GET ['Account'] : $_POST ['Account']
				)
			);
		}
		catch (Exception $e)
		{
			$Style->Output ('xsl/content/account/notfound.xsl');
			exit;
		}
	}
	
	// Error handler
	$strError = $Style->attachObject (new dataString ('Error'));
	
	// Attach Payment Types
	$ptlPaymentTypes = $Style->attachObject (new PaymentTypes);
	
	// Make the basics
	$arrValues = $Style->attachObject (new dataArray ('ui-values'));
	
	$fltAmount			= $arrValues->Push (new dataFloat	('Amount'));
	$strTXNReference	= $arrValues->Push (new dataString	('TXNReference'));
	
	if ($_POST ['Amount'])
	{
		// If an amount has been posted - then we're attempting to 
		// add the information into the database
		
		if (!$ptlPaymentTypes->setValue ($_POST ['PaymentType']))
		{
			$strError->setValue ('PaymentType');
		}
		else if (!$fltAmount->setValue ($_POST ['Amount']))
		{
			$strError->setValue ('Amount');
		}
		else if (!$strTXNReference->setValue ($_POST ['TXNReference']))
		{
			$strError->setValue ('TXNReference');
		}
		else
		{
			$intPayment = Payments::Pay (
				Array (
					"AccountGroup"			=> ($acgAccountGroup) ? $acgAccountGroup->Pull ('Id')->getValue () : $actAccount->Pull ('AccountGroup')->getValue (),
					"Account"				=> $actAccount->Pull ('Id')->getValue (),
					"PaidOn"				=> date ("Y-m-d"),
					"PaymentType"			=> $_POST ['PaymentType'],
					"Amount"				=> $fltAmount->getValue (),
					"TXNReference"			=> $strTXNReference->getValue (),
					"EnteredBy"				=> $athAuthentication->AuthenticatedEmployee ()->Pull ('Id')->getValue (),
					"Status"				=> PAYMENT_WAITING
				)
			);
			
			header ("Location: payment_added.php?Id=" . $intPayment);
			exit;
		}
	}
	
	// Pull the required documentation information
	$docDocumentation->Explain ('AccountGroup');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Payment');
	
	$Style->Output ('xsl/content/payment/add.xsl');
	
?>
