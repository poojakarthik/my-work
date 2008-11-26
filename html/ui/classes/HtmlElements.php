<?php

//----------------------------------------------------------------------------//
// HtmlElements.php
//----------------------------------------------------------------------------//
/**
 * HtmlElements.php
 *
 * File containing HTML Elements Class
 *
 * File containing HTML Elements Class
 *
 * @file		html_elements.php
 * @language	PHP
 * @package		ui_app
 * @author		Sean Mailander
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlElements
//----------------------------------------------------------------------------//
/**
 * HtmlElements
 *
 * HtmlElements class
 *
 * HtmlElements class
 *
 *
 * @package	ui_app
 * @class	HtmlElements
 */

class HtmlElements
{
	/* 
		An example of the $arrParams array that is passed to each of these functions
		is as follows.  Note that this can have a more complex structure to it if it is
		defining a set of radio buttons, or a similar control
	 
		Array
		(
			[Object] => Account
			[Property] => Balance
			[Context] => 1
			[Value] => -50000
			[Valid] => 
			[Required] => 
			[Definition] => Array
				(
					[ValidationRule] => 
					[InputType] => NA
					[OutputType] => Label
					[Label] => Balance
					[OutputLabel] => $<value>
					[OutputMask] => 
					[Class] => Red
					[BaseClass] => Default
				)
			[Type] => Output
		)
		
		A property who's output label is dependent on its value (ie radio buttons), will have the 
		$arrParams structure:
		
		Array
		(
			[Object] => Account
			[Property] => BillingType
			[Context] => 0
			[Value] => 3
			[Valid] => 
			[Required] => 
			[Definition] => Array
				(
					[ValidationRule] => 
					[InputType] => Text
					[OutputType] => Label
					[Label] => Billing Type
					[OutputLabel] => Unknown billing type (BillingType = <value>)
					[OutputMask] => 
					[Class] => Default
					[Options] => Array
						(
							[0] => Array
								(
									[Value] => -1
									[OutputLabel] => Not Assigned Yet
									[InputLabel] => 
								)
							[1] => Array
								(
									[Value] => 1
									[OutputLabel] => Credit Card (<value>)
									[InputLabel] => 
								)
							[2] => Array
								(
									[Value] => 2
									[OutputLabel] => Direct Debit (<value>)
									[InputLabel] => 
								)
							[3] => Array
								(
									[Value] => 3
									[OutputLabel] => Cheque (<value>)
									[InputLabel] => 
								)
							[4] => Array
								(
									[Value] => 10
									[OutputLabel] => 
									[InputLabel] => 
								)
						)
					[BaseClass] => Default
				)
			[Type] => Input
		)
	*/


	//------------------------------------------------------------------------//
	// instance
	//------------------------------------------------------------------------//
	/**
	 * instance()
	 *
	 * Returns a singleton instance of this class
	 *
	 * Returns a singleton instance of this class
	 *
	 * @return	__CLASS__
	 *
	 * @method
	 */
	public static function instance()
	{
		static $instance;
		if (!isset($instance))
		{
			$instance = new self();
		}
		return $instance;
	}

	//------------------------------------------------------------------------//
	// InputText
	//------------------------------------------------------------------------//
	/**
	 * InputText()
	 * 
	 * Creates an input with type='text'
	 * 
	 * Returns a formatted HTML input tag, using data from an array to build
	 * the element's attributes like class, name, id and value
	 *
	 * @param	array	$arrParams			parameters to use when building the
	 * 										input box (see above for format).
	 * @return	string						html code
	 *
	 * @method
	 */
	function InputText($arrParams, $arrAdditionalArgs=NULL)
	{
		$strLabel = $arrParams['Definition']['Label'];
		$strStyle = '';
		$strElementAttributes = '';
		
		if (array_key_exists('Valid', $arrParams) && $arrParams['Valid'] === FALSE)
		{
			$strValue = $arrParams['Value'];
		}
		else
		{
			$strValue = $this->BuildInputValue($arrParams);
		}
		//$strValue = nl2br($strValue);
		
		$arrElementAttributes = Array();
		
		$arrStyles = Array();
		// Handle additional arguments
		if (is_array($arrAdditionalArgs))
		{
			foreach ($arrAdditionalArgs as $strArgName=>$mixArgValue)
			{
				$strCommand = strtolower($strArgName);
				if (substr($strCommand, 0, 10) == "attribute:")
				{
					$arrElementAttributes[] = substr($strArgName, 10) . "='$mixArgValue'"; 
				}
				elseif (substr($strCommand, 0, 13) == "setrequiredid")
				{
					// The "Required" span denoting that the field is manditory, requires an id so that it can be manipulated
					$strRequiredIdClause = "id='<id>.Required'";
				}
				elseif (substr($strCommand, 0, 6) == "style:")
				{
					$arrStyles[] = substr($strArgName, 6) . ":$mixArgValue";
				}
			}
			if (count($arrStyles))
			{
				// Some inline styling was defined
				$strStyle = "style='". implode(";", $arrStyles) ."'";
			}
			
			if (count($arrElementAttributes))
			{
				$strElementAttributes = implode(" ", $arrElementAttributes);
			}
		}
		
		// convert any apostrophe's into &#39;
		$strValue = str_replace("'", "&#39;", $strValue);

		$strName	= "{$arrParams['Object']}.{$arrParams['Property']}";
		$strId		= "{$arrParams['Object']}.{$arrParams['Property']}";
		$strClass	=  "{$arrParams['Definition']['BaseClass']}InputText {$arrParams['Definition']['Class']}";
		
		if (isset($strRequiredIdClause))
		{
			$strRequiredIdClause = str_replace("<id>", $strId, $strRequiredIdClause);
		}
		else
		{
			$strRequiredIdClause = '';
		}
		
		$strHtml  = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		// The potentially taller of the two divs must go first
		// create the input box
		$strHtml .= "	<input type='text' id='$strId' name='$strName' value='$strValue' class='$strClass' $strStyle $strElementAttributes/>\n";
		$strHtml .= "   <div id='$strId.Label' class='{$arrParams['Definition']['BaseClass']}Label'>\n";
		$strHtml .= "   <span class='RequiredInput' $strRequiredIdClause>". (($arrParams['Required'])? "*" : "&nbsp") ."</span>\n";
		$strHtml .= "   <span id='$strId.Label.Text'>{$strLabel} : </span></div>\n";
		$strHtml .= "</div>\n";
		
		return $strHtml;
	}	
	
	
	//------------------------------------------------------------------------//
	// InputShortDate
	//------------------------------------------------------------------------//
	/**
	 * InputShortDate()
	 * 
	 * Creates an input with type='text' and an adjacent calendar for setting value
	 * 
	 * Returns a formatted HTML input tag, using data from an array to build
	 * the element's attributes like class, name, id and value
	 *
	 * @param	array	$arrParams			parameters to use when building the
	 * 										input box (see above for format).
	 * @return	string						html code
	 *
	 * @method
	 */
	function InputShortDate($arrParams, $arrAdditionalArgs=NULL)
	{
		$strLabel = $arrParams['Definition']['Label'];
		
		if (array_key_exists('Valid', $arrParams) && $arrParams['Valid'] === FALSE)
		{
			$strValue = $arrParams['Value'];
		}
		else
		{
			$strValue = $this->BuildInputValue($arrParams);
		}
				
		$fromYear = "1900";
		$toYear = "2037";
		$defaultYear = "";
		if (is_array($arrAdditionalArgs))
		{
			if (array_key_exists("FROM_YEAR", $arrAdditionalArgs))
			{
				$fromYear = $arrAdditionalArgs["FROM_YEAR"];
			}
			if (array_key_exists("TO_YEAR", $arrAdditionalArgs))
			{
				$toYear = $arrAdditionalArgs["TO_YEAR"];
			}
			if (array_key_exists("DEFAULT_YEAR", $arrAdditionalArgs))
			{
				$defaultYear = $arrAdditionalArgs["DEFAULT_YEAR"];
			}
		}
		if (!$defaultYear)
		{
			$defaultYear = date("Y");
		}
		
		// convert any apostrophe's into &#39;
		$strValue = str_replace("'", "&#39;", $strValue);

		$strName	= "{$arrParams['Object']}.{$arrParams['Property']}";
		$strId		= "{$arrParams['Object']}.{$arrParams['Property']}";
		$strClass	=  "{$arrParams['Definition']['BaseClass']}InputText {$arrParams['Definition']['Class']}";
		
		$strHtml  = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		$strHtml .= "	<input type='text' id='$strId' name='$strName' value='$strValue' class='$strClass' $defaultYear />\n";
		$strHtml .= "   <a style='position: relative; left: 198px;' href='javascript:DateChooser.showChooser(document.getElementById(\"$strId\"), document.getElementById(\"{$strName}Calender\"), $fromYear, $toYear, \"d/m/Y\", false, true, true, $defaultYear);'><img src='img/template/calendar_small.png' width='16' height='16' title='Calendar date picker' /></a>";
		$strHtml .= "   <div id='{$strName}Calender' class='date-time select-free' style='display: none; visibility: hidden;'></div>";
		$strHtml .= "   <div id='$strId.Label' class='{$arrParams['Definition']['BaseClass']}Label'>\n";
		$strHtml .= "   <span class='RequiredInput'>". (($arrParams['Required'])? "*" : "&nbsp") ."</span>\n";
		$strHtml .= "   <span id='$strId.Label.Text'>{$strLabel} : </span></div>\n";
		$strHtml .= "</div>\n";
		
		return $strHtml;
	}
	
	//------------------------------------------------------------------------//
	// InputPassword
	//------------------------------------------------------------------------//
	/**
	 * InputText()
	 * 
	 * Creates an input with type='text'
	 * 
	 * Returns a formatted HTML input tag, using data from an array to build
	 * the element's attributes like class, name, id and value
	 *
	 * @param	array	$arrParams			parameters to use when building the
	 * 										input box (see above for format).
	 * @return	string						html code
	 *
	 * @method
	 */
	function InputPassword($arrParams)
	{
		$strLabel = $arrParams['Definition']['Label'];
		
		if (array_key_exists('Valid', $arrParams) && $arrParams['Valid'] === FALSE)
		{
			$strValue = $arrParams['Value'];
		}
		else
		{
			$strValue = $this->BuildInputValue($arrParams);
		}
		$strValue = nl2br($strValue);
		
		// convert any apostrophe's into &#39;
		$strValue = str_replace("'", "&#39;", $strValue);

		$strName	= "{$arrParams['Object']}.{$arrParams['Property']}";
		$strId		= "{$arrParams['Object']}.{$arrParams['Property']}";
		$strClass	=  "{$arrParams['Definition']['BaseClass']}InputText {$arrParams['Definition']['Class']}";
		
		$strHtml  = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		// The potentially taller of the two divs must go first
		// create the input box
		$strHtml .= "	<input type='password' id='$strId' name='{$strName}[]' value='$strValue' class='$strClass'/>\n";
		$strHtml .= "   <div id='$strId.Label' class='{$arrParams['Definition']['BaseClass']}Label'>\n";
		$strHtml .= "   <span class='RequiredInput'>". (($arrParams['Required'])? "*" : "&nbsp") ."</span>\n";
		$strHtml .= "   <span id='$strId.Label.Text'>{$strLabel} : </span></div>\n";
		$strHtml .= "</div>\n";
		
		$strHtml  .= "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		// The potentially taller of the two divs must go first
		// create the input box
		$strHtml .= "	<input type='password' id='$strId' name='{$strName}[]' value='$strValue' class='$strClass'/>\n";
		$strHtml .= "   <div id='$strId.Label' class='{$arrParams['Definition']['BaseClass']}Label'>\n";
		$strHtml .= "   <span class='RequiredInput'>". (($arrParams['Required'])? "*" : "&nbsp") ."</span>\n";
		$strHtml .= "   <span id='$strId.Label.Text'>{$strLabel} Confirmation: </span></div>\n";
		$strHtml .= "</div>\n";
		
		return $strHtml;
	}
	
	
	//------------------------------------------------------------------------//
	// InputHidden
	//------------------------------------------------------------------------//
	/**
	 * InputHidden()
	 * 
	 * Creates an input with type='hidden'
	 * 
	 * Returns a formatted HTML input tag, using data from an array to build
	 * the element's attributes like class, name, id and value
	 * Note that this does not modify the value with OutputMask, or OutputLabel.
	 * It just sets the hidden input's value to the value of the property.
	 *
	 * @param	array	$arrParams			parameters to use when building the
	 * 										input box (see above for format).
	 * @return	string						html code
	 *
	 * @method
	 */
	function InputHidden($arrParams)
	{
		// Preprocess the value
		$mixValue = $arrParams['Value'];
		if ($mixValue === FALSE)
		{
			$mixValue = "0";
		}
		elseif ($mixValue === NULL)
		{
			$mixValue = "";
		}
		
		// Convert any apostrophe's into &#39;
		$mixValue = str_replace("'", "&#39;", $mixValue);
		
		$strId = $strName = $arrParams['Object'] .".". $arrParams['Property'];
		
		$strHtml = "<input type='hidden' id='$strId' name='$strName' value='$mixValue'/>\n";
		return $strHtml;
	}
	
	//------------------------------------------------------------------------//
	// TextArea
	//------------------------------------------------------------------------//
	/**
	 * TextArea()
	 * 
	 * Creates a HTML text area
	 * 
	 * Returns a formatted HTML input tag, using data from an array to build
	 * the element's attributes like class, name, id and value
	 *
	 * @param	array	$arrParams			parameters to use when building the
	 * 										text area (see above for format).
	 * @return	string						html code
	 *
	 * @method
	 */
	function TextArea($arrParams)
	{
		$strLabel = $arrParams['Definition']['Label'];
		$strValue = $this->BuildInputValue($arrParams);

		$strName	= "{$arrParams['Object']}.{$arrParams['Property']}";
		$strId		= "{$arrParams['Object']}.{$arrParams['Property']}";
		$strClass	= "{$arrParams['Definition']['BaseClass']}InputTextArea {$arrParams['Definition']['Class']}";
		
		$strHtml  = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		// The potentially taller of the two divs must go first
		// create the text area
		//TODO! Find out if the number of rows and columns in the textarea should be hard coded here
		$strHtml .= "   <textarea id='$strId' name='$strName' class='$strClass' rows='6' style='overflow:auto;'>$strValue</textarea>\n";
		$strHtml .= "   <div id='$strId.Label' class='{$arrParams['Definition']['BaseClass']}Label'>\n";
		$strHtml .= "   <span class='RequiredInput'>". (($arrParams['Required'])? "*" : "&nbsp") ."</span>\n";
		$strHtml .= "   <span id='$strId.Label.Text'>{$strLabel} : </span></div>\n";
		$strHtml .= "</div>\n";
		
		return $strHtml;
	}
	
	//------------------------------------------------------------------------//
	// Label
	//------------------------------------------------------------------------//
	/**
	 * Label()
	 * 
	 * Creates a label
	 * 
	 * Returns a formatted HTML div tag, using data from an array to build
	 * the element's attributes like class, id and value
	 * The value of the property is inserted into the OutputLabel string, if 
	 * an appropriate string is defined in the UIAppDocumentation or 
	 * UIAppDocumentationOptions tables of the database
	 *
	 * @param	array	$arrParams			parameters to use when building the
	 * 										label (see above for format).
	 * @return	string						html code
	 *
	 * @method
	 */
	function Label($arrParams)
	{
		$strLabel = $arrParams['Definition']['Label'];
		$strValue = $this->BuildOutputValue($arrParams);
		
		$strValue = nl2br($strValue);
		
		$strId = "{$arrParams['Object']}.{$arrParams['Property']}";
		
		$strHtml  = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		// The potentially taller of the two divs must go first
		$strHtml .= "   <div id='$strId.Output' name='{$arrParams['Object']}.{$arrParams['Property']}' class='{$arrParams['Definition']['BaseClass']}Output {$arrParams['Definition']['Class']} '>{$strValue}</div>\n";
		$strHtml .= "   <div id='$strId.Label' class='{$arrParams['Definition']['BaseClass']}Label'>\n";
		$strHtml .= "      <span> &nbsp;</span>\n";
		$strHtml .= "      <span id='$strId.Label.Text'>{$strLabel} : </span>\n";
		$strHtml .= "   </div>\n";
		$strHtml .= "</div>\n";

		return $strHtml;
	}
	
	//------------------------------------------------------------------------//
	// RenderValue
	//------------------------------------------------------------------------//
	/**
	 * RenderValue()
	 * 
	 * Renders a value as a label, within <span></span> tags instead of <div> tags
	 * 
	 * Renders a value just like HtmlElements->Label(), except within <span></span> tags instead of <div> tags.
	 * The value's accompanying descriptive label is not rendered
	 *
	 * @param	array	$arrParams			The parameters to use when building the
	 * 										label (see above for format).
	 * @return	string						html code
	 * @method
	 */
	function RenderValue($arrParams)
	{
		$strValue = $this->BuildOutputValue($arrParams);
		
		$strValue = nl2br($strValue);
		
		// output the formatted value in <span> tags
		$strHtml = "<span id='{$arrParams['Object']}.{$arrParams['Property']}.Output' name='{$arrParams['Object']}.{$arrParams['Property']}' class='{$arrParams['Definition']['BaseClass']}OutputSpan {$arrParams['Definition']['Class']}'>{$strValue}</span>\n";

		return $strHtml;
	}
	
	//------------------------------------------------------------------------//
	// RenderLink
	//------------------------------------------------------------------------//
	/**
	 * RenderLink()
	 * 
	 * Renders a value as a hyperlink, within <span></span> tags
	 * 
	 * Renders a value as a hyperlink, within <span></span> tags
	 * The value's accompanying descriptive label is not rendered
	 *
	 * @param	array	$arrParams			The parameters to use when building the
	 * 										label (see above for format).
	 * @param	string	$strHref			href to use
	 * @return	string						html code
	 * @method
	 */
	function RenderLink($arrParams, $strHref)
	{
		// format the value
		$strValue = $this->BuildOutputValue($arrParams);

		// output the formatted value in a hyperlink tag, in a <span> tag
		$strHtml = "<span id='{$arrParams['Object']}.{$arrParams['Property']}.Output' name='{$arrParams['Object']}.{$arrParams['Property']}' class='{$arrParams['Definition']['BaseClass']}OutputSpan {$arrParams['Definition']['Class']}'><a href='{$strHref}'>{$strValue}</a></span>\n";
		
		return $strHtml;
	}
	
	
	
	//------------------------------------------------------------------------//
	// EmailLinkLabel
	//------------------------------------------------------------------------//
	/**
	 * EmailLinkLabel()
	 * 
	 * Renders a property as a "mailto:" hyperlink, within <div></div> tags
	 * 
	 * Renders a property as a "mailto:" hyperlink, within <div></div> tags
	 * The property's accompanying descriptive label is also included
	 *
	 * @param	array	$arrParams			The parameters to use when building the
	 * 										email address (see above for format).
	 * @return	string						html code
	 * @method
	 */
	function EmailLinkLabel($arrParams)
	{
		// The fact that an email address might have the value "no email" is handled by the
		// ConditionalContexts table
	
		// explode on whitespace
		$arrRawEmails = explode(" ", $arrParams['Value']);

		// remove all whitespace and commas from the email addresses
		foreach ($arrRawEmails as $strEmail)
		{
			$strEmail = trim($strEmail, " ,");
			if ($strEmail)
			{
				$arrEmail[] = $strEmail;
			}
		}
		
		// join the emails by separating them with commas
		$strValue = implode(", ", $arrEmail);
		
		$strLabel = $arrParams['Definition']['Label'];
		
		$strHtml  = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		$strHtml .= "   <div id='{$arrParams['Object']}.{$arrParams['Property']}.Output' name='{$arrParams['Object']}.{$arrParams['Property']}' class='{$arrParams['Definition']['BaseClass']}Output {$arrParams['Definition']['Class']}'><a href='mailto:{$strValue}'>{$strValue}</a></div>\n";
		$strHtml .= "   <div id='{$arrParams['Object']}.{$arrParams['Property']}.Label' class='{$arrParams['Definition']['BaseClass']}Label'>{$strLabel} : </div>\n";
		$strHtml .= "</div>\n";

		return $strHtml;
	}
	
	//------------------------------------------------------------------------//
	// CheckBox
	//------------------------------------------------------------------------//
	/**
	 * CheckBox()
	 * 
	 * Creates a check box
	 * 
	 * Creates a check box
	 * Returns a formatted HTML div tag, using data from an array to build
	 * the element's attributes like class, id and value
	 *
	 * @param	array	$arrParams			parameters to use when building the
	 * 										checkbox (see above for format).
	 * @return	string						html code
	 *
	 * @method
	 */
	function CheckBox($arrParams)
	{
		$strLabel = $arrParams['Definition']['Label'];
		
		// determine whether the checkbox should be checked
		$strChecked = "";
		if ($arrParams['Value'])
		{
			$strChecked	= "checked='checked'";
			$intValue	= 1;
		}
		else
		{
			$intValue = 0;
		}
		
		// determine whether the checkbox should be disabled
		$strDisabled = "";
		if ($arrParams['Type'] != RENDER_INPUT)
		{
			$strDisabled	= "disabled";
		}

		// create the name and id for the radio button
		$strName 	= $arrParams['Object'] .".". $arrParams['Property'];
	
		$strHtml  = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		$strHtml .= "   <div class='{$arrParams['Definition']['BaseClass']}InputCheckBox {$arrParams['Definition']['Class']}'>\n";
		if ($arrParams['Required'])
		{
			$strHtml .= "      <span class='RequiredInput'>*</span>\n";
		}		
		$strHtml .= "      <input type='checkbox' class='{$arrParams['Definition']['BaseClass']}InputCheckBox {$arrParams['Definition']['Class']}' id='$strName' $strChecked $strDisabled \n";
		
		// include the onchange javascript to handle the changing of the checkbox
		// 
		$strHtml .= "         onchange='javascript:\n";
		$strHtml .= "            if (this.checked)\n";
		$strHtml .= "            {\n";
		$strHtml .= "               document.getElementById(\"{$strName}_hidden\").value = 1;\n";
		$strHtml .= "            }\n";
		$strHtml .= "            else\n";
		$strHtml .= "            {\n";
		$strHtml .= "               document.getElementById(\"{$strName}_hidden\").value = 0;\n";
		$strHtml .= "            }'\n";
		$strHtml .= "      ></input>\n";
		$strHtml .= "      <label id='$strName.Label' for='$strName'>$strLabel</label>\n";
		$strHtml .= "      <input type='hidden' id='{$strName}_hidden' name='$strName' value='$intValue'></input>\n";
		$strHtml .= "   </div>\n";
		$strHtml .= "</div>\n";
		
		return $strHtml;
	}

	//------------------------------------------------------------------------//
	// CheckBox2
	//------------------------------------------------------------------------//
	/**
	 * CheckBox2()
	 * 
	 * Creates a check box, but arranges the label and checkbox just like the function InputText does
	 * 
	 * Creates a check box, but arranges the label and checkbox just like the function InputText does
	 * Returns a formatted HTML div tag, using data from an array to build
	 * the element's attributes like class, id and value
	 *
	 * @param	array	$arrParams			parameters to use when building the
	 * 										checkbox (see above for format).
	 * @return	string						html code
	 *
	 * @method
	 */
	function CheckBox2($arrParams)
	{
		$strLabel = $arrParams['Definition']['Label'];
		
		// determine whether the checkbox should be checked
		$strChecked = "";
		if ($arrParams['Value'])
		{
			$strChecked	= "checked='checked'";
			$intValue	= 1;
		}
		else
		{
			$intValue = 0;
		}
		
		// determine whether the checkbox should be disabled
		$strDisabled = "";
		if ($arrParams['Type'] != RENDER_INPUT)
		{
			$strDisabled	= "disabled='disabled'";
		}

		// create the name and id for the Checkbox
		$strName 	= "{$arrParams['Object']}.{$arrParams['Property']}";
		$strId		= $strName;

		$strHtml  = "<div class='{$arrParams['Definition']['BaseClass']}Element' style='height:22px;'>\n";

		$strHtml .= "      <input type='checkbox' class='{$arrParams['Definition']['BaseClass']}InputCheckBox2 {$arrParams['Definition']['Class']}' id='$strName' $strChecked $strDisabled \n";
		
		// include the onchange javascript to handle the changing of the checkbox
		// 
		$strHtml .= "         onchange='javascript:\n";
		$strHtml .= "            if (this.checked)\n";
		$strHtml .= "            {\n";
		$strHtml .= "               document.getElementById(\"{$strName}_hidden\").value = 1;\n";
		$strHtml .= "            }\n";
		$strHtml .= "            else\n";
		$strHtml .= "            {\n";
		$strHtml .= "               document.getElementById(\"{$strName}_hidden\").value = 0;\n";
		$strHtml .= "            }'\n";
		$strHtml .= "      ></input>\n";
		$strHtml .= "   <div id='$strId.Label' class='{$arrParams['Definition']['BaseClass']}Label'>\n";
		$strHtml .= "      <span class='RequiredInput'>". (($arrParams['Required'])? "*" : "&nbsp") ."</span>\n";
		$strHtml .= "      <span id='$strId.Label.Text'>{$strLabel} : </span></div>\n";
		$strHtml .= "      <input type='hidden' id='{$strName}_hidden' name='$strName' value='$intValue'></input>\n";
		$strHtml .= "</div>\n";
		
		return $strHtml;
	}


	//------------------------------------------------------------------------//
	// RadioButtons
	//------------------------------------------------------------------------//
	/**
	 * RadioButtons()
	 * 
	 * Creates a set of radio buttons
	 * 
	 * Creates a set of radio buttons
	 * Returns a formatted HTML div tag, using data from an array to build
	 * the element's attributes like class, id and value
	 *
	 * @param	array	$arrParams			parameters to use when building the
	 * 										set of radio buttons (see above for format).
	 * @return	string						html code
	 *
	 * @method
	 */
	function RadioButtons($arrParams)
	{
		$mixValue = $arrParams['Value'];
		
		// if the property is equal to null, then convert this to zero
		if ($mixValue === NULL)
		{
			$mixValue = 0;
		}
		
		if (!is_array($arrParams['Definition']['Options']))
		{
			return "HtmlElements->RadioButtons: ERROR: no options are specified for property {$arrParams['Object']}.{$arrParams['Property']}";
		}

		// determine whether the radio buttons should be disabled
		$strDisabled = "";
		if ($arrParams['Type'] != RENDER_INPUT)
		{
			$strDisabled = "disabled";
		}

		$strHtml = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";

		foreach ($arrParams['Definition']['Options'] as $arrOption)
		{
			// check if this is the option that is currently selected
			$strChecked = "";

			if ($mixValue == $arrOption['Value'])
			{
				$strChecked = "checked";
			}

			//convert any \n placesholders in the radio option's label to actual new line chars and then convert these to <br> tags
			$strOptionLabel = str_replace("\\n", "\n", $arrOption['InputLabel']);
			$strOptionLabel = nl2br($strOptionLabel);

			// create the name and id for the radio button
			$strName 	= $arrParams['Object'] .".". $arrParams['Property'];
			$strId		= $strName ."_". $arrOption['Value'];
			
			// define the button
			$strHtml .= "   <table border='0' cellspacing='0' cellpadding='0' class='{$arrParams['Definition']['BaseClass']}InputRadioButtons {$arrParams['Definition']['Class']}'>\n";
			$strHtml .= "      <tr>\n";
			$strHtml .= "         <td valign='top'>\n";
			$strHtml .= "            <input type='radio' name='$strName' id='$strId' value='{$arrOption['Value']}' $strChecked $strDisabled></input>\n";
			$strHtml .= "         </td>\n";
			$strHtml .= "         <td>\n";
			$strHtml .= "            <div><label id='$strId.Label' for='$strId'>$strOptionLabel</label></div>\n";
			$strHtml .= "         </td>\n";
			$strHtml .= "      </tr>\n";
			$strHtml .= "   </table>\n";
		}
		
		$strHtml .= "</div>\n";

		return $strHtml;
	}

	//------------------------------------------------------------------------//
	// RadioButtons2
	//------------------------------------------------------------------------//
	/**
	 * RadioButtons2()
	 * 
	 * Creates a set of radio buttons, but arranges the label and options just like the function InputText does
	 * 
	 * Creates a set of radio buttons, but arranges the label and options just like the function InputText does
	 * Returns a formatted HTML div tag, using data from an array to build
	 * the element's attributes like class, id and value
	 *
	 * @param	array	$arrParams			parameters to use when building the
	 * 										set of radio buttons (see above for format).
	 * @return	string						html code
	 *
	 * @method
	 */
	function RadioButtons2($arrParams)
	{
		$mixValue = $arrParams['Value'];
		$strLabel = $arrParams['Definition']['Label'];
		
		// if the property is equal to null, then convert this to zero
		if ($mixValue === NULL)
		{
			$mixValue = 0;
		}
		
		if (!is_array($arrParams['Definition']['Options']))
		{
			return "HtmlElements->RadioButtons: ERROR: no options are specified for property {$arrParams['Object']}.{$arrParams['Property']}";
		}

		// determine whether the radio buttons should be disabled
		$strDisabled = "";
		if ($arrParams['Type'] != RENDER_INPUT)
		{
			$strDisabled = "disabled";
		}
		
		$strId = $arrParams['Object'] .".". $arrParams['Property'];

		$strHtml = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";

		$strHtml .= "<table border='0' cellspacing='0' cellpadding='0' width='100%'>\n";
		$strHtml .= "   <tr>\n";
		$strHtml .= "      <td width='195px'>\n";
		$strHtml .= "         <div id='$strId.Label' class='{$arrParams['Definition']['BaseClass']}Label'>\n";
		
		$strHtml .= "         <span class='RequiredInput'>". (($arrParams['Required'])? "*" : "&nbsp") ."</span>\n";
		
		$strHtml .= "            <span id='$strId.Label.Text'>$strLabel : </span>\n";
		$strHtml .= "         </div>\n";
		$strHtml .= "      </td><td>\n";

		foreach ($arrParams['Definition']['Options'] as $arrOption)
		{
			// check if this is the option that is currently selected
			$strChecked = "";

			if ($mixValue == $arrOption['Value'])
			{
				$strChecked = "checked='checked'";
			}

			//convert any \n placesholders in the radio option's label to actual new line chars and then convert these to <br> tags
			$strOptionLabel = str_replace("\\n", "\n", $arrOption['InputLabel']);
			$strOptionLabel = nl2br($strOptionLabel);

			// create the name and id for the radio button
			$strName 	= $arrParams['Object'] .".". $arrParams['Property'];
			$strId		= $strName ."_". $arrOption['Value'];
			
			// define the button
			$strHtml .= "   <table border='0' cellspacing='0' cellpadding='0' class='{$arrParams['Definition']['BaseClass']}InputRadioButtons {$arrParams['Definition']['Class']}'>\n";
			$strHtml .= "      <tr>\n";
			$strHtml .= "         <td valign='top'>\n";
			$strHtml .= "            <input type='radio' name='$strName' id='$strId' value='{$arrOption['Value']}' $strChecked $strDisabled></input>\n";
			$strHtml .= "         </td>\n";
			$strHtml .= "         <td>\n";
			$strHtml .= "            <div><label id='$strId.Label' for='$strId'>$strOptionLabel</label></div>\n";
			$strHtml .= "         </td>\n";
			$strHtml .= "      </tr>\n";
			$strHtml .= "   </table>\n";
		}
		
		$strHtml .= "   </td></tr></table>\n";
		$strHtml .= "</div>\n";

		return $strHtml;
	}
	
	//------------------------------------------------------------------------//
	// ApplyOutputMask
	//------------------------------------------------------------------------//
	/**
	 * ApplyOutputMask()
	 * 
	 * Applies an output mask to a value
	 * 
	 * Applies an output mask to a value
	 * 
	 *
	 * @param	mix		$mixValue			Value to apply the mask to
	 * @param	string	$strMask			mask to apply to the value
	 *
	 * @return	mix							$mixValue formatted to comply with $strMask
	 *										If $strMask is NULL or whitespace, then $mixValue is returned, unchanged.
	 *
	 * @method
	 */
	function ApplyOutputMask($mixValue, $strMask)
	{
		$strMask = trim($strMask);
		
		// Check if the mask is a function call which can be evaluated as a method of the OutputMasks class
		if (strtolower(substr($strMask, 0, 7)) == "method:")
		{
			// The output mask is a method of the Validation class, complete with parameters and a <value> placeholder
			// Prepare it for execution using eval()
			$strMethod = substr($strMask, 7);
			
			// Only grab the first line of code (this should protect against malicious code)
			$arrMethodParts = explode(";", $strMethod, 2);
			$strMethod = $arrMethodParts[0];
			$strMethod = trim($strMethod);

			//Prepare the value for cases where it needs to be converted
			if ($mixValue === NULL)
			{
				$mixValue = "NULL";
			}
			elseif ($mixValue === FALSE)
			{
				$mixValue = "FALSE";
			}
			elseif ($mixValue === TRUE)
			{
				$mixValue = "TRUE";
			}
			
			// Restrict the chars allowed to protect against malicious code
			if (!preg_match("/^[a-z0-9]+ *\(?[a-z0-9,\<\>\" ]*\)?$/i", $strMethod))
			{
				die("Invalid OutputMask: $strMethod");
			}

			// Replace the value placeholder with $mixValue
			$strMethod = str_replace("<value>", $mixValue, $strMethod);
			
			$strCodeToEval = "return OutputMask()->$strMethod;";
			
			$mixValue = eval($strCodeToEval);
			
			return $mixValue;
		}
		
		if ($strMask)
		{
			switch ($strMask)
			{
				case "Currency2DecPlaces":
					// remove the dollar sign if it is already present
					$mixValue = ltrim($mixValue, '$');
					
					$mixValue = OutputMask()->MoneyValue($mixValue, 2, TRUE);
					break;
				case "Currency2DecWithNegAsCR":
					// remove the dollar sign if it is already present
					$mixValue = ltrim($mixValue, '$');
					
					if ($mixValue < 0)
					{
						// Remove the negative sign and append "CR" to the formatted value
						$mixValue = $mixValue * (-1);
						$mixValue = OutputMask()->MoneyValue($mixValue, 2, TRUE) . " CR";
					}
					else
					{
						$mixValue = OutputMask()->MoneyValue($mixValue, 2, TRUE);
					}
					break;
				case "Currency4DecPlaces":
					// remove the dollar sign if it is already present
					$mixValue = ltrim($mixValue, '$');
					
					$mixValue = OutputMask()->MoneyValue($mixValue, 4, TRUE);
					break;
				case "Currency8DecPlaces":
					// remove the dollar sign if it is already present
					$mixValue = ltrim($mixValue, '$');
					
					$mixValue = OutputMask()->MoneyValue($mixValue, 8, TRUE);
					break;
				default:
					// Try running the name of the output mask, as a method of OutputMask()
					$mixValue = OutputMask()->{$strMask}($mixValue);
					break;
			}
		}
		
		return $mixValue;
	}
	
	//------------------------------------------------------------------------//
	// _OutputValue
	//------------------------------------------------------------------------//
	/**
	 * _OutputValue()
	 * 
	 * Formats an output string based on the value and output string passed in
	 * 
	 * Formats an output string based on the value and output string passed in
	 * 
	 *
	 * @param	mix		$mixValue			Value to use in the output string
	 * @param	string	$strOutputString	String to output. This can utilise the <value> placeholder
	 * @param	string	$strMask			mask to apply to the value, before the value is embedded in $strOutputString
	 *
	 * @return	mix							If $strOutputString is not null, then it is returned with $mixValue
	 *										substittuted for the placeholder <value>
	 *										Else, $mixValue is returned
	 *
	 * @method
	 */
	private function _OutputValue($mixValue, $strOutputString, $strMask)
	{
		$strOutputString = trim($strOutputString);

		// apply output mask to the value (if defined in UIAppDocumentation)
		$mixValue = $this->ApplyOutputMask($mixValue, $strMask);
		
		// replace <value> case-insensitive
		if ($strOutputString)
		{
			$mixValue = str_ireplace("<value>", $mixValue, $strOutputString);
		}
		
		return $mixValue;
	}

	//------------------------------------------------------------------------//
	// BuildInputValue
	//------------------------------------------------------------------------//
	/**
	 * BuildInputValue()
	 * 
	 * Builds the input value based on the property definition in UIAppDocumentation and UIAppDocumentationOptions tables
	 * 
	 * Builds the input value based on the property definition in UIAppDocumentation and UIAppDocumentationOptions tables
	 * 
	 *
	 * @param	Array	$arrParams		The standard set of parameters passed to all HtmlElement public methods
	 * 									(see above for format).
	 * @return	string					The value to output as the default input for the property.
	 *
	 * @method
	 */
	function BuildInputValue($arrParams)
	{
		$strValue = $this->BuildOutputValue($arrParams);

		// BuildOutputValue will never return an empty string, but BuildInputValue should be able to
		if ("$strValue" == "&nbsp;")
		{
			$strValue = "";
		}
		
		return $strValue;
	}
	
	//------------------------------------------------------------------------//
	// BuildOutputValue
	//------------------------------------------------------------------------//
	/**
	 * BuildOutputValue()
	 * 
	 * Builds the output value based on the property definition in UIAppDocumentation and UIAppDocumentationOptions tables
	 * 
	 * Builds the output value based on the property definition in UIAppDocumentation and UIAppDocumentationOptions tables
	 * 
	 *
	 * @param	Array	$arrParams		The standard set of parameters passed to all HtmlElement public methods
	 * 									(see above for format).
	 * @return	string					The value to output.  This will never be an empty string
	 *									at the very least it will be "&nbsp;"
	 *
	 * @method
	 */
	function BuildOutputValue($arrParams)
	{
		$strValue = NULL;
		
		// Check if the output mask should be applied
		if ($arrParams['ApplyOutputMask'] === FALSE)
		{
			// Don't apply output mask
			$strValue = $arrParams['Value'];
		}
		elseif (array_key_exists('Options', $arrParams['Definition']) && is_array($arrParams['Definition']['Options']))
		{			
			// Find the correct output label to use instead of the value
			foreach ($arrParams['Definition']['Options'] as $arrOption)
			{
				if ($arrParams['Value'] == $arrOption['Value'])
				{
					// Set the new value to output
					$strValue = $this->_OutputValue($arrParams['Value'], $arrOption['OutputLabel'], $arrParams['Definition']['OutputMask']);
					break;
				}
			}
			
			// If the value has not been found in the list of values in 'Options' then use 
			// the default OutputLabel for this context
			if (!$strValue)
			{
				$strValue = $this->_OutputValue($arrParams['Value'], $arrParams['Definition']['OutputLabel'], $arrParams['Definition']['OutputMask']);
			}
		}
		else
		{
			// Use the default OutputLabel
			$strValue = $this->_OutputValue($arrParams['Value'], $arrParams['Definition']['OutputLabel'], $arrParams['Definition']['OutputMask']);
		}

		// An empty string cannot be used 
		if (trim($strValue) == "")
		{
			$strValue = "&nbsp;";
		}
		
		return $strValue;
	}
	

	//------------------------------------------------------------------------//
	// ComboBox
	//------------------------------------------------------------------------//
	/**
	 * ComboBox()
	 * 
	 * Creates a ComboBox, using values in the exact same way that the RadioButtons works
	 * 
	 * Creates a ComboBox, using values in the exact same way that the RadioButtons works
	 * Returns a formatted HTML div tag, using data from an array to build
	 * the element's attributes like class, id and value
	 *
	 * @param	Array	$arrParams		The parameters to use when building the
	 * 									combo box (see above for format).
	 *
	 * @method
	 */
	function ComboBox($arrParams, $arrAdditionalArgs)
	{
		$mixValue = $arrParams['Value'];
		$strLabel = $arrParams['Definition']['Label'];
		$bolValueFound = FALSE;
		
		// If the property is equal to null, then convert this to zero
		if ($mixValue === NULL)
		{
			$mixValue = 0;
		}
		
		if (!is_array($arrParams['Definition']['Options']))
		{
			return "HtmlElements->ComboBox: ERROR: no options are specified for property {$arrParams['Object']}.{$arrParams['Property']}";
		}
		
		// Handle additional arguments
		$arrElementAttributes = Array();
		$arrStyles = Array();
		if (is_array($arrAdditionalArgs))
		{
			foreach ($arrAdditionalArgs as $strArgName=>$mixArgValue)
			{
				$strCommand = strtolower($strArgName);
				if (substr($strCommand, 0, 10) == "attribute:")
				{
					$arrElementAttributes[] = substr($strArgName, 10) . "='$mixArgValue'"; 
				}
				elseif (substr($strCommand, 0, 6) == "style:")
				{
					$arrStyles[] = substr($strArgName, 6) . ":$mixArgValue";
				}
			}
			if (count($arrStyles))
			{
				// Some inline styling was defined
				$strStyle = "style='". implode(";", $arrStyles) ."'";
			}
			
			if (count($arrElementAttributes))
			{
				$strElementAttributes = implode(" ", $arrElementAttributes);
			}
		}
		
		// Convert any apostrophe's into &#39;
		$strValue = str_replace("'", "&#39;", $strValue);
		
		$strId		= $arrParams['Object'] .".". $arrParams['Property'];
		$strName	= $strId;
		$strClass	= "{$arrParams['Definition']['BaseClass']}InputComboBox {$arrParams['Definition']['Class']}";

		// Determine whether the ComboBox should be disabled
		$strDisabled = "";
		if ($arrParams['Type'] != RENDER_INPUT)
		{
			$strDisabled = "disabled='disabled'";
		}

		$strHtml = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		$strHtml .= "   <div class='DefaultLabel'>\n";
		$strHtml .= "      <span class='RequiredInput'>". (($arrParams['Required'])? "*" : "&nbsp;") ."</span>\n";
		$strHtml .= "      <span id='$strId.Label.Text'>{$strLabel} : </span>\n";
		$strHtml .= "   </div>\n"; // DefaultLabel
		
		$strHtml .= "   <select id='$strId' name='$strName' class='$strClass' $strElementAttributes $strStyle $strDisabled>\n";
		
		// Add each option to the combo box, in the order that they have been defined in the UIAppDocumentationOptions table
		foreach ($arrParams['Definition']['Options'] as $arrOption)
		{
			$strOptionId = $strId ."_". $arrOption['Value'];
			
			$strSelected = "";
			if ($mixValue == $arrOption['Value'])
			{
				$strSelected = "selected='selected'";
				$bolValueFound = TRUE;
			}
			
			$strHtml .= "<option id='$strOptionId' value='{$arrOption['Value']}' $strSelected>{$arrOption['InputLabel']}</option>\n";
		}
		if (!$bolValueFound)
		{
			// The value of the property has not been found yet.  Add it as an option
			$strHtml .= "<option id='{$strId}_{$mixValue}' value='$mixValue' selected='selected'>$mixValue</option>\n";
		}
		
		$strHtml .= "   </select>\n";
		$strHtml .= "</div>\n"; // DefaultElement

		return $strHtml;
	}

	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 * 
	 * Handles undefined methods
	 * 
	 * If the called method does not exist, this function will execute and pass
	 * off to a default HTML element to output something
	 *
	 * @param   String  $strMethodName  The name of the called method
	 * @param	Array	$arrParams		The parameters which were passed in
	 *
	 * @method
	 */	
	function __call($strMethodName, $arrMethodParams)
    {
		$arrParams = $arrMethodParams[0];
		echo "<td>";
		echo "<div>$strMethodName() was called with paramaters: <br />{$arrParams['Value']}</div>";
		echo "</td>";
    }
	
	
}


?>
