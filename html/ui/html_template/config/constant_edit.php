<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// constant_edit.php
//----------------------------------------------------------------------------//
/**
 * constant_edit
 *
 * HTML Template for the Add/Edit Constant popup
 *
 * HTML Template for the Add/Edit Constant popup
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add/edit a ConfigConstant
 *
 * @file		constant_edit.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateConfigConstantEdit
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateConfigConstantEdit
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateConfigConstantEdit
 * @extends	HtmlTemplate
 */
class HtmlTemplateConfigConstantEdit extends HtmlTemplate
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
		
		//$this->LoadJavascript("config_constant_edit");
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
	 *
	 * @method
	 */
	function Render()
	{
		// ConstantGroups that get loaded into the $GLOBAL['*arrConstant'] array, are considered special,
		// and have several restrictions imposed on them 
		$bolIsSpecialGroup = (DBO()->ConfigConstantGroup->Special->Value)? TRUE : FALSE;
		
		// Start the form
		$this->FormStart("Constant", "Config", "EditConstant");
		echo "<div class='GroupedContent'>\n";
		
		// Render Hidden variables
		DBO()->ConfigConstant->Id->RenderHidden();
		DBO()->ConfigConstantGroup->Id->RenderHidden();
		
		// Display the name of the group that the constant belongs to
		$strConstantGroupName = DBO()->ConfigConstantGroup->Name->Value; 
		DBO()->ConfigConstant->ConstantGroup->RenderArbitrary($strConstantGroupName, RENDER_OUTPUT);
		
		// If the user isn't a GOD user, and the Constant can be edited, but not deleted, then don't allow them
		// to edit the name, description or type
		if ((!AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD)) 
			&& DBO()->ConfigConstant->Id->Value 
			&& (DBO()->ConfigConstant->Editable->Value)
			&& (!DBO()->ConfigConstant->Deletable->Value))
		{
			// The only property that the user can edit is the value
			DBO()->ConfigConstant->Name->RenderOutput();
			DBO()->ConfigConstant->Description->RenderOutput();
			
			if ($bolIsSpecialGroup)
			{
				DBO()->ConfigConstantGroup->Type->RenderCallback("GetConstantDescription", Array("DataType"), RENDER_OUTPUT);
			}
			else
			{
				DBO()->ConfigConstant->Type->RenderCallback("GetConstantDescription", Array("DataType"), RENDER_OUTPUT);
			}
			
		}
		else
		{
			// The user can edit the name, description, datatype and value
			
			// Constant Name
			DBO()->ConfigConstant->Name->RenderInput(CONTEXT_DEFAULT, TRUE);
			
			// It is only manditory for a constant to have a description if it belongs to a special group
			DBO()->ConfigConstant->Description->RenderInput(CONTEXT_DEFAULT, $bolIsSpecialGroup);
			
			// If the constant belongs to a special group, then its DataType cannot be changed, 
			// and its value cannot be set to NULL
			if ($bolIsSpecialGroup)
			{
				DBO()->ConfigConstantGroup->Type->RenderCallback("GetConstantDescription", Array("DataType"), RENDER_OUTPUT);
			}
			else
			{
				// Render a combo box for the data types
				echo "<div class='DefaultElement'>\n";
				echo "   <div class='DefaultLabel' style='padding-left:8px'>Data Type :</div>\n";
				echo "      <select id='DataTypeCombo' name='ConfigConstant.Type' class='DefaultInputComboBox' style='width:100px;'>\n";
				foreach ($GLOBALS['*arrConstant']['DataType'] as $intKey=>$arrValue)
				{
					// Check if this is the currently selected DataType
					$strSelected = (DBO()->ConfigConstant->Type->Value == $intKey) ? "selected='selected'" : "";
					echo "         <option value='$intKey' $strSelected>{$arrValue['Description']}</option>\n";
				}
				echo "      </select>\n";
				echo "</div>\n"; // DefaultElement
				
			}
		}

		if (!$bolIsSpecialGroup)
		{
			// Render a check box for setting the value of the constant to NULL
			DBO()->ConfigConstant->ValueIsNull->RenderInput();
		}
		
		// If the Constant is a boolean then we have to change its value to be either TRUE or FALSE
		if (!DBO()->ConfigConstant->IsInvalid() && DBO()->ConfigConstant->Type->Value == DATA_TYPE_BOOLEAN)
		{
			DBO()->ConfigConstant->Value = ((int)(DBO()->ConfigConstant->Value->Value))? "TRUE" : "FALSE";
		}
		
		// If the value is NULL, then don't display anything as the value
		if (DBO()->ConfigConstant->ValueIsNull->Value)
		{
			DBO()->ConfigConstant->Value = "";
		}
		
		DBO()->ConfigConstant->Value->RenderInput();
		
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			// Render the "Editable" and "Deletable" flags
			DBO()->ConfigConstant->Editable->RenderInput();
			DBO()->ConfigConstant->Deletable->RenderInput();
		}
		
		echo "</div>\n";  // GroupedContent
		
		// Create the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(this);");
		$this->AjaxSubmit("Ok");
		echo "</div></div>\n"; // Buttons
		
		$this->FormEnd();
		
		// Javascript
		$strJsCode = 	"var elmConstName = document.getElementById('ConfigConstant.Name');
						if (elmConstName != null)
						{
							elmConstName.style.width = 234;
						}
						var elmCheckbox = document.getElementById('ConfigConstant.ValueIsNull');
						function DisableValue(bolDisable)
						{
							document.getElementById('ConfigConstant.Value').disabled = bolDisable;
						}
						if (elmCheckbox != null)
						{
							elmCheckbox.addEventListener('change', function(objEvent){DisableValue(objEvent.target.checked)}, true);
							DisableValue(elmCheckbox.checked);
						}";
						
		echo "<script type='text/javascript'>$strJsCode</script>";
	}
}

?>
