<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// group_details.php
//----------------------------------------------------------------------------//
/**
 * group_details
 *
 * HTML Template for the details of a CustomerGroup 
 *
 * HTML Template for the details of a CustomerGroup 
 *
 * @file		group_details.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// HtmlTemplateCustomerGroupDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateCustomerGroupDetails
 *
 * HTML Template for the details of a CustomerGroup
 *
 * HTML Template for the details of a CustomerGroup
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateCustomerGroupDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateCustomerGroupDetails extends HtmlTemplate
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
		
		$this->LoadJavascript("customer_group_details");
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
			case HTML_CONTEXT_VIEW:
				$this->_RenderForViewing();
				break;
			case HTML_CONTEXT_EDIT:
				$this->_RenderForEditing();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// _RenderForViewing
	//------------------------------------------------------------------------//
	/**
	 * _RenderForViewing()
	 *
	 * Renders the CustomerGroup Details in "View" mode
	 *
	 * Renders the CustomerGroup Details in "View" mode
	 *
	 * @method
	 */
	private function _RenderForViewing()
	{
		$bolUserIsSuperAdmin = AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN);
		
		echo "<h2 class='CustomerGroup'>Details</h2>\n";
		echo "<div class='GroupedContent'>\n";

		// Render the details of the CustomerGroup
		DBO()->CustomerGroup->InternalName->RenderOutput();
		DBO()->CustomerGroup->ExternalName->RenderOutput();
		DBO()->CustomerGroup->OutboundEmail->RenderOutput();
		DBO()->CustomerGroup->flex_url->RenderOutput();
		DBO()->CustomerGroup->email_domain->RenderOutput();
		DBO()->CustomerGroup->customer_primary_color->RenderOutput();
		DBO()->CustomerGroup->customer_secondary_color->RenderOutput();
		DBO()->CustomerGroup->customer_exit_url->RenderOutput();
		
		echo "</div>\n"; // GroupedContent

		// Render the buttons
		if ($bolUserIsSuperAdmin)
		{
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->Button("Edit Details", "Vixen.CustomerGroupDetails.RenderDetailsForEditing();");
			echo "</div></div>\n";
		}
		else
		{
			echo "<div class='SmallSeparator'></div>\n";
		}

		echo "<h2 class='CustomerGroup'>Add/Change Primary Logo</h2>\n";
		echo "<div class='GroupedContent'>\n";
		foreach($_GET as $key=>$val)
		{
			 $$key=$val;
		}
		print "
			<form enctype=\"multipart/form-data\" action=\"./flex.php/CustomerGroup/ChangeLogo/\" method=\"POST\">
			   <!-- MAX_FILE_SIZE must precede the file input field -->
			   <input type=\"hidden\" name=\"CustomerGroup_Id\" value=\"$CustomerGroup_Id\" />
			   <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"30000\" />
			   <!-- Name of input element determines name in $_FILES array -->
			   Send this file: <input name=\"userfile\" type=\"file\" />
			   <input type=\"submit\" value=\"Send File\" /> (320x60 pixel gif, e.g. voicetalk_logo_320x60.gif)
			</form>
			</div>
			<br/><br/>";

		// Initialise the CustomerGroupDetails object and register the OnCustomerGroupDetailsUpdate Listener
		$intCustomerGroupId = DBO()->CustomerGroup->Id->Value;
		$strJavascript = "Vixen.CustomerGroupDetails.InitialiseView($intCustomerGroupId, '{$this->_strContainerDivId}');";
		echo "<script type='text/javascript'>$strJavascript</script>\n";
	}
	
	//------------------------------------------------------------------------//
	// _RenderForEditing
	//------------------------------------------------------------------------//
	/**
	 * _RenderForEditing()
	 *
	 * Renders the CustomerGroup Details in "Edit" mode
	 *
	 * Renders the CustomerGroup Details in "Edit" mode
	 *
	 * @method
	 */
	private function _RenderForEditing()
	{
	
		$this->FormStart("EditCustomerGroup", "CustomerGroup", "SaveDetails");
		echo "<h2 class='CustomerGroup'>Details</h2>\n";
		echo "<div class='GroupedContent'>\n";

		// Render hidden values
		DBO()->CustomerGroup->Id->RenderHidden();
		
		// Render the details of the CustomerGroup
		DBO()->CustomerGroup->InternalName->RenderInput(CONTEXT_DEFAULT, TRUE, TRUE, Array("attribute:maxlength"=>255, "style:width"=>"650px"));
		DBO()->CustomerGroup->ExternalName->RenderInput(CONTEXT_DEFAULT, TRUE, TRUE, Array("attribute:maxlength"=>255, "style:width"=>"650px"));
		DBO()->CustomerGroup->OutboundEmail->RenderInput(CONTEXT_DEFAULT, TRUE, TRUE, Array("attribute:maxlength"=>255, "style:width"=>"650px"));
		DBO()->CustomerGroup->flex_url->RenderInput(CONTEXT_DEFAULT, TRUE, TRUE, Array("attribute:maxlength"=>255, "style:width"=>"650px"));
		DBO()->CustomerGroup->email_domain->RenderInput(CONTEXT_DEFAULT, TRUE, TRUE, Array("attribute:maxlength"=>255, "style:width"=>"650px"));
		DBO()->CustomerGroup->customer_primary_color->RenderInput(CONTEXT_DEFAULT, TRUE, TRUE, Array("attribute:maxlength"=>255, "style:width"=>"650px"));
		DBO()->CustomerGroup->customer_secondary_color->RenderInput(CONTEXT_DEFAULT, TRUE, TRUE, Array("attribute:maxlength"=>255, "style:width"=>"650px"));
		DBO()->CustomerGroup->customer_exit_url->RenderInput(CONTEXT_DEFAULT, TRUE, TRUE, Array("attribute:maxlength"=>255, "style:width"=>"650px"));
		
		echo "</div>\n"; // GroupedContent

		// Render the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.CustomerGroupDetails.CancelEdit();");
		$this->AjaxSubmit("Commit Changes");
		echo "</div></div>\n";
		
		// Initialise the CustomerGroupDetails object
		$intCustomerGroupId = DBO()->CustomerGroup->Id->Value;
		$strJavascript = "Vixen.CustomerGroupDetails.InitialiseEdit($intCustomerGroupId, '{$this->_strContainerDivId}');";
		echo "<script type='text/javascript'>$strJavascript</script>\n";
		
		$this->FormEnd();
	}
}

?>
