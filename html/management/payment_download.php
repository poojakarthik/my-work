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
			
			$aCustomerGroups	= $_POST['CustomerGroup'];
			$intPaymentType		= (int)$_POST['PaymentType'];
			if ($intPaymentType === PAYMENT_TYPE_CHEQUE)
			{
				$oDOMDocument		= new DOMDocument('1.0', 'utf-8');
				
				// Cheque Report
				$oCustomerGroups	= $oDOMDocument->createElement('customer-groups');
				$oDOMDocument->appendChild($oCustomerGroups);
				foreach ($aCustomerGroups as $mCustomerGroup)
				{
					$iCustomerGroup	= (int)$mCustomerGroup;
					
					// Customer Group Details
					$oCustomerGroupResult	= $qryQuery->Execute("	SELECT		internal_name		AS name,
																				bank_account_name,
																				bank_bsb,
																				bank_account_number
																	FROM		CustomerGroup cg
																	WHERE		cg.Id = {$iCustomerGroup}
																	LIMIT		1");
					if ($oCustomerGroupResult === false)
					{
						throw new Exception($qryQuery->Error());
					}
					elseif ($aCustomerGroup = $oCustomerGroupResult->fetch_assoc())
					{
						$oCustomerGroup	= $oDOMDocument->createElement('customer-group');
						$oCustomerGroup->setAttribute('id', "customer-group[{$iCustomerGroup}]");
						$oCustomerGroup->setAttribute('uid', $iCustomerGroup);
						
						foreach ($aCustomerGroup as $sField=>$mValue)
						{
							$oCustomerGroupProperty	= $oDOMDocument->createElement(str_replace('_', '-', $sField));
							$oCustomerGroupProperty->appendChild($oDOMDocument->createCDATASection($mValue));
							$oCustomerGroup->appendChild($oCustomerGroupProperty);
						}
						
						// Cheque Records
						$oPaymentsResult	= $qryQuery->Execute("	SELECT		p.Id			AS payment_id,
																				p.AccountGroup	AS account_group_id,
																				p.Account		AS account_id,
																				a.BusinessName	AS business_name,
																				a.TradingName	AS trading_name, 
																				p.TXNReference	AS transaction_reference,
																				p.PaidOn		AS paid_on,
																				p.Amount		AS amount
																				
																	FROM		Payment p
																				JOIN Account a ON (a.Id = p.Account)
																				
																	WHERE		p.PaymentType = {$intPaymentType}
																				AND CAST(p.PaidOn AS DATE) = CAST('{$strPaidOn}' AS DATE)
																				AND a.CustomerGroup = {$iCustomerGroup}");
						if ($oPaymentsResult === false)
						{
							throw new Exception($qryQuery->Error());
						}
						else
						{
							$oCustomerGroupPayments	= $oDOMDocument->createElement('payments');
							$oCustomerGroup->appendChild($oCustomerGroupPayments);
							while ($aPayment = $oPaymentsResult->fetch_assoc())
							{
								// Add the Payment
								$oPayment	= $oDOMDocument->createElement('payment');
								$oPayment->setAttribute('id', "payment[{$aPayment['payment_id']}]");
								$oPayment->setAttribute('uid', $aPayment['payment_id']);
								foreach ($aCustomerGroup as $sField=>$mValue)
								{
									$oPaymentProperty	= $oDOMDocument->createElement(str_replace('_', '-', $sField));
									$oPaymentProperty->appendChild($oDOMDocument->createCDATASection($mValue));
									$oPayment->appendChild($oPaymentProperty);
								}
							}
						}
					}
				}
				
				var_dump($oDOMDocument);
				echo $oDOMDocument->saveXML();
				exit;
				
				// Output
				header('Content-type: text/csv');
				header('Content-Disposition: attachment; filename="Payments-' . $strPaidOn . '.csv"');
				header("Pragma: no-cache");
				header("Expires: 0");
				
				$Style->outputXML('xsl/content/payment/download_super_csv.xsl', $oDOMDocument);
				exit;
			}
			else
			{
				// Other Payment Types
				$strCustomerGroups	= implode(', ', $aCustomerGroups);
				//throw new Exception("'{$strCustomerGroups}'");
				$resPayments		= $qryQuery->Execute(	"SELECT Payment.AccountGroup, Payment.Account, Account.BusinessName, Account.TradingName, CustomerGroup.external_name AS CustomerGroup, Payment.TXNReference, Payment.PaidOn, Payment.Amount " .
															"FROM Payment JOIN Account ON Payment.Account = Account.Id JOIN CustomerGroup ON CustomerGroup.Id = Account.CustomerGroup " .
															"WHERE PaymentType = {$intPaymentType} AND PaidOn = '{$strPaidOn}' AND Account.CustomerGroup IN ({$strCustomerGroups})");
				if ($resPayments === false)
				{
					//throw new Exception($qryQuery->Error());
				}
				$arrPayments		= array();
				while ($arrPayment = $resPayments->fetch_assoc())
				{
					$arrPayments[] = $arrPayment;
				}
				//$GLOBALS['Style']->InsertDOM($arrCreatedByEmployeeResults, 'Payments');
				$Style->InsertDOM($arrPayments, 'Payments');
			}
			
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
