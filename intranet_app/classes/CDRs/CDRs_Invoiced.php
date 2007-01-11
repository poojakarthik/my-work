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
		
		function __construct (Invoice &$invInvoice)
		{
			$this->_invInvoice =& $invInvoice;
			$this->_srvService =& $srvService;
			
			parent::__construct ('CDRs-Invoiced', 'CDR', 'CDR');
			
			$this->Constrain ('InvoiceRun',	'=', $invInvoice->Pull ('InvoiceRun')->getValue ());
			$this->Constrain ('Account',	'=', $invInvoice->Pull ('Account')->getValue ());
		}
	}
	
?>
