<?php

	//----------------------------------------------------------------------------//
	// CDRs_Invoiced.php
	//----------------------------------------------------------------------------//
	/**
	 * CDRs_Invoiced.php
	 *
	 * Contains Invoiced CDR Records for a Service
	 *
	 * Contains Invoiced CDR Records for a Service
	 *
	 * @file		CDRs-Invoiced.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.12
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// CDRs_Invoiced
	//----------------------------------------------------------------------------//
	/**
	 * CDRs_Invoiced
	 *
	 * Holds a collation of Invoiced Calls
	 *
	 * Holds a collation of Invoiced Calls
	 *
	 *
	 * @prefix		icr
	 *
	 * @package		intranet_app
	 * @class		CDRs_Invoiced
	 * @extends		dataCollation
	 */
	
	class CDRs_Invoiced extends Search
	{
		
		//------------------------------------------------------------------------//
		// _invInvoice
		//------------------------------------------------------------------------//
		/**
		 * _invInvoice
		 *
		 * The Invoice object for CDRs that we wish to View
		 *
		 * The Invoice object for CDRs that we wish to View
		 *
		 * @type	Invoice
		 *
		 * @property
		 */
		
		private $_invInvoice;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor to create a new Invoiced Calls collation
		 *
		 * Constructor to create a new Invoiced Calls collation
		 *
		 * @param	Invoice			$invInvoice			An Invoice Object containing information about which calls to view
		 *
		 * @method
		 */
		
		function __construct(Invoice &$invInvoice)
		{
			$this->_invInvoice =& $invInvoice;
			//$this->_srvService =& $srvService;
			
			// This is potentially inadequate!! 
			// There will be a period of time between the issuing of an invoice and the archiving of 
			// the CDRInvoiced record. This should really query both the live and archive database
			// and combine the results in one resultset.
			// Note: As this object is used for retreiving results that can be paged accross, returning
			// a resultset that is a combination from two databases would require more considerable work
			// to get working smoothly.
			parent::__construct ('CDRs-Invoiced', 'cdr_invoiced', 'CDR', FLEX_DATABASE_CONNECTION_CDR);
			
			$this->Constrain	('invoice_run_id',		'=',	$invInvoice->Pull ('invoice_run_id')->getValue ());
			$this->Constrain	('account',			'=',	$invInvoice->Pull ('Account')->getValue ());
			$this->Order		('start_date_time');
		}
	}
	
?>
