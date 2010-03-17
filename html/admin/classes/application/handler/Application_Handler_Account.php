<?php

class Application_Handler_Account extends Application_Handler
{
	
	const DEFAULT_DISABLE_LATE_PAYMENT_NOTICES	= 0;
	const DEFAULT_SAMPLE						= 0;
	const DEFAULT_CUSTOMER_CONTACT				= 0;
	const DEFAULT_SESSION_EXPIRE				= '1970-01-01 00:00:00';
	const DEFAULT_CONTACT_ARCHIVED				= 0;
	const DEFAULT_VIP_STATUS					= 0;
	const DEFAULT_PASSWORD_LENGTH_REQUIREMENT	= 6;
	

	// Renders the page for Account Creation
	public function Create($subPath)
	{
			
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		
		try
		{

			//----------------------------------------------------------------//
			// Retrieve Data required to build the page
			//----------------------------------------------------------------//

			$arrDetailsToRender								= array();
			$arrDetailsToRender['arrCustomerGroups']		= Customer_Group::getAll();
			$arrDetailsToRender['arrStates']				= State::getAll();
			$arrDetailsToRender['arrDeliveryMethods']		= Delivery_Method::getAccountSettingOptions();
			$arrDetailsToRender['arrCreditCardTypes']		= Credit_Card_Type::listAll();
			$arrDetailsToRender['arrContactTitles']			= Contact_Title::getAll();
			
			//----------------------------------------------------------------//
			// Retrieve Associated Accounts
			//----------------------------------------------------------------//
			
			if (array_key_exists("Associated", $_GET))
			{
				$iAssociated = (int)$_GET['Associated'];

				// Validates the entered account group is true.
				if (($oAccountGroup = Account_Group::getForAccountId($iAssociated)))
				{

					// Select contacts associated with the account group
					$qryQuery = new Query();
					$resAssociatedContacts = $qryQuery->Execute("
					SELECT DISTINCT c.*
					FROM AccountGroup ag
							JOIN Account a ON (a.AccountGroup = ag.Id AND a.Archived IN (" . ACCOUNT_STATUS_ACTIVE . ", " . ACCOUNT_STATUS_PENDING_ACTIVATION . "))
							JOIN Contact c ON (a.PrimaryContact = c.Id
												OR (ag.Id = c.AccountGroup
													AND c.CustomerContact = 1)
												) AND c.Archived = 0
					WHERE a.Id =$iAssociated;");

					if ($resAssociatedContacts)
					{
						// Populate contacts array for the html template.
						$arrDetailsToRender['arrAssociatedContacts']	= array();
						while ($arrContact = $resAssociatedContacts->fetch_assoc())
						{
							$arrDetailsToRender['arrAssociatedContacts'][$arrContact['Id']]	= $arrContact;
							
						}
					}
				
				}
				
			}
			
			BreadCrumb()->Employee_Console();
			if(array_key_exists("Associated", $_GET))
			{
				BreadCrumb()->AccountOverview((int)$_GET['Associated']);	
			}
			BreadCrumb()->SetCurrentPage("Add Customer");
			
			// Merge the PHP with the HTML template
			$this->LoadPage('account_create', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
			
		}
		catch (Exception $eException)
		{
			$arrDetailsToRender['Message']		= "An error occured";
			$arrDetailsToRender['ErrorMessage']	= $eException->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
	
	public function Save()
	{
	
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		
		try
		{
			
			//----------------------------------------------------------------//
			// Validate Proposed Account
			//----------------------------------------------------------------//
			
			if(!Validation::IsValidABN($_POST['Account']['ABN']) && !Validation::IsValidACN($_POST['Account']['ACN']))
			{
				throw new Exception('A valid ABN or ACN is required');
			}
			if(!Validation::IsValidPostcode($_POST['Account']['Postcode']))
			{
				throw new Exception('Invalid Post Code');
			}
			if(!Validation::IsNotEmptyString($_POST['Account']['BusinessName']))
			{
				throw new Exception('Invalid Business Name');
			}
			if(!Validation::IsNotEmptyString($_POST['Account']['Address1']))
			{
				throw new Exception('Invalid Address Line 1');
			}
			if(!Validation::IsNotEmptyString($_POST['Account']['Suburb']))
			{
				throw new Exception('Invalid Suburb');
			}
			if(!Validation::IsValidInteger($_POST['Account']['Postcode']))
			{
				throw new Exception('Invalid Postcode');
			}
			if(!Validation::IsNotEmptyString($_POST['Account']['State']))
			{
				throw new Exception('Invalid State');
			}
			if(!Validation::IsValidInteger($_POST['Account']['CustomerGroup']))
			{
				throw new Exception('Invalid Customer Group');
			}
			
			
			//----------------------------------------------------------------//
			// Validate Proposed Billing Details
			//----------------------------------------------------------------//
			
			if(!array_key_exists('DisableLatePayment', $_POST['Account']))
			{				
				throw new Exception('No Late Payment option has been selected');
			}
			if(!array_key_exists('DeliveryMethod', $_POST['Account']))
			{				
				throw new Exception('No Delivery Method option has been selected');
			}
			if(!array_key_exists('BillingType', $_POST['Account']))
			{				
				throw new Exception('No Payment Method option has been selected');
			}
			if($_POST['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT)
			{
				if(!Validation::IsNotEmptyString('BankName', $_POST['DDR'])){throw new Exception('Invalid Direct Debit Bank Name');}
				if(!Validation::IsNotEmptyString('BSB', $_POST['DDR'])){throw new Exception('Invalid Direct Debit BSB');}
				if(!Validation::IsNotEmptyString('AccountNumber', $_POST['DDR'])){throw new Exception('Invalid Direct Debit Account Number');}
				if(!Validation::IsNotEmptyString('AccountName', $_POST['DDR'])){throw new Exception('Invalid Direct Debit Account Name');}
			}
			if($_POST['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD)
			{
				if(!Validation::IsNotEmptyString('CardType', $_POST['CC'])){throw new Exception('Invalid Credit Card Type');}
				if(!Validation::IsNotEmptyString('Name', $_POST['CC'])){throw new Exception('Invalid Credit Card Name');}
				if(!Validation::IsNotEmptyString('ExpMonth', $_POST['CC'])){throw new Exception('Invalid Credit Card Exp Month');}
				if(!Validation::IsNotEmptyString('ExpYear', $_POST['CC'])){throw new Exception('Invalid Credit Card Exp Year');}
				if(!array_key_exists('CardNumber', $_POST['CC']) || !CheckCC($_POST['CC']['CardNumber'], $_POST['CC']['CardType']))
				{
					throw new Exception('Invalid Credit Card Number');
				}				
				if(!array_key_exists('CardNumber', $_POST['CC']) || !Validation::IsValidInteger($_POST['CC']['CVV']))
				{
					throw new Exception('Invalid Credit Card CVV');
				}
			}
			
			
			//----------------------------------------------------------------//
			// Validate Proposed Primary Contact
			//----------------------------------------------------------------//
			
			if(!array_key_exists('USE', $_POST['Contact']))
			{
				throw new Exception('Invalid Primary Contact Selected (Needs to be either Existing or New)');
			}
			if ($_POST['Contact']['USE'] == 0)
			{
				if(Contact::isEmailInUse($_POST['Contact']['Email']) || !Validation::IsValidEmail($_POST['Contact']['Email']))
				{
					throw new Exception('Invalid Email address or the Email address is already in use');
				}
				if(!checkdate((int)$_POST ['Contact']['DOB']['Month'], (int)$_POST ['Contact']['DOB']['Day'], (int)$_POST ['Contact']['DOB']['Year']))
				{
					throw new Exception('Invalid Date Of Birth');
				}
				if(!Validation::IsNotEmptyString($_POST['Contact']['Title']))
				{
					throw new Exception('Invalid Contact Title Selected');
				}
				if(!Validation::IsNotEmptyString($_POST['Contact']['FirstName']))
				{
					throw new Exception('Invalid Contact First Name Selected');
				}
				if(!Validation::IsNotEmptyString($_POST['Contact']['LastName']))
				{
					throw new Exception('Invalid Contact Last Name Selected');
				}
				if(!Validation::IsNotEmptyString($_POST['Contact']['Password']) && strlen($_POST['Contact']['Password'])<DEFAULT_PASSWORD_LENGTH_REQUIREMENT)
				{
					throw new Exception('Invalid Contact Password, must be at least ' . DEFAULT_PASSWORD_LENGTH_REQUIREMENT . ' characterrs');
				}
				if(!Validation::IsValidPhoneNumber($_POST['Contact']['Mobile']) && !Validation::IsValidPhoneNumber($_POST['Contact']['Phone']))
				{
					throw new Exception('A valid Mobile or Phone number is required.');
				}
			}
			if ($_POST['Contact']['USE'] == 1 && !Contact::getForId($_POST['Contact']['USE']))
			{
				throw new Exception('Invalid Primary Contact Selected');
			}


			//----------------------------------------------------------------//
			// Create Account BEGINS
			//----------------------------------------------------------------//
			// If we have made it this far, everything has been validated and we are ready to create a new account!


			//----------------------------------------------------------------//
			// 1. Create Account Group or Assign Existing
			//----------------------------------------------------------------//
			
			if(array_key_exists("Associated", $_POST))
			{
				$intAccountGroupId = Account_Group::getForAccountId($_POST['Associated'])->Id;
			}
			if(!array_key_exists("Associated", $_POST))
			{
				$oAccountGroup = new Account_Group();
				$oAccountGroup->CreatedBy							= AuthenticatedUser()->getUserId();
				$oAccountGroup->CreatedOn							= Data_Source_Time::currentTimeStamp();
				$oAccountGroup->ManagedBy							= null;
				$oAccountGroup->Archived							= 0;
				$oAccountGroup->save();
				$intAccountGroupId									= $oAccountGroup->Id;
			}


			//----------------------------------------------------------------//
			// 2. Add Payment Method
			//----------------------------------------------------------------//
			
			$intBillingType		= (array_key_exists((int)$_POST['Account']['BillingType'], $GLOBALS['*arrConstant']['BillingType'])) ? $_POST['Account']['BillingType'] : BILLING_TYPE_ACCOUNT;
			$intDirectDebitId	= null;
			$intCreditCardId	= null;
			
			if($_POST['Account']['BillingType'] == BILLING_TYPE_DIRECT_DEBIT)
			{
				$oDirectDebit = new DirectDebit();
				$oDirectDebit->AccountGroup							= $intAccountGroupId;
				$oDirectDebit->BankName								= $_POST['DDR']['BankName'];
				$oDirectDebit->BSB									= $_POST['DDR']['BSB'];
				$oDirectDebit->AccountNumber						= $_POST['DDR']['AccountNumber'];
				$oDirectDebit->AccountName							= $_POST['DDR']['AccountName'];
				$oDirectDebit->Archived								= 0;
				$oDirectDebit->created_on							= Data_Source_Time::currentTimeStamp();
				$oDirectDebit->employee_id							= AuthenticatedUser()->getUserId();
				$oDirectDebit->save();
				$intDirectDebitId									= $oDirectDebit->Id;
			}
			if($_POST['Account']['BillingType'] == BILLING_TYPE_CREDIT_CARD)
			{
				$oCreditCard = new Credit_Card();
				$oCreditCard->AccountGroup							= $intAccountGroupId;
				$oCreditCard->CardType								= $_POST['CC']['CardType'];
				$oCreditCard->Name									= $_POST['CC']['Name'];
				$oCreditCard->CardNumber							= Encrypt($_POST['CC']['CardNumber']);
				$oCreditCard->ExpMonth								= $_POST['CC']['ExpMonth'];
				$oCreditCard->ExpYear								= $_POST['CC']['ExpYear'];
				$oCreditCard->CVV									= Encrypt($_POST['CC']['CVV']);
				$oCreditCard->Archived								= 0;
				$oCreditCard->created_on							= Data_Source_Time::currentTimeStamp();
				$oCreditCard->employee_id							= AuthenticatedUser()->getUserId();
				$oCreditCard->save();
				$intCreditCardId									= $oCreditCard->Id;
			}
			
			
			//----------------------------------------------------------------//
			// 3. Assign properties of new Account
			//----------------------------------------------------------------//
			
			$oAccount = new Account();
			$oAccount->BusinessName								= $_POST['Account']['BusinessName'];
			$oAccount->TradingName								= $_POST['Account']['TradingName'];
			$oAccount->ABN										= $_POST['Account']['ABN'];
			$oAccount->ACN										= $_POST['Account']['ACN'];
			$oAccount->Address1									= $_POST['Account']['Address1'];
			$oAccount->Address2									= $_POST['Account']['Address2'];
			$oAccount->Suburb									= $_POST['Account']['Suburb'];
			$oAccount->Postcode									= (int)$_POST['Account']['Postcode'];
			$oAccount->State									= $_POST['Account']['State'];
			$oAccount->Country									= 'AU';
			$oAccount->BillingType								= $intBillingType;
			$oAccount->PrimaryContact							= null;
			$oAccount->CustomerGroup							= (int)$_POST['Account']['CustomerGroup'];
			$oAccount->CreditCard								= $intCreditCardId;
			$oAccount->DirectDebit								= $intDirectDebitId;
			$oAccount->AccountGroup								= $intAccountGroupId;
			$oAccount->LastBilled								= null;
			$oAccount->BillingDate								= Payment_Terms::getCurrentForCustomerGroup((int)$_POST['Account']['CustomerGroup'])->invoice_day;
			$oAccount->BillingFreq								= BILLING_DEFAULT_FREQ;
			$oAccount->BillingFreqType							= BILLING_DEFAULT_FREQ_TYPE;
			$oAccount->BillingMethod							= $_POST['Account']['DeliveryMethod'];
			$oAccount->PaymentTerms								= Payment_Terms::getCurrentForCustomerGroup((int)$_POST['Account']['CustomerGroup'])->payment_terms;
			$oAccount->CreatedBy								= AuthenticatedUser()->getUserId();
			$oAccount->CreatedOn								= Data_Source_Time::currentTimeStamp();
			$oAccount->DisableDDR								= ($_POST['Account']['DisableDDR']) ? $_POST['Account']['DisableDDR'] : 0;
			$oAccount->DisableLatePayment						= $_POST['Account']['DisableLatePayment'];
			$oAccount->DisableLateNotices						= self::DEFAULT_DISABLE_LATE_PAYMENT_NOTICES;
			$oAccount->LatePaymentAmnesty						= null;
			$oAccount->Sample									= self::DEFAULT_SAMPLE;
			$oAccount->Archived									= ACCOUNT_STATUS_PENDING_ACTIVATION;
			$oAccount->credit_control_status					= CREDIT_CONTROL_STATUS_UP_TO_DATE;
			$oAccount->last_automatic_invoice_action			= AUTOMATIC_INVOICE_ACTION_NONE;
			$oAccount->last_automatic_invoice_action_datetime	= null;
			$oAccount->automatic_barring_status					= AUTOMATIC_BARRING_STATUS_NONE;
			$oAccount->automatic_barring_datetime				= null;
			$oAccount->tio_reference_number						= null;
			$oAccount->vip										= self::DEFAULT_VIP_STATUS;
			$oAccount->save(AuthenticatedUser()->getUserId(), false);

			
			//----------------------------------------------------------------//
			// 4. Assign properties of new Contact
			//----------------------------------------------------------------//
			
			switch ($_POST['Contact']['USE'])
			{
				case 0:
					// Create a new Contact
					$oContact = new Contact();
					$oContact->AccountGroup						= $intAccountGroupId;
					$oContact->Title							= $_POST['Contact']['Title'];
					$oContact->FirstName						= $_POST['Contact']['FirstName'];
					$oContact->LastName							= $_POST['Contact']['LastName'];
					$oContact->DOB								= (int)$_POST['Contact']['DOB']['Year'] . "-" . (int)str_pad($_POST['Contact']['DOB']['Month'], 2, "0", STR_PAD_LEFT) . "-" . (int)str_pad($_POST['Contact']['DOB']['Day'], 2, "0", STR_PAD_LEFT);
					$oContact->JobTitle							= $_POST['Contact']['JobTitle'];
					$oContact->Email							= $_POST['Contact']['Email'];
					$oContact->Account							= $oAccount->Id;
					$oContact->CustomerContact					= self::DEFAULT_CUSTOMER_CONTACT;
					$oContact->Phone							= $_POST['Contact']['Phone'];
					$oContact->Mobile							= $_POST['Contact']['Mobile'];
					$oContact->Fax								= $_POST['Contact']['Fax'];
					$oContact->PassWord							= sha1($_POST['Contact']['Password']);
					$oContact->SessionId						= '';
					$oContact->SessionExpire					= self::DEFAULT_SESSION_EXPIRE;
					$oContact->Archived							= self::DEFAULT_CONTACT_ARCHIVED;
					$oContact->LastLogin						= null;
					$oContact->CurrentLogin						= null;
					$oContact->save();
					$intPrimaryContactId						= $oContact->Id;
					break;
				case 1:
					// Use an existing Contact
					$intPrimaryContactId						= $_POST['Contact']['Id'];
					break;
				default:
					// No option detected.
					$intPrimaryContactId = null;
					break;				
			}
			
			//----------------------------------------------------------------//
			// 5. Assign additional properties of New Account
			//----------------------------------------------------------------//
			
			$oAccount->PrimaryContact							= $intPrimaryContactId;
			$oAccount->save(AuthenticatedUser()->getUserId());
			
			
			//----------------------------------------------------------------//
			// 6. Add A System Note
			//----------------------------------------------------------------//
						
			$strNote  = "Account created with the following details:\n";
			$strNote .= "Business Name:			{$oAccount->BusinessName}\n";
			$strNote .= "Trading Name:			{$oAccount->TradingName}\n";
			$strNote .= "ABN:					{$oAccount->ABN}\n";
			$strNote .= "ACN:					{$oAccount->ACN}\n";
			$strNote .= "Address line 1:		{$oAccount->Address1}\n";
			$strNote .= "Address line 2:		{$oAccount->Address2}\n";
			$strNote .= "Suburb:				{$oAccount->Suburb}\n";
			$strNote .= "Postcode:				{$oAccount->Postcode}\n";
			$strNote .= "State:					{$oAccount->State}\n";
			$strNote .= "Customer Group:		{$oAccount->CustomerGroup}\n";
			$strNote .= "Account Group:			{$oAccount->AccountGroup}\n";
			$strNote .= "Billing Type:			{$oAccount->BillingType}\n";
			$strNote .= "Billing Method:		{$oAccount->BillingMethod}\n";
			
			Note::createSystemNote($strNote, AuthenticatedUser()->getUserId(), $oAccount->Id, null, null);
			

			//----------------------------------------------------------------//
			// 7. Send Response
			//----------------------------------------------------------------//
						
			// Redirect to the Account Overview
			header ('Location: /' . MenuItems::AccountOverview($oAccount->Id));
			exit;
				
		}
		
		//----------------------------------------------------------------//
		// Fail
		//----------------------------------------------------------------//
					
		// When we fail to create an account, load the error page
		catch (Exception $eException)
		{
			$arrDetailsToRender['Message']		= "An error occured";
			$arrDetailsToRender['ErrorMessage']	= $eException->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
			
	}
	
}
?>