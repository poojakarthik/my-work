<?php
//----------------------------------------------------------------------------//
// HtmlTemplateCustomerGroupList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateCustomerGroupList
 *
 * Lists all Customer Groups in a table
 *
 * Lists all Customer Groups in a table
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateCustomerGroupList
 * @extends	HtmlTemplate
 */
class HtmlTemplateCustomerGroupList extends HtmlTemplate
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
	 * @param	string	$_strId			the id of the div that this HtmlTemplate is rendered in
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
		$bolUserIsSuperAdmin = AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN);
		
		// Set up the header information for the table of CustomerGroups
		Table()->CustomerGroups->SetHeader("Internal Name", "External Name");
		Table()->CustomerGroups->SetWidth("50%", "50%");
		Table()->CustomerGroups->SetAlignment("Left", "Left");

		foreach (DBL()->CustomerGroup as $dboCustomerGroup)
		{
			$strViewCustomerGroupHref = Href()->ViewCustomerGroup($dboCustomerGroup->Id->Value);

			Table()->CustomerGroups->AddRow($dboCustomerGroup->InternalName->AsValue(),
											$dboCustomerGroup->ExternalName->AsValue()
											);
			Table()->CustomerGroups->SetOnClick("window.location = '$strViewCustomerGroupHref'");
		}
		
		// Check if the table is empty
		if (Table()->CustomerGroups->RowCount() == 0)
		{
			// There are no CustomerGroups to stick in this table
			Table()->CustomerGroups->AddRow("<span>No Customer Groups to display</span>");
			Table()->CustomerGroups->SetRowAlignment("left");
			Table()->CustomerGroups->SetRowColumnSpan(3);
		}
		else
		{
			Table()->CustomerGroups->RowHighlighting = TRUE;
		}
				
		Table()->CustomerGroups->Render();
		
		// Render an "Add new Customer Group" button, if the user is allowed to add a new customer group
		if ($bolUserIsSuperAdmin)
		{
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->Button("Add New Customer Group", "window.location=\"" . Href()->AddCustomerGroup() . "\"");
			echo "</div></div>\n";
		}
	}
}

?>
