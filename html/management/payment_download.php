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
	$arrPage['Modules']		= MODULE_BASE | MODULE_PAYMENT | MODULE_CUSTOMER_GROUP;
	
	// call application
	require ('config/application.php');
	
	// Payment Type
	$ptlPaymentTypes	= $Style->attachObject (new PaymentTypes());
	$cglCustomerGroups	= $Style->attachObject (new CustomerGroups());
	
	// Error Handler
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	$qryQuery	= new Query();
	
	if ($_POST ['PaymentType'])
	{
		// Handle the PaidOn option
		switch ($_POST ['PaidOn'])
		{
			case 'TODAY':
				$strPaidOn = date ('Y-m-d');
				break;
				
			case 'YESTERDAY':
				$strPaidOn = date ('Y-m-d', strtotime ('-1 day'));
				break;
				
			case 'CUSTOM':
				$strPaidOn = date ('Y-m-d', mktime (0, 0, 0, $_POST ['PaidOn:CUSTOM:month'], $_POST ['PaidOn:CUSTOM:day'], $_POST ['PaidOn:CUSTOM:year']));
				break;
		}
		
		if (!$ptlPaymentTypes->setValue ($_POST ['PaymentType']))
		{
			$oblstrError->setValue ('PaymentType');
		}
		else
		{
			/*$payPayments = $Style->attachObject(new Payments);
			//$payPayments->Constrain ('EnteredBy',	'EQUALS', $athAuthentication->AuthenticatedEmployee ()->Pull ('Id')->getValue ());
			$payPayments->Constrain('PaymentType',	'EQUALS', $_POST ['PaymentType']);
			$payPayments->Constrain('PaidOn',		'EQUALS', $strPaidOn);
			$oblsamPayments = $payPayments->Sample();*/
			
			$intCustomerGroup	= (int)$_POST['CustomerGroup'];
			$intPaymentType		= (int)$_POST['PaymentType'];
			$resPayments		= $qryQuery->Execute(	"SELECT Payment.AccountGroup, Payment.Account, Account.BusinessName, Account.TradingName, Payment.TXNReference, Payment.PaidOn, Payment.Amount " .
														"FROM Payment JOIN Account ON Payment.Account = Account.Id",
														"WHERE PaymentType = {$intPaymentType} AND PaidOn = '{$strPaidOn}' AND Account.CustomerGroup = {$intCustomerGroup}");
			$arrPayments		= array();
			while ($arrPayment = $resPayments->fetch_assoc())
			{
				$arrPayments[] = $arrPayment;
			}
			$GLOBALS['Style']->InsertDOM($arrCreatedByEmployeeResults, 'Payments');
			
			/*
			$arrAccounts	= array();
			$oblarrAccounts	= $Style->attachObject(new dataArray('Accounts', 'Account'));
			
			foreach ($oblsamPayments as $mixIndex=>$payPayment)
			{
				$intAccount	= $payPayment->Pull('Account')->getValue();
				if ($intAccount)
				{
					$accAccount	= new Account($intAccount);
					
					// Ensure this is of the correct CustomerGroup
					$intCustomerGroup	= $accAccount->Pull('CustomerGroup')->getValue();
					if ($intCustomerGroup != $_POST['CustomerGroup'])
					{
						//throw new Exception("Trying to POP index {$mixIndex}");
						throw new Exception(print_r($oblsamPayments, true));
					}
					elseif (!isset($arrAccounts[$intAccount]))
					{
						$arrAccounts[$payPayment->Pull('Account')->getValue()]	= $oblarrAccounts->Push ($accAccount);
					}
				}
				else
				{
					// Has no Account, and therefore no CustomerGroup
					$oblsamPayments->popItem($mixIndex);
				}
			}
			//throw new Exception(get_class($oblsamPayments));
			*/
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="Payments-' . $strPaidOn . '.csv"');
			header("Pragma: no-cache");
			header("Expires: 0");
			
			$Style->Output ('xsl/content/payment/download_csv.xsl');
			exit;
		}
	}
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Payment');
	$docDocumentation->Explain ('CustomerGroup');
	
	// Output the Account View
	$Style->Output ('xsl/content/payment/download.xsl');
	
?>
