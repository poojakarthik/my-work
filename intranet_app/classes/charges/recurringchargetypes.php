<?php

	//----------------------------------------------------------------------------//
	// recurringchargetypes.php
	//----------------------------------------------------------------------------//
	/**
	 * recurringchargetypes.php
	 *
	 * Contains the Class that Controls RecurringChargeType Searching
	 *
	 * Contains the Class that Controls RecurringChargeType Searching
	 *
	 * @file		recurringchargetypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// RecurringChargeTypes
	//----------------------------------------------------------------------------//
	/**
	 * RecurringChargeTypes
	 *
	 * Controls Searching for an existing RecurringChargeType
	 *
	 * Controls Searching for an existing RecurringChargeType
	 *
	 *
	 * @prefix		rcl
	 *
	 * @package		intranet_app
	 * @class		RecurringChargeTypes
	 * @extends		dataObject
	 */
	
	class RecurringChargeTypes extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a RecurringChargeType Searching Routine
		 *
		 * Constructs a RecurringChargeType Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('RecurringChargeTypes', 'RecurringChargeType', 'RecurringChargeType');
		}
	}
	
?>
