<?php

	//----------------------------------------------------------------------------//
	// permissions.php
	//----------------------------------------------------------------------------//
	/**
	 * permissions.php
	 *
	 * Contains the Permission object
	 *
	 * Contains the Permission object
	 *
	 * @file		permissions.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Permissions
	//----------------------------------------------------------------------------//
	/**
	 * Permissions
	 *
	 * Textual Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 * @prefix	svt
	 *
	 * @package	intranet_app
	 * @class	Permission
	 * @extends	dataEnumerative
	 */
	
	class Permissions extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// _arrOptions
		//------------------------------------------------------------------------//
		/**
		 * _arrOptions
		 *
		 * List of Selectable Options
		 *
		 * List of Selectable Options
		 *
		 * @type	dataArray
		 *
		 * @property
		 */
		
		private $_arrOptions;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of Permission
		 *
		 * Controls a List of Permission
		 *
		 * @param	Integer		$intPermission			[Optional] An Integer representation of a Permission which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($intPermission=null)
		{
			parent::__construct ('Permissions');
			
			foreach ($GLOBALS['Permissions'] AS $intKey => $intValue)
			{
				$this->_arrOptions [$intKey] = $this->Push (new Permission ($intKey));
			}
			
			$this->setValue ($intPermission);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected Service Type
		 *
		 * Change the Selected Service Type to another Service Type
		 *
		 * @param	Integer		$intPermission		The value of the Permission Constant wishing to be set
		 * @return	Boolean							Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($intPermission)
		{
			if (isset ($this->_arrOptions [$intPermission]))
			{
				$this->Select ($this->_arrOptions [$intPermission]);
				return true;
			}
			
			return false;
		}
	}
	
?>
