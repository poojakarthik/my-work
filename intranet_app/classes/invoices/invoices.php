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
	
	//class Invoices extends Search
	class Invoices extends dataObject
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
		 
		function __construct ($intId)
		{	
		
			//Problem here: the date can not be formatted (using existing structure of
			// /invoice/createdon/year /createdon/month /createdon/day
			// to follow this formatting system, find a way of splitting the 'createdon' field
			// into three fields of 'day' 'month' 'year'
		
			//Problem here: the data is not sorted in any way, and to avoid sorting
			// using sql, the array will need to be re-arranged using a purpose-built function
			
			// Pull all the Invoice information and Store it ...
			$selInvoice = new StatementSelect ('Invoice', 'Id, DATE_FORMAT(CreatedOn, \'%e/%m/%Y\') AS CreatedOn, AccountBalance, Credits, Debits, Total+Tax AS Total, Balance, Disputed' , 'Account = <Id>', 'Id DESC');
			$selInvoice->useObLib (TRUE);
			$selInvoice->Execute (Array ('Id' => $intId));

			$arrResults = $selInvoice->FetchAll ($this);
			//Debug ( $arrResults);
			$GLOBALS['Style']->InsertDOM($arrResults, 'Invoices');
			
			
			/* ORIGINAL
			parent::__construct ('Invoices', 'Invoice', 'Invoice');
			
			$this->Constrain ('Status', 'NOT EQUAL', 'INVOICE_TEMP');
			$this->Order ('CreatedOn', FALSE);
			*/
			
		}
	}
	
?>
