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
	 * @prefix		pdl
	 *
	 * @package		intranet_app
	 * @class		Invoices_PDFS
	 * @extends		dataCollection
	 */
	
	class Invoices_PDFS extends dataCollection
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
		 * @param	Account		$actAccount			The Account we are requesting PDFs for
		 *
		 * @method
		 */
		 
		function __construct (Account $actAccount)
		{
			$intAccount = $actAccount->Pull ('Id')->getValue ();
			
			// Get the list
			$arrPDFs = listPDF ($intAccount);
			
			// Create Information Objects
			foreach ($arrPDFs as $intYear => $arrYear)
			{
				foreach ($arrYear as $intMonth => $strName)
				{
					$this->Push (
						new Invoice_PDF (
							$intAccount, $intYear, $intMonth, $strName
						)
					);
				}
			}
			
			parent::__construct ('Invoices-PDFs', 'Invoice', 'Invoice');
		}
	}
	
?>
