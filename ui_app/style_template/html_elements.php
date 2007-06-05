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
	// Definition of array of parameters which gets passed to all methods
	// Quite a few things still to do:
	//		o For drop downs / radio buttons / check boxes, we need to have some
	//			way of selecting one of them
	//		o Need to write code to add '*' to required inputs
	//		o Need to write code to add javascript validation to elements which
	//			require it (eg, onkeyup=Validate(this) for inputs which are ABNs
	//		o Need to find some way to parse masking data and add values in
	
	/*  ******************This structure is not actually used any more********
	$arrParams['Object'] 		= $this->object;		// 'Account'
	$arrParams['Property'] 		= $this->property;		// 'Id'
	$arrParams['Context'] 		= $this->context;		// DEFAULT
	$arrParams['Definition'] 	= $;					// definition array
	$arrParams['Value'] 		= $this->Value;			// '1000123456'
	$arrParams['Valid']			= $;					// TRUE
	$arrParams['Required'] 		= $bolRequired;			// TRUE
	
	$arrDefinition['ValidationRule']	= $;			// VALID_EMAIL
	$arrDefinition['InputType']	= $;					// 
	$arrDefinition['OutputType']	= $;				//
	$arrDefinition['Label']	= $;						//
	$arrDefinition['InputOptions']	= $;				//
	$arrDefinition['OutputOptions']	= $;				// ['-1'] = "blah <value> blah"
														// ['0']  = "blah bleh blah"
	$arrDefinition['DefaultOutput']	= $;				// "Do not charge for <value> months"
	$arrDefinition['OutputMask']	= $;				// 
	
	*************************************************************************/
	/* 
		An example of the $arrParams array that is passed to each of these functions
		is as follows.  Note that this can have a more complex structure to it if it is
		defining a set of radio buttons, or a similar control
	 
		Array
		(
			[Object] => Account
			[Property] => BusinessName
			[Context] => 0
			[Value] => West Trend Interiors
			[Valid] => 
			[Required] => 
			[Definition] => Array
				(
					[ValidationRule] => 0
					[InputType] => Text
					[OutputType] => Label
					[Label] => Business Name
					[DefaultOutput] => 
					[OutputMask] => 0
					[Class] => Default
					[FullClass] => DefaultOutput
				)
		
		)	
		
		A property that can be displayed as a group of radio buttons, will have the 
		$arrParams structure:
		
		Array
		(
			[Object] => Account
			[Property] => BillingType
			[Context] => 1
			[Value] => 3
			[Valid] => 
			[Required] => 1
			[Definition] => Array
				(
					[ValidationRule] => 0
					[InputType] => ComboBox
					[OutputType] => Radio
					[Label] => Billing Type Context 2
					[DefaultOutput] => hello
					[OutputMask] => 0
					[Class] => Default
					[Options] => Array
						(
							[0] => Array				<-- this is the Group column found in UIAppDocumentation
								(
									[0] => Array
										(
											[Value] => -1
											[Label] => Credit Card
										)
		
									[1] => Array
										(
											[Value] => 2
											[Label] => Direct Debit
										)
		
									[2] => Array
										(
											[Value] => 3
											[Label] => Cheque
										)
		
								)
		
						)
		
					[FullClass] => DefaultOutput
				)
		
		)

		The Options array is an associated array where the key is the "Group" that
		relates to the radio buttons group.  Group should either be "Input" or "Output" or a constant
		defining these two options.

	*/
	
	//------------------------------------------------------------------------//
	// Input
	//------------------------------------------------------------------------//
	/**
	 * Input()
	 * 
	 * Creates an input box
	 * 
	 * Echoes out a formatted HTML input tag, using data from an array to build
	 * the element's attributes like class, name, id and value
	 *
	 * @param	Array	$arrParams		The parameters to use when building the
	 * 									input box (see above for format).
	 *
	 * @method
	 */
	function Input($arrParams)
	{
		// get documentation for label
		$strDocumentation = explode(".",$arrParams['Name']);
		
		// work out the class to use
		if (!$arrParams['Definition']['Class'])
		{
			$arrParams['Definition']['Class'] = CLASS_DEFAULT; // Default
		}
		$strClass = $arrParams['Definition']['FullClass']; // DefaultInput
		if ($arrParams['Valid'] === FALSE)
		{
			$strClass .= "Invalid"; // DefaultInputInvalid
		}
		
		echo "<td>";
		echo "{$arrParams['Definition']['Label']} : \n";
		echo "</td>";
		echo "<td>";
		echo "<input name='{$arrParams['Object']}.{$arrParams['Property']}' value='{$arrParams['Value']}' class='$strClass'></input>";
		echo "</td>";
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
	 *
	 * @param	Array	$arrParams		The parameters to use when building the
	 * 									label (see above for format).
	 *
	 * @method
	 */
	function Label($arrParams)
	{
		echo "	<tr>\n";
		echo "		<td>\n";
		echo "			{$arrParams['Definition']['Label']} : \n";
		echo "		</td>\n";
		echo "		<td>\n";
		echo "			<div class='{$arrParams['Definition']['FullClass']}'>{$arrParams['Value']}</div>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
	}
	
	//------------------------------------------------------------------------//
	// MultiLinedLabel
	//------------------------------------------------------------------------//
	/**
	 * MultiLinedLabel()
	 * 
	 * Creates a label that can have new line characters in it
	 * 
	 * Echoes out a formatted HTML div tag, using data from an array to build
	 * the element's attributes like class, id and value
	 *
	 * @param	Array	$arrParams		The parameters to use when building the
	 * 									label (see above for format).
	 *
	 * @method
	 */
	function MultiLinedLabel($arrParams)
	{
		$strValue = str_replace("\n", "<br>", $arrParams['Value']);
		$strValue = str_replace("\r", "", $strValue);  //data entered using MySqlAdmin which contains new line chars also includes '\r' characters
		echo "	<tr>\n";
		echo "		<td>\n";
		echo "			{$arrParams['Definition']['Label']} : \n";
		echo "		</td>\n";
		echo "		<td>\n";
		echo "			<div class='{$arrParams['Definition']['FullClass']}'>{$strValue}</div>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
	}
	
	//------------------------------------------------------------------------//
	// CheckBox
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
	// RadioButtons
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
	// ComboBox
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
