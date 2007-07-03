<?php

//----------------------------------------------------------------------------//
// html_elements.php
//----------------------------------------------------------------------------//
/**
 * html_elements.php
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
// HTMLElements
//----------------------------------------------------------------------------//
/**
 * HTMLElements
 *
 * HTML Elements class
 *
 * HTML Elements class
 *
 *
 * @package	ui_app
 * @class	HTMLElements
 */

class HTMLElements
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
	function InputText($arrParams)
	{
		$strLabel = $arrParams['Definition']['Label'];
		$strValue = $this->BuildOutputValue($arrParams);
		$strValue = nl2br($strValue);
		
		$strName	= "{$arrParams['Object']}.{$arrParams['Property']}";
		$strId		= "{$arrParams['Object']}.{$arrParams['Property']}";
		$strClass	= "{$arrParams['Definition']['BaseClass']}Input {$arrParams['Definition']['Class']}";
		
		$strHtml  = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		// The potentially taller of the two divs must go first
		// create the input box
		$strHtml .= "		<input type='text' id='$strId' name='$strName' value='$strValue' class='$strClass'/>\n";
		$strHtml .= "   <div id='$strId.Label' class='{$arrParams['Definition']['BaseClass']}Label'>{$strLabel} : </div>\n";
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
		$strValue = $arrParams['Value'];
		$strHtml .= "<input type='hidden' id='{$arrParams['Object']}.{$arrParams['Property']}' name='{$arrParams['Object']}.{$arrParams['Property']}' value='$strValue'/>\n";
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
		$strValue = $this->BuildOutputValue($arrParams);
		
		$strHtml  = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		// The potentially taller of the two divs must go first
		$strHtml .= "   <div class='{$arrParams['Definition']['BaseClass']}Input {$arrParams['Definition']['Class']}'>\n";
		// create the text area
		//TODO! Find out if the number of rows and columns in the textarea should be hard coded here
		$strHtml .= "		<textarea id='{$arrParams['Object']}.{$arrParams['Property']}' name='{$arrParams['Object']}.{$arrParams['Property']}' rows='6' cols='30'>$strValue</textarea>\n";
		$strHtml .= "   </div>\n";
		$strHtml .= "   <div id='{$arrParams['Object']}.{$arrParams['Property']}.Label' class='{$arrParams['Definition']['BaseClass']}Label'>{$strLabel} : </div>\n";
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
		
		$strHtml  = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		// The potentially taller of the two divs must go first
		$strHtml .= "   <div id='{$arrParams['Object']}.{$arrParams['Property']}' name='{$arrParams['Object']}.{$arrParams['Property']}' class='{$arrParams['Definition']['BaseClass']}Output {$arrParams['Definition']['Class']} '>{$strValue}</div>\n";
		$strHtml .= "   <div id='{$arrParams['Object']}.{$arrParams['Property']}.Label' class='{$arrParams['Definition']['BaseClass']}Label'>{$strLabel} : </div>\n";
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
		$strHtml = "<span id='{$arrParams['Object']}.{$arrParams['Property']}' name='{$arrParams['Object']}.{$arrParams['Property']}' class='{$arrParams['Definition']['BaseClass']}OutputSpan {$arrParams['Definition']['Class']}'>{$strValue}</span>\n";

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
		$strHtml = "<span id='{$arrParams['Object']}.{$arrParams['Property']}' name='{$arrParams['Object']}.{$arrParams['Property']}' class='{$arrParams['Definition']['BaseClass']}OutputSpan {$arrParams['Definition']['Class']}'><a href='{$strHref}'>{$strValue}</a></span>\n";
		
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
		$strHtml .= "   <div id='{$arrParams['Object']}.{$arrParams['Property']}' name='{$arrParams['Object']}.{$arrParams['Property']}' class='{$arrParams['Definition']['BaseClass']}Output {$arrParams['Definition']['Class']}'><a href='mailto:{$strValue}'>{$strValue}</a></div>\n";
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
			$strChecked	= "checked";
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
		$strHtml .= "      <input type='checkbox' id='$strName' $strChecked $strDisabled \n";
		
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
		
		if (!is_array($arrParams['Definition']['Options']))
		{
			return "HtmlElements->Radio: ERROR: no options are specified for property {$arrParams['Object']}.{$arrParams['Property']}";
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
			
			// create the name and id for the radio button
			$strName 	= $arrParams['Object'] .".". $arrParams['Property'];
			//$strId		= $strRadioName ."(". $arrOption['Value'] .")";
			$strId		= $strRadioName ."_". $arrOption['Value'];
			
			// define the button
			$strHtml .= "   <div class='{$arrParams['Definition']['BaseClass']}InputRadioButtons {$arrParams['Definition']['Class']}'>\n";
			$strHtml .= "      <input type='radio' name='$strName' id='$strId' value='{$arrOption['Value']}' $strChecked $strDisabled></input>\n";
			$strHtml .= "      <label id='$strId.Label' for='$strId'>{$arrOption['InputLabel']}</label>\n";
			$strHtml .= "   </div>\n";
		}
		
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
		if ($strMask)
		{
			switch ($strMask)
			{
				case "Currency2DecPlaces":
					if ($mixValue < 0)
					{
						// negative currency value
						$mixValue = $mixValue * (-1.0);
						$mixValue = "($". number_format($mixValue, 2, ".", ",") .")";
					}
					else
					{
						// possitive currency value
						$mixValue = "$". number_format($mixValue, 2, ".", ",") ."";
					}
					break;
				case "ShortDate":
					// MySql Dates are of the format YYYY-MM-DD
					// convert this to DD/MM/YYYY
					$arrDate = explode("-", $mixValue);
					$mixValue = $arrDate[2] ."/". $arrDate[1] ."/". $arrDate[0];
					break;
				case "LongDateAndTime":
					// MySql Datetime is in the format YYYY-MM-DD HH:MM:SS
					// convert this to the format "Wednesday, Jun 21, 2007 11:36:54 AM"
					$arrDateAndTime = explode(" ", $mixValue);
					$arrTime = explode(":", $arrDateAndTime[1]);
					$arrDate = explode("-", $arrDateAndTime[0]);
					$intUnixTime = mktime($arrTime[0], $arrTime[1], $arrTime[2], $arrDate[1], $arrDate[2], $arrDate[0]);
					$mixValue = date("l, M j, Y g:i:s A", $intUnixTime);
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

		// check if there is any options data related to this property
		if (is_array($arrParams['Definition']['Options']))
		{			
			// find the correct output label to use instead of the value
			foreach ($arrParams['Definition']['Options'] as $arrOption)
			{
				if ($arrParams['Value'] == $arrOption['Value'])
				{
					// set the new value to output
					$strValue = $this->_OutputValue($arrParams['Value'], $arrOption['OutputLabel'], $arrParams['Definition']['OutputMask']);
					break;
				}
			}
			
			// if the value has not been found in the list of values in 'Options' then use 
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
	// ComboBox TODO
	//------------------------------------------------------------------------//
	/**
	 * ComboBox()
	 * 
	 * Creates a combo box
	 * 
	 * Echoes out a block of formatted HTML select/option tags, using data from
	 * an array to build the element's attributes like class, name, id and value
	 *
	 * @param	Array	$arrParams		The parameters to use when building the
	 * 									combo box (see above for format).
	 *
	 * @method
	 */
	function ComboBox($arrParams)
	{
		// $arrParams has an array of keys=>values for the options

		// get documentation for label
		$strDocumentation = explode(".",$arrParams['Name']);
		
		// work out the class to use
		if (!$arrParams['Definition']['Class'])
		{
			$arrParams['Definition']['Class'] = CLASS_DEFAULT; // Default
		}
		$strClass = $arrParams['Definition']['Class']."Input"; // DefaultInput
		if ($arrParams['Valid'] === FALSE)
		{
			$strClass .= "Invalid"; // DefaultInputInvalid
		}
		
		echo "<td>";
		echo "$strDocumentation[1]:";
		echo "</td>";
		echo "<td>";
		echo "<select name='{$arrParams['Property']}'>";
		foreach ($arrParams['OutputOptions'] as $key=>$value)
		{
			echo "<option value='$key'>$value</option>";
		}
		echo "</select>";
		echo "</td>";
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
