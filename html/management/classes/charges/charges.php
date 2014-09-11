<?php

	//----------------------------------------------------------------------------//
	// Charges.php
	//----------------------------------------------------------------------------//
	/**
	 * Charges.php
	 *
	 * Contains the Class that Controls Charge Searching
	 *
	 * Contains the Class that Controls Charge Searching
	 *
	 * @file		Charges.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Charges
	//----------------------------------------------------------------------------//
	/**
	 * Charges
	 *
	 * Controls Searching for an existing Charge
	 *
	 * Controls Searching for an existing Charge
	 *
	 *
	 * @prefix		cgs
	 *
	 * @package		intranet_app
	 * @class		Charges
	 * @extends		dataObject
	 */
	
	class Charges extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a Charge Searching Routine
		 *
		 * Constructs a Charge Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Charges', 'Charge', 'Charge');
		}
	}
	
?>
