<?php

class Application_Handler_Account extends Application_Handler
{
	
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
			$arrDetailsToRender['arrCreditCardExpiryMonth']	= array(); // 12 month
			$arrDetailsToRender['arrCreditCardExpiryYear']	= array(); // 10 year
			$arrDetailsToRender['arrContactTitles']			= Contact_Title::getAll();
			$arrDetailsToRender['arrDateOfBirthDay']		= array();
			$arrDetailsToRender['arrDateOfBirthMonth']		= array();
			$arrDetailsToRender['arrDateOfBirthYear']		= array();


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
					WHERE ag.Id = $iAssociated;");

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
			$iAccountId = 1000174964;
			header ('Location: /' . MenuItems::AccountOverview($iAccountId));
			
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