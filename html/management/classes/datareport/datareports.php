<?php

	//----------------------------------------------------------------------------//
	// datareports.php
	//----------------------------------------------------------------------------//
	/**
	 * datareports.php
	 *
	 * Contains the Class that Controls DataReport Listing
	 *
	 * Contains the Class that Controls DataReport Listing
	 *
	 * @file		datareports.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// DataReports
	//----------------------------------------------------------------------------//
	/**
	 * DataReports
	 *
	 * Controls listing of Available DataReports
	 *
	 * Controls listing of Available DataReports
	 *
	 *
	 * @prefix		rps
	 *
	 * @package		intranet_app
	 * @class		DataReports
	 * @extends		dataObject
	 */
	
	class DataReports extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a DataReport Searching Routine
		 *
		 * Constructs a DataReport Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('DataReports', 'DataReport', 'DataReport');
		}
	}
	
?>
