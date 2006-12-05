<?php
	
	//----------------------------------------------------------------------------//
	// rateplans.php
	//----------------------------------------------------------------------------//
	/**
	 * rateplans.php
	 *
	 * Rate Plan Searching Class Definition
	 *
	 * Rate Plan Searching Class Definition
	 *
	 * @file		rateplans.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.10
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	 
	//----------------------------------------------------------------------------//
	// RatePlans
	//----------------------------------------------------------------------------//
	/**
	 * RatePlans
	 *
	 * Searched for Rate Plans
	 *
	 * Searched for Rate Plans
	 *
	 *
	 * @prefix		rpl
	 *
	 * @package		intranet_app
	 * @class		RatePlans
	 * @extends		Search
	 */
	
	class RatePlans extends Search
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Create a new Rate Plan Searching Controller
		 *
		 * Create a new Rate Plan Searching Controller
		 *
		 * @method
		 */
		
		function __construct ()
		{
			parent::__construct ('RatePlans', 'RatePlan', 'RatePlan');
		}
	}
	
?>
