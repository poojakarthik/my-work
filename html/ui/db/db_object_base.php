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
	protected $_arrProperties = Array();
	
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
		reset($this->_arrProperties);
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
		return PropertyToken()->_Property($this, key($this->_arrProperties));
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
		return key($this->_arrProperties);
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
		next($this->_arrProperties);
		return PropertyToken()->_Property($this, key($this->_arrProperties));
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
	protected $_arrDataArray = Array();
	
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
		reset($this->_arrDataArray);
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
		return current($this->_arrDataArray);
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
		return key($this->_arrDataArray);
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
		return next($this->_arrDataArray);
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

?>
