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
 * @package		framework_ui
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
	function Property($dboOwner, $strProperty)
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
		}
		
		// Do we have a column property by this name?
		if (isset($this->_dboOwner->_arrColumns[$this->_strProperty][$strName]))
		{
			return $this->_dboOwner->_arrColumns[$this->_strProperty][$strName];
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
		
		// Do we have a column property by this name?
		return $this->_dboOwner->_arrColumns[$this->_strProperty][$strName] = $mixValue;
	}
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Call Private Property Methods
	 *
	 * Call Private Property Methods
	 *
	 * @param	dbo		$dboObject		The owner object
	 * @param	string	$strMethod		Method to run
	 * @param	array	$arrArguments	Passed Arguments
	 * 
	 * @return	mixed
	 *
	 * @method
	 */
	function __call($strMethod, $arrArguments)
	{
		// call private method
		$strPrivateMethod = "_$strMethod";
		if (method_exists($this, $strPrivateMethod))
		{
			$arrCallback = Array($this, $strPrivateMethod);
			return call_user_func_array($arrCallback, $arrArguments);
		}
		else
		{
			return FALSE;
		}
	}

	//------------------------------------------------------------------------//
	// RenderInput
	//------------------------------------------------------------------------//
	/**
	 * RenderInput()
	 *
	 * Renders the property in it's HTML input form
	 *
	 * Renders the property in it's HTML input form
	 *
	 * @param	bool	$bolRequired	Whether the field should be mandatory
	 * @param	string	$strContext		The context in which the property will be displayed
	 * 
	 * @return	mixed	PropertyValue	returns the value of the property or FALSE if it failed to render
	 *
	 * @method
	 */
	function RenderInput($bolRequired=NULL, $intContext=CONTEXT_DEFAULT)
	{
		return $this->_RenderIO("Input", $bolRequired, $intContext);
	}

	//------------------------------------------------------------------------//
	// RenderOutput
	//------------------------------------------------------------------------//
	/**
	 * RenderOutput()
	 *
	 * Renders the property in it's standard label form
	 *
	 * Renders the property in it's standard label form
	 *
	 * @param	bool	$bolRequired	Whether the field should be mandatory
	 * @param	string	$strContext		The context in which the property will be displayed
	 * 
	 * @return	mixed	PropertyValue	returns the value of the property or FALSE if it failed to render
	 *
	 * @method
	 */
	function RenderOutput($bolRequired=NULL, $intContext=CONTEXT_DEFAULT)
	{
		return $this->_RenderIO("Output", $bolRequired, $intContext);
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
	 * @param	string	$strType		either "Output" or "Input"
	 * @param	bool	$bolRequired	Whether the field should be mandatory
	 * @param	string	$strContext		The context in which the property will be displayed
	 * 
	 * @return	mixed	PropertyValue	returns the value of the property or FALSE if it failed to render
	 *
	 * @method
	 */
	private function _RenderIO($strType, $bolRequired=NULL, $intContext=CONTEXT_DEFAULT)
	{
		// require a definition
		if (!$this->_dboOwner->_arrDefine[$this->_strProperty][$intContext])
		{
			return FALSE;
		}
		
		// Build up parameters for RenderHTMLTemplate()
		$arrParams = Array();
		$arrParams['Object'] 	= $this->_dboOwner->_strName;
		$arrParams['Property'] 	= $this->_strProperty;
		$arrParams['Context'] 	= $intContext;
		$arrParams['Value'] 	= DBO()->$arrParams['Object']->$arrParams['Property']->Value;
		
		$arrParams['Valid'] 	= DBO()->$arrParams['Object']->$arrParams['Property']->Valid;
		$arrParams['Required'] 	= $bolRequired;
		$arrParams['Definition'] = $this->_dboOwner->_arrDefine[$this->_strProperty][$intContext];

		// work out the class to use
		if (!$arrParams['Definition']['Class'])
		{
			$arrParams['Definition']['FullClass'] = CLASS_DEFAULT; // Default
		}
		else
		{
			$arrParams['Definition']['FullClass'] = $arrParams['Definition']['Class'];
		}
		$arrParams['Definition']['FullClass'] .= $strType; // DefaultInput or DefaultOutput
		if ($arrParams['Valid'] === FALSE)
		{
			$arrParams['Definition']['FullClass'] .= "Invalid"; // DefaultInputInvalid or DefaultOutput
		}

		HTMLElements()->$arrParams['Definition'][$strType.'Type']($arrParams);
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Renders the property in it's standard label form
	 *
	 * Renders the property in it's standard label form
	 *
	 * @param	string	$strOutputMask	optional output mask 
	 * 
	 * @return	mixed PropertyValue
	 *
	 * @method
	 */
	function Render($strOutputMask=NULL)
	{
		echo $this->_dboOwner->_arrProperties[$this->_strProperty];
		return $this->_dboOwner->_arrProperties[$this->_strProperty];		
	}
	
}

?>