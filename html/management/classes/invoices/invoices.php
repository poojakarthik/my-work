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
		 * Gets Invoice information
		 *
		 * Gets the invoice information using a StatementSelect and outputs 
		 * to the page using the bypass method.
		 *
		 * @param 	Integer		$intId			The Id number of the invoice
		 *
		 * @method
		 */
		 
		function __construct ($intId)
		{	
			// Pull all the Invoice information and Store it ...
			$selInvoice = new StatementSelect ('Invoice', 'Id, DATE_FORMAT(CreatedOn, \'%e/%m/%Y\') AS CreatedOn, AccountBalance, Total+Tax AS Total, TotalOwing, Balance, Disputed' , 'Account = <Id>', 'Id DESC');
			$selInvoice->Execute (Array ('Id' => $intId));
			$arrResults = $selInvoice->FetchAll ();
			
			// Get previous invoice for opening balance
			
			$arrColumns = Array();
			$arrColumns[]	= "TotalOwing";
			$selLastBill	= new StatementSelect(	"Invoice",
													$arrColumns,
													"Account = <Account> AND Id < <Id>",
													"CreatedOn DESC",
													1);
			foreach ($arrResults as $intKey => $arrResult)
			{
				$selLastBill->Execute (Array ('Account' => $intId, 'Id' => $arrResult['Id']));
				$arrLastBill = $selLastBill->Fetch ();
				$arrResults[$intKey]['OpeningBalance'] = $arrLastBill['TotalOwing'];
				$arrResults[$intKey]['Received'] = max(0,$arrLastBill['TotalOwing'] - $arrResults[$intKey]['AccountBalance']);
			}
			
			
			//Insert into the DOM Document
			$GLOBALS['Style']->InsertDOM($arrResults, 'Invoices');
		}
	}
	
?>
