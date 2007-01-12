<?php

	//----------------------------------------------------------------------------//
	// Invoices_PDFS.php
	//----------------------------------------------------------------------------//
	/**
	 * Invoices_PDFS.php
	 *
	 * Contains the Class that Controls Invoice Searching
	 *
	 * Contains the Class that Controls Invoice Searching
	 *
	 * @file		Invoices_PDFS.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Invoices_PDFS
	//----------------------------------------------------------------------------//
	/**
	 * Invoices_PDFS
	 *
	 * Controls Searching for an existing Invoice
	 *
	 * Controls Searching for an existing Invoice
	 *
	 *
	 * @prefix		acs
	 *
	 * @package		intranet_app
	 * @class		Invoices_PDFS
	 * @extends		dataObject
	 */
	
	class Invoices_PDFS extends Search
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
			parent::__construct ('Invoices_PDFS', 'Invoice', 'Invoice');
			
			$this->Constrain ('Status', 'NOT EQUAL', 'INVOICE_TEMP');
			$this->Order ('CreatedOn', FALSE);
		}
	}
	
?>
