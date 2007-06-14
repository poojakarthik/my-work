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
			[Type] => Output
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
	 * Echoes out a formatted HTML input tag, using data from an array to build
	 * the element's attributes like class, name, id and value
	 *
	 * @param	Array	$arrParams			The parameters to use when building the
	 * 										input box (see above for format).
	 * @param	bool	$bolReturnHtml		If FALSE then the html generated is echoed
	 *										If TRUE then the html generated is returned but not echoed
	 * @return	mix							If $bolReturnHtml == FALSE then return the property's value
	 *										Else return the html generated
	 *
	 * @method
	 */
	function InputText($arrParams, $bolReturnHtml=FALSE)
	{
		$strLabel = $arrParams['Definition']['Label'];
		$strValue = nl2br($arrParams['Value']);
		
		$strHtml  = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		// The potentially taller of the two divs must go first
		$strHtml .= "   <div class='{$arrParams['Definition']['BaseClass']}Input {$arrParams['Definition']['Class']}'>\n";
		// create the input box
		$strHtml .= "		<input type='text' name='{$arrParams['Object']}.{$arrParams['Property']}' value='$strValue'/>";
		$strHtml .= "   </div>\n";
		$strHtml .= "   <div class='{$arrParams['Definition']['BaseClass']}Label'>{$strLabel} : </div>\n";
		$strHtml .= "</div>\n";
		
		if ($bolReturnHtml)
		{
			return $strHtml;
		}
		
		echo $strHtml;
		return $arrParams['Value'];
		
		/*
		echo "<td>";
		echo "{$arrParams['Definition']['Label']} : \n";
		echo "</td>";
		echo "<td>";
		echo "<input name='{$arrParams['Object']}.{$arrParams['Property']}' value='{$arrParams['Value']}' class='{$arrParams['Definition']['FullClass']}'></input>";
		echo "</td>";
		*/
	}
	
	//------------------------------------------------------------------------//
	// Label
	//------------------------------------------------------------------------//
	/**
	 * Label()
	 * 
	 * Creates a label
	 * 
	 * Echoes out a formatted HTML div tag, using data from an array to build
	 * the element's attributes like class, id and value
	 * The value of the property is inserted into the OutputLabel string, if 
	 * an appropriate string is defined in the UIAppDocumentation or 
	 * UIAppDocumentationOptions tables of the database
	 *
	 * @param	Array	$arrParams			The parameters to use when building the
	 * 										label (see above for format).
	 * @param	bool	$bolReturnHtml		If FALSE then the html generated is echoed
	 *										If TRUE then the html generated is returned but not echoed
	 * @return	mix							If $bolReturnHtml == FALSE then return the property's value
	 *										Else return the html generated
	 *
	 * @method
	 */
	function Label($arrParams, $bolReturnHtml=FALSE)
	{
		$strLabel = $arrParams['Definition']['Label'];
		$strValue = $this->BuildOutputValue($arrParams);
		$strValue = nl2br($strValue);
		
		$strHtml  = "<div class='{$arrParams['Definition']['BaseClass']}Element'>\n";
		// The potentially taller of the two divs must go first
		$strHtml .= "   <div class='{$arrParams['Definition']['BaseClass']}Output {$arrParams['Definition']['Class']}'>{$strValue}</div>\n";
		$strHtml .= "   <div class='{$arrParams['Definition']['BaseClass']}Label'>{$strLabel} : </div>\n";
		$strHtml .= "</div>\n";
		
		if ($bolReturnHtml)
		{
			return $strHtml;
		}
		
		echo $strHtml;
		return $arrParams['Value'];
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
	 * @param	Array	$arrParams			The parameters to use when building the
	 * 										label (see above for format).
	 * @param	bool	$bolReturnHtml		If FALSE then the html generated is echoed
	 *										If TRUE then the html generated is returned but not echoed
	 * @return	mix							If $bolReturnHtml == FALSE then return the property's value
	 *										Else return the html generated
	 * @method
	 */
	function RenderValue($arrParams, $bolReturnHtml=FALSE)
	{
		$strValue = $this->BuildOutputValue($arrParams);
		$strValue = nl2br($strValue);
		
		// output the formatted value in <span> tags
		$strHtml = "<span class='{$arrParams['Definition']['BaseClass']}OutputSpan {$arrParams['Definition']['Class']}'>{$strValue}</span>\n";

		if ($bolReturnHtml)
		{
			return $strHtml;
		}
		
		echo $strHtml;
		return $arrParams['Value'];
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
	 * @return	mix							If $strOutputString is not null, then it is returned with $mixValue
	 *										substittuted for the placeholder <value>
	 *										Else, $mixValue is returned
	 *
	 * @method
	 */
	private function _OutputValue($mixValue, $strOutputString)
	{
		$mixReturn = $mixValue;
		$strOutputString = trim($strOutputString);
		
		// replace <value> case-insensitive
		if ($strOutputString)
		{
			$mixReturn = str_ireplace("<value>", $mixValue, $strOutputString);
		}
		
		return $mixReturn;
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
					$strValue = $this->_OutputValue($arrParams['Value'], $arrOption['OutputLabel']);
					break;
				}
			}
			
			// if the value has not been found in the list of values in 'Options' then use 
			// the default OutputLabel for this context
			if (!$strValue)
			{
				$strValue = $this->_OutputValue($arrParams['Value'], $arrParams['Definition']['OutputLabel']);
			}
		}
		elseif ($arrParams['Definition']['OutputLabel'])
		{
			// Use the default OutputLabel
			$strValue = $this->_OutputValue($arrParams['Value'], $arrParams['Definition']['OutputLabel']);
		}
		else
		{
			// Use the actual value
			$strValue = $arrParams['Value'];
		}
						
		// An empty string cannot be used 
		if (trim($strValue) == "")
		{
			$strValue = "&nbsp;";
		}
		else
		{
			// Apply the mask as defined in UIAppDocumentation
			//TODO!
		}
		
		return $strValue;
	}
	
	//------------------------------------------------------------------------//
	// CheckBox TODO
	//------------------------------------------------------------------------//
	/**
	 * CheckBox()
	 * 
	 * Creates a check box
	 * 
	 * Echoes out a formatted HTML input check-box tag, using data from an array
	 * to build the element's attributes like class, name, id and value
	 *
	 * @param	Array	$arrParams		The parameters to use when building the
	 * 									input check-box (see above for format).
	 *
	 * @method
	 */
	function CheckBox($arrParams)
	{
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
		echo "<input type='checkbox' name='" . $arrParams['Object'] . $arrParams['Property'] . "' value='{$arrParams['Value']}' class='$strClass'></input>";
		echo "</td>";
	
	}

	//------------------------------------------------------------------------//
	// RadioButtons TODO
	//------------------------------------------------------------------------//
	/**
	 * RadioButtons()
	 * 
	 * Creates a set of linked radio buttons
	 * 
	 * Echoes out a block of formatted HTML input radio tags, using data from an
	 * array to build the element's attributes like class, name, id and value
	 *
	 * @param	Array	$arrParams		The parameters to use when building the
	 * 									input radio-buttons (see above for format).
	 *
	 * @method
	 */
	function Radio($arrParams)
	{
		// an example of Late Payments radio buttons on account_edit.php
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
		echo "<table border='0' cellpadding='3' cellspacing='0'>";
		foreach ($arrParams['OutputOptions'] as $key=>$value)
		{
			echo "<tr>";
			echo "<td>";
			echo "<input type='radio' name='{$arrParams['Property']}' id='{$arrParams['Property']}:$key' value='$key' />";
			echo "</td>";
			echo "<td>";
			echo "<label for='{$arrParams['Property']}:$key'>$value</label>";
			echo "</td>";
			echo "</tr>";
		}

		echo "</table>";
		echo "</td>";
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
