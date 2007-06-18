<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// token
//----------------------------------------------------------------------------//
/**
 * token
 *
 * Contains all of the Database Token classes
 *
 * Contains all of the Database Token classes
 *
 * @file		token.php
 * @language	PHP
 * @package		ui_app
 * @author		Rich 'Waste Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// PropertyToken
//----------------------------------------------------------------------------//
/**
 * PropertyToken
 *
 * Token Property Object for Database Objects
 *
 * Token Property Object for Database Objects
 *
 *
 * @prefix	tok
 *
 * @package	framework_ui
 * @class	PropertyToken
 */
class PropertyToken
{
	//------------------------------------------------------------------------//
	// Properties
	//------------------------------------------------------------------------//
	private $_dboOwner;
	private $_strProperty;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Token constructor
	 *
	 * Token constructor
	 *
	 * @return	PropertyToken
	 *
	 * @method
	 */
	function __construct()
	{
		$this->_dboOwner	= NULL;
		$this->_strProperty	= NULL;
	}
	
	
	//------------------------------------------------------------------------//
	// Property
	//------------------------------------------------------------------------//
	/**
	 * Property()
	 *
	 * Token Object takes form of the passed Property and returns itself
	 *
	 * Token Object takes form of the passed Property and returns itself
	 *
	 * @param	DBObject		$dboOwner	The owner object
	 * @param	string			
	 *
	 * @return	PropertyToken
	 *
	 * @method
	 */
	function _Property($dboOwner, $strProperty)
	{
		$this->_dboOwner	= $dboOwner;
		$this->_strProperty	= $strProperty;
		//Debug("Token = {$dboOwner->_strName}->$strProperty");
		return $this;
	}
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Accessor for Token Property's... Properties? 
	 *
	 * Accessor for Token Property's... Properties?
	 *
	 * @param	string	$strName	Property's Property
	 * 
	 * @return	mixed
	 *
	 * @method
	 */
	function __get($strName)
	{
		//Debug("Get: {$this->_dboOwner->_strName}->{$this->_strProperty}->$strName");

		// Are we after one of our "magic" variables?
		switch (strtolower($strName))
		{
			// The property's value
			case "value":
				return $this->_dboOwner->_arrProperties[$this->_strProperty];
			// The property's validity
			case "valid":
				return $this->_dboOwner->_arrValid[$this->_strProperty];
		}
		
		// Do we have a Define property by this name?
		$intContext = (int)$this->_dboOwner->_intContext;
		if (isset($this->_dboOwner->_arrDefine[$this->_strProperty][$intContext][$strName]))
		{
			return $this->_dboOwner->_arrDefine[$this->_strProperty][$intContext][$strName];
		}
		
		return NULL;
	}
	
	
	//------------------------------------------------------------------------//
	// __set
	//------------------------------------------------------------------------//
	/**
	 * __set()
	 *
	 * Modifier for Token Property's... Properties? 
	 *
	 * Modifier for Token Property's... Properties?
	 *
	 * @param	string	$strName	Property's Property
	 * @param	mixed	$mixValue	Value to assign
	 * 
	 * @return	boolean				Pass/Fail
	 *
	 * @method
	 */
	function __set($strName, $mixValue)
	{		
		// Validate
		// TODO
		
		// Set the value & return
		switch (strtolower($strName))
		{
			// The property's value
			case "value":
				return (bool)($this->_dboOwner->_arrProperties[$this->_strProperty] = $mixValue);
		}
		
		// Do we have a define property by this name?
		$intContext = (int)$this->_dboOwner->_intContext;
		return $this->_dboOwner->_arrDefine[$this->_strProperty][$intContext][$strName] = $mixValue;
	}
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * 
	 *
	 * 
	 *
	 * @param	dbo		$dboObject		The owner object
	 * @param	string	$strMethod		Method to run
	 * @param	array	$arrArguments	Passed Arguments
	 * 
	 * @return	mixed
	 *
	 * @method
	 */
	/*function __call($strMethod, $arrArguments)
	{
	
	}*/

	//------------------------------------------------------------------------//
	// RenderInput
	//------------------------------------------------------------------------//
	/**
	 * RenderInput()
	 *
	 * Renders the property in its HTML input form
	 *
	 * Renders the property in its HTML input form
	 *
	 * @param	int		$intContext		[optional] The context in which the property will be displayed
	 * @param	bool	$bolRequired	[optional] Whether the field should be mandatory
	 * 
	 * @return	mixed	PropertyValue	returns the value of the property
	 *
	 * @method
	 */
	function RenderInput($intContext=CONTEXT_DEFAULT, $bolRequired=NULL)
	{
		echo $this->_RenderIO("Input", $intContext, $bolRequired);
		
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}

	//------------------------------------------------------------------------//
	// RenderOutput
	//------------------------------------------------------------------------//
	/**
	 * RenderOutput()
	 *
	 * Renders the property in its standard label form
	 *
	 * Renders the property in its standard label form
	 *
	 * @param	int		$intContext		[optional] The context in which the property will be displayed
	 * 
	 * @return	mixed	PropertyValue	returns the value of the property
	 *
	 * @method
	 */
	function RenderOutput($intContext=CONTEXT_DEFAULT)
	{
		echo $this->_RenderIO("Output", $intContext);
		
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}

	//------------------------------------------------------------------------//
	// _RenderIO
	//------------------------------------------------------------------------//
	/**
	 * _RenderIO()
	 *
	 * Renders the property in its specified template
	 *
	 * Renders the property in its specified template
	 *
	 * @param	string	$strType			either "Output" or "Input"
	 * @param	int		$intContext			[optional] The context in which the property will be displayed
	 * @param	bool	$bolRequired		[optional] Whether the field should be mandatory
	 *
	 * @return	mixed						Html generated
	 *										If there was no definition data for this property/context, then returns NULL
	 *
	 * @method
	 */
	private function _RenderIO($strType, $intContext=CONTEXT_DEFAULT, $bolRequired=NULL)
	{
		$intContext = $this->_CalculateContext($intContext);
		
		// require a definition
		if (!$this->_dboOwner->_arrDefine[$this->_strProperty][$intContext])
		{
			//var_dump($this->_dboOwner->_arrDefine);
			//echo "<br />" . $intContext . "=" . CONTEXT_DEFAULT;
			echo "ERROR: Could not render '".$this->_strProperty ."' with context $intContext; No documentation data";
			return NULL;
		}
		
		// build up parameters for HtmlElements
		$arrParams = $this->_BuildParams($intContext, $bolRequired);
		$arrParams['Type'] = $strType;

		return HTMLElements()->$arrParams['Definition'][$strType.'Type']($arrParams);
	}
	
	//------------------------------------------------------------------------//
	// _CalculateContext
	//------------------------------------------------------------------------//
	/**
	 * _CalculateContext()
	 *
	 * Calculates what context to use when rendering a property
	 *
	 * Calculates what context to use when rendering a property 
	 * The property's context can be conditional based on the property's value.
	 * These conditions are defined in the database table ConditionalContexts
	 *
	 * @param	int		$intCurrentContext		The context in which the property will be 
	 *											displayed if it is not subject to conditions
	 * @param	mixed	$mixValue				[optional] the value to check the conditions against.
	 *											if not supplied then the property's actual value will be used
	 * 
	 * @return	int								context to use
	 *
	 * @method
	 */
	private function _CalculateContext($intCurrentContext, $mixValue = NULL)
	{
		// if a value has not been specified then use the current value of the property
		if ($mixValue === NULL)
		{
			$mixValue = $this->_dboOwner->_arrProperties[$this->_strProperty];
		}
		
		$intContext = $intCurrentContext;
		
		// work out if the context of the property is subject to its value
		if (is_array($this->_dboOwner->_arrDefine[$this->_strProperty]['ConditionalContexts']))
		{
			// test each defined condition and use the context of the first one that is found to be true
			foreach ($this->_dboOwner->_arrDefine[$this->_strProperty]['ConditionalContexts'] as $arrCondition)
			{
				if (IsConditionTrue($mixValue, $arrCondition['Operator'], $arrCondition['Value']))
				{
					// set the context to use
					$intContext = $arrCondition['Context'];
					break;
				}
			}
		}

		return $intContext;
	}
	
	//------------------------------------------------------------------------//
	// _BuildParams
	//------------------------------------------------------------------------//
	/**
	 * _BuildParams()
	 *
	 * Builds the parameter array which is then used by the HtmlElements class to render properties
	 *
	 * Builds the parameter array which is then used by the HtmlElements class to render properties
	 *
	 * @param	int		$intContext			context in which the property will be rendered
	 * @param	bool	$bolRequired		[optional] Whether the field should be mandatory
	 * 
	 * @return	array						This array is defined at the top of the HtmlElements class
	 *
	 * @method
	 */
	private function _BuildParams($intContext, $bolRequired=NULL)
	{
		$arrParams = Array();
		$arrParams['Object'] 		= $this->_dboOwner->_strName;
		$arrParams['Property'] 		= $this->_strProperty;
		$arrParams['Context'] 		= $intContext;
		$arrParams['Value'] 		= $this->_dboOwner->_arrProperties[$this->_strProperty];
		$arrParams['Valid'] 		= $this->_dboOwner->_arrValid[$this->_strProperty];
		$arrParams['Required'] 		= $bolRequired;
		$arrParams['Definition'] 	= $this->_dboOwner->_arrDefine[$this->_strProperty][$intContext];

		// work out the base class to use
		$arrParams['Definition']['BaseClass'] = CLASS_DEFAULT; // Default
		if ($arrParams['Valid'] === FALSE)
		{
			$arrParams['Definition']['BaseClass'] .= "Invalid"; // DefaultInvalid
		}
		
		return $arrParams;
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Renders the property's value without formating or mark-up
	 *
	 * Renders the property's value without formating or mark-up
	 *
	 * @param	string	$strOutputMask	[optional] output mask 
	 * 
	 * @return	mixed					property's value
	 *
	 * @method
	 */
	function Render($strOutputMask=NULL)
	{
		$strValue = HTMLElements()->ApplyOutputMask($this->_dboOwner->_arrProperties[$this->_strProperty], $strOutputMask);
		
		echo $strValue;
		return $this->_dboOwner->_arrProperties[$this->_strProperty];		
	}


	//------------------------------------------------------------------------//
	// _Value
	//------------------------------------------------------------------------//
	/**
	 * _Value()
	 *
	 * Used by RenderValue and AsValue to build the html required
	 *
	 * Used by RenderValue and AsValue to build the html required
	 *
	 * @param	int		$intContext		[optional] context in which the property will be displayed
	 * @return	mixed					html code, or NULL
	 * @method
	 */
	private function _Value($intContext=CONTEXT_DEFAULT)
	{
		$intContext = $this->_CalculateContext($intContext);

		// require a definition
		if (!$this->_dboOwner->_arrDefine[$this->_strProperty][$intContext])
		{
			echo "ERROR: Could not render '".$this->_strProperty ."' with context $intContext; No documentation data";
			return NULL;
		}

		// build up parameters
		$arrParams = $this->_BuildParams($intContext);
		
		return HTMLElements()->RenderValue($arrParams);
	}

	//------------------------------------------------------------------------//
	// RenderValue
	//------------------------------------------------------------------------//
	/**
	 * RenderValue()
	 *
	 * Renders the property's value with formating and mark-up
	 *
	 * Renders the property's value with formating and mark-up
	 * The value's accompanying label is not included
	 *
	 * @param	int		$intContext		[optional] context in which the property will be displayed
	 * @return	mixed					property's value
	 * @method
	 */
	function RenderValue($intContext=CONTEXT_DEFAULT)
	{
		echo $this->_Value($intContext);
		
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}
	
	//------------------------------------------------------------------------//
	// AsValue
	//------------------------------------------------------------------------//
	/**
	 * AsValue()
	 *
	 * Returns the html code used to render the property's value with formating and mark-up
	 *
	 * Returns the html code used to render the property's value with formating and mark-up
	 * The value's accompanying label is not included
	 *
	 * @param	int		$intContext		[optional] context in which the property will be displayed
	 * @return	string					html code
	 * @method
	 */
	function AsValue($intContext=CONTEXT_DEFAULT)
	{
		return $this->_Value($intContext);
	}

	//------------------------------------------------------------------------//
	// AsInput
	//------------------------------------------------------------------------//
	/**
	 * AsInput()
	 *
	 * Returns the html code used to render the property as an input
	 *
	 * Returns the html code used to render the property as an input
	 *
	 * @param	int		$intContext		[optional] The context in which the property will be displayed
	 * @param	bool	$bolRequired	[optional] Whether the field should be mandatory
	 * 
	 * @return	string 					html code
	 *
	 * @method
	 */
	function AsInput($intContext=CONTEXT_DEFAULT, $bolRequired=NULL)
	{
		return $this->_RenderIO("Input", $intContext, $bolRequired);
	}

	//------------------------------------------------------------------------//
	// AsOutput
	//------------------------------------------------------------------------//
	/**
	 * AsOutput()
	 *
	 * Returns the html code used to render the property as an output
	 *
	 * Returns the html code used to render the property as an output
	 *
	 * @param	int		$intContext		[optional] The context in which the property will be displayed
	 * 
	 * @return	string					html code
	 *
	 * @method
	 */
	function AsOutput($intContext=CONTEXT_DEFAULT)
	{
		return $this->_RenderIO("Output", $intContext);
	}
	
	//------------------------------------------------------------------------//
	// FormattedValue
	//------------------------------------------------------------------------//
	/**
	 * FormattedValue()
	 *
	 * Returns the property's value, formatted
	 *
	 * Returns the property's value, formatted
	 *
	 * @param	int		$intContext		[optional] context in which the property will be formatted
	 * @return	mixed					property's formatted value
	 *									If the context could not be found
	 *									then NULL is returned
	 *
	 * @method
	 */
	function FormattedValue($intContext=CONTEXT_DEFAULT)
	{
		$intContext = $this->_CalculateContext($intContext);

		// require a definition
		if (!$this->_dboOwner->_arrDefine[$this->_strProperty][$intContext])
		{
			return NULL;
		}

		// build up parameters for HtmlElements
		$arrParams = $this->_BuildParams($intContext);
		
		$strFormattedValue = HTMLElements()->BuildOutputValue($arrParams);
		return $strFormattedValue;
	}
	
	//------------------------------------------------------------------------//
	// _Link
	//------------------------------------------------------------------------//
	/**
	 * _Link()
	 *
	 * Used by RenderLink and AsLink to build the html required
	 *
	 * Used by RenderLink and AsLink to build the html required
	 *
	 * @param	string	$strHref		href for the hyperlink
	 * @param	int		$intContext		[optional] context in which the property will be displayed
	 *
	 * @return	string					html code
	 * @method
	 */
	private function _Link($strHref, $intContext = CONTEXT_DEFAULT)
	{
		$intContext = $this->_CalculateContext($intContext);

		// require a definition
		if (!$this->_dboOwner->_arrDefine[$this->_strProperty][$intContext])
		{
			echo "ERROR: Could not render '".$this->_strProperty ."' with context $intContext; No documentation data";
			return NULL;
		}

		// build up parameters
		$arrParams = $this->_BuildParams($intContext);
		
		return HTMLElements()->RenderLink($arrParams, $strHref);
	}
	
	//------------------------------------------------------------------------//
	// AsLink
	//------------------------------------------------------------------------//
	/**
	 * AsLink()
	 *
	 * Returns the html code used to render the property as a hyperlink
	 *
	 * Returns the html code used to render the property as a hyperlink
	 *
	 * @param	string	$strHref		href for the hyperlink
	 * @param	int		$intContext		[optional] context in which the property will be displayed
	 *
	 * @return	string					html code
	 * @method
	 */
	function AsLink($strHref, $intContext=CONTEXT_DEFAULT)
	{
		return $this->_Link($strHref, $intContext);
	}
	
	//------------------------------------------------------------------------//
	// RenderLink
	//------------------------------------------------------------------------//
	/**
	 * RenderLink()
	 *
	 * Renders the property's value with formating and mark-up within a hyperlink
	 *
	 * Renders the property's value with formating and mark-up within a hyperlink
	 *
	 * @param	string	$strHref		href for the hyperlink
	 * @param	int		$intContext		[optional] context in which the property will be displayed
	 *
	 * @return	mix						property's value
	 * @method
	 */
	function RenderLink($strHref, $intContext=CONTEXT_DEFAULT)
	{
		echo $this->_Link($strHref, $intContext);
		
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}

	//------------------------------------------------------------------------//
	// _Arbitrary
	//------------------------------------------------------------------------//
	/**
	 * _Arbitrary()
	 *
	 * Used by RenderArbitrary and AsArbitrary to build the html required
	 *
	 * Used by RenderArbitrary and AsArbitrary to build the html required
	 *
	 * @param	mixed	$mixValue		href for the hyperlink
	 * @param	int		$intContext		[optional] context in which the property will be displayed
	 *
	 * @return	string					html code
	 * @method
	 */
	private function _Arbitrary($mixValue, $intContext=CONTEXT_DEFAULT)
	{
		$intContext = $this->_CalculateContext($intContext, $mixValue);

		// require a definition
		if (!$this->_dboOwner->_arrDefine[$this->_strProperty][$intContext])
		{
			echo "ERROR: Could not render '".$this->_strProperty ."' with context $intContext; No documentation data";
			return NULL;
		}

		// build up parameters
		$arrParams = $this->_BuildParams($intContext);
		
		// set the arbitrary value as the value to render
		$arrParams['Value'] = $mixValue;
		
		return HTMLElements()->RenderValue($arrParams);
	}
	
	//------------------------------------------------------------------------//
	// AsArbitrary
	//------------------------------------------------------------------------//
	/**
	 * AsArbitrary()
	 *
	 * Returns the html code used to render $mixValue as the value of the property (with formatting and <span> mark-up)
	 *
	 * Returns the html code used to render $mixValue as the value of the property (with formatting and <span> mark-up)
	 * $mixValue will be subject to conditions defined in the ConditionalContexts and UIAppDocumentationOptions tables
	 *
	 * @param	mixed	$mixValue		The value to substitute for the property's value
	 * @param	int		$intContext		[optional] context in which the property will be displayed
	 *
	 * @return	string					html code
	 * @method
	 */
	function AsArbitrary($mixValue, $intContext=CONTEXT_DEFAULT)
	{
		return $this->_Arbitrary($mixValue, $intContext);
	}
	
	//------------------------------------------------------------------------//
	// RenderArbitrary
	//------------------------------------------------------------------------//
	/**
	 * RenderArbitrary()
	 *
	 * Renders $mixValue as the value of the property (with formatting and <span> mark-up)
	 *
	 * Renders $mixValue as the value of the property (with formatting and <span> mark-up)
	 * $mixValue will be subject to conditions defined in the ConditionalContexts and UIAppDocumentationOptions tables
	 *
	 * @param	mixed	$mixValue		The value to substitute for the property's value
	 * @param	int		$intContext		[optional] context in which the property will be displayed
	 *
	 * @return	mixed	PropertyValue	returns the actual value of the property
	 * @method
	 */
	function RenderArbitrary($mixValue, $intContext=CONTEXT_DEFAULT)
	{
		echo $this->_Arbitrary($mixValue, $intContext);
		
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}

	//------------------------------------------------------------------------//
	// _Callback
	//------------------------------------------------------------------------//
	/**
	 * _Callback()
	 *
	 * Used by RenderCallback and AsCallback to build the html required
	 *
	 * Used by RenderCallback and AsCallback to build the html required
	 *
	 * @param	mixed	$mixCallbackFunc	name of the function to call
	 *										This can be specified as "FunctionName"
	 *										or Array("ClassName", "MethodName")
	 * @param	int		$intContext			context in which the property will be displayed
	 * @param	array	$arrAdditionalArgs	[optional] additional arguments required of the callback function
	 *
	 * @return	string					html code
	 * @method
	 */
	private function _Callback($mixCallbackFunc, $intContext, $arrAdditionalArgs=NULL)
	{
		$intContext = $this->_CalculateContext($intContext);

		// require a definition
		if (!$this->_dboOwner->_arrDefine[$this->_strProperty][$intContext])
		{
			echo "ERROR: Could not render '".$this->_strProperty ."' with context $intContext; No documentation data";
			return NULL;
		}

		// build up parameters
		$arrParams = $this->_BuildParams($intContext);
		
		// build arguement array for the callback function
		$arrArgs = Array($arrParams['Value']);
		if (is_array($arrAdditionalArgs))
		{
			$arrArgs = array_merge($arrArgs, $arrAdditionalArgs);
		}
		
		// execute the callback function
		$arrParams['Value'] = call_user_func_array($mixCallbackFunc, $arrArgs);
		
		return HTMLElements()->RenderValue($arrParams);
	}
	
	//------------------------------------------------------------------------//
	// RenderCallback
	//------------------------------------------------------------------------//
	/**
	 * RenderCallback()
	 *
	 * Returns the html code used to render the value of the property (with formatting and <span>) after having modified it using the callback function
	 *
	 * Returns the html code used to render the value of the property (with formatting and <span>) after having modified it using the callback function
	 * The property's value will be the first argument passed to the callback function, 
	 * followed by any other arguements defined in $arrAdditionalArgs
	 *
	 * @param	mixed	$mixCallbackFunc	name of the function to call
	 *										This can be specified as "FunctionName"
	 *										or Array("ClassName", "MethodName")
	 * @param	array	$arrAdditionalArgs	[optional] additional arguments required of the callback function
	 * @param	int		$intContext			[optional] context in which the property will be displayed
	 *
	 * @return	string						html code
	 * @method
	 */
	function AsCallback($strCallbackFunc, $arrAdditionalArgs=NULL, $intContext=CONTEXT_DEFAULT)
	{
		return $this->_Callback($strCallbackFunc, $intContext, $arrAdditionalArgs);
	}
	
	//------------------------------------------------------------------------//
	// RenderCallback
	//------------------------------------------------------------------------//
	/**
	 * RenderCallback()
	 *
	 * Renders the value of the property (with formatting and <span>) after having modified the property's value using the callback function
	 *
	 * Renders the value of the property (with formatting and <span>) after having modified the property's value using the callback function
	 * The property's value will be the first argument passed to the callback function, 
	 * followed by any other arguements defined in $arrAdditionalArgs
	 *
	 * @param	mixed	$mixCallbackFunc	name of the function to call
	 *										This can be specified as "FunctionName"
	 *										or Array("ClassName", "MethodName")
	 * @param	array	$arrAdditionalArgs	[optional] additional arguments required of the callback function
	 * @param	int		$intContext			[optional] context in which the property will be displayed
	 *
	 * @return	mixed	PropertyValue	returns the actual value of the property
	 * @method
	 */
	function RenderCallback($mixCallbackFunc, $arrAdditionalArgs=NULL, $intContext=CONTEXT_DEFAULT)
	{
		echo $this->_Callback($mixCallbackFunc, $intContext, $arrAdditionalArgs);
		
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}
	
	

	//------------------------------------------------------------------------//
	// Validate
	//------------------------------------------------------------------------//
	/**
	 * Validate()
	 *
	 * Validate the property
	 *
	 * Validate the property
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function Valid()
	{
		return $this->_dboOwner->ValidateProperty($this->_strProperty);
	}
	
}



//----------------------------------------------------------------------------//
// MenuToken
//----------------------------------------------------------------------------//
/**
 * MenuToken
 *
 * Token Menu Object for Inteface Context Menu
 *
 * Token Menu Object for Inteface Context Menu
 *
 * @prefix	tok
 *
 * @package	framework_ui
 * @class	MenuToken
 */
class MenuToken
{
	//------------------------------------------------------------------------//
	// Properties
	//------------------------------------------------------------------------//
	private $_objOwner;
	private $_strProperty;
	private $_arrPath;
	private $_strMenu;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Token constructor
	 *
	 * Token constructor
	 *
	 * @return	MenuToken
	 *
	 * @method
	 */
	function __construct()
	{
		$this->_objOwner	= NULL;
		$this->_strMenu		= NULL;
		$this->_arrPath		= NULL;
	}
	
	
	//------------------------------------------------------------------------//
	// NewPath
	//------------------------------------------------------------------------//
	/**
	 * NewPath()
	 *
	 * Token Object takes form of the passed Menu and returns itself
	 *
	 * Token Object takes form of the passed Menu and returns itself
	 *
	 * @param	DBObject		$objOwner		The owner object
	 * @param	string			$strName		The name of the first level in the path
	 *
	 * @return	MenuToken
	 *
	 * @method
	 */
	function NewPath($objOwner, $strName)
	{
		$this->_objOwner	= $objOwner;
		$this->_strMenu		= $strName;
		$this->_arrPath		= Array($strName);
		return $this;
	}
	
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Token Object takes form of the passed Menu and returns itself
	 *
	 * Token Object takes form of the passed Menu and returns itself
	 *
	 * @param	string	$strName	Menu Option name
	 * 
	 * @return	mixed
	 *
	 * @method
	 */
	function __get($strName)
	{
		$this->_strMenu		= $strName;
		$this->_arrPath[]	= $strName;
		return $this;
	}
	
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Creates a new Menu item with this name
	 *
	 * Creates a new Menu item with this name
	 *
	 * @param	string	$strItem		Item to create
	 * @param	array	$arrArguments	Passed Arguments where first and only member should be the value
	 * 
	 * @return	mixed
	 *
	 * @method
	 */
	function __call($strItem, $arrArguments)
	{
		// Dereference the Item
		$arrMenu = &$this->_objOwner->arrProperties;
		foreach ($this->_arrPath as $strPathItem)
		{
			if (!isset($arrMenu[$strPathItem]))
			{
				$arrMenu[$strPathItem] = Array();
			}
			$arrMenu = &$arrMenu[$strPathItem];
		}
		
		// Set item value
		$arrMenu[$strItem]	= $arrArguments;
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * Returns all private data attributes
	 *
	 * Returns all private data attributes
	 *
	 * @return	array
	 *
	 * @method
	 */
	function Info()
	{
		$arrReturn['Owner'] = $this->_objOwner;
		$arrReturn['Property'] = $this->_strProperty;
		$arrReturn['Path'] = $this->_arrPath;
		$arrReturn['Menu'] = $this->_strMenu;
	
		return $arrReturn;
	}
	
	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats a string representing where the menu item is located in the context menu
	 *
	 * Formats a string representing where the menu item is located in the context menu
	 *
	 * @params	string		$strTabs	[optional] used to indent the formatted string.
	 *									if not inlcuded then the string is output.
	 * @return	string
	 *
	 * @method
	 */
	function ShowInfo($strTabs='')
	{
		// recursively printout the names from the array, until it gets to the final
		// one where it will printout value
		$arrMenu = $this->_objOwner->arrProperties;
		foreach ($this->_arrPath as $strPathItem)
		{
			$strOutput .=  $strExtraTabs . $strTabs.$strPathItem . "\n";
			$strExtraTabs .= "\t";
			
			$arrMenu = $arrMenu[$strPathItem];
		}
		
		// remove the last new line char from $strOutput
		$strOutput = substr($strOutput, 0, strlen($strOutput)-1);
		
		// add the parameters associated with the menu token
		$strOutput .= "(";
		foreach ($arrMenu as $strValue)
		{
			$strParams .=  "$strValue, ";
		}
		$strParams = substr($strParams, 0, strlen($strParams)-2);
		$strOutput .= $strParams . ")\n";

		if (!$strTabs)
		{
			Debug($strOutput);
		}
		return $strOutput;
	}
}
?>
