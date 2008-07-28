<?php

//----------------------------------------------------------------------------//
// ModuleLoader
//----------------------------------------------------------------------------//
/**
 * ModuleLoader
 *
 * The ModuleLoader class - loads modules when requested
 *
 * The ModuleLoader class - loads modules when requested
 *
 *
 * @package	ui_app
 * @class	ModuleLoader
 */
class ModuleLoader
{
	//------------------------------------------------------------------------//
	// _arrModules
	//------------------------------------------------------------------------//
	/**
	 * _arrModules
	 *
	 * list of modules currently loaded
	 *
	 * list of modules currently loaded
	 *
	 * @type		array 
	 *
	 * @property
	 */
	private $_arrModules;
	
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * returns the requested module
	 *
	 * returns the requested module.
	 * 
	 * @param	string	$strPropertyName		Name of the module to load
	 *
	 * @method
	 */
	function __get($strPropertyName)
	{
		
		if (!is_object($this->_arrModules[$strPropertyName]))
		{
			// try to instantiate the object
			$strClassName = "Module" . $strPropertyName;
			$this->_arrModules[$strPropertyName] = new $strClassName;			
		}
		
		return $this->_arrModules[$strPropertyName];
	}
}

?>
