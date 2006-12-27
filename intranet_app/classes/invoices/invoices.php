<?php

	//----------------------------------------------------------------------------//
	// Invoices.php
	//----------------------------------------------------------------------------//
	/**
	 * Invoices.php
	 *
	 * Contains the Class that Controls Invoice Searching
	 *
	 * Contains the Class that Controls Invoice Searching
	 *
	 * @file		Invoices.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Invoices
	//----------------------------------------------------------------------------//
	/**
	 * Invoices
	 *
	 * Controls Searching for an existing Invoice
	 *
	 * Controls Searching for an existing Invoice
	 *
	 *
	 * @prefix		acs
	 *
	 * @package		intranet_app
	 * @class		Invoices
	 * @extends		dataObject
	 */
	
	class Invoices extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an Invoice Searching Routine
		 *
		 * Constructs an Invoice Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Invoices', 'Invoice', 'Invoice');
			$this->Order ('CreatedOn', FALSE);
		}
	}
	
?>
