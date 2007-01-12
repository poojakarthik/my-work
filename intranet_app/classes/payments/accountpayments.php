<?php
	
	//----------------------------------------------------------------------------//
	// AccountPayments.php
	//----------------------------------------------------------------------------//
	/**
	 * AccountPayments.php
	 *
	 * Contains the Class that Controls Searching for Payments made against an Account
	 *
	 * Contains the Class that Controls Searching for Payments made against an Account
	 *
	 * @file		AccountPayments.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// AccountPayments
	//----------------------------------------------------------------------------//
	/**
	 * AccountPayments
	 *
	 * Contains the Class that Controls Searching for Payments made against an Account
	 *
	 * Contains the Class that Controls Searching for Payments made against an Account
	 *
	 *
	 * @prefix		acp
	 *
	 * @package		intranet_app
	 * @class		AccountPayments
	 * @extends		dataCollation
	 */
	
	class AccountPayments extends dataCollation
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an AccountPayment Searching Routine
		 *
		 * Constructs an AccountPayment Searching Routine
		 *
		 * @param	Account			$actAccount			The account we are viewing Payments for
		 *
		 * @method
		 */
		
		function __construct (Account &$actAccount)
		{
			$this->_actAccount =& $actAccount;
			
			$selPayments = new StatementSelect(
				'Invoice i INNER JOIN InvoicePayment p ON (i.Id = p.Invoice)', 
				'count(p.Id) AS collationLength', 
				'i.Account = <Account>'
			);
			
			$selPayments->Execute (Array ('Account'	=> $this->_actAccount->Pull ('Id')->getValue ()));
			$arrLength = $selPayments->Fetch ();
			
			// Construct the collation with the number of CDRs that are unbilled
			parent::__construct ('AccountPayments', 'InvoicePayment', $arrLength ['collationLength']);
		}
		
		//------------------------------------------------------------------------//
		// ItemId
		//------------------------------------------------------------------------//
		/**
		 * ItemId()
		 *
		 * Shortcut for Payments
		 *
		 * Shortcut for Payments
		 *
		 * @param	Integer		$intId		The Id of the Payment wishing to be retrieved
		 *
		 * @return	InvoicePayment
		 *
		 * @method
		 */

		public function ItemId ($intId)
		{
			return new InvoicePayment ($intId);
		}
		
		//------------------------------------------------------------------------//
		// ItemIndex
		//------------------------------------------------------------------------//
		/**
		 * ItemIndex()
		 *
		 * Get an item (Identified by its Index)
		 *
		 * Get an InvoicePayment record
		 *
		 * @param	Integer		$intIndex	The Index of the InvoicePayment wishing to be retrieved
		 *
		 * @return	InvoicePayment
		 *
		 * @method
		 */
		
		public function ItemIndex ($intIndex)
		{
			// Get the Actual Id of the InvoicePayment, rather than an Index
			
			$selPayment = new StatementSelect (
				'Invoice i INNER JOIN InvoicePayment p ON (i.Id = p.Invoice)', 
				'p.Id', 
				'i.Account = <Account>',
				'Invoice DESC', 
				$intIndex . ', 1'
			);
			
			$selPayment->Execute (Array ('Account'	=> $this->_actAccount->Pull ('Id')->getValue ()));
			
			// If the CDR could not be found by Index, we've reached past the end of the list. So return null.
			if (!$selPayment = $selPayment->Fetch ())
			{
				return null;
			}
			
			return $this->ItemId ($selPayment ['Id']);
		}
		
		
		//------------------------------------------------------------------------//
		// ItemList
		//------------------------------------------------------------------------//
		/**
		 * ItemList()
		 *
		 * Return a list of results
		 *
		 * Return a list of results that are pagination controlled
		 *
		 * @param	Integer		$intStart 		The number of the Starting Index
		 * @param	Integer		$intLength 		The number of results to return
		 * @return	Array
		 *
		 * @method
		 */
		
		public function ItemList ($intStart, $intLength)
		{
			$_DATA = Array ();
			
			// Pull all Id values which match against the Constraints
			// that are within the page limit
			$selPayments = new StatementSelect (
				'Invoice i INNER JOIN InvoicePayment p ON (i.Id = p.Invoice)', 
				'p.Id', 
				'i.Account = <Account>',
				'Invoice DESC', 
				$intStart . ', ' . $intLength
			);
			
			$selPayments->Execute (Array ('Account'	=> $this->_actAccount->Pull ('Id')->getValue ()));
			
			// Store the Results as Objects in an array
			while ($arrItem = $selPayments->Fetch ())
			{
				$_DATA [] = $this->Push ($this->ItemId ($arrItem ['Id']));
			}
			
			return $_DATA;
		}
	}
	
?>
