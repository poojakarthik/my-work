<?php

	//----------------------------------------------------------------------------//
	// ServiceTotals.php
	//----------------------------------------------------------------------------//
	/**
	 * ServiceTotals.php
	 *
	 * Contains the Class that Controls ServiceTotal Searching
	 *
	 * Contains the Class that Controls ServiceTotal Searching
	 *
	 * @file		ServiceTotals.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceTotals
	//----------------------------------------------------------------------------//
	/**
	 * ServiceTotals
	 *
	 * Controls Searching for an existing ServiceTotal
	 *
	 * Controls Searching for an existing ServiceTotal
	 *
	 *
	 * @prefix		svs
	 *
	 * @package		intranet_app
	 * @class		ServiceTotals
	 * @extends		dataObject
	 */
	
	class ServiceTotals extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an ServiceTotal Searching Routine
		 *
		 * Constructs an ServiceTotal Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('ServiceTotals', 'ServiceTotal', 'ServiceTotal');
		}
	}
	
?>
