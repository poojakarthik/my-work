<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// db_object_base
//----------------------------------------------------------------------------//
/**
 * db_object_base
 *
 * Database Object Base Class and related Classes
 *
 * Database Object Base Class and related Classes
 *
 * @file		db_object_base.php
 * @language	PHP
 * @package		framework
 * @author		Rich 'Waste Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// DBObjectBase
//----------------------------------------------------------------------------//
/**
 * DBObjectBase
 *
 * Database Object Base Class
 *
 * Database Object Base Class
 *
 *
 * @prefix	dbo
 *
 * @package	framework_ui
 * @class	DBObjectBase
 * @extends	ApplicationBaseClass
 */
class DBObjectBase extends DataAccessUI implements Iterator
{
	protected $_arrProperty = Array();
	
	//------------------------------------------------------------------------//
	// rewind
	//------------------------------------------------------------------------//
	/**
	 * rewind()
	 *
	 * Iterator Reset
	 *
	 * Iterator Reset
	 *
	 * @method
	 */
	public function rewind()
	{
		reset($this->_arrProperty);
	}
	
	//------------------------------------------------------------------------//
	// current
	//------------------------------------------------------------------------//
	/**
	 * current()
	 *
	 * Gets current property's value
	 *
	 * Gets current property's value
	 * 
	 * @return	mixed			Current property's value
	 *
	 * @method
	 */
	public function current()
	{
		return PropertyToken->Property($this, current($this->_arrProperty));
	}
	
	//------------------------------------------------------------------------//
	//key
	//------------------------------------------------------------------------//
	/**
	 * key()
	 *
	 * Gets current property's name
	 *
	 * Gets current property's name
	 * 
	 * @return	string			Current property's name
	 *
	 * @method
	 */
	public function key()
	{
		return key($this->_arrProperty);
	}
	
	//------------------------------------------------------------------------//
	// next
	//------------------------------------------------------------------------//
	/**
	 * next()
	 *
	 * Advances Iterator to the next property, and returns its value
	 *
	 * Advances Iterator to the next property, and returns its value
	 * 
	 * @return	mixed			Next property's value
	 *
	 * @method
	 */
	public function next()
	{
		return PropertyToken->Property($this, next($this->_arrProperty));
	}
	
	//------------------------------------------------------------------------//
	// valid
	//------------------------------------------------------------------------//
	/**
	 * valid()
	 *
	 * Checks whether there are any more properties
	 *
	 * Checks whether there are any more properties
	 * 
	 * @return	boolean
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	public function valid()
	{
		return !is_null($this->key());
	}
}


//----------------------------------------------------------------------------//
// DBListBase
//----------------------------------------------------------------------------//
/**
 * DBListBase
 *
 * Database Object List Base Class
 *
 * Database Object List Base Class
 *
 * @prefix	dbl
 *
 * @package	framework_ui
 * @class	DBListBase
 * @extends	ApplicationBaseClass
 */
class DBListBase extends DataAccessUI implements Iterator
{
	protected $_arrProperty = Array();
	
	//------------------------------------------------------------------------//
	// rewind
	//------------------------------------------------------------------------//
	/**
	 * rewind()
	 *
	 * Iterator Reset
	 *
	 * Iterator Reset
	 *
	 * @method
	 */
	public function rewind()
	{
		reset($this->_arrProperty);
	}
	
	//------------------------------------------------------------------------//
	// current
	//------------------------------------------------------------------------//
	/**
	 * current()
	 *
	 * Gets current property's value
	 *
	 * Gets current property's value
	 * 
	 * @return	mixed			Current property's value
	 *
	 * @method
	 */
	public function current()
	{
		return current($this->_arrProperty);
	}
	
	//------------------------------------------------------------------------//
	//key
	//------------------------------------------------------------------------//
	/**
	 * key()
	 *
	 * Gets current property's name
	 *
	 * Gets current property's name
	 * 
	 * @return	string			Current property's name
	 *
	 * @method
	 */
	public function key()
	{
		return key($this->_arrProperty);
	}
	
	//------------------------------------------------------------------------//
	// next
	//------------------------------------------------------------------------//
	/**
	 * next()
	 *
	 * Advances Iterator to the next property, and returns its value
	 *
	 * Advances Iterator to the next property, and returns its value
	 * 
	 * @return	mixed			Next property's value
	 *
	 * @method
	 */
	public function next()
	{
		return next($this->_arrProperty);
	}
	
	//------------------------------------------------------------------------//
	// valid
	//------------------------------------------------------------------------//
	/**
	 * valid()
	 *
	 * Checks whether there are any more properties
	 *
	 * Checks whether there are any more properties
	 * 
	 * @return	boolean
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	public function valid()
	{
		return !is_null($this->key());
	}
}



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
		// Are we after one of our "magic" variables?
		switch (strtolower($strName))
		{
			// The property's value
			case "value":
				return $this->_dboObject->_arrProperties[$this->_strProperty];
		}
		
		// Do we have a column property by this name?
		if (isset($this->_dboObject->_arrProperties[$this->_strProperty][$strName]))
		{
			return $this->_dboObject->_arrProperties[$this->_strProperty][$strName];
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
		// TODO: Validate
		
		// Set the value & return
		return (bool)($this->_dboObject->_arrProperties[$this->_strProperty] = $mixValue);
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
	 * @param	string	$strContext		???????
	 * 
	 * @return	void
	 *
	 * @method
	 */
	function RenderInput($bolRequired=NULL, $strContext=NULL)
	{
		// Build up parameters for RenderHTMLTemplate()
		//TODO!Interface-kids!Actually do this
		
		RenderHTMLTemplate($arrParams);
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
	 * @param	string	$strContext		???????
	 * 
	 * @return	void
	 *
	 * @method
	 */
	function RenderOutput($bolRequired=NULL, $strContext=NULL)
	{
		// Build up parameters for RenderHTMLTemplate()
		//TODO!Interface-kids!Actually do this
		
		RenderHTMLTemplate($arrParams);
	}
}
?>