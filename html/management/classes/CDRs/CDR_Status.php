<?php

	//----------------------------------------------------------------------------//
	// CDR_Status.php
	//----------------------------------------------------------------------------//
	/**
	 * CDR_Status.php
	 *
	 * Contains the CDR Status object
	 *
	 * Contains the CDR Status object
	 *
	 * @file		CDR_Status.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// CDR_Status
	//----------------------------------------------------------------------------//
	/**
	 * CDR_Status
	 *
	 * Allows Textual (named) Representation of the Constants which form the Status of a CDR
	 *
	 * Allows Textual (named) Representation of the Constants which form the Status of a CDR
	 *
	 *
	 * @prefix	cst
	 *
	 * @package	intranet_app
	 * @class	CDR_Status
	 * @extends	dataObject
	 */
	
	class CDR_Status extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the CDR Status
		 *
		 * The Id of the CDR Status
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
		 * The name of the CDR Status
		 *
		 * The name of the CDR Status
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrName;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Holds CDR Status Constant Information
		 *
		 * Holds CDR Status Constant Information
		 *
		 * @param	Integer		$intType			The Id of the CDR Status (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType)
		{
			parent::__construct ('CDR-Status');
			
			$strName = $GLOBALS['*arrConstant']['CDR'][$intType]['Constant'];
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>
