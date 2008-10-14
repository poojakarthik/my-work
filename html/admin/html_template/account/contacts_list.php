<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_contacts_list.php
//----------------------------------------------------------------------------//
/**
 * account_contacts_list
 *
 * HTML Template for the Account Contacts popup
 *
 * HTML Template for the Account Contacts popup
 * This file defines the class responsible for defining and rendering the layout
 * of the HTML Template used by the Account Contacts popup
 *
 * @file		account_contacts_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel "MagnumSwordFortress" Dawkins
 * @version		7.10
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// HtmlTemplateAccountContactsList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountContactsList
 *
 * HTML Template object defining the presentation of the Account Contacts popup
 *
 * HTML Template object defining the presentation of the Account Contacts popup
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountContactsList
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountContactsList extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("account_contacts");
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function Render()
	{
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_POPUP:
				$this->_RenderAsPopup();
				break;
			case HTML_CONTEXT_PAGE:
				$this->_RenderInPage();
				break;
			default:
				$this->_RenderInPage();
				break;
		}
	}
	
	//------------------------------------------------------------------------//
	// RenderList
	//------------------------------------------------------------------------//
	/**
	 * RenderList()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	private function _RenderList()
	{
		$bolUserHasOperatorPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		
		Table()->ContactTable->SetHeader("Name", "Email", "Phone#", "Status", "&nbsp;");
		//Table()->ContactTable->SetWidth("40%", "20%", "20%", "10%", "10%");
		Table()->ContactTable->SetAlignment("Left", "Left", "Left", "Left", "Left");
		$arrContacts = array();
		foreach (DBL()->Contact as $dboContact)
		{
			// Record the Status of the Contact
			$strStatus = ($dboContact->Archived->Value) ? "Archived" : "Active";
			
			// Build Name Cell
			$strName		= ucwords(strtolower(trim("{$dboContact->Title->Value} {$dboContact->FirstName->Value} {$dboContact->LastName->Value}")));
			$strNameCell	= htmlspecialchars($strName);
			
			// Build Primary Contact flag
			if (DBO()->Account->PrimaryContact->Value == $dboContact->Id->Value)
			{
				// The current contact is the account's primary contact
				$strNameCell .= " (Primary Contact)";
			}
			
			
			// Build the Actions Cell
			$strViewContactLink = Href()->ViewContact($dboContact->Id->Value);
			$strViewContact = "<a href='$strViewContactLink'><img src='img/template/article.png' title='View Contact Details'</a>";
			if ($bolUserHasOperatorPerm && ($dboContact->Id->Value != DBO()->Account->PrimaryContact->Value) && ($dboContact->Archived->Value != 1))
			{
				// The contact is not the primary contact, and they are not archived
				// Allow them to be set as the primary contact
				//$strSetAsPrimaryContactLink	= "javascript:";
				$jsonName = JSON_Services::encode($strNameCell);
				$strSetAsPrimaryContact		= "<img src='img/template/primary_contact.png' onclick='Vixen.AccountContactsList.SetPrimary({$dboContact->Id->Value}, $jsonName)' title='Set as Primary Contact'</img>";
			}
			else
			{
				// The contact cannot be set as the primary, or it currently already is
				$strSetAsPrimaryContact = "";
			}
			
			$strActionsCell = "{$strViewContact}&nbsp;{$strSetAsPrimaryContact}";
			
			// Build the phone number cell
			if (trim($dboContact->Phone->Value) != "")
			{
				$strPhoneCell = $dboContact->Phone->AsValue();
			}
			elseif (trim($dboContact->Mobile->Value != ""))
			{
				$strPhoneCell = $dboContact->Mobile->AsValue();
			}
			else
			{
				$strPhoneCell = "[Not Specified]";
			}
			
			$strEmailCell = (trim($dboContact->Email->Value) != "")? htmlspecialchars($dboContact->Email->Value) : "[Not Specified]";
			
			Table()->ContactTable->AddRow($strNameCell, $strEmailCell, $strPhoneCell, $strStatus, $strActionsCell);
		}
		
		// If the account has no contacts then output an appropriate message in the table
		if (Table()->ContactTable->RowCount() == 0)
		{
			// There are no services to stick in this table
			Table()->ContactTable->AddRow("No contacts to display");
			Table()->ContactTable->SetRowAlignment("left");
			Table()->ContactTable->SetRowColumnSpan(5);
		}
		else
		{
			// This doesn't seem to be working.  Has it ever worked for popups?
			Table()->ContactTable->RowHighlighting = TRUE;
		}
		
		Table()->ContactTable->Render();
	}
	
	//------------------------------------------------------------------------//
	// RenderInPage
	//------------------------------------------------------------------------//
	/**
	 * RenderInPage()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	private function _RenderInPage()
	{
		$bolHasButtons = FALSE;
		echo "<h2 class='Contact'>Contacts</h2>\n";
		$this->_RenderList();
		// Check if there are more than 3
		DBL()->Contact->SetLimit(4);
		DBL()->Contact->Load();
		
		// Draw buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			// Include the AddContact button
			$strAddContactHref = Href()->AddContact(DBO()->Account->Id->Value);
			$this->Button("Add Contact", "window.location='$strAddContactHref'");
			$bolHasButtons = TRUE;
		}
		
		if (DBL()->Contact->RecordCount() > 3)
		{
			// Include the ViewAll button
			$strViewAccountContactsLink = Href()->ListContacts(DBO()->Account->Id->Value);
			$this->Button("View All", $strViewAccountContactsLink);
			$bolHasButtons = TRUE;
		}
		echo "</div></div>\n";
		
		if (!$bolHasButtons)
		{
			// There are no buttons, so include a spacer
			echo "<div class='TinySeparator'></div>\n";
		}
		
		// Initialise the javascript object which facilitates this HtmlTemplate
		$intAccountId = DBO()->Account->Id->Value;
		$strJsCode = "	if (Vixen.AccountContactsList == undefined)
						{
							Vixen.AccountContactsList = new VixenAccountContactsListClass;
						}
						Vixen.AccountContactsList.Initialise($intAccountId, '{$this->_strContainerDivId}')";
		echo "<script type='text/javascript'>$strJsCode</script>";
	}
	
	//------------------------------------------------------------------------//
	// RenderAsPopup
	//------------------------------------------------------------------------//
	/**
	 * RenderAsPopup()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	private function _RenderAsPopup()
	{
		echo "<div class='PopupLarge'>\n";
		
		// Work out if a virtical scroll bar will be required
		$strTableContainerStyle = (DBL()->Contact->RecordCount() > 14) ? "style='overflow:auto; height:450px'": "";
		
		// Draw the table container
		echo "<div $strTableContainerStyle>\n";

		$this->_RenderList();
		
		echo "</div>\n";  // Table Container
	
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			$strAddContactHref = Href()->AddContact(DBO()->Account->Id->Value);
			$this->Button("Add Contact", "window.location='$strAddContactHref'");
		}
		$this->Button("Close", "Vixen.Popup.Close(this);");
		echo "</div></div>\n";

		echo "</div>\n";  //PopupLarge

		// Initialise the javascript object which facilitates this HtmlTemplate
		$intAccountId = DBO()->Account->Id->Value;
		$strJsCode = "	if (Vixen.AccountContactsList == undefined)
						{
							Vixen.AccountContactsList = new VixenAccountContactsListClass;
						}
						Vixen.AccountContactsList.Initialise($intAccountId, '{$this->_strContainerDivId}')";
		echo "<script type='text/javascript'>$strJsCode</script>";
	}
}

?>
