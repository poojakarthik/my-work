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
	//TODO!!!! - finish this
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT_GROUP | MODULE_CUSTOMER_GROUP | MODULE_CREDIT_CARD | MODULE_BILLING | MODULE_ACCOUNT;
	
	// call application
	require ('config/application.php');
	
	
	$oblstrError = $Style->attachObject (new dataString ('Error', ''));
	
	try
	{
		if ($_GET ['AccountGroup'])
		{
			$acgAccountGroup = $Style->attachObject (new AccountGroup ($_GET ['AccountGroup']));
		}
		else if ($_POST ['AccountGroup'])
		{
			$acgAccountGroup = $Style->attachObject (new AccountGroup ($_POST ['AccountGroup']));
		}
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/accountgroup/notfound.xsl');
		exit;
	}
	
	if ($acgAccountGroup)
	{
		$ctsContacts = $Style->attachObject ($acgAccountGroup->getContacts ());
	}
	
	// Attach essentials (CustomerGroup and CreditCardTypes)
	$cgsCustomerGroups		= $Style->attachObject (new CustomerGroups ());
	$ccsCreditCardTypes		= $Style->attachObject (new CreditCardTypes ());
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('AccountGroup');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	$docDocumentation->Explain ('Contact');
	$docDocumentation->Explain ('CustomerGroup');
	$docDocumentation->Explain ('Billing');
	$docDocumentation->Explain ('Payment');
	$docDocumentation->Explain ('Direct Debit');
	$docDocumentation->Explain ('Credit Card');
	
	// Setup the BillingMethod
	$bmeBillingMethods = $Style->attachObject (new BillingMethods ());
		
	
	// If we're wishing to save the details, we can identify this by
	// whether or not we're using GET or POST
	if ($_SERVER ['REQUEST_METHOD'] == "POST")
	{
		$oblarrAccount = $Style->attachObject (new dataArray ('Account'));
		$oblarrAccount->Push (new dataString		('BusinessName',		$_POST ['BusinessName']		));
		$oblarrAccount->Push (new dataString		('TradingName',		$_POST ['TradingName']			));
		$oblarrAccount->Push (new ABN				('ABN',					$_POST ['ABN']					));
		$oblarrAccount->Push (new ACN				('ACN',					$_POST ['ACN']					));
		$oblarrAccount->Push (new dataString		('Address1',			$_POST ['Address1']				));
		$oblarrAccount->Push (new dataString		('Address2',			$_POST ['Address2']				));
		$oblarrAccount->Push (new dataString		('Suburb',				$_POST ['Suburb']				));
		$oblarrAccount->Push (new dataString		('Postcode',			$_POST ['Postcode']				));
		$oblarrAccount->Push (new dataString		('State',				$_POST ['State']				));
		
		$oblarrDirectDebit = $Style->attachObject (new dataArray ('DirectDebit'));
		$oblarrDirectDebit->Push (new dataString		('BankName',		$_POST ['DDR']['BankName']			));
		$oblarrDirectDebit->Push (new dataString		('BSB',				$_POST ['DDR']['BSB']				));
		$oblarrDirectDebit->Push (new dataString		('AccountNumber',	$_POST ['DDR']['AccountNumber']	));
		$oblarrDirectDebit->Push (new dataString		('AccountName',	$_POST ['DDR']['AccountName']		));
		
		$oblarrCreditCard = $Style->attachObject (new dataArray ('CreditCard'));
		$oblarrCreditCard->Push (new dataString		('Name',				$_POST ['CC']['Name']					));
		$oblarrCreditCard->Push (new dataString		('CardNumber',			$_POST ['CC']['CardNumber']			));
		$oblarrCreditCard->Push (new dataInteger	('ExpMonth',			$_POST ['CC']['ExpMonth']				));
		$oblarrCreditCard->Push (new dataInteger	('ExpYear',				$_POST ['CC']['ExpYear']				));
		
		$oblarrContact = $Style->attachObject (new dataArray ('Contact'));
		
		$oblarrContact->Push (new dataString		('Title',			$_POST ['Contact']['Title']			));
		$oblarrContact->Push (new dataString		('FirstName',		$_POST ['Contact']['FirstName']	));
		$oblarrContact->Push (new dataString		('LastName',		$_POST ['Contact']['LastName']		));
		$oblarrContact->Push (new dataString		('JobTitle',		$_POST ['Contact']['JobTitle']		));
		$oblarrContact->Push (new dataString		('Email',			$_POST ['Contact']['Email']		));
		$oblarrContact->Push (new dataInteger		('DOB-year',		$_POST ['Contact']['DOB']['year']	));
		$oblarrContact->Push (new dataInteger		('DOB-month',		$_POST ['Contact']['DOB']['month']	));
		$oblarrContact->Push (new dataInteger		('DOB-day',			$_POST ['Contact']['DOB']['day']	));
		$oblarrContact->Push (new dataString		('Phone',			$_POST ['Contact']['Phone']			));
		$oblarrContact->Push (new dataString		('Mobile',			$_POST ['Contact']['Mobile']		));
		$oblarrContact->Push (new dataString		('Fax',				$_POST ['Contact']['Fax']			));
		$oblarrContact->Push (new dataString		('UserName',		$_POST ['Contact']['UserName']		));
		$oblarrContact->Push (new dataString		('PassWord',		$_POST ['Contact']['PassWord']		));
		
		$selUserName = new StatementSelect ('Contact', 'Id', 'UserName = <UserName> AND Archived = 0');
		$selUserName->Execute (array ('UserName' => $_POST ['Contact']['UserName']));
		
		if ($selUserName->Count () <> 0)
		{
			$oblstrError->setValue ('UserName');
		}
		else if (!$cgsCustomerGroups->setValue ($_POST ['CustomerGroup']))
		{
			$oblstrError->setValue ('CustomerGroup');
		}
		else if (!$bmeBillingMethods->setValue ($_POST ['BillingMethod']))
		{
			$oblstrError->setValue ('BillingMethod');
		}
		else if (!$ccsCreditCardTypes->setValue ($_POST ['CC']['CardType']))
		{
			$oblstrError->setValue ('CardType');
		}
		else
		{
			// if we're up to here ... make the account
			
			if ($_POST ['Select_Contact']) {
				$cntContact = new Contact ($_POST ['Contact']['Id']);
			}
			
			$agsAccountGroups = new AccountGroups ();
			
			$intAccount = $agsAccountGroups->Add (
				(($acgAccountGroup) ? $acgAccountGroup : null),
				(($cntContact) ? $cntContact : null),
				Array (
					"Account"		=> Array (
						"BusinessName"		=> $_POST ['BusinessName'],
						"TradingName"		=> $_POST ['TradingName'],
						"ABN"				=> $_POST ['ABN'],
						"ACN"				=> $_POST ['ACN'],
						"Address1"			=> $_POST ['Address1'],
						"Address2"			=> $_POST ['Address2'],
						"Suburb"			=> $_POST ['Suburb'],
						"Postcode"			=> $_POST ['Postcode'],
						"State"				=> $_POST ['State'],
						"CustomerGroup"	=> $_POST ['CustomerGroup'],
						"BillingType"		=> $_POST ['BillingType'],
						"BillingMethod"		=> $_POST ['BillingMethod']
					),
					
					"CreditCard"	=> Array (
						"CardType"			=> $_POST ['CC']['CardType'],
						"Name"				=> $_POST ['CC']['Name'],
						"CardNumber"		=> $_POST ['CC']['CardNumber'],
						"ExpMonth"			=> $_POST ['CC']['ExpMonth'],
						"ExpYear"			=> $_POST ['CC']['ExpYear'],
					),
					
					"DirectDebit"	=> Array (
						"BankName"			=> $_POST ['DDR']['BankName'],
						"BSB"				=> $_POST ['DDR']['BSB'],
						"AccountNumber"	=> $_POST ['DDR']['AccountNumber'],
						"AccountName"		=> $_POST ['DDR']['AccountName'],
					),
					
					"Contact"		=> Array (
						"Title"				=> $_POST ['Contact']['Title'],
						"FirstName"		=> $_POST ['Contact']['FirstName'],
						"LastName"			=> $_POST ['Contact']['LastName'],
						"DOB:year"			=> $_POST ['Contact']['DOB']['year'],
						"DOB:month"		=> $_POST ['Contact']['DOB']['month'],
						"DOB:day"			=> $_POST ['Contact']['DOB']['day'],
						"JobTitle"			=> $_POST ['Contact']['JobTitle'],
						"Email"			=> $_POST ['Contact']['Email'],
						"Phone"				=> $_POST ['Contact']['Phone'],
						"Mobile"			=> $_POST ['Contact']['Mobile'],
						"Fax"				=> $_POST ['Contact']['Fax'],
						"UserName"			=> $_POST ['Contact']['UserName'],
						"PassWord"			=> $_POST ['Contact']['PassWord']
					)
				)
			);
			
			header ("Location: account_view.php?Id=" . $intAccount);
			exit;
		}
	}
	
	$Style->Output ('xsl/content/account/add.xsl');
	
?>
