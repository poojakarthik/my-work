<?php

	//----------------------------------------------------------------------------//
	// permission.php
	//----------------------------------------------------------------------------//
	/**
	 * permission.php
	 *
	 * Contains the Permission object
	 *
	 * Contains the Permission object
	 *
	 * @file		permission.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Permission
	//----------------------------------------------------------------------------//
	/**
	 * Permission
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 *
	 * @prefix	srt
	 *
	 * @package	intranet_app
	 * @class	Permission
	 * @extends	dataEnumerative
	 */
	
	class Permission extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the Service Type
		 *
		 * The Id of the Service Type
		 *
		 * @type	dataInteger
		 *
		 * @property
		 */
		
		private $_oblintType;
		
		//------------------------------------------------------------------------//
		// _oblstrName
		//------------------------------------------------------------------------//
		/**
		 * _oblstrName
		 *
		 * The name of the Service Type
		 *
		 * The name of the Service Type
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrName;
		
		//------------------------------------------------------------------------//
		// Permission
		//------------------------------------------------------------------------//
		/**
		 * Permission()
		 *
		 * Holds Service Type Constant Information
		 *
		 * Holds Service Type Constant Information
		 *
		 * @param	Integer		$intType			The Id of the Service Type (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType)
		{
			parent::__construct ('Permission');
			
			$strName = 'Unknown';
			
			if (isset ($GLOBALS['Permissions'][$intType]))
			{
				$strName = $GLOBALS['Permissions'][$intType];
			}
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>
