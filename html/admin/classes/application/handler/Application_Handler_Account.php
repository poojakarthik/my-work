<?php

class Application_Handler_Account extends Application_Handler
{
	
	const DEFAULT_DISABLE_LATE_PAYMENT_NOTICES	= 0;
	const DEFAULT_SAMPLE						= 0;
	const DEFAULT_CUSTOMER_CONTACT				= 0;
	const DEFAULT_SESSION_EXPIRE				= '1970-01-01 00:00:00';
	const DEFAULT_CONTACT_ARCHIVED				= 0;
	const DEFAULT_VIP_STATUS					= 0;
	

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
					SELECT	DISTINCT c.*
					FROM	AccountGroup ag
							JOIN Account a ON (a.AccountGroup = ag.Id AND a.Archived = 0)
							JOIN Contact c ON (a.PrimaryContact = c.Id
												OR (ag.Id = c.AccountGroup
													AND c.CustomerContact = 1)
												) AND c.Archived = 0
					WHERE a.Id = $iAssociated;");

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
			// Validate Information
			//----------------------------------------------------------------//
			// I've consolidated some error checks (Email as an example.)
			// For most cases, users should not reach any error here because it will be caught by JavaScript.

			if(Contact::isEmailInUse($_POST['Contact']['Email']) || !Validation::IsValidEmail($_POST['Contact']['Email']))
			{
				throw new Exception('Invalid Email address or the Email address is already in use');
			}
			if(!@checkdate((int)$_POST ['Contact']['DOB']['Month'], (int)$_POST ['Contact']['DOB']['Day'], (int)$_POST ['Contact']['DOB']['Year']))
			{
				throw new Exception('Invalid Date Of Birth');
			}
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
			if(!Validation::IsValidInteger($_POST['Account']['State']))
			{
				throw new Exception('Invalid State');
			}
			if(!Validation::IsValidInteger($_POST['Account']['CustomerGroup']))
			{
				throw new Exception('Invalid Customer Group');
			}
			//----------------------------------------------------------------//
			// TODO
			//----------------------------------------------------------------//
			// Validate: Late Payments, Delivery Method, Payment Method, Primarty Contact
			
			
			//----------------------------------------------------------------//
			// Assign properties of new Account Group
			//----------------------------------------------------------------//
			
			if(array_key_exists("Associated", $_POST))
			{
				$intAccountGroupId = (int)Account_Group::getForAccountId($_POST['Associated']);
			}
			if(!array_key_exists("Associated", $_POST))
			{
				$oAccountGroup = new Account_Group();
				$oAccountGroup->CreatedBy							= AuthenticatedUser()->getUserId();
				$oAccountGroup->CreatedOn							= Data_Source_Time::currentTimeStamp();
				$oAccountGroup->ManagedBy							= null;
				$oAccountGroup->Archived							= 0;
				$oAccountGroup->save();
				$intAccountGroupId									= $oAccountGroup->Id
			}

			//----------------------------------------------------------------//
			// Assign properties of new Account
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
			$oAccount->State									= (int)$_POST['Account']['State'];
			$oAccount->Country									= 'AU';
			$oAccount->BillingType								= (int)$_POST['Account']['BillingType'];
			$oAccount->PrimaryContact							= null;
			$oAccount->CustomerGroup							= (int)$_POST['Account']['CustomerGroup'];
			// TODO
			//$oAccount->CreditCard								= $_POST['Account']['CreditCard'];
			//$oAccount->DirectDebit							= $_POST['Account']['DirectDebit'];
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
			// Assign properties of new Contact
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
					$oContact->DOB								= $_POST['Contact']['DOB']['Year'] . "-" . $_POST['Contact']['DOB']['Month'] . "" . $_POST['Contact']['DOB']['Day'];
					$oContact->JobTitle							= $_POST['Contact']['JobTitle'];
					$oContact->Email							= $_POST['Contact']['Email'];
					$oContact->Account							= $oAccount->Id;
					$oContact->CustomerContact					= self::DEFAULT_CUSTOMER_CONTACT;
					$oContact->Phone							= $_POST['Contact']['Phone'];
					$oContact->Mobile							= $_POST['Contact']['Mobile'];
					$oContact->Fax								= $_POST['Contact']['Fax'];
					$oContact->PassWord							= $_POST['Contact']['Password'];
					$oContact->SessionId						= '';
					$oContact->SessionExpire					= self::DEFAULT_SESSION_EXPIRE;
					$oContact->Archived							= self::DEFAULT_CONTACT_ARCHIVED;
					$oContact->LastLogin						= null;
					$oContact->CurrentLogin						= null;
					$oContact->save();
					// This integer is used when creating $oAccount
					$intPrimaryContactId						= $oContact->Id;
					break;
				case 1:
					// Use an existing Contact
					// This integer is used when creating $oAccount
					$intPrimaryContactId						= $_POST['Contact']['Id'];
					break;
				default:
					// No option detected.
					// This integer is used when creating $oAccount
					$intPrimaryContactId = null;
					break;				
			}
			
			//----------------------------------------------------------------//
			// Assign properties of New Account
			//----------------------------------------------------------------//
			$oAccount->PrimaryContact							= $intPrimaryContactId;
			$oAccount->save(AuthenticatedUser()->getUserId());
			
			//----------------------------------------------------------------//
			// Add A System Note
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
			

			// Redirect to the Account Overview
			header ('Location: /' . MenuItems::AccountOverview($oAccount->Id));
			exit;
				
		}
		
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