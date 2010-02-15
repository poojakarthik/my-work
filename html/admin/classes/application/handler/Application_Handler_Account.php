<?php

class Application_Handler_Account extends Application_Handler
{
	// Renders the page for Account Creation
	public function Create($subPath)
	{
		/*
		 * POSSIBLE ERRORS IN A SUBMISSION
		Please select a valid Customer Group.
		Please select a valid Billing Method.
		Please select a valid Credit Card Type.
		Please select a valid State.
		Please enter a Business Name.
		Please enter an ABN or ACN.
		Please enter a valid ABN.
		Please enter a valid ACN.
		Please enter an Address.
		Please enter a Suburb.
		Please enter a Postcode.
		
		Please enter a Direct Debit Bank Name.
		Please enter a Direct Debit BSB #.
		Please enter a valid Direct Debit BSB #.
		Please enter a Direct Debit Account #.
		Please enter a valid Direct Debit Account #.
		Please enter a Direct Debit Account Name.
		
		Please enter a Credit Card Holder Name.
		Please enter a Credit Card #.
		Please enter a valid Credit Card Number.
		Please enter a Credit Card Expiry Month.
		Please enter a Credit Card Expiry Year.
		Please enter a valid Credit Card Expiration Date.
		Please enter a valid Credit Card CVV.
		
		
		Please enter a Title.
		Please enter a First Name.
		Please enter a Last Name.
		Please enter a valid Date of Birth.
		Please enter an Email Address.
		Please enter a Contact Number.
		Please enter a valid Phone Number.
		Please enter a valid Mobile Number.
		Please enter a valid Fax Number.
		Please enter a Username.
		The Username you entered already exists. Please enter a unique Username.
		The Email you entered is already in use by an active contact. Please use this contact, or enter a different email address.
		Please enter a Password.
		*/
		
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
			$arrDetailsToRender['arrCreditCardExpiryMonth']	= array(); // 12 month
			$arrDetailsToRender['arrCreditCardExpiryYear']	= array(); // 10 year
			if (array_key_exists("Associated", $_GET))
			{	
				if (($oAccountGroup = Account_Group::getForAccountId($_GET['Associated'])) && is_numeric($_GET['Associated']))
				{
					var_dump($oAccountGroup->getContacts());
					$arrDetailsToRender['arrAccountGroupContacts'] = $oAccountGroup->getContacts();
				}
				else
				{
					throw new Exception('Invalid associated account id specified.');
				}
			}
			else
			{
				$oAccountGroup = new Account_Group();
			}
			$arrDetailsToRender['arrContactTitles']			= Contact_Title::getAll();
			$arrDetailsToRender['arrDateOfBirthDay']		= array();
			$arrDetailsToRender['arrDateOfBirthMonth']		= array();
			$arrDetailsToRender['arrDateOfBirthYear']		= array();

			// Set the final breadcrumb
			// BreadCrumb()->SetCurrentPage("Add Account");
			
			$this->LoadPage('account_create', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		catch (Exception $eException)
		{
			$arrDetailsToRender['Message']		= "An error occured";
			$arrDetailsToRender['ErrorMessage']	= $eException->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
}
?>