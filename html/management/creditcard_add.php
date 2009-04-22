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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT_GROUP | MODULE_CREDIT_CARD | MODULE_BILLING;
	
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
	$oblarrCreditCard		= $oblarrUIValues->Push (new dataArray ('CreditCard'));
	$oblintCardType			= $oblarrCreditCard->Push (new dataInteger('CardType',			$_POST ['CreditCard']['CardType']));
	$oblstrName				= $oblarrCreditCard->Push (new dataString ('Name',				$_POST ['CreditCard']['Name']));
	$oblstrCardNumber		= $oblarrCreditCard->Push (new dataString ('CardNumber',		$_POST ['CreditCard']['CardNumber']));
	$oblintExpMonth			= $oblarrCreditCard->Push (new dataInteger('ExpMonth',			$_POST ['CreditCard']['ExpMonth']));
	$oblintExpYear			= $oblarrCreditCard->Push (new dataInteger('ExpYear',			$_POST ['CreditCard']['ExpYear']));
	$oblstrCVV				= $oblarrCreditCard->Push (new dataString ('CVV',				$_POST ['CreditCard']['CVV']));
	$cctCreditCardTypes		= $Style->attachObject (new CreditCardTypes);
	
	if ($_SERVER ['REQUEST_METHOD'] == 'POST')
	{
		if (!$cctCreditCardTypes->setValue ($_POST ['CreditCard']['CardType']))
		{
			// Check that the Card Type is a Valid selection
			$oblstrError->setValue ('CardType');
		}
		else if (!$_POST ['CreditCard']['Name'])
		{
			// Check that the Card Holder's Name Exists
			$oblstrError->setValue ('Name');
		}
		else if (!$_POST ['CreditCard']['CardNumber'])
		{
			// Check that the Card Number Exists
			$oblstrError->setValue ('CardNumber');
		}
		else if (!CheckLuhn ($_POST ['CreditCard']['CardNumber']))
		{
			// Check that the Card Number is LUHN Valid
			$oblstrError->setValue ('Card Invalid');
		}
		else if (!CheckCC ($_POST ['CreditCard']['CardNumber'], $_POST ['CreditCard']['CardType']))
		{
			// Check that the Card Number is Company-Type Valid
			$oblstrError->setValue ('Card Number Type');
		}
		else if (!$_POST ['CreditCard']['ExpMonth'])
		{
			// Check that the Expiration Month Exists
			$oblstrError->setValue ('ExpMonth');
		}
		else if (!$_POST ['CreditCard']['ExpYear'])
		{
			// Check that the Expiration Year Exists
			$oblstrError->setValue ('ExpYear');
		}
		else if (!expdate ($_POST ['CreditCard']['ExpMonth'], $_POST ['CreditCard']['ExpYear']))
		{
			// Check that the Expiration Date has not Expired
			$oblstrError->setValue ('Expired');
		}
		else if (!preg_match ("/^(\d{3,4})$/", $_POST ['CreditCard']['CVV']))
		{
			// Check that the CVV is Valid
			$oblstrError->setValue ('CVV');
		}
		else
		{
			// AddCreditCard() takes care of encrypting the CardNumber and CVV
			$crcCreditCard = $acgAccountGroup->AddCreditCard (
				Array (
					'CardType'			=> $_POST ['CreditCard']['CardType'],
					'Name'				=> $_POST ['CreditCard']['Name'],
					'CardNumber'		=> $_POST ['CreditCard']['CardNumber'],
					'ExpMonth'			=> $_POST ['CreditCard']['ExpMonth'],
					'ExpYear'			=> $_POST ['CreditCard']['ExpYear'],
					'CVV'				=> $_POST ['CreditCard']['CVV'],
					'employee_id'		=> $athAuthentication->AuthenticatedEmployee()->Pull('Id')->getValue()
				)
			);
			
			header ('Location: account_payment.php?Id=' . $actAccount->Pull ('Id')->getValue ());
			exit;
		}
	}
	
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Credit Card');
	
	$Style->Output (
		'xsl/content/creditcard/add.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue ()
		)
	);
	
?>
