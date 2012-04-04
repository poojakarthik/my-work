<?php

	//----------------------------------------------------------------------------//
	// carrier.php
	//----------------------------------------------------------------------------//
	/**
	 * carrier.php
	 *
	 * Contains the Carrier object
	 *
	 * Contains the Carrier object
	 *
	 * @file		carrier.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Carrier
	//----------------------------------------------------------------------------//
	/**
	 * Carrier
	 *
	 * Allows Textual (named) Representation of the Constants which form a Carrier
	 *
	 * Allows Textual (named) Representation of the Constants which form a Carrier
	 *
	 *
	 * @prefix	car
	 *
	 * @package	intranet_app
	 * @class	Carrier
	 * @extends	dataEnumerative
	 */
	
	class Carrier extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the Carrier
		 *
		 * The Id of the Carrier
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
		 * The name of the Carrier
		 *
		 * The name of the Carrier
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
		 * Holds Carrier Constant Information
		 *
		 * Holds Carrier Constant Information
		 *
		 * @param	Integer		$intType			The Id of the Carrier (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType)
		{
			parent::__construct ('Carrier');
			
			$strName = 'Unknown';
			
			$strName = Carrier::getForId($intType)->description;
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>
