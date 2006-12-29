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
	 * @extends	dataEnumerative
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
			
			$strName = 'Unknown';
			$strSuffix = '';
			
			switch ($intType)
			{
				// Type 92
				case RECORD_DISPLAY_S_AND_E:
					$strName = "Service & Equipment";
					$strSuffix = "QTY";
					break;
					
				// Type 93
				case RECORD_DISPLAY_DATA:
					$strName = "GPRS and ADSL Data";
					$strSuffix = "KB";
					break;
					
				// Type 94
				case RECORD_DISPLAY_SMS:
					$strName = "SMS (Short Message Service)";
					$strSuffix = "MSG";
					break;
					
				// Type 91
				case RECORD_DISPLAY_CALL:
				// Unknown Record Type (should never happen) - just display as a normal Call
				default:
					$strName = "Voice Calls";
					$strSuffix = "MM:SS";
					break;
			}
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
			$this->oblstrSuffix		= $this->Push (new dataString	('Suffix',	$strSuffix));
		}
	}
	
?>
