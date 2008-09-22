<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// SYNOPSIS:
	// As you can imagine, this page allows you to create a new account. When entering
	// this page for the first time, you may or may not have the following variable set:
	// 	
	//		$_GET ['Associated']	<or>	$_POST ['Associated']
	//		(represented in documentation as $_~ ['Associated'])
	//
	// If the $_~ ['Associated'] variable is set, then the Account that is being added
	// will be a Child of the Account Group which is identified by:
	//
	//		$_~ ['Associated']		<as>	Database.Account.AccountGroup
	//
	//
	//	This page will ask for basic details which will poluate the follow tables:
	//		
	//		*	Account Group
	//		*	Account
	//		*	DirectDebit
	//		*	CreditCard
	//		*	Contact
	
	
// The following block is the basic part of headers in the System.
	
	// call application loader
	
	// This calls the most fundamental requirements for a page to exist
	// within the viXen system. It loads up the objects in the Framework
	// Directory and creates a new instance of the Framework object
	require ('config/application_loader.php');
	
	// set page details
	
	// Whether or not this page is a Popup. If it is, different error responses will be used when
	// authentication mismatches or when other errors occur
	$arrPage['PopUp']		= FALSE;
	
	// The Permission (constant value) that is required in order to view this page. Permissions are 
	// defined in the config/config.php and config/definitions.php directory. Examples include:
	// 
	//		PERMISSION_PUBLIC
	//		PERMISSION_ADMIN
	//		PERMISSION_OPERATOR
	//		PERMISSION_SALES
	//		PERMISSION_ACCOUNTS
	// 
	// The value that is used is BITWISE.
	//
	// If you want access to be only for people who are at least an Operator
	//		PERMISSION_OPERATOR
	//
	// If you want access to be only for people who are at least an Administrator
	//		PERMISSION_ADMIN
	//
	// If you want access to be only for people who are at least an Operator AND be an Administrative user
	//		PERMISSION_OPERATOR | PERMISSION_ADMIN
	// 
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	
	// Modules (also a Constant Value) define which modules (or classes) are to be loaded into the system
	// 
	// MODULE_BASE				Means that the minimum requirements will be loaded to use this system. This
	//							includes (at time of publication):
	//							MODULE_SEARCH | MODULE_DOCUMENTATION | MODULE_ACCOUNT | MODULE_CONTACT
	//
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT_GROUP | MODULE_ACCOUNT | MODULE_CUSTOMER_GROUP | MODULE_DIRECT_DEBIT | MODULE_CREDIT_CARD | MODULE_BILLING | MODULE_STATE | MODULE_TITLE;
	
	// call application
	// This loads up the Permissioning System and other Required Modules (as defined in $arrPage above)
	// If a person does not have permission to access this particular page, then the config/application.php
	// script will deal with their rejection
	require ('config/application.php');
	
// Main Body:
// This is the Main area of this Particular Script
// To start off - we will be implementing the fundamentals of generic pages
	
	// The first fundamental of a page is its Error Container
	// The error container basically holds a String which can be used
	// in XSLT to identify what error occurred during the last postback.
	// If this value is empty, then there is no abstention recorded
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	
	// We need to check whether or not we're adding a new Account
	// and a new Account Group or whether we're using an Existing 
	// Account Group. In order for us to have the ability to return
	// to the previous page that we were at, the Associated 
	// Account is passed (through $_~ ['Associated']), which use to
	// evaluate which AccountGroup we are dealing with.
	
	if ($_GET ['Associated'] || $_POST ['Associated'])
	{
		// This assumes that we have an Associated Account we are attempting to view
		
		try
		{
			// Attempt to get the Associated Account
			$actAssociated		= new Account (($_GET ['Associated']) ? $_GET ['Associated'] : $_POST ['Associated']);
			
			// Store the Associated Account Id in the XML [oblib]
			$intAssociated		= $Style->attachObject (new dataInteger ('Associated', $actAssociated->Pull ('Id')->getValue ()));
			
			// Get information about the Account Group this Account is associated with 
			// and the Contacts that are in this Account
			
			// AccountGroup can be reference in XPath using: /Response/AccountGroup
			$acgAccountGroup	= $Style->attachObject ($actAssociated->AccountGroup ());
			
			// AccountGroup can be reference in XPath using: /Response/Contacts
			$ctsContacts		= $Style->attachObject ($acgAccountGroup->getContacts(TRUE));
		}
		catch (Exception $e)
		{
			// If any errors occur, we can blame the user by saying that 
			// the Account they entered is not correct. Usually this catch will
			// only be executed when the account is not found, so you don't have
			// to feel too guilty
			$Style->Output ('xsl/content/account/notfound.xsl');
			exit;
		}
	}
	
	// Attach "Add Account" Essentials (BillingMethod, CustomerGroup and CreditCardTypes)
	// The following items (bmeBillingMethods - ttyTitleTypes) are Enumerations
	// The code below loads up the enumeration (with a default value selected, as identifed by Parameter 1)
	$bmeBillingMethods		= $Style->attachObject (new BillingMethods		($_POST ['Account']['BillingMethod']));
	$btyBillingTypes		= $Style->attachObject (new BillingTypes		($_POST ['Account']['BillingType']));
	$cgsCustomerGroups		= $Style->attachObject (new CustomerGroups		($_POST ['Account']['CustomerGroup']));
	$ccsCreditCardTypes		= $Style->attachObject (new CreditCardTypes		($_POST ['CC']['CardType']));
	$sstStates				= $Style->attachObject (new ServiceStateTypes	($_POST ['Account']['State']));
	$ttyTitleTypes			= $Style->attachObject (new TitleTypes			($_POST ['Contact']['Title']));
	
	// Set up the basis of information within the system
	// This will basically remember any values that you submit in case there is an error
	// It can be reference in XPath using: /Response/ui-values
	$oblarrUIValues			= $Style->attachObject (new dataArray ('ui-values'));
	
	
	
	
	
	// I'm not going to explain each and every one of the following variables in detail, but i
	// am going to display the XPath for reaching the variable above the line:
	
	// Basis Values
	
	// XPath:	/Response/ui-values/Account
	$oblarrAccount = $oblarrUIValues->Push (new dataArray ('Account'));
	
	// XPath:	/Response/ui-values/DirectDebit
	$oblarrDirectDR = $oblarrUIValues->Push (new dataArray ('DirectDebit'));
	
	// XPath:	/Response/ui-values/CreditCard
	$oblarrCRCard = $oblarrUIValues->Push (new dataArray ('CreditCard'));
	
	// XPath:	/Response/ui-values/Contact
	$oblarrContact = $oblarrUIValues->Push (new dataArray ('Contact'));
	
	
	
	// Start with the Account Entity
	
	// XPath:	/Response/ui-values/Account/BusinessName
	$oblarrAccount->Push	(new dataString	('BusinessName',		$_POST ['Account']['BusinessName']));
	
	// XPath:	/Response/ui-values/Account/TradingName
	$oblarrAccount->Push	(new dataString	('TradingName',			$_POST ['Account']['TradingName']));
	
	// XPath:	/Response/ui-values/Account/ABN
	$oblarrAccount->Push	(new dataString	('ABN',					$_POST ['Account']['ABN']));
	
	// XPath:	/Response/ui-values/Account/ACB
	$oblarrAccount->Push	(new dataString	('ACN',					$_POST ['Account']['ACN']));
	
	// XPath:	/Response/ui-values/Account/Address1
	$oblarrAccount->Push	(new dataString	('Address1',			$_POST ['Account']['Address1']));
	
	// XPath:	/Response/ui-values/Account/Address2
	$oblarrAccount->Push	(new dataString	('Address2',			$_POST ['Account']['Address2']));
	
	// XPath:	/Response/ui-values/Account/Suburb
	$oblarrAccount->Push	(new dataString	('Suburb',				$_POST ['Account']['Suburb']));
	
	// XPath:	/Response/ui-values/Account/Postcode
	$oblarrAccount->Push	(new dataString	('Postcode',			$_POST ['Account']['Postcode']));
	
	// XPath:	/Response/ui-values/Account/State
	$oblarrAccount->Push	(new dataString	('State',				$_POST ['Account']['State']));
	
	// XPath:	/Response/ui-values/Account/DisableDDR
	$oblarrAccount->Push	(new dataBoolean('DisableDDR',			$_POST ['Account']['DisableDDR']));
	
	// XPath:	/Response/ui-values/Account/DisableLatePayment
	$oblarrAccount->Push	(new dataInteger('DisableLatePayment',	(isset ($_POST ['Account']['DisableLatePayment']) ? 
																		$_POST ['Account']['DisableLatePayment'] : 0)));
	
	
	// Direct Debit Information
	
	// XPath:	/Response/ui-values/DirectDebit/BankName
	$oblarrDirectDR->Push	(new dataString	('BankName',			$_POST ['DDR']['BankName']));
	
	// XPath:	/Response/ui-values/DirectDebit/BSB
	$oblarrDirectDR->Push	(new dataString	('BSB',					$_POST ['DDR']['BSB']));
	
	// XPath:	/Response/ui-values/DirectDebit/AccountNumber
	$oblarrDirectDR->Push	(new dataString	('AccountNumber',		$_POST ['DDR']['AccountNumber']));
	
	// XPath:	/Response/ui-values/DirectDebit/AccountName
	$oblarrDirectDR->Push	(new dataString	('AccountName',			$_POST ['DDR']['AccountName']));
	
	
	
	// Credit Card Information
	
	// XPath:	/Response/ui-values/CreditCard/Name
	$oblarrCRCard->Push		(new dataString	('Name',				$_POST ['CC']['Name']));
	
	// XPath:	/Response/ui-values/CreditCard/CardNumber
	$oblarrCRCard->Push		(new dataString	('CardNumber',			$_POST ['CC']['CardNumber']));
	
	// XPath:	/Response/ui-values/CreditCard/ExpMonth
	$oblarrCRCard->Push		(new dataInteger('ExpMonth',			$_POST ['CC']['ExpMonth']));
	
	// XPath:	/Response/ui-values/CreditCard/ExpYear
	$oblarrCRCard->Push		(new dataInteger('ExpYear',				$_POST ['CC']['ExpYear']));
	
	// XPath:	/Response/ui-values/CreditCard/CVV
	$oblarrCRCard->Push		(new dataInteger('CVV',					$_POST ['CC']['CVV']));
	
	
	
	// Contact Information (Existing Contact)
	
	// XPath:	/Response/ui-values/Contact/USE
	$oblarrContact->Push	(new dataBoolean('USE',					$_POST ['Contact']['USE']));
	
	// XPath:	/Response/ui-values/Contact/Id
	$oblarrContact->Push	(new dataInteger('Id',					$_POST ['Contact']['Id']));
	
	// Contact Information (New Contact)
	
	// XPath:	/Response/ui-values/Contact/Title
	$oblarrContact->Push	(new dataString	('Title',				$_POST ['Contact']['Title']));
	
	// XPath:	/Response/ui-values/Contact/FirstName
	$oblarrContact->Push	(new dataString	('FirstName',			$_POST ['Contact']['FirstName']));
	
	// XPath:	/Response/ui-values/Contact/LastName
	$oblarrContact->Push	(new dataString	('LastName',			$_POST ['Contact']['LastName']));
	
	// XPath:	/Response/ui-values/Contact/JobTitle
	$oblarrContact->Push	(new dataString	('JobTitle',			$_POST ['Contact']['JobTitle']));
	
	// XPath:	/Response/ui-values/Contact/Email
	$oblarrContact->Push	(new dataString	('Email',				$_POST ['Contact']['Email']));
	
	// XPath:	/Response/ui-values/Contact/DOB-year
	$oblarrContact->Push	(new dataInteger('DOB-year',			$_POST ['Contact']['DOB']['year']));
	
	// XPath:	/Response/ui-values/Contact/DOB-month
	$oblarrContact->Push	(new dataInteger('DOB-month',			$_POST ['Contact']['DOB']['month']));
	
	// XPath:	/Response/ui-values/Contact/DOB-day
	$oblarrContact->Push	(new dataInteger('DOB-day',				$_POST ['Contact']['DOB']['day']));
	
	// XPath:	/Response/ui-values/Contact/Phone
	$oblarrContact->Push	(new dataString	('Phone',				$_POST ['Contact']['Phone']));
	
	// XPath:	/Response/ui-values/Contact/Mobile
	$oblarrContact->Push	(new dataString	('Mobile',				$_POST ['Contact']['Mobile']));
	
	// XPath:	/Response/ui-values/Contact/Fax
	$oblarrContact->Push	(new dataString	('Fax',					$_POST ['Contact']['Fax']));
	
	// XPath:	/Response/ui-values/Contact/UserName
	//$oblarrContact->Push	(new dataString	('UserName',			$_POST ['Contact']['UserName']));
	
	// XPath:	/Response/ui-values/Contact/UserName
	$oblarrContact->Push	(new dataString	('PassWord',			$_POST ['Contact']['PassWord']));
	
	$selUserNames = new StatementSelect ('Contact', 'Id', 'Email = <UserName>', null, 1);
	$selUserNames->Execute (Array ('UserName' => $_POST['Contact']['Email']));
	$bolUserNameTaken = ($selUserNames->Count () == 1);


	// If we're wishing to save the details, we can identify this by
	// whether or not we have identified a Business Name
	if ($_POST ['Account'])
	{
		if (!$acgAccountGroup || !$_POST ['Contact']['USE'])
		{
			try
			{
				$cntUsername = Contacts::UnarchivedUsername ($_POST ['Contact']['Email']);
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
		/*else if (!$_POST ['Account']['ABN'] && !$_POST ['Account']['ACN'])
		{
			// This throws an error if the ABN and the ACN is Blank
			$oblstrError->setValue ('Account ABN-ACN');
		}*/
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
		else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT && !BSBValid ($_POST ['DDR']['BSB']))
		{
			// This throws an error if the BSB number is invalid
			$oblstrError->setValue ('DirectDebit BSB Invalid');
		}
		else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT && !$_POST ['DDR']['AccountNumber'])
		{
			// This throws an error if there is no Account Number
			$oblstrError->setValue ('DirectDebit AccountNumber');
		}
		else if ($_POST ['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT && !BankAccountValid ($_POST ['DDR']['AccountNumber']))
		{
			// This throws an error if the Bank Account number is invalid
			$oblstrError->setValue ('DirectDebit AccountNumber Invalid');
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
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && ($_POST ['Contact']['Phone'] && !PhoneNumberValid ($_POST ['Contact']['Phone'])))
		{
			// This throws an error if a Contact's Phone Number is specified but is Invalid
			$oblstrError->setValue ('Contact Phone Invalid');
		}
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && ($_POST ['Contact']['Mobile'] && !PhoneNumberValid ($_POST ['Contact']['Mobile'])))
		{
			// This throws an error if a Contact's Mobile Number is specified but is Invalid
			$oblstrError->setValue ('Contact Mobile Invalid');
		}
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && ($_POST ['Contact']['Fax'] && !PhoneNumberValid ($_POST ['Contact']['Fax'])))
		{
			// This throws an error if a Contact's Fax Number is specified but is Invalid
			$oblstrError->setValue ('Contact Fax Invalid');
		}
		//else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && !$_POST ['Contact']['UserName'])
		//{
			// This throws an error if the Contact's Username is Blank
			//$oblstrError->setValue ('Contact UserName Empty');
		//}
		else if ($bolUserNameTaken)
		{
			// This throws an error if the User Name exists
			$oblstrError->setValue ('Contact Email Exists');
		}
		else if ((!$acgAccountGroup || !$_POST ['Contact']['USE']) && !$_POST ['Contact']['PassWord'])
		{
			// This throws an error if the Contact's Password is Blank
			$oblstrError->setValue ('Contact PassWord');
		}
		
		else
		{
			// If we reach this point in the Script, then we're probably going to make the Account
			// All we have to do is make sure the Contact being requested to add exists in the Account
			// that is being requested - if an account is being made in an Account Group
			if ($_POST['Contact']['USE']) {
				$cntContact = new Contact ($_POST ['Contact']['Id']);
			}
			
			$actAccount = AccountGroups::Add (
				(($acgAccountGroup) ? $acgAccountGroup : null),
				(($cntContact) ? $cntContact : null),
				$athAuthentication->AuthenticatedEmployee (),
				Array (
					"Account"		=> Array (
						"BusinessName"			=> $_POST ['Account']['BusinessName'],
						"TradingName"			=> $_POST ['Account']['TradingName'],
						"ABN"					=> $_POST ['Account']['ABN'],
						"ACN"					=> $_POST ['Account']['ACN'],
						"Address1"				=> $_POST ['Account']['Address1'],
						"Address2"				=> $_POST ['Account']['Address2'],
						"Suburb"				=> $_POST ['Account']['Suburb'],
						"Postcode"				=> $_POST ['Account']['Postcode'],
						"State"					=> $_POST ['Account']['State'],
						"DisableDDR"			=> $_POST ['Account']['DisableDDR'],
						"DisableLatePayment"	=> $_POST ['Account']['DisableLatePayment'],
						"CustomerGroup"			=> $_POST ['Account']['CustomerGroup'],
						"BillingType"			=> $_POST ['Account']['BillingType'],
						"BillingMethod"			=> $_POST ['Account']['BillingMethod']
					),
					
					"DirectDebit"	=> Array (
						"BankName"				=> $_POST ['DDR']['BankName'],
						"BSB"					=> $_POST ['DDR']['BSB'],
						"AccountNumber"			=> $_POST ['DDR']['AccountNumber'],
						"AccountName"			=> $_POST ['DDR']['AccountName']
					),
					
					"CreditCard"	=> Array (
						"CardType"				=> $_POST ['CC']['CardType'],
						"Name"					=> $_POST ['CC']['Name'],
						"CardNumber"			=> $_POST ['CC']['CardNumber'],
						"ExpMonth"				=> $_POST ['CC']['ExpMonth'],
						"ExpYear"				=> $_POST ['CC']['ExpYear'],
						"CVV"					=> $_POST ['CC']['CVV']
					),
					
					"Contact"		=> Array (
						"Title"					=> $_POST ['Contact']['Title'],
						"FirstName"				=> $_POST ['Contact']['FirstName'],
						"LastName"				=> $_POST ['Contact']['LastName'],
						"DOB:year"				=> $_POST ['Contact']['DOB']['year'],
						"DOB:month"				=> $_POST ['Contact']['DOB']['month'],
						"DOB:day"				=> $_POST ['Contact']['DOB']['day'],
						"JobTitle"				=> $_POST ['Contact']['JobTitle'],
						"Email"					=> $_POST ['Contact']['Email'],
						"Phone"					=> $_POST ['Contact']['Phone'],
						"Mobile"				=> $_POST ['Contact']['Mobile'],
						"Fax"					=> $_POST ['Contact']['Fax'],
						//"UserName"				=> $_POST ['Contact']['UserName'],
						"PassWord"				=> $_POST ['Contact']['PassWord']
					)
				)
			);
			
			$athAuthentication->AuthenticatedEmployee ()->Audit ()->RecordAccount ($actAccount);
			
			header ('Location: ../admin/flex.php/Account/Overview/?Account.Id=' . $actAccount->Pull ('Id')->getValue ());
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
	
	$Style->Output (
		'xsl/content/account/add.xsl',
		Array (
			'Account'	=>	(isset ($actAssociated) ? $actAssociated->Pull ('Id')->getValue () : null)
		)
	);
	
?>
