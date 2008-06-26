<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// recent_customer_list.php
//----------------------------------------------------------------------------//
/**
 * recent_customer_list
 *
 * HTML Template for the Recent Customers popup
 *
 * HTML Template for the Recent Customers popup
 *
 * @file		recent_customer_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel "MagnumSwordFortress" Dawkins
 * @version		8.02
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// HtmlTemplateEmployeeRecentCustomerList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateEmployeeRecentCustomerList
 *
 * HTML Template object defining the presentation of the Recent Customers popup
 *
 * HTML Template object defining the presentation of the Recent Customers popup
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateEmployeeRecentCustomerList
 * @extends	HtmlTemplate
 */
class HtmlTemplateEmployeeRecentCustomerList extends HtmlTemplate
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
		$arrAlreadyIncludedContacts = array();
		
	
		Table()->RecentCustomers->SetHeader("Account", "&nbsp;", "Contact", "Verified");
		Table()->RecentCustomers->SetAlignment("Left", "Left", "Left", "Left");
		Table()->RecentCustomers->SetWidth("12%", "40%", "33%", "15%");
	
		$strToday = date("jS M Y");
	
		foreach (DBL()->RecentCustomers as $dboCustomer)
		{
			$intContactId = $dboCustomer->ContactId->Value;
			$intAccountId = $dboCustomer->AccountId->Value;
			
			if (isset($arrAlreadyIncludedContacts[$intContactId]) && in_array($intAccountId, $arrAlreadyIncludedContacts[$intContactId]))
			{
				// The customer has already been added to the list.  Don't add it again
				continue;
			}
			
			// Add the contact to the list of already displayed contacts
			if (isset($arrAlreadyIncludedContacts[$intContactId]))
			{
				// Append the Account Id to the list of accounts associated with this contact
				$arrAlreadyIncludedContacts[$intContactId][] = $intAccountId;
			}
			else
			{
				$arrAlreadyIncludedContacts[$intContactId] = array($intAccountId);
			}
			
			$intRequestedOn = strtotime($dboCustomer->RequestedOn->Value);
			$strDate = date("jS M Y", $intRequestedOn);
			if ($strToday == $strDate)
			{
				// The customer was requested today
				$strDate = date("g:i:s a", $intRequestedOn);
			}
			
			if ($dboCustomer->BusinessName->Value)
			{
				$strAccountName = $dboCustomer->BusinessName->HtmlSafeValue;
			}
			elseif ($dboCustomer->TradingName->Value)
			{
				$strAccountName = $dboCustomer->TradingName->HtmlSafeValue;
			}
			else
			{
				$strAccountName = "";
			}
			
			$strAccountLink = Href()->AccountOverview($dboCustomer->AccountId->Value);
			$strAccountCell = "<span onclick='window.location = \"$strAccountLink\"' title='View Account Details'>{$dboCustomer->AccountId->Value}</span>";
			
			$strAccountNameCell = "<a href='$strAccountLink' title='View Account Details' style='color:black'>$strAccountName</a>";
			
			
			if ($dboCustomer->ContactId->Value)
			{
				// A contact is associated with this customer
				$strContactLink = Href()->ViewContact($dboCustomer->ContactId->Value);
				$strContactName = ucwords(strtolower(trim("{$dboCustomer->Title->Value} {$dboCustomer->FirstName->Value} {$dboCustomer->LastName->Value}")));
				$strContactCell = "<a href='$strContactLink' title='View Contact Details' style='color:black;'>$strContactName</a>";
			}
			else
			{
				// A contact is not associated with this customer
				$strContactCell = "[no contact specified]";
			}
			Table()->RecentCustomers->AddRow($strAccountCell, $strAccountNameCell, $strContactCell, $strDate);
		}
		
		if (Table()->RecentCustomers->RowCount() == 0)
		{
			// There are no customers to display
			Table()->RecentCustomers->AddRow("No customers to display");
			Table()->RecentCustomers->SetRowAlignment("left");
			Table()->RecentCustomers->SetRowColumnSpan(4);
		}
		else
		{
			Table()->RecentCustomers->RowHighlighting = TRUE;
		}
		
		// Only draw the scrollable div container if there are more than 10 Customers to display
		if (Table()->RecentCustomers->RowCount() > 10)
		{
			echo "<div class='GroupedContent'>\n";
			echo "<div id='ScrollableContainer_RecentCustomers' style='padding: 0px 3px 0px 3px;overflow:auto; height:265px;'>\n";
			
			Table()->RecentCustomers->Render();
			
			// End the scrollable container div
			echo "</div></div>\n";
		}
		else
		{
			Table()->RecentCustomers->Render();
		}
		
		echo "<div class='ButtonContainer'><div class='right'>\n";
		$this->Button("Close", "Vixen.Popup.Close(this);");
		echo "</div></div>\n";
	}
}

?>
