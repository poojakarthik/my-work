<?php

	//----------------------------------------------------------------------------//
	// reports.php
	//----------------------------------------------------------------------------//
	/**
	 * reports.php
	 *
	 * Contains the Class that Controls Report Listing
	 *
	 * Contains the Class that Controls Report Listing
	 *
	 * @file		reports.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Reports
	//----------------------------------------------------------------------------//
	/**
	 * Reports
	 *
	 * Controls listing of Available Reports
	 *
	 * Controls listing of Available Reports
	 *
	 *
	 * @prefix		rps
	 *
	 * @package		intranet_app
	 * @class		Reports
	 * @extends		dataObject
	 */
	
	class Reports extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a Report Searching Routine
		 *
		 * Constructs a Report Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Reports', 'Report', 'Report');
		}
	}
	
?>
