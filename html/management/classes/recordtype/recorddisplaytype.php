<?php

	//----------------------------------------------------------------------------//
	// recorddisplaytype.php
	//----------------------------------------------------------------------------//
	/**
	 * RecordDisplayType.php
	 *
	 * Contains the RecordDisplayType object
	 *
	 * Contains the RecordDisplayType object
	 *
	 * @file		RecordDisplayType.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// RecordDisplayType
	//----------------------------------------------------------------------------//
	/**
	 * RecordDisplayType
	 *
	 * Allows Textual (named) Representation of the Constants which form Record Display Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Record Display Types
	 *
	 *
	 * @prefix	rdt
	 *
	 * @package	intranet_app
	 * @class	RecordDisplayType
	 * @extends	dataObject
	 */
	
	class RecordDisplayType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the Record Display Type
		 *
		 * The Id of the Record Display Type
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
		 * The name of the Record Display Type
		 *
		 * The name of the Record Display Type
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrName;
		
		//------------------------------------------------------------------------//
		// _oblstrSuffix
		//------------------------------------------------------------------------//
		/**
		 * _oblstrSuffix
		 *
		 * The Suffix of the Record Display Type
		 *
		 * The Suffix of the Record Display Type
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrSuffix;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Holds Record Display Type Constant Information
		 *
		 * Holds Record Display Type Constant Information
		 *
		 * @param	Integer		$intType			The Id of the Record Display Type (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType)
		{
			parent::__construct ('RecordDisplayType');
			
			$strName	= $GLOBALS['RecordDisplayRateName'][$intType];
			$strSuffix	= $GLOBALS['RecordDisplayRateSuffix'][$intType];
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
			$this->oblstrSuffix		= $this->Push (new dataString	('Suffix',	$strSuffix));
		}
	}
	
?>
