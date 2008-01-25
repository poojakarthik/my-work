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
		// Set up the header information for the table of CustomerGroups
		Table()->CustomerGroups->SetHeader("Internal Name", "External Name", "&nbsp;");
		Table()->CustomerGroups->SetWidth("45%", "45%", "10%");
		Table()->CustomerGroups->SetAlignment("Left", "Left", "Right");

		foreach (DBL()->CustomerGroup as $dboCustomerGroup)
		{
			$strViewCustomerGroupHref = Href()->ViewCustomerGroup($dboCustomerGroup->Id->Value);
			$strViewCustomerGroupLink = "<a href='$strViewCustomerGroupHref' title='View'><img src='img/template/view.png'></img></a>";

			Table()->CustomerGroups->AddRow($dboCustomerGroup->InternalName->AsValue(),
											$dboCustomerGroup->ExternalName->AsValue(),
											$strViewCustomerGroupLink);
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
		
		// Render an "Add new Customer Group" button
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Add New Customer Group", "window.location=\"" . Href()->AddCustomerGroup() . "\"");
		echo "</div></div>\n";
	}
}

?>
