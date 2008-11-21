<?php

class JSON_Handler_Customer_Verification extends JSON_Handler
{
	const REQUIRED_ACCOUNT_SCORE = 10;
	const REQUIRED_CONTACT_SCORE = 10;

	const REQUIRED_PERMISSIONS_TO_OVERRIDE_VERIFICATION = PERMISSION_OPERATOR;
	const PAGE_CONTACT = "contact";
	const PAGE_ACCOUNT = "account";

	// The Customer Verification script
	// It verifies the customer's details, and returns the link to the page that the user wants
	public function verify($intContactId, $intAccountId, $objVerifiedContactProperties=NULL, $objVerifiedAccountProperties=NULL, $strRequestedPage=NULL, $bolOverrideVerification=FALSE)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		
		try
		{
			if ($intContactId == NULL && $intAccountId == NULL)
			{
				throw new Exception("Neither Contact Id nor Account Id have been specified");
			}
			
			if ($intAccountId != NULL && $intContactId != NULL)
			{
				// Both the AccountId and ContactId have been specified
				// Check that the Contact belongs to the Account
				$objAccount = Account::getForId($intAccountId);
				$arrAccountContacts = $objAccount->getContacts();
				if (!array_key_exists($intContactId, $arrAccountContacts))
				{
					throw new Exception("Contact with id: $intContactId can not be associated with Account with id: $intAccountId, ". $objAccount->getName());
				}
			}
			
			// Verify details
			if ($bolOverrideVerification == FALSE)
			{
				//TODO! It is assumed that the customer has been verified on the client side.  They should now be verified on the Server side, so as to protect against hacks
			}
			else
			{
				// The user wants to override verification
				// Make sure they have permission to
				if (!AuthenticatedUser()->UserHasPerm(self::REQUIRED_PERMISSIONS_TO_OVERRIDE_VERIFICATION))
				{
					throw new Exception("The user does not have the necessary privileges to override customer verification");
				}
			}
			
			// Work out where to redirect the user
			switch ($strRequestedPage)
			{
				case (self::PAGE_CONTACT):
					$strLocation = Href()->ViewContact($intContactId);
					break;
				case (self::PAGE_ACCOUNT):
					$strLocation = Href()->AccountOverview($intAccountId);
					break;
				default:
					if ($intAccountId != NULL)
					{
						$strLocation = Href()->AccountOverview($intAccountId);
					}
					elseif ($intContactId != NULL)
					{
						$strLocation = Href()->ViewContact($intContactId);
					}
					else
					{
						// This scenario should never happen
						throw new Exception("Invalid page to redirect to: $strRequestedPage");
					}
			}

			// Record the Customer Verification in the EmployeeAccountAudit table
			TransactionStart();
			$objUser = Employee::getForId(AuthenticatedUser()->GetUserId());
			$objUser->recordCustomerInAccountHistory($intAccountId, $intContactId);
			TransactionCommit();
			
			return array(	"Success"			=> TRUE,
							"PageToRelocateTo"	=> $strLocation
						);
		}
		catch (Exception $e)
		{
			return array(	"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}

	
	//------------------------------------------------------------------------//
	// buildPopup
	//------------------------------------------------------------------------//
	/**
	 * buildPopup()
	 *
	 * Handles ajax request from client, to build the VerifyCustomer popup
	 * 
	 * Handles ajax request from client, to build the VerifyCustomer popup
	 *
	 * @param	int		$intContactId
	 * @param	int		$intAccountId
	 * 
	 * @return	array		["Success"]				TRUE if search was executed successfully, else FALSE
	 * 						["ErrorMessage"]		Declares what went wrong (only defined when Success == FALSE)
	 * @method
	 */
	public function buildPopup($intContactId, $intAccountId)
	{
		// If just the account is specified, then the account is locked in
		// If just the contact is specified, then the contact is locked in
		// If both are specified, then just the Contact is locked in
		
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		
		try
		{
			$arrCustomer = array();
			if ($intContactId != NULL)
			{
				// A contact has been specified
				$objContact = Contact::getForId($intContactId);
				if ($objContact === NULL)
				{
					throw new Exception("Invalid contact id: $intContactId");
				}
				
				$arrCustomer['FixedContact'] = TRUE;
				$arrContacts = array($intContactId	=> $objContact);
								
				// Get list of accounts, that the contact can view
				// It is assumed that they can view at least 1 account
				$arrAccounts = $objContact->getAccounts(TRUE);
				
				if ($intAccountId == NULL) 
				{
					// An Account hasn't been defined
					if (count($arrAccounts) == 1)
					{
						// There is only one account that they can view, have it be the default one
						reset($arrAccounts);
						$objAccount		= current($arrAccounts);
						$intAccountId	= $objAccount->id;
					}
					else
					{
						$intAccountId = NULL;
					}
				}
				else
				{
					// $intAccountId has been specified
					if (array_search($intAccountId, array_keys($arrAccounts)) === FALSE)
					{
						// The customer can't view $intAccountId
						$intAccountId = NULL;
					}
				}
			}
			else if ($intAccountId != NULL)
			{
				// An Account has been specified
				$objAccount = Account::getForId($intAccountId);
				if ($objAccount === NULL)
				{
					throw new Exception("Invalid account id: $intAccountId");
				}
				
				$arrCustomer['FixedAccount'] = TRUE;
				$arrAccounts = array($intAccountId	=> $objAccount);
				
				// Get list of Contacts that can view this account
				$arrContacts = $objAccount->getContacts(TRUE);
				
				// If there is only one contact, then set it up as the selected one
				if (count($arrContacts) == 1)
				{
					reset($arrContacts);
					$objContact		= current($arrContacts);
					$intContactId	= $objContact->id;
				}
			}
			else
			{
				throw new Exception("No valid Contact or Account details specified");
			}
			
			// Build data structure defining Accounts and Contacts and Properties to verify
			
			
			
			// Work out the default values
			$arrCustomer['SelectedContact'] = $intContactId;
			$arrCustomer['SelectedAccount'] = $intAccountId;
			
			$arrCustomer['Contacts'] = array();
			foreach ($arrContacts as $objContact)
			{
				$intDOB = strtotime($objContact->dob);
				if ($objContact->dob == "0000-00-00" || $intDOB === FALSE)
				{
					// The Address is invalid
					$arrDOB = NULL;
				}
				else
				{
					// The contact has a valid DOB
					$arrDOB = array("Sha1"		=> sha1(date("j/n/Y", $intDOB)),
									"Weight"	=> 5
									);
				}
				
				if (EmailAddressValid($objContact->email))
				{
					// The contact has a valid Email Address
					$arrEmail = array(	"Sha1"		=> sha1(strtolower($objContact->email)),
										"Weight"	=> 5
										);
				}
				else
				{
					$arrEmail = NULL;
				}
				
				if (strlen($objContact->passWord) != 0)
				{
					// All contacts have a password
					$arrPassword = array(	"Sha1"		=> $objContact->passWord,
											"Weight"	=> 10
										);
				}
				else
				{
					$arrPassword = NULL;
				}
				$arrCustomer['Contacts'][$objContact->id] = array(	"Id"			=> $objContact->id,
																	"Name"			=> htmlspecialchars($objContact->getName()),
																	"Verifiable"	=> array(	"DOB"		=> $arrDOB,
																								"Email"		=> $arrEmail,
																								"Password"	=> $arrPassword
																							)
																);
			}
			
			$arrCustomer['Accounts'] = array();
			foreach ($arrAccounts as $objAccount)
			{
				$strAddress = htmlspecialchars(trim($objAccount->address1)) ."\n". htmlspecialchars(trim($objAccount->address2)) ."\n". htmlspecialchars(trim($objAccount->suburb)) .", ". htmlspecialchars(trim($objAccount->postcode)) ."\n". htmlspecialchars(trim($objAccount->state));
				$strAddress = str_replace("\n\n", "\n", $strAddress);
				$strAddress = str_replace("\n", "<br />", $strAddress);
				
				// Every account has an address
				$arrAddress = array("Value"		=> $strAddress,
									"Weight"	=> 5
									);
				if (strlen($objAccount->abn) != 0)
				{
					$arrABN = array("Sha1"		=> sha1(str_replace(" ", "", $objAccount->abn)),
									"Weight"	=> 5
									);
				}
				else
				{
					$arrABN = NULL;
				}
				
				if (strlen($objAccount->acn) != 0)
				{
					$arrACN = array("Sha1"		=> sha1(str_replace(" ", "", $objAccount->acn)),
									"Weight"	=> 5
									);
				}
				else
				{
					$arrACN = NULL;
				}
				
				
				$arrCustomer['Accounts'][$objAccount->id] = array(	"Id"			=> $objAccount->id,
																	"Name"			=> htmlspecialchars($objAccount->getName()),
																	"BusinessName"	=> (strlen($objAccount->businessName) == 0)? NULL : htmlspecialchars($objAccount->businessName),
																	"TradingName"	=> (strlen($objAccount->tradingName) == 0)? NULL : htmlspecialchars($objAccount->tradingName),
																	"Verifiable"	=> array(	"Address"	=> $arrAddress,
																								"ABN"		=> $arrABN,
																								"ACN"		=> $arrACN
																							)
																);
			}
			
			
			$strPopupContent = $this->_buildPopupContent($arrCustomer);
			
			
			
			return array(	"Success"						=> TRUE,
							"Customer"						=> $arrCustomer,
							"RequiredScoreToVerifyAccount"	=> self::REQUIRED_ACCOUNT_SCORE,
							"RequiredScoreToVerifyContact"	=> self::REQUIRED_CONTACT_SCORE,
							"PopupContent"					=> $strPopupContent
						);
		}
		catch (Exception $e)
		{
			return array(	"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}
	
	
	private function _buildPopupContent($arrCustomer)
	{
		if (isset($arrCustomer['FixedContact']) && $arrCustomer['FixedContact'] == TRUE)
		{
			// The Contact is known, and cannot be changed
			$strContactControl = "<span id='CustomerVerificationPopup_Contact' name='CustomerVerificationPopup_Contact'>{$arrCustomer['Contacts'][$arrCustomer['SelectedContact']]['Name']}</span>";
		}
		else
		{
			// The Contact can be changed.  Use a combobox
			$strOptions = "<option value='0' >&nbsp;</option>";
			foreach ($arrCustomer['Contacts'] as $intId=>$arrContact)
			{
				$strOptions .= "<option value='$intId'>{$arrContact['Name']}</option>";
			}
			
			$strContactControl = "<select id='CustomerVerificationPopup_Contact' name='CustomerVerificationPopup_Contact' style='width:100%'>$strOptions</select>";	
		}
		
		if (isset($arrCustomer['FixedAccount']) && $arrCustomer['FixedAccount'] == TRUE)
		{
			// The Account is known, and cannot be changed
			$strAccountControl = "<span id='CustomerVerificationPopup_Account' name='CustomerVerificationPopup_Account'>{$arrCustomer['Accounts'][$arrCustomer['SelectedAccount']]['Id']} - {$arrCustomer['Accounts'][$arrCustomer['SelectedAccount']]['Name']}</span>";
		}
		else
		{
			// The Account can be changed.  Use a combobox
			$strOptions = "<option value='0'>&nbsp;</option";
			foreach ($arrCustomer['Accounts'] as $intId=>$arrAccount)
			{
				$strOptions .= "<option value='$intId'>{$arrAccount['Id']} - {$arrAccount['Name']}</option>";
			}
			
			$strAccountControl = "<select id='CustomerVerificationPopup_Account' name='CustomerVerificationPopup_Account' style='width:100%'>$strOptions</select>";
		}
		
		// Build Date combo boxes
		$strOptions = "<option value='0'>&nbsp</option>";
		for ($i = 1; $i <= 31; $i++)
		{
			$strOptions .= "<option value='$i'>$i</option>";
		}
		$strDaysCombo = "<select id='CustomerVerificationPopup_ContactDOBDay' name='CustomerVerificationPopup_ContactDOBDay'>$strOptions</select>";
		
		$strMonthsCombo = "
<select id='CustomerVerificationPopup_ContactDOBMonth' name='CustomerVerificationPopup_ContactDOBMonth'>
	<option value='0'>&nbsp;</option>
	<option value='1'>01 - Jan</option>
	<option value='2'>02 - Feb</option>
	<option value='3'>03 - Mar</option>
	<option value='4'>04 - Apr</option>
	<option value='5'>05 - May</option>
	<option value='6'>06 - Jun</option>
	<option value='7'>07 - Jul</option>
	<option value='8'>08 - Aug</option>
	<option value='9'>09 - Sep</option>
	<option value='10'>10 - Oct</option>
	<option value='11'>11 - Nov</option>
	<option value='12'>12 - Dec</option>
</select>";
		
		$strOptions = "<option value='0'>&nbsp;</option>\n";
		$intMaxYear = intval(date("Y")) - 15;
		for ($i = $intMaxYear; $i > $intMaxYear -90; $i--)
		{
			$strOptions .= "<option value='$i'>$i</option>\n";
		}
		$strYearsCombo = "<select id='CustomerVerificationPopup_ContactDOBYear' name='CustomerVerificationPopup_ContactDOBYear'>$strOptions</select>";
		
		$strHtml = "
<div id='PopupPageBody'>
	<form id='CustomerVerificationPopup_Form' name='CustomerVerificationPopup_Form'>
		<div id='CustomerVerificationPopup_VerificationStatus' name='CustomerVerificationPopup_VerificationStatus' class='MsgNotice'>
		</div>
		<div class='GroupedContent'>
			<table class='form-data'>
				<tr>
					<td class='title' style='width:30%'>Contact</td>
					<td>$strContactControl</td>
				</tr>
				<tr>
					<td class='title'>Account</td>
					<td>$strAccountControl</td>
				</tr>
			</table>
		</div>
		<div id='CustomerVerificationPopup_AccountDetailsContainer' name='CustomerVerificationPopup_AccountDetailsContainer' class='GroupedContent' style='margin-top:5px;display:none'>
			<table class='form-data'>
				<tr id='CustomerVerificationPopup_AccountBusinessNameContainer' name='CustomerVerificationPopup_AccountBusinessNameContainer'>
					<td class='title' style='width:30%'>Business Name</td>
					<td>
						<span id='CustomerVerificationPopup_AccountBusinessName' name='CustomerVerificationPopup_AccountBusinessName'>INSERT BUSINESS NAME HERE</span>
					</td>
				</tr>
				<tr id='CustomerVerificationPopup_AccountTradingNameContainer' name='CustomerVerificationPopup_AccountTradingNameContainer'>
					<td class='title' style='width:30%'>Trading Name</td>
					<td>
						<span id='CustomerVerificationPopup_AccountTradingName' name='CustomerVerificationPopup_AccountTradingName'>INSERT TRADING NAME HERE</span>
					</td>
				</tr>
				<tr>
					<td class='title' style='width:30%'>Address</td>
					<td>
						<div id='CustomerVerificationPopup_AccountAddress' name='CustomerVerificationPopup_AccountAddress'>INSERT ADDRESS HERE</div>
					</td>
				</tr>
				<tr>
					<td class='title'>Address Verified</td>
					<td>
						<input type='checkbox' id='CustomerVerificationPopup_AccountAddressVerified' name='CustomerVerificationPopup_AccountAddressVerified'></input>
					</td>
				</tr>
				<tr id='CustomerVerificationPopup_AccountABNContainer' name='CustomerVerificationPopup_AccountABNContainer'>
					<td class='title'>ABN</td>
					<td>
						<input type='text' id='CustomerVerificationPopup_AccountABN' name='CustomerVerificationPopup_AccountABN' maxlength='11'></input>
					</td>
				</tr>
				<tr id='CustomerVerificationPopup_AccountACNContainer' name='CustomerVerificationPopup_AccountACNContainer'>
					<td class='title'>ACN</td>
					<td>
						<input type='text' id='CustomerVerificationPopup_AccountACN' name='CustomerVerificationPopup_AccountACN' maxlength='9'></input>
					</td>
				</tr>
			</table>
		</div>
		<div id='CustomerVerificationPopup_ContactDetailsContainer' name='CustomerVerificationPopup_ContactDetailsContainer' class='GroupedContent' style='margin-top:5px;display:none'>
			<table class='form-data'>
				<tr id='CustomerVerificationPopup_ContactPasswordContainer' name='CustomerVerificationPopup_ContactPasswordContainer'>
					<td class='title' style='width:30%'>Password</td>
					<td>
						<input type='text' id='CustomerVerificationPopup_ContactPassword' name='CustomerVerificationPopup_ContactPassword' maxlength='255'></input>
					</td>
				</tr>
				<tr id='CustomerVerificationPopup_ContactEmailContainer' name='CustomerVerificationPopup_ContactEmailContainer'>
					<td class='title' style='width:30%'>Email Address</td>
					<td>
						<input type='text' id='CustomerVerificationPopup_ContactEmail' name='CustomerVerificationPopup_ContactEmail' maxlength='255'></input>
					</td>
				</tr>
				<tr id='CustomerVerificationPopup_ContactDOBContainer' name='CustomerVerificationPopup_ContactDOBContainer'>
					<td class='title' style='width:30%'>Date Of Birth</td>
					<td style='width:70%'>
						$strDaysCombo / $strMonthsCombo / $strYearsCombo
					</td>
				</tr>
			</table>
		</div>
	</form>
	<div style='padding-top:3px;height:auto:width:100%'>
		<div style='float:right'>
			<input type='button' id='CustomerVerificationPopup_ContactButton' name='CustomerVerificationPopup_ContactButton' value='Contact Details' onclick='FlexCustomerVerification.viewContact()' style='margin-left:3px'></input>
			<input type='button' id='CustomerVerificationPopup_AccountButton' name='CustomerVerificationPopup_AccountButton' value='Account Overview' onclick='FlexCustomerVerification.viewAccount()' style='margin-left:3px'></input>
			<input type='button' value='Close' onclick='Vixen.Popup.Close(this)' style='margin-left:3px'></input>
		</div>
		<div style='clear:both;float:none'></div>
	</div>
</div>
";
		return $strHtml;
		
	}
}

?>
