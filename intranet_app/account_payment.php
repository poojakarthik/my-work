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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT_GROUP | MODULE_DIRECT_DEBIT | MODULE_CREDIT_CARD | MODULE_BILLING;
	
	// call application
	require ('config/application.php');
	
	try
	{
		if ($_GET ['Id'])
		{
			// Using GET
			$actAccount = $Style->attachObject (new Account ($_GET ['Id']));
		}
		else
		{
			// Using POST
			$actAccount = $Style->attachObject (new Account ($_POST ['Id']));
		}
		
		$acgAccountGroup = $Style->attachObject ($actAccount->AccountGroup ());
	}
	catch (Exception $e)
	{
		// If the account does not exist, an exception will be thrown
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	// Start the Error String
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	// Start remembering
	$oblarrUIValues = $Style->attachObject (new dataArray ('ui-values'));
	
	$oblintBillingType = $oblarrUIValues->Push (
		new dataInteger (
			'BillingType',
			($_POST ['BillingType']) ? $_POST ['BillingType'] : $actAccount->Pull ('BillingType')->getValue ()
		)
	);
	
	$oblintDDR = $oblarrUIValues->Push (
		new dataInteger (
			'DirectDebit',
			($_POST ['DirectDebit']) ? $_POST ['DirectDebit'] : $actAccount->Pull ('DirectDebit')->getValue ()
		)
	);
	
	$oblintCC = $oblarrUIValues->Push (
		new dataInteger (
			'CreditCard',
			($_POST ['CreditCard']) ? $_POST ['CreditCard'] : $actAccount->Pull ('CreditCard')->getValue ()
		)
	);
	
	// If the Billing Type is set in the POST, then we are
	// going to be updating the information
	if ($_POST ['BillingType'])
	{
		$btsBillingTypes = new BillingTypes ();
		
		if (!$btsBillingTypes->setValue ($_POST ['BillingType']))
		{
			$oblstrError->setValue ('BillingType Invalid');
		}
		else
		{
			try
			{
				$objBillingVia = null;
				
				switch ($_POST ['BillingType'])
				{
					case BILLING_TYPE_DIRECT_DEBIT:
						$objBillingVia = $acgAccountGroup->getDirectDebit ($_POST ['DirectDebit']);
						break;
						
					case BILLING_TYPE_CREDIT_CARD:
						$objBillingVia = $acgAccountGroup->getCreditCard ($_POST ['CreditCard']);
						break;
				}
				
				$actAccount->BillingTypeSelect ($_POST ['BillingType'], $objBillingVia);
				
				$Style->Output ('xsl/content/account/payment_selected.xsl');
				exit;
			}
			catch (Exception $e)
			{
				$oblstrError->setValue ($e->getMessage ());
			}
		}
	}
	
	// Get Direct Debit and Credit Card Details
	$ddrDirectDebits	= $Style->attachObject ($acgAccountGroup->getDirectDebits ());
	$crcCreditCards		= $Style->attachObject ($acgAccountGroup->getCreditCards ());
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	
	$Style->Output ('xsl/content/account/payment.xsl');
	
?>
