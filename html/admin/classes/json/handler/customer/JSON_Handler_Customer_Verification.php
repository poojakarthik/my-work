<?php

class JSON_Handler_Customer_Verification extends JSON_Handler
{
	//------------------------------------------------------------------------//
	// quickSearch
	//------------------------------------------------------------------------//
	/**
	 * quickSearch()
	 *
	 * Handles ajax request from client, to search for Accounts/Contacts/Services
	 * 
	 * Handles ajax request from client, to search for Accounts/Contacts/Services
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
						$objAccount = current($arrAccounts);
						$intAccountId = $objAccount->id;
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
				$arrCustomer['Contacts'][$objContact->id] = array(	"Id"			=> $objContact->id,
																	"Name"			=> htmlspecialchars($objContact->getName()),
																	"Sha1DOB"		=> ($objContact->dob == "0000-00-00")? NULL : sha1(date("d/m/Y", strtotime($objContact->dob))),
																	"Sha1Email"		=> (strlen($objContact->email) == 0)? NULL : sha1($objContact->email),
																	"Sha1Password"	=> $objContact->password
																);
			}
			
			$arrCustomer['Accounts'] = array();
			foreach ($arrAccounts as $objAccount)
			{
				$strAddress = htmlspecialchars(trim($objAccount->address1)) ."\n". htmlspecialchars(trim($objAccount->address2)) ."\n". htmlspecialchars(trim($objAccount->suburb)) .", ". htmlspecialchars(trim($objAccount->postcode)) ."\n". htmlspecialchars(trim($objAccount->state));
				$strAddress = str_replace("\n\n", "\n", $strAddress);
				$strAddress = str_replace("\n", "<br />", $strAddress);
				
				$arrCustomer['Accounts'][$objAccount->id] = array(	"Id"			=> $objAccount->id,
																	"Name"			=> htmlspecialchars($objAccount->getName()),
																	"BusinessName"	=> (strlen($objAccount->businessName) == 0)? NULL : htmlspecialchars($objAccount->businessName),
																	"TradingName"	=> (strlen($objAccount->tradingName) == 0)? NULL : htmlspecialchars($objAccount->tradingName),
																	"Address"		=> $strAddress,
																	"Sha1ABN"		=> (strlen($objAccount->abn) == 0)? NULL : sha1(str_replace(" ", "", $objAccount->abn))
																);
			}
			
			
			$strPopupContent = $this->_buildPopupContent($arrCustomer);
			
			
			
			return array(	"Success"		=> TRUE,
							"Customer"		=> $arrCustomer,
							"PopupContent"	=> $strPopupContent
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
			$strContactControl = "<span id='CustomerVerificationPopup_Contact' name='CustomerVerificationPopup_Contact'>". htmlspecialchars($arrCustomer['Contacts'][$arrCustomer['SelectedContact']]['Name']) ."</span>";
		}
		else
		{
			// The Contact can be changed.  Use a combobox
			$strOptions = "<option value='0' >&nbsp;</option>";
			foreach ($arrCustomer['Contacts'] as $intId=>$arrContact)
			{
				$strOptions .= "<option value='$intId'>". htmlspecialchars($arrContact['Name']) ."</option>";
			}
			
			$strContactControl = "<select id='CustomerVerificationPopup_Contact' name='CustomerVerificationPopup_Contact' style='width:100%'>$strOptions</select>";	
		}
		
		if (isset($arrCustomer['FixedAccount']) && $arrCustomer['FixedAccount'] == TRUE)
		{
			// The Account is known, and cannot be changed
			$strAccountControl = "<span id='CustomerVerificationPopup_Account' name='CustomerVerificationPopup_Account'>{$arrCustomer['Accounts'][$arrCustomer['SelectedAccount']]['Id']} - ". htmlspecialchars($arrCustomer['Accounts'][$arrCustomer['SelectedAccount']]['Name']) ."</span>";
		}
		else
		{
			// The Account can be changed.  Use a combobox
			$strOptions = "<option value='0'>&nbsp;</option";
			foreach ($arrCustomer['Accounts'] as $intId=>$arrAccount)
			{
				$strOptions .= "<option value='$intId'>{$arrAccount['Id']} - ". htmlspecialchars($arrAccount['Name']) ."</option>";
			}
			
			$strAccountControl = "<select id='CustomerVerificationPopup_Account' name='CustomerVerificationPopup_Account' style='width:100%'>$strOptions</select>";
		}
		
		$strHtml = "
<div id='PopupPageBody'>
	<form id='CustomerVerificationPopup_Form' name='CustomerVerificationPopup_Form'>
		<div id='CustomerVerificationPopup_VerificationStatus' name='CustomerVerificationPopup_VerificationStatus' class='MsgNotice'>
			INSERT CUSTOMER VERIFICATION STATUS HERE
		</div>
		<div class='GroupedContent'>
			<table class='form-data'>
				<tr>
					<td class='title' style='width:30%'>Contact</td>
					<td >$strContactControl</td>
				</tr>
				<tr>
					<td class='title'>Account</td>
					<td>$strAccountControl</td>
				</tr>
			</table>
		</div>
		<div id='CustomerVerificationPopup_AccountDetailsContainer' name='CustomerVerificationPopup_AccountDetailsContainer' class='GroupedContent' style='margin-top:5px'>
			<table class='form-data'>
				<tr>
					<td class='title' style='width:30%' id='CustomerVerificationPopup_AccountBusinessNameContainer' name='CustomerVerificationPopup_AccountBusinessNameContainer'>Business Name</td>
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
			</table>
		</div>
		<div id='CustomerVerificationPopup_ContactDetailsContainer' name='CustomerVerificationPopup_ContactDetailsContainer' class='GroupedContent' style='margin-top:5px'>
			<table class='form-data'>
				<tr>
					<td class='title' style='width:30%'>Password</td>
					<td>
						<input type='text' id='CustomerVerificationPopup_ContactPassword' name='CustomerVerificationPopup_ContactPassword' maxlength='255'></input>
					</td>
				</tr>
				<tr id='CustomerVerificationPopup_ContactDOBContainer' name='CustomerVerificationPopup_ContactDOBContainer'>
					<td class='title'>Date Of Birth</td>
					<td>
						<input type='text' id='CustomerVerificationPopup_ContactDOB' name='CustomerVerificationPopup_ContactDOB' maxlength='10'></input>
					</td>
				</tr>
				<tr id='CustomerVerificationPopup_ContactEmailContainer' name='CustomerVerificationPopup_ContactEmailContainer'>
					<td class='title'>Email Address</td>
					<td>
						<input type='text' id='CustomerVerificationPopup_ContactEmail' name='CustomerVerificationPopup_ContactEmail' maxlength='255'></input>
					</td>
				</tr>
			</table>
		</div>
	</form>
	<div style='padding-top:3px;height:auto:width:100%'>
		<input type='button' value='Close' onclick='Vixen.Popup.Close(this)' style='float:right;margin-left:3px'></input>
		<input type='button' id='CustomerVerificationPopup_AccountButton' name='CustomerVerificationPopup_AccountButton' value='Account Overview' onclick='FlexCustomerVerification.ViewAccount()' style='float:right;margin-left:3px'></input>
		<input type='button' id='CustomerVerificationPopup_ContactButton' name='CustomerVerificationPopup_ContactButton' value='Contact Details' onclick='FlexCustomerVerification.ViewContact()' style='float:right;margin-left:3px'></input>
		<div style='clear:both;float:none'></div>
	</div>
</div>
";
		return $strHtml;
		
	}
}

?>
