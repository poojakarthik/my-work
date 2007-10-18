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
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	public $_intContext;

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
		echo "<div class='PopupLarge'>\n";
		
		// Work out if a virtical scroll bar will be required
		$strTableContainerStyle = (DBL()->Contact->RecordCount() > 14) ? "style='overflow:auto; height:450px'": "";
		
		// Draw the table container
		echo "<div $strTableContainerStyle>\n";

		Table()->ContactTable->SetHeader("Last Name", "First Name", "Title", "User Name", "Status", "Actions");
		Table()->ContactTable->SetWidth("20%", "20%", "10%","20%","10%", "20%");
		Table()->ContactTable->SetAlignment("Left", "Left", "Left", "Left", "Left", "Center");
		
		foreach (DBL()->Contact as $dboContact)
		{
			// Record the Status of the Contact
			$strStatus = ($dboContact->Archived->Value) ? "Archived" : "Active";
			$strStatusCell = "<span class='DefaultOutputSpan'>$strStatus</span>";
			
			// Build the Actions Cell
			$strViewContactLink = Href()->ViewContact($dboContact->Id->Value);
			$strActionsCell = "<a href='$strViewContactLink'><span class='DefaultOutputSpan'>View</span></a>";
		
			Table()->ContactTable->AddRow(	$dboContact->LastName->AsValue(),
											$dboContact->FirstName->AsValue(),
											$dboContact->Title->AsValue(),
											$dboContact->UserName->AsValue(),
											$strStatusCell,
											$strActionsCell);
					
		}
		
		// If the account has no contacts then output an appropriate message in the table
		if (Table()->ContactTable->RowCount() == 0)
		{
			// There are no services to stick in this table
			Table()->ContactTable->AddRow("<span class='DefaultOutputSpan Default'>No contacts to display</span>");
			Table()->ContactTable->SetRowAlignment("left");
			Table()->ContactTable->SetRowColumnSpan(6);
		}
		else
		{
			// This doesn't seem to be working.  Has it ever worked for popups?
			Table()->ContactTable->RowHighlighting = TRUE;
		}
		
		Table()->ContactTable->Render();
		
		echo "</div>\n";  // Table Container
	
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Close", "Vixen.Popup.Close(this);");
		echo "</div></div>\n";

		echo "</div>\n";  //PopupLarge
	}
}

?>
