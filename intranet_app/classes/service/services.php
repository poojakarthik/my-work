<?php

	//----------------------------------------------------------------------------//
	// services.php
	//----------------------------------------------------------------------------//
	/**
	 * services.php
	 *
	 * Contains the Class that Controls Service Searching
	 *
	 * Contains the Class that Controls Service Searching
	 *
	 * @file		services.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Services
	//----------------------------------------------------------------------------//
	/**
	 * Services
	 *
	 * Controls Searching for an existing Service
	 *
	 * Controls Searching for an existing Service
	 *
	 *
	 * @prefix		svs
	 *
	 * @package		intranet_app
	 * @class		Services
	 * @extends		dataObject
	 */
	
	class Services extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an Service Searching Routine
		 *
		 * Constructs an Service Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Services', 'Service', 'Service');
		}
	}
	
?>
