<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// group_new.php
//----------------------------------------------------------------------------//
/**
 * group_new
 *
 * HTML Template for the CustomerGroupNew HTML object
 *
 * HTML Template for the CustomerGroupNew HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add a new Customer Group.
 *
 * @file		group_new.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.01
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateCustomerGroupNew
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateCustomerGroupNew
 *
 * HTML Template class for the CustomerGroupNew HTML object
 *
 * HTML Template class for the CustomerGroupNew HTML object
 * displays the form used to add a new CustomerGroup
 *
 * @package	ui_app
 * @class	HtmlTemplateCustomerGroupNew
 * @extends	HtmlTemplate
 */
class HtmlTemplateCustomerGroupNew extends HtmlTemplate
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
		$this->FormStart("NewCustomerGroup", "CustomerGroup", "Add");

		echo "<div class='WideForm'>\n";

		DBO()->CustomerGroup->InternalName->RenderInput(CONTEXT_DEFAULT, TRUE);
		DBO()->CustomerGroup->ExternalName->RenderInput(CONTEXT_DEFAULT, TRUE);
		DBO()->CustomerGroup->OutboundEmail->RenderInput(CONTEXT_DEFAULT, TRUE);
		
		echo "</div>"; // WideForm
		
		// Create the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "window.location = '". Href()->ViewAllCustomerGroups() ."'");
		$this->AjaxSubmit("Ok");
		echo "</div></div>\n";	

		$this->FormEnd();
	}
}

?>
