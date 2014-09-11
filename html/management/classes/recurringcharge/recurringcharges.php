<?php

	//----------------------------------------------------------------------------//
	// recurringcharges.php
	//----------------------------------------------------------------------------//
	/**
	 * recurringcharges.php
	 *
	 * Contains the Class that Controls RecurringCharge Searching
	 *
	 * Contains the Class that Controls RecurringCharge Searching
	 *
	 * @file		recurringcharges.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// RecurringCharges
	//----------------------------------------------------------------------------//
	/**
	 * RecurringCharges
	 *
	 * Controls Searching for an existing RecurringCharge
	 *
	 * Controls Searching for an existing RecurringCharge
	 *
	 *
	 * @prefix		rcl
	 *
	 * @package		intranet_app
	 * @class		RecurringCharges
	 * @extends		dataObject
	 */
	
	class RecurringCharges extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a RecurringCharge Searching Routine
		 *
		 * Constructs a RecurringCharge Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('RecurringCharges', 'RecurringCharge', 'RecurringCharge');
		}
	}
	
?>
