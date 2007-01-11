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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT_GROUP | MODULE_ACCOUNT | MODULE_CUSTOMER_GROUP | MODULE_CREDIT_CARD | MODULE_BILLING;
	
	// call application
	require ('config/application.php');
	
	
	// First of all, set an Error Container
	$oblstrError = $Style->attachObject (new dataString ('Error', ''));
	
	
	// If there is an Account Group Specified, then we desire to create
	// An account that has some predefined information contained in it.
	// Pull the information from the Database and store it in an object.
	
	if ($_GET ['AccountGroup'] || $_POST ['AccountGroup'])
	{
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
			
			if ($acgAccountGroup)
			{
				$ctsContacts = $Style->attachObject ($acgAccountGroup->getContacts ());
			}
		}
		catch (Exception $e)
		{
			$Style->Output ('xsl/content/accountgroup/notfound.xsl');
			exit;
		}
	}
	
	// Attach essentials (BillingMethod, CustomerGroup and CreditCardTypes)
	$bmeBillingMethods		= $Style->attachObject (new BillingMethods);
	$cgsCustomerGroups		= $Style->attachObject (new CustomerGroups);
	$ccsCreditCardTypes		= $Style->attachObject (new CreditCardTypes);
	
	
	// Set up the basis of information within the system
	
	// Start with the Account Entity
	$oblarrAccount = $Style->attachObject (new dataArray ('Account'));
	$oblarrAccount->Push	(new dataString	('BusinessName',	$_POST ['Account']['BusinessName']));
	$oblarrAccount->Push	(new dataString	('TradingName',		$_POST ['Account']['TradingName']));
	$oblarrAccount->Push	(new ABN		('ABN',				$_POST ['Account']['ABN']));
	$oblarrAccount->Push	(new ACN		('ACN',				$_POST ['Account']['ACN']));
	$oblarrAccount->Push	(new dataString	('Address1',		$_POST ['Account']['Address1']));
	$oblarrAccount->Push	(new dataString	('Address2',		$_POST ['Account']['Address2']));
	$oblarrAccount->Push	(new dataString	('Suburb',			$_POST ['Account']['Suburb']));
	$oblarrAccount->Push	(new dataString	('Postcode',		$_POST ['Account']['Postcode']));
	$oblarrAccount->Push	(new dataString	('State',			$_POST ['Account']['State']));
	
	$oblarrDirectDR = $Style->attachObject (new dataArray ('DirectDebit'));
	$oblarrDirectDR->Push	(new dataString	('BankName',		$_POST ['DDR']['BankName']));
	$oblarrDirectDR->Push	(new dataString	('BSB',				$_POST ['DDR']['BSB']));
	$oblarrDirectDR->Push	(new dataString	('AccountNumber',	$_POST ['DDR']['AccountNumber']));
	$oblarrDirectDR->Push	(new dataString	('AccountName',		$_POST ['DDR']['AccountName']));
	
	$oblarrCRCard = $Style->attachObject (new dataArray ('CreditCard'));
	$oblarrCRCard->Push		(new dataString	('Name',			$_POST ['CC']['Name']));
	$oblarrCRCard->Push		(new dataString	('CardNumber',		$_POST ['CC']['CardNumber']));
	$oblarrCRCard->Push		(new dataInteger('ExpMonth',		$_POST ['CC']['ExpMonth']));
	$oblarrCRCard->Push		(new dataInteger('ExpYear',			$_POST ['CC']['ExpYear']));
	
	$oblarrContact = $Style->attachObject (new dataArray ('Contact'));
	
	$oblarrContact->Push	(new dataString	('Title',			$_POST ['Contact']['Title']));
	$oblarrContact->Push	(new dataString	('FirstName',		$_POST ['Contact']['FirstName']));
	$oblarrContact->Push	(new dataString	('LastName',		$_POST ['Contact']['LastName']));
	$oblarrContact->Push	(new dataString	('JobTitle',		$_POST ['Contact']['JobTitle']));
	$oblarrContact->Push	(new dataString	('Email',			$_POST ['Contact']['Email']));
	$oblarrContact->Push	(new dataInteger('DOB-year',		$_POST ['Contact']['DOB']['year']));
	$oblarrContact->Push	(new dataInteger('DOB-month',		$_POST ['Contact']['DOB']['month']));
	$oblarrContact->Push	(new dataInteger('DOB-day',			$_POST ['Contact']['DOB']['day']));
	$oblarrContact->Push	(new dataString	('Phone',			$_POST ['Contact']['Phone']));
	$oblarrContact->Push	(new dataString	('Mobile',			$_POST ['Contact']['Mobile']));
	$oblarrContact->Push	(new dataString	('Fax',				$_POST ['Contact']['Fax']));
	$oblarrContact->Push	(new dataString	('UserName',		$_POST ['Contact']['UserName']));
	$oblarrContact->Push	(new dataString	('PassWord',		$_POST ['Contact']['PassWord']));
	
	// If we're wishing to save the details, we can identify this by
	// whether or not we have identified a Business Name
	if ($_POST ['Account']['BusinessName'])
	{
		$selUserName = new StatementSelect ('Contact', 'Id', 'UserName = <UserName> AND Archived = 0');
		$selUserName->Execute (array ('UserName' => $_POST ['Contact']['UserName']));
		
		if (!$_POST ['Account']['TradingName'])
		{
			$oblstrError->setValue ('TradingName');
		}
		else if ($selUserName->Count () <> 0)
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
			
			$agsAccountGroups = new AccountGroups;
			
			$intAccount = $agsAccountGroups->Add (
				(($acgAccountGroup) ? $acgAccountGroup : null),
				(($cntContact) ? $cntContact : null),
				Array (
					"Account"		=> Array (
						"BusinessName"		=> $_POST ['Account']['BusinessName'],
						"TradingName"		=> $_POST ['Account']['TradingName'],
						"ABN"				=> $_POST ['Account']['ABN'],
						"ACN"				=> $_POST ['Account']['ACN'],
						"Address1"			=> $_POST ['Account']['Address1'],
						"Address2"			=> $_POST ['Account']['Address2'],
						"Suburb"			=> $_POST ['Account']['Suburb'],
						"Postcode"			=> $_POST ['Account']['Postcode'],
						"State"				=> $_POST ['Account']['State'],
						"CustomerGroup"		=> $_POST ['Account']['CustomerGroup'],
						"BillingType"		=> $_POST ['Account']['BillingType'],
						"BillingMethod"		=> $_POST ['Account']['BillingMethod']
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
	
	// Pull the required documentation information
	$docDocumentation->Explain ('AccountGroup');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	$docDocumentation->Explain ('Contact');
	$docDocumentation->Explain ('CustomerGroup');
	$docDocumentation->Explain ('Billing');
	$docDocumentation->Explain ('Payment');
	$docDocumentation->Explain ('Direct Debit');
	$docDocumentation->Explain ('Credit Card');
	
	$Style->Output ('xsl/content/account/add.xsl');
	
?>
