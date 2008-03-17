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
			case "isset":
				return isset($this->_dboOwner->_arrProperties[$this->_strProperty]);
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
	// Trim
	//------------------------------------------------------------------------//
	/**
	 * Trim()
	 *
	 * Performs a Trim operation on the property
	 *
	 * Performs a Trim operation on the property
	 *
	 * @param	string	$strTrimType	optional, the type of trim to perform (trim, ltrim, rtrim)
	 *									Defaults to trim
	 * @param	string	$strCharList	optional, list of chars to strip.  Defaults 
	 *									to the default of the trim method (usually 
	 *									all whitespace chars)
	 * 
	 * @method
	 */
	function Trim($strTrimType=NULL, $strCharList=NULL)
	{
		switch (strtolower($strTrimType))
		{
			case "ltrim":
				$strFunc = "ltrim";
				break;
			case "rtrim":
				$strFunc = "rtrim";
				break;
			default:
				$strFunc = "trim";
				break;
		}
		
		$arrArgs = Array();
		$arrArgs[] = $this->_dboOwner->_arrProperties[$this->_strProperty];
		
		if ($strCharList !== NULL)
		{
			$arrArgs[] = $strCharList;
		}
		
		$this->_dboOwner->_arrProperties[$this->_strProperty] = call_user_func_array($strFunc, $arrArgs);
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
	function RenderInput($intContext=CONTEXT_DEFAULT, $bolRequired=FALSE, $bolApplyOutputMask=TRUE, $arrAdditionalArgs=NULL)
	{
		echo $this->_RenderIO(RENDER_INPUT, $intContext, $bolRequired, $bolApplyOutputMask, $arrAdditionalArgs);
		
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
		echo $this->_RenderIO(RENDER_OUTPUT, $intContext);
		
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
	 * @param	string	$strType			either RENDER_OUTPUT or RENDER_INPUT
	 * @param	int		$intContext			[optional] The context in which the property will be displayed
	 * @param	bool	$bolRequired		[optional] Whether the field should be mandatory
	 *
	 * @return	mixed						Html generated
	 *										If there was no definition data for this property/context, then returns NULL
	 *
	 * @method
	 */
	private function _RenderIO($strType, $intContext=CONTEXT_DEFAULT, $bolRequired=FALSE, $bolApplyOutputMask=TRUE, $arrAdditionalArgs=NULL)
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
		$arrParams = $this->_BuildParams($intContext, $strType, $bolRequired, $bolApplyOutputMask);

		return HTMLElements()->$arrParams['Definition'][$strType.'Type']($arrParams, $arrAdditionalArgs);
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
	 * @param	string	$strType			[optional] either RENDER_OUTPUT, RENER_INPUT or RENDER_VALUE, defines
	 *										how the property will be rendered. Defaults to RENDER_OUTPUT
	 * @param	bool	$bolRequired		[optional] Whether the field should be mandatory
	 * 
	 * @return	array						This array is defined at the top of the HtmlElements class
	 *
	 * @method
	 */
	private function _BuildParams($intContext, $strType=RENDER_OUTPUT, $bolRequired=FALSE, $bolApplyOutputMask=TRUE)
	{
		$arrParams = Array();
		$arrParams['Object'] 			= $this->_dboOwner->_strName;
		$arrParams['Property'] 			= $this->_strProperty;
		$arrParams['Context'] 			= $intContext;
		$arrParams['Value'] 			= $this->_dboOwner->_arrProperties[$this->_strProperty];
		$arrParams['Valid'] 			= $this->_dboOwner->_arrValid[$this->_strProperty];
		$arrParams['Required'] 			= $bolRequired;
		$arrParams['Definition'] 		= $this->_dboOwner->_arrDefine[$this->_strProperty][$intContext];
		$arrParams['Type']				= $strType;
		$arrParams['ApplyOutputMask']	= $bolApplyOutputMask;

		// work out the base class to use
		$arrParams['Definition']['BaseClass'] = CLASS_DEFAULT; // Default
		if ($arrParams['Valid'] === FALSE)
		{
			$arrParams['Definition']['BaseClass'] .= "Invalid"; // DefaultInvalid
		}
		
		return $arrParams;
	}
	
	
	//------------------------------------------------------------------------//
	// strGetLabel
	//------------------------------------------------------------------------//
	/**
	 * strGetLabel()
	 *
	 * Returns the label for the field in a given context
	 *
	 * Returns the label for the field in a given context
	 *
	 * @param	int		$intContext	[optional] context for which the label is required
	 * 
	 * @return	string	label for the field in a given context
	 *
	 * @method
	 */
	function strGetLabel($intContext=CONTEXT_DEFAULT)
	{
		return $this->_dboOwner->_arrDefine[$this->_strProperty][$intContext]['Label'];
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
	 *
	 * always sets the context to zero
	 */
	private function _Value($intContext=CONTEXT_DEFAULT, $bolUseConditionalContext=FALSE)
	{
		if ($bolUseConditionalContext)
		{
			$intContext = $this->_CalculateContext($intContext);
		}

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
	function RenderValue($intContext=CONTEXT_DEFAULT, $bolUseConditionalContext=FALSE)
	{
		echo $this->_Value($intContext, $bolUseConditionalContext);
		
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
	function AsValue($intContext=CONTEXT_DEFAULT, $bolUseConditionalContext=FALSE)
	{
		return $this->_Value($intContext, $bolUseConditionalContext);
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
	function AsInput($intContext=CONTEXT_DEFAULT, $bolRequired=NULL, $bolApplyOutputMask=TRUE)
	{
		return $this->_RenderIO(RENDER_INPUT, $intContext, $bolRequired, $bolApplyOutputMask);
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
		return $this->_RenderIO(RENDER_OUTPUT, $intContext);
	}
	
	//------------------------------------------------------------------------//
	// FormattedValue
	//------------------------------------------------------------------------//
	/**
	 * FormattedValue()
	 *
	 * Returns the property's value, formatted but not marked-up
	 *
	 * Returns the property's value, formatted but not marked-up
	 *
	 * @param	int		$intContext		[optional] context in which the property will be formatted
	 * @param	mixed	$mixArbitrary	[optional] arbitrary value to use as the property's value 
	 *									when creating the formatted value
	 * @return	mixed					property's formatted value
	 *									If the context could not be found
	 *									then NULL is returned
	 *
	 * @method
	 */
	function FormattedValue($intContext=CONTEXT_DEFAULT, $mixArbitrary=NULL)
	{
		$intContext = $this->_CalculateContext($intContext);

		// Require a definition
		if (!$this->_dboOwner->_arrDefine[$this->_strProperty][$intContext])
		{
			return NULL;
		}

		// Build up parameters for HtmlElements
		$arrParams = $this->_BuildParams($intContext);
		
		if ($mixArbitrary !== NULL)
		{
			// An arbitrary value has been specified, use it instead
			$arrParams['Value'] = $mixArbitrary;
		}
		
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
	 * @param	string	$strRenderType	[optional] either RENDER_VALUE, RENDER_OUTPUT or RENDER_INPUT
	 * @param	int		$intContext		[optional] context in which the property will be displayed
	 *
	 * @return	string					html code
	 * @method
	 */
	private function _Arbitrary($mixValue, $strRenderType=RENDER_VALUE, $intContext=CONTEXT_DEFAULT, $bolRequired=FALSE, $bolApplyOutputMask=TRUE)
	{
		$intContext = $this->_CalculateContext($intContext, $mixValue);

		// require a definition
		if (!$this->_dboOwner->_arrDefine[$this->_strProperty][$intContext])
		{
			echo "ERROR: Could not render '".$this->_strProperty ."' with context $intContext; No documentation data";
			return NULL;
		}

		// build up parameters
		$arrParams = $this->_BuildParams($intContext, $strRenderType, $bolRequired, $bolApplyOutputMask);
		
		// set the arbitrary value as the value to render
		$arrParams['Value'] = $mixValue;
		
		// Render the value
		switch ($strRenderType)
		{
			case RENDER_INPUT:
				return HTMLElements()->$arrParams['Definition']['InputType']($arrParams);
				break;
			case RENDER_OUTPUT:
				return HTMLElements()->$arrParams['Definition']['OutputType']($arrParams);
				break;
			case RENDER_VALUE:
			default:
				return HTMLElements()->RenderValue($arrParams);
				break;
		}
	}
	
	//------------------------------------------------------------------------//
	// AsArbitrary
	//------------------------------------------------------------------------//
	/**
	 * AsArbitrary()
	 *
	 * Returns the html code used to render $mixValue as the value of the property (with formatting and mark-up)
	 *
	 * Returns the html code used to render $mixValue as the value of the property (with formatting and mark-up)
	 * $mixValue will be subject to conditions defined in the ConditionalContexts and UIAppDocumentationOptions tables
	 *
	 * @param	mixed	$mixValue		The value to substitute for the property's value
	 * @param	string	$strRenderType	[optional] either RENDER_VALUE, RENDER_OUTPUT or RENDER_INPUT
	 * @param	int		$intContext		[optional] context in which the property will be displayed
	 *
	 * @return	string					html code
	 * @method
	 */
	function AsArbitrary($mixValue, $strRenderType=RENDER_VALUE, $intContext=CONTEXT_DEFAULT, $bolRequired=FALSE, $bolApplyOutputMask=TRUE)
	{
		return $this->_Arbitrary($mixValue, $strRenderType, $intContext, $bolRequired, $bolApplyOutputMask);
	}
	
	//------------------------------------------------------------------------//
	// RenderArbitrary
	//------------------------------------------------------------------------//
	/**
	 * RenderArbitrary()
	 *
	 * Renders $mixValue as the value of the property (with formatting and mark-up)
	 *
	 * Renders $mixValue as the value of the property (with formatting and mark-up)
	 * $mixValue will be subject to conditions defined in the ConditionalContexts and UIAppDocumentationOptions tables
	 *
	 * @param	mixed	$mixValue		The value to substitute for the property's value
	 * @param	string	$strRenderType	[optional] either RENDER_VALUE, RENDER_OUTPUT or RENDER_INPUT
	 * @param	int		$intContext		[optional] context in which the property will be displayed
	 *
	 * @return	mixed	PropertyValue	returns the actual value of the property
	 * @method
	 */
	function RenderArbitrary($mixValue, $strRenderType=RENDER_VALUE, $intContext=CONTEXT_DEFAULT, $bolRequired=FALSE, $bolApplyOutputMask=TRUE)
	{
		echo $this->_Arbitrary($mixValue, $strRenderType, $intContext, $bolRequired, $bolApplyOutputMask);
		
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
	 * @param	array	$arrAdditionalArgs	[optional] additional arguments required of the callback function
 	 * @param	string	$strRenderType		[optional] either RENDER_VALUE, RENDER_OUTPUT or RENDER_INPUT
	 * @param	int		$intContext			[optional] context in which the property will be displayed
	 *
	 * @return	string						html code
	 * @method
	 */
	private function _Callback($mixCallbackFunc, $arrAdditionalArgs=NULL, $strRenderType=RENDER_VALUE, $intContext=CONTEXT_DEFAULT, $bolRequired=FALSE, $bolApplyOutputMask=TRUE)
	{
		$intContext = $this->_CalculateContext($intContext);

		// require a definition
		if (!$this->_dboOwner->_arrDefine[$this->_strProperty][$intContext])
		{
			echo "ERROR: Could not render '".$this->_strProperty ."' with context $intContext; No documentation data";
			return NULL;
		}

		// build up parameters
		$arrParams = $this->_BuildParams($intContext, $strRenderType, $bolRequired, $bolApplyOutputMask);
		
		// build arguement array for the callback function
		$arrArgs = Array($arrParams['Value']);
		if (is_array($arrAdditionalArgs))
		{
			$arrArgs = array_merge($arrArgs, $arrAdditionalArgs);
		}
		
		// execute the callback function
		$arrParams['Value'] = call_user_func_array($mixCallbackFunc, $arrArgs);

		// Render the value
		switch ($strRenderType)
		{
			case RENDER_INPUT:
				return HTMLElements()->$arrParams['Definition']['InputType']($arrParams);
				break;
			case RENDER_OUTPUT:
				return HTMLElements()->$arrParams['Definition']['OutputType']($arrParams);
				break;
			case RENDER_VALUE:
			default:
				return HTMLElements()->RenderValue($arrParams);
				break;
		}
	}
	
	//------------------------------------------------------------------------//
	// AsCallback
	//------------------------------------------------------------------------//
	/**
	 * AsCallBack()
	 *
	 * Returns the html code used to render the value of the property (with formatting and markup) after having modified it using the callback function
	 *
	 * Returns the html code used to render the value of the property (with formatting and markup) after having modified it using the callback function
	 * The property's value will be the first argument passed to the callback function, 
	 * followed by any other arguements defined in $arrAdditionalArgs
	 *
	 * @param	mixed	$mixCallbackFunc	name of the function to call
	 *										This can be specified as "FunctionName"
	 *										or Array("ClassName", "MethodName")
	 * @param	array	$arrAdditionalArgs	[optional] additional arguments required of the callback function
	 * @param	string	$strRenderType		[optional] either RENDER_VALUE, RENDER_OUTPUT or RENDER_INPUT
	 * @param	int		$intContext			[optional] context in which the property will be displayed
	 *
	 * @return	string						html code
	 * @method
	 */
	function AsCallback($strCallbackFunc, $arrAdditionalArgs=NULL, $strRenderType=RENDER_VALUE, $intContext=CONTEXT_DEFAULT, $bolRequired=FALSE, $bolApplyOutputMask=TRUE)
	{
		return $this->_Callback($strCallbackFunc, $arrAdditionalArgs, $strRenderType, $intContext, $bolRequired, $bolApplyOutputMask);
	}
	
	//------------------------------------------------------------------------//
	// RenderCallback
	//------------------------------------------------------------------------//
	/**
	 * RenderCallback()
	 *
	 * Renders the value of the property (with formatting and markup) after having modified the property's value using the callback function
	 *
	 * Renders the value of the property (with formatting and markup) after having modified the property's value using the callback function
	 * The property's value will be the first argument passed to the callback function, 
	 * followed by any other arguements defined in $arrAdditionalArgs
	 *
	 * @param	mixed	$mixCallbackFunc	name of the function to call
	 *										This can be specified as "FunctionName"
	 *										or Array("ClassName", "MethodName")
	 * @param	array	$arrAdditionalArgs	[optional] additional arguments required of the callback function
	 * @param	string	$strRenderType		[optional] either RENDER_VALUE, RENDER_OUTPUT or RENDER_INPUT
	 * @param	int		$intContext			[optional] context in which the property will be displayed
	 *
	 * @return	mixed	PropertyValue		returns the actual value of the property
	 * @method
	 */
	function RenderCallback($mixCallbackFunc, $arrAdditionalArgs=NULL, $strRenderType=RENDER_VALUE, $intContext=CONTEXT_DEFAULT, $bolRequired=FALSE, $bolApplyOutputMask=TRUE)
	{
		echo $this->_Callback($mixCallbackFunc, $arrAdditionalArgs, $strRenderType, $intContext, $bolRequired, $bolApplyOutputMask);
		
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}
	
	//------------------------------------------------------------------------//
	// _Hidden
	//------------------------------------------------------------------------//
	/**
	 * _Hidden()
	 *
	 * Used by RenderHidden and AsHidden to build the html required
	 *
	 * Used by RenderHidden and AsHidden to build the html required
	 *
	 * @return	string					html code
	 * @method
	 */
	private function _Hidden()
	{
		$arrParams['Object']	= $this->_dboOwner->_strName;
		$arrParams['Property']	= $this->_strProperty;
		$arrParams['Value']		= $this->_dboOwner->_arrProperties[$this->_strProperty];

		// Render the value as hidden
		return HTMLElements()->InputHidden($arrParams);
	}
	
	//------------------------------------------------------------------------//
	// AsHidden
	//------------------------------------------------------------------------//
	/**
	 * AsHidden()
	 *
	 * Returns the html code used to render the property as a hidden input
	 *
	 * Returns the html code used to render the property as a hidden input
	 *
	 * @return	string					html code
	 * @method
	 */
	function AsHidden()
	{
		return $this->_Hidden();
	}
	
	//------------------------------------------------------------------------//
	// RenderHidden
	//------------------------------------------------------------------------//
	/**
	 * RenderHidden()
	 *
	 * Renders the property as a hidden input
	 *
	 * Renders the property as a hidden input
	 *
	 * @return	mixed	PropertyValue	returns the actual value of the property
	 * @method
	 */
	function RenderHidden()
	{
		echo $this->_Hidden();
		
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
	
	//------------------------------------------------------------------------//
	// SetToInvalid
	//------------------------------------------------------------------------//
	/**
	 * SetToInvalid()
	 *
	 * Explicitly sets the property to invalid
	 *
	 * Explicitly sets the property to invalid
	 *
	 * @return	void
	 *
	 * @method
	 */
	function SetToInvalid()
	{
		$this->_dboOwner->_arrValid[$this->_strProperty] = FALSE;
	}
	
	//------------------------------------------------------------------------//
	// IsInvalid
	//------------------------------------------------------------------------//
	/**
	 * IsInvalid()
	 *
	 * If the property had been explicitly sets to invalid
	 *
	 * If the property had been explicitly sets to invalid
	 *
	 * @return	bool If the property has been explicitly set to invalid
	 *
	 * @method
	 */
	function IsInvalid()
	{
		return $this->_dboOwner->_arrValid[$this->_strProperty] === FALSE;
	}
	
	
	//------------------------------------------------------------------------//
	// ValidateProperty
	//------------------------------------------------------------------------//
	/**
	 * ValidateProperty()
	 *
	 * Validates property and explicitly sets the property to invalid if invalid 
	 *
	 * Validates property using logic supplied by parameters
	 *
	 * @param	array	&$arrValidationErrors	Array to which any error message will be added	
	 * @param	boolean	$bolRequired			Whether or not the value is required (cannot be empty)
	 * @param	string	$strValidationFunction	Name of validation function to be invoked if required, 
	 * 											returning TRUE if valid or FALSE if invalid
	 * @param	string	$strValidationMessage	Message to be used if $strValidationFunction returns false.
	 * 											Message can contain '<label>' which will be replaced with the
	 * 											appropriate field label.
	 *
	 * @return	void
	 *
	 * @method
	 */
	function ValidateProperty(&$arrValidationErrors, $bolRequired, $intContext=CONTEXT_DEFAULT, $strValidationFunction=NULL, $strValidationMessage="<label> is invalid.")
	{
		if (strlen($this->Value) == 0)
		{
			if ($bolRequired)
			{
				$this->SetToInvalid();
				$strLabel = $this->strGetLabel($intContext);
				$arrValidationErrors[] = "$strLabel is required.";
				return FALSE;
			}
			return TRUE;
		}
		if ($strValidationFunction !== NULL)
		{
			if (!Validation::$strValidationFunction($this->Value))
			{
				$this->SetToInvalid();
				$strLabel = $this->strGetLabel($intContext);
				$arrValidationErrors[] = str_ireplace("<label>", $strLabel, $strValidationMessage);
				return FALSE;
			}
		}
		return TRUE;
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
