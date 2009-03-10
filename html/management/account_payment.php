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
		// Retrieve the details of the Current Billing method (Account.BillingType) (used for note generation)
		$intCurrentBillingType = $actAccount->Pull('BillingType')->getValue();
		switch ($intCurrentBillingType)
		{
			case BILLING_TYPE_ACCOUNT:
				$strOldBillingMethod = "Invoice";
				break;
				
			case BILLING_TYPE_DIRECT_DEBIT:
				// Retrieve the old 'Direct Debit from Bank Account' details
				$objBankAccount			= $acgAccountGroup->getDirectDebit($actAccount->Pull('DirectDebit')->getValue());
				$strBankAccountNumber	= $objBankAccount->Pull('AccountNumber')->getValue();
				$strBankAccountNumber	= "XXXX". substr($strBankAccountNumber, -3);
				
				$strOldBillingMethod  = "\nDirect Debit from bank account\n";
				$strOldBillingMethod .= "Account Name: ". $objBankAccount->Pull('AccountName')->getValue() ."\n";
				$strOldBillingMethod .= "Account Number: $strBankAccountNumber\n";
				break;
				
			case BILLING_TYPE_CREDIT_CARD:
				// Retrieve the old 'Direct Debit from Credit Card' details
				$objCreditCard	= $acgAccountGroup->getCreditCard($actAccount->Pull('CreditCard')->getValue());
				$strCardNumber	= $objCreditCard->Pull('CardNumber')->getValue();
				$strCardNumber	= "XXXXXXXXXXXX". substr($strCardNumber, -4);
				
				$strOldBillingMethod  = "\nDirect Debit from credit card\n";
				$strOldBillingMethod .= "Card Name: ". $objCreditCard->Pull('Name')->getValue() ."\n";
				$strOldBillingMethod .= "Card Number: $strCardNumber\n";
				break;
		}
		
		$strBillingType	= substr ($_POST ['BillingType'], 0, 2);
		$strBillingVia	= substr ($_POST ['BillingType'], 2);
		
		$intBillingType = 0;
		
		try
		{
			$objBillingVia = null;
			
			switch ($strBillingType)
			{
				case 'DD':
					$intBillingType	= BILLING_TYPE_DIRECT_DEBIT;
					$objBillingVia	= $acgAccountGroup->getDirectDebit ($strBillingVia);
					
					// System note stuff
					$strBankAccountNumber	= $objBillingVia->Pull('AccountNumber')->getValue();
					$strBankAccountNumber	= "XXXX". substr($strBankAccountNumber, -3);
					
					$strNewBillingMethod  = "Direct Debit from bank account\n";
					$strNewBillingMethod .= "Account Name: ". $objBillingVia->Pull('AccountName')->getValue() ."\n";
					$strNewBillingMethod .= "Account Number: $strBankAccountNumber";
					break;
					
				case 'CC':
					$intBillingType	= BILLING_TYPE_CREDIT_CARD;
					$objBillingVia	= $acgAccountGroup->getCreditCard ($strBillingVia);
					
					// System note stuff
					$strCardNumber	= $objBillingVia->Pull('CardNumber')->getValue();
					$strCardNumber	= "XXXXXXXXXXXX". substr($strCardNumber, -4);
					
					$strNewBillingMethod  = "Direct Debit from credit card\n";
					$strNewBillingMethod .= "Card Name: ". $objBillingVia->Pull('Name')->getValue() ."\n";
					$strNewBillingMethod .= "Card Number: $strCardNumber";
					break;
					
				case 'AC':
					$intBillingType = BILLING_TYPE_ACCOUNT;
					
					$strNewBillingMethod = "Invoice";
					break;
					
				default:
					exit;
			}
			
			TransactionStart();
			$actAccount->BillingTypeSelect($intBillingType, $objBillingVia);
			
			// Record any changes in the account_history table
			$intEmployeeId		= $athAuthentication->AuthenticatedEmployee()->Pull('Id')->getValue();
			$intAccountId		= $actAccount->Pull('Id')->getValue();
			$intAccountGroup	= $acgAccountGroup->Pull('Id')->getValue();
			try
			{
				require_once("../../lib/classes/account/Account_History.php");
				Account_History::recordCurrentState($intAccountId, $intEmployeeId, GetCurrentISODateTime());
			}
			catch (Exception $e)
			{
				// The state could not be recorded
				TransactionRollback();
				throw new Exception("Could not save state of account - ". $e->getMessage());
			}
			
			TransactionCommit();
						
			// System note is generated when payment method is changed
			$strNote = "Payment method changed from $strOldBillingMethod to $strNewBillingMethod";
	
			$GLOBALS['fwkFramework']->AddNote($strNote, SYSTEM_NOTE_TYPE, $intEmployeeId , $intAccountGroup, $intAccountId);
				
			$Style->Output (
				'xsl/content/account/payment_selected.xsl',
				Array (
					'Account'	=> $actAccount->Pull ('Id')->getValue ()
				)
			);
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
	
	Debug(PERMISSION_CREDIT_MANAGEMENT.' vs '.$athAuthentication->AuthenticatedEmployee()->Pull('Privileges')->Value());
	exit;
	$Style->attachObject(new dataBoolean('CanSeeCVV', (PERMISSION_CREDIT_MANAGEMENT & $athAuthentication->AuthenticatedEmployee()->Privileges())));
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	
	$Style->Output (
		'xsl/content/account/payment.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue ()
		)
	);
	
?>
