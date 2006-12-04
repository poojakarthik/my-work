<?php

	//----------------------------------------------------------------------------//
	// servicetype.php
	//----------------------------------------------------------------------------//
	/**
	 * servicetype.php
	 *
	 * Contains the ServiceType object
	 *
	 * Contains the ServiceType object
	 *
	 * @file		servicetype.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceType
	//----------------------------------------------------------------------------//
	/**
	 * ServiceType
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 *
	 * @prefix	srt
	 *
	 * @package	intranet_app
	 * @class	ServiceType
	 * @extends	dataEnumerative
	 */
	
	class ServiceType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _intId
		//------------------------------------------------------------------------//
		/**
		 * _intId
		 *
		 * The Id of the Service Type
		 *
		 * The Id of the Service Type
		 *
		 * @type	dataInteger
		 *
		 * @property
		 */
		
		private $_intId;
		
		//------------------------------------------------------------------------//
		// _strName
		//------------------------------------------------------------------------//
		/**
		 * _strName
		 *
		 * The name of the Service Type
		 *
		 * The name of the Service Type
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_strName;
		
		//------------------------------------------------------------------------//
		// ServiceType
		//------------------------------------------------------------------------//
		/**
		 * ServiceType()
		 *
		 * Holds Service Type Constant Information
		 *
		 * Holds Service Type Constant Information
		 *
		 * @param	Integer		$intId			The Id of the Service Type (Constant Value)
		 * @param	String		$strName		The Name of the Service Type
		 *
		 * @method
		 */
		
		function __construct ($intId, $strName)
		{
			parent::__construct ('ServiceType', $intId);
			
			$this->intId		= $this->Push (new dataInteger	('Id',		$intId));
			$this->strName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>
