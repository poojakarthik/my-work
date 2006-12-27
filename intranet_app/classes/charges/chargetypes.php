<?php

	//----------------------------------------------------------------------------//
	// chargetypes.php
	//----------------------------------------------------------------------------//
	/**
	 * chargetypes.php
	 *
	 * Contains the Class that Controls ChargeType Searching
	 *
	 * Contains the Class that Controls ChargeType Searching
	 *
	 * @file		chargetypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ChargeTypes
	//----------------------------------------------------------------------------//
	/**
	 * ChargeTypes
	 *
	 * Controls Searching for an existing ChargeType
	 *
	 * Controls Searching for an existing ChargeType
	 *
	 *
	 * @prefix		ocl
	 *
	 * @package		intranet_app
	 * @class		ChargeTypes
	 * @extends		dataObject
	 */
	
	class ChargeTypes extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a ChargeType Searching Routine
		 *
		 * Constructs a ChargeType Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('ChargeTypes', 'ChargeType', 'ChargeType');
		}
	}
	
?>
