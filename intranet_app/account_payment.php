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
		$actAccount = $Style->attachObject (new Account (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
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
	$oblstrBillingType = $oblarrUIValues->Push (new dataString ('BillingType'));
	
	switch ($actAccount->Pull ('BillingType')->getValue ())
	{
		case BILLING_TYPE_ACCOUNT:		$oblstrBillingType->setValue ('AC'); break;
		case BILLING_TYPE_DIRECT_DEBIT:	$oblstrBillingType->setValue ('DD' . $actAccount->Pull ('DirectDebit')->getValue ()); break;
		case BILLING_TYPE_CREDIT_CARD:	$oblstrBillingType->setValue ('CC' . $actAccount->Pull ('CreditCard')->getValue ()); break;
	}
	
	// If the Billing Type is set in the POST, then we are
	// going to be updating the information
	if ($_POST ['BillingType'])
	{
		$strBillingType	= substr ($_POST ['BillingType'], 0, 2);
		$strBillingVia	= substr ($_POST ['BillingType'], 2);
		
		$intBillingType = 0;
		
		try
		{
			$objBillingVia = null;
			
			switch ($strBillingType)
			{
				case 'DD':
					$intBillingType = BILLING_TYPE_DIRECT_DEBIT;
					$objBillingVia = $acgAccountGroup->getDirectDebit ($strBillingVia);
					break;
					
				case 'CC':
					$intBillingType = BILLING_TYPE_CREDIT_CARD;
					$objBillingVia = $acgAccountGroup->getCreditCard ($strBillingVia);
					break;
					
				case 'AC':
					$intBillingType = BILLING_TYPE_ACCOUNT;
					break;
					
				default:
					exit;
			}
			
			$actAccount->BillingTypeSelect ($intBillingType, $objBillingVia);
			
			$Style->Output ('xsl/content/account/payment_selected.xsl');
			exit;
		}
		catch (Exception $e)
		{
			$oblstrError->setValue ($e->getMessage ());
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
