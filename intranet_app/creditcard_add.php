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
	$oblintCardType			= $oblarrCreditCard->Push (new dataInteger('CardType',            $_POST ['CreditCard']['CardType']));
	$oblstrName				= $oblarrCreditCard->Push (new dataString ('Name',                $_POST ['CreditCard']['Name']));
	$oblstrCardNumber		= $oblarrCreditCard->Push (new dataString ('CardNumber',          $_POST ['CreditCard']['CardNumber']));
	$oblintExpMonth			= $oblarrCreditCard->Push (new dataInteger('ExpMonth',            $_POST ['CreditCard']['ExpMonth']));
	$oblintExpYear			= $oblarrCreditCard->Push (new dataInteger('ExpYear',             $_POST ['CreditCard']['ExpYear']));
	$oblstrCVV				= $oblarrCreditCard->Push (new dataString ('CVV',                 $_POST ['CreditCard']['CVV']));
	$cctCreditCardTypes		= $Style->attachObject (new CreditCardTypes);
    
    if ($_SERVER ['REQUEST_METHOD'] == 'POST')
    {
        if (!$cctCreditCardTypes->setValue ($_POST ['CreditCard']['CardType']))
        {
            $oblstrError->setValue ('CardType');
        }
        else if (!$_POST ['CreditCard']['Name'])
        {
            $oblstrError->setValue ('Name');
        }
        else if (!$_POST ['CreditCard']['CardNumber'])
        {
            $oblstrError->setValue ('CardNumber');
        }
        else if (!$_POST ['CreditCard']['ExpMonth'])
        {
            $oblstrError->setValue ('ExpMonth');
        }
        else if (!$_POST ['CreditCard']['ExpYear'])
        {
            $oblstrError->setValue ('ExpYear');
        }
        else
        {
            $crcCreditCard = $acgAccountGroup->AddCreditCard (
                Array (
                    'CardType'			=> $_POST ['CreditCard']['CardType'],
                    'Name'				=> $_POST ['CreditCard']['Name'],
                    'CardNumber'		=> $_POST ['CreditCard']['CardNumber'],
                    'ExpMonth'			=> $_POST ['CreditCard']['ExpMonth'],
                    'ExpYear'			=> $_POST ['CreditCard']['ExpYear'],
                    'CVV'				=> $_POST ['CreditCard']['CVV']
                )
            );
            
			$actAccount->BillingTypeSelect (BILLING_TYPE_CREDIT_CARD, $crcCreditCard);
            
            // TODO!bash! [  DONE  ]		what if we came from account payment.php ?
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
