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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT_GROUP | MODULE_ACCOUNT | MODULE_CUSTOMER_GROUP | MODULE_DIRECT_DEBIT | MODULE_CREDIT_CARD | MODULE_BILLING | MODULE_STATE;
	
	// call application
	require ('config/application.php');
	
	
	// First of all, set an Error Container
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	
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
			
			// We assume that at this point, there is an Account Group specified
			$ctsContacts = $Style->attachObject ($acgAccountGroup->getContacts ());
		}
		catch (Exception $e)
		{
			$Style->Output ('xsl/content/accountgroup/notfound.xsl');
			exit;
		}
	}
	
	// Attach essentials (BillingMethod, CustomerGroup and CreditCardTypes)
	$bmeBillingMethods		= $Style->attachObject (new BillingMethods		($_POST ['Account']['BillingMethod']));
	$btyBillingTypes		= $Style->attachObject (new BillingTypes		($_POST ['Account']['BillingType']));
	$cgsCustomerGroups		= $Style->attachObject (new CustomerGroups		($_POST ['Account']['CustomerGroup']));
	$ccsCreditCardTypes		= $Style->attachObject (new CreditCardTypes		($_POST ['CC']['CardType']));
	$sstStates				= $Style->attachObject (new ServiceStateTypes	($_POST ['Account']['State']));
	
	// Set up the basis of information within the system
	$oblarrUIValues			= $Style->attachObject (new dataArray ('ui-values'));
	
	// Basis Values
	$oblarrAccount = $oblarrUIValues->Push (new dataArray ('Account'));
	$oblarrDirectDR = $oblarrUIValues->Push (new dataArray ('DirectDebit'));
	$oblarrCRCard = $oblarrUIValues->Push (new dataArray ('CreditCard'));
	$oblarrContact = $oblarrUIValues->Push (new dataArray ('Contact'));
	
	// Start with the Account Entity
	$oblarrAccount->Push	(new dataString	('BusinessName',	$_POST ['Account']['BusinessName']));
	$oblarrAccount->Push	(new dataString	('TradingName',		$_POST ['Account']['TradingName']));
	$oblarrAccount->Push	(new dataString	('ABN',				$_POST ['Account']['ABN']));
	$oblarrAccount->Push	(new dataString	('ACN',				$_POST ['Account']['ACN']));
	$oblarrAccount->Push	(new dataString	('Address1',		$_POST ['Account']['Address1']));
	$oblarrAccount->Push	(new dataString	('Address2',		$_POST ['Account']['Address2']));
	$oblarrAccount->Push	(new dataString	('Suburb',			$_POST ['Account']['Suburb']));
	$oblarrAccount->Push	(new dataString	('Postcode',		$_POST ['Account']['Postcode']));
	$oblarrAccount->Push	(new dataString	('State',			$_POST ['Account']['State']));
	
	// Direct Debit Information
	$oblarrDirectDR->Push	(new dataString	('BankName',		$_POST ['DDR']['BankName']));
	$oblarrDirectDR->Push	(new dataString	('BSB',				$_POST ['DDR']['BSB']));
	$oblarrDirectDR->Push	(new dataString	('AccountNumber',	$_POST ['DDR']['AccountNumber']));
	$oblarrDirectDR->Push	(new dataString	('AccountName',		$_POST ['DDR']['AccountName']));
	
	// Credit Card Information
	$oblarrCRCard->Push		(new dataString	('Name',			$_POST ['CC']['Name']));
	$oblarrCRCard->Push		(new dataString	('CardNumber',		$_POST ['CC']['CardNumber']));
	$oblarrCRCard->Push		(new dataInteger('ExpMonth',		$_POST ['CC']['ExpMonth']));
	$oblarrCRCard->Push		(new dataInteger('ExpYear',			$_POST ['CC']['ExpYear']));
	$oblarrCRCard->Push		(new dataInteger('CVV',				$_POST ['CC']['CVV']));
	
	// Contact Information (Existing Contact)
	$oblarrContact->Push	(new dataBoolean('USE',				$_POST ['Contact']['USE']));
	$oblarrContact->Push	(new dataInteger('Id',				$_POST ['Contact']['Id']));
	
	// Contact Information (New Contact)
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
	if ($_POST ['Account'])
	{
		if (!$acgAccountGroup || !$_POST ['Contact']['USE'])
		{
			try
			{
				$cntUsername = Contacts::UnarchivedUsername ($_POST ['Contact']['UserName']);
			}
			catch (Exception $e)
			{
			}
		}
		
		$abnABN = new ABN ('ABN', '');
		$acnACN = new ACN ('ACN', '');
		
		if (!$_POST ['Account']['BusinessName'])
		{
			// This throws an error if the Business Name is Blank
			$oblstrError->setValue ('Account BusinessName');
		}
		else if (!$_POST ['Account']['ABN'] && !$_POST ['Account']['ACN'])
		{
			// This throws an error if the ABN and the ACN is Blank
			$oblstrError->setValue ('Account ABN-ACN');
		}
		else if (!$abnABN->setValue ($_POST ['Account']['ABN']))
		{
			// This throws an error if the ABN is Invalid
			$oblstrError->setValue ('Account ABN Invalid');
		}
		else if (!$acnACN->setValue ($_POST ['Account']['ACN']))
		{
			// This throws an error if the ACN is Invalid
			$oblstrError->setValue ('Account ACN Invalid');
		}
		else if (!$_POST ['Account']['Address1'])
		{
			// This throws an error if the Address (Line 1) is Blank
			$oblstrError->setValue ('Account Address');
		}
		else if (!$_POST ['Account']['Suburb'])
		{
			// This throws an error if the Suburb is Blank
			$oblstrError->setValue ('Account Suburb');
		}
		else if (!$_POST ['Account']['Postcode'])
		{
			// This throws an error if the Postcode is Blank
			$oblstrError->setValue ('Account Postcode');
		}
		else if (!PostcodeValid ($_POST ['Account']['Postcode']))
		{
			// This throws an error if the Postcode is not XXXX digits
			$oblstrError->setValue ('Account Postcode');
		}
		else if (!$sstStates->setValue ($_POST ['Account']['State']))
		{
			// This throws an error if the State is Blank
			$oblstrError->setValue ('Account State');
		}
		else if (!$cgsCustomerGroups->setValue ($_POST ['Account']['CustomerGroup']))
		{
			// This throws an error if the Customer Group is Invalid
			$oblstrError->setValue ('Account CustomerGroup');
		}
		else if (!$bmeBillingMethods->setValue ($_POST ['Account']['BillingMethod']))
		{
			// This throws an error if the Billing Method is Invalid
			$oblstrError->setValue ('Billing Method');
		}
		
		// This section deals with Direct Debits
		else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT && !$_POST ['DDR']['BankName'])
		{
			// This throws an error if there is no Bank Name
			$oblstrError->setValue ('DirectDebit BankName');
		}
		else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT && !$_POST ['DDR']['BSB'])
		{
			// This throws an error if there is no BSB information
			$oblstrError->setValue ('DirectDebit BSB');
		}
		else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT && !$_POST ['DDR']['AccountNumber'])
		{
			// This throws an error if there is no Account Number
			$oblstrError->setValue ('DirectDebit AccountNumber');
		}
		else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT && !$_POST ['DDR']['AccountName'])
		{
			// This throws an error if there is no Account Name
			$oblstrError->setValue ('DirectDebit AccountName');
		}
		
		// This section deals with Credit Cards
		else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD && !$ccsCreditCardTypes->setValue ($_POST ['CC']['CardType']))
		{
			// This throws an error if the Credit Card Type is Invalid (not likely to be done often)
			$oblstrError->setValue ('CreditCard CardType');
		}
		else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD && !$_POST ['CC']['Name'])
		{
			// This throws an error if there is no Name for the Credit Card
			$oblstrError->setValue ('CreditCard Name');
		}
		else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD && !$_POST ['CC']['CardNumber'])
		{
			// This throws an error if there is no Credit Card Number
			$oblstrError->setValue ('CreditCard CardNumber');
		}
		else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD && !CheckCC ($_POST ['CC']['CardNumber'], $_POST ['CC']['CardType']))
        {
			// This throws an error if the Credit Card is Invalid
			$oblstrError->setValue ('CreditCard Invalid');
        }
		else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD && !$_POST ['CC']['ExpMonth'])
		{
			// This throws an error if there is no selected Expiration Month
			$oblstrError->setValue ('CreditCard ExpMonth');
		}
		else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD && !$_POST ['CC']['ExpYear'])
		{
			// This throws an error if there is no selected Expiration Year
			$oblstrError->setValue ('CreditCard ExpYear');
		}
        else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD && !expdate ($_POST ['CC']['ExpMonth'], $_POST ['CC']['ExpYear']))
        {
			// This throws an error if the Expiration Date is Invalid
            $oblstrError->setValue ('CreditCard Expired');
        }
        else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD && !preg_match ("/^(\d{3,4})$/", $_POST ['CC']['CVV']))
        {
			// This throws an error if there is no CVV
            $oblstrError->setValue ('CreditCard CVV');
        }
        

        
		
		// The following errors are related to New Contact Creation. These
		// errors will only be run when a New Contact has been requested (or forced)
		
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && !$_POST ['Contact']['Title'])
		{
			// This throws an error if the Contact's Title is Blank
			$oblstrError->setValue ('Contact Title');
		}
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && !$_POST ['Contact']['FirstName'])
		{
			// This throws an error if the Contact's First Name is Blank
			$oblstrError->setValue ('Contact FirstName');
		}
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && !$_POST ['Contact']['LastName'])
		{
			// This throws an error if the Contact's Last Name is Blank
			$oblstrError->setValue ('Contact LastName');
		}
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && !@checkdate ((int) $_POST ['Contact']['DOB']['month'], (int) $_POST ['Contact']['DOB']['day'], (int) $_POST ['Contact']['DOB']['year']))
		{
			// This throws an error if the Contact's DOB is Invalid
			$oblstrError->setValue ('Contact DOB');
		}
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && !$_POST ['Contact']['Email'])
		{
			// This throws an error if the Contact's Email is Blank
			$oblstrError->setValue ('Contact Email');
		}
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && !EmailAddressValid ($_POST ['Contact']['Email']))
		{
			// This throws an error if the Contact's Email is Blank
			$oblstrError->setValue ('Contact Email');
		}
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && !$_POST ['Contact']['Phone'] && !$_POST ['Contact']['Mobile'])
		{
			// This throws an error if the Contact's Phone and Mobile are Blank
			$oblstrError->setValue ('Contact Phones Empty');
		}
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && !$_POST ['Contact']['UserName'])
		{
			// This throws an error if the Contact's Username is Blank
			$oblstrError->setValue ('Contact UserName Empty');
		}
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && $cntUsername)
		{
			// This throws an error if the User Name exists
			$oblstrError->setValue ('Contact UserName Exists');
		}
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && !$_POST ['Contact']['PassWord'])
		{
			// This throws an error if the Contact's Password is Blank
			$oblstrError->setValue ('Contact PassWord');
		}
		else if (!$ccsCreditCardTypes->setValue ($_POST ['CC']['CardType']))
		{
			// This throws an error if the Credit Card Type is Invalid
			$oblstrError->setValue ('CreditCard CardType');
		}
		else
		{
			// If we reach this point in the Script, then we're probably going to making the Account
			// All we have to do is make sure the Contact being requested to add exists in the Account
			// that is being requested - if an account is being made in an Account Group
			
			if ($_POST ['Contact']['USE']) {
				$cntContact = new Contact ($_POST ['Contact']['Id']);
			}
			
			$actAccount = AccountGroups::Add (
				(($acgAccountGroup) ? $acgAccountGroup : null),
				(($cntContact) ? $cntContact : null),
				$athAuthentication->AuthenticatedEmployee (),
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
					
					"DirectDebit"	=> Array (
						"BankName"			=> $_POST ['DDR']['BankName'],
						"BSB"				=> $_POST ['DDR']['BSB'],
						"AccountNumber"		=> $_POST ['DDR']['AccountNumber'],
						"AccountName"		=> $_POST ['DDR']['AccountName']
					),
					
					"CreditCard"	=> Array (
						"CardType"			=> $_POST ['CC']['CardType'],
						"Name"				=> $_POST ['CC']['Name'],
						"CardNumber"		=> $_POST ['CC']['CardNumber'],
						"ExpMonth"			=> $_POST ['CC']['ExpMonth'],
						"ExpYear"			=> $_POST ['CC']['ExpYear'],
						"CVV"				=> $_POST ['CC']['CVV']
					),
					
					"Contact"		=> Array (
						"Title"				=> $_POST ['Contact']['Title'],
						"FirstName"			=> $_POST ['Contact']['FirstName'],
						"LastName"			=> $_POST ['Contact']['LastName'],
						"DOB:year"			=> $_POST ['Contact']['DOB']['year'],
						"DOB:month"			=> $_POST ['Contact']['DOB']['month'],
						"DOB:day"			=> $_POST ['Contact']['DOB']['day'],
						"JobTitle"			=> $_POST ['Contact']['JobTitle'],
						"Email"				=> $_POST ['Contact']['Email'],
						"Phone"				=> $_POST ['Contact']['Phone'],
						"Mobile"			=> $_POST ['Contact']['Mobile'],
						"Fax"				=> $_POST ['Contact']['Fax'],
						"UserName"			=> $_POST ['Contact']['UserName'],
						"PassWord"			=> $_POST ['Contact']['PassWord']
					)
				)
			);
			
			$athAuthentication->AuthenticatedEmployee ()->Audit ()->RecordAccount ($actAccount);
			
			header ("Location: account_view.php?Id=" . $actAccount->Pull ('Id')->getValue ());
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
