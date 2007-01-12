<?php

//----------------------------------------------------------------------------//
// unbilledcalls.php
//----------------------------------------------------------------------------//
/**
 * unbilledcalls.php
 *
 * Contains Unbilled Calls Class
 *
 * Contains the Class for the collation of Unbilled Calls
 *
 * @file	unbilledcalls.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim 'Bash' Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// UnbilledCalls
	//----------------------------------------------------------------------------//
	/**
	 * UnbilledCalls
	 *
	 * Holds a collation of Unbilled Calls
	 *
	 * Holds a collation of Unbilled Calls
	 *
	 *
	 * @prefix	uca
	 *
	 * @package	client_app
	 * @class	UnbilledCalls
	 * @extends	dataCollation
	 */
	
	class UnbilledCalls extends dataCollation
	{
		
		//------------------------------------------------------------------------//
		// _cntContact
		//------------------------------------------------------------------------//
		/**
		 * _cntContact
		 *
		 * The AuthenticatedContact which the User currently Holds
		 *
		 * The AuthenticatedContact object which a user holds that can be used to
		 * identify their login status.
		 *
		 * @type	AuthenticatedContact
		 *
		 * @property
		 */
		
		private $_cntContact;
		
		//------------------------------------------------------------------------//
		// _srvService
		//------------------------------------------------------------------------//
		/**
		 * _srvService
		 *
		 * The Service object for CDRs that we wish to View
		 *
		 * The Service Object which tells us what CDRs to retrieve
		 *
		 * @type	Service
		 *
		 * @property
		 */
		
		private $_srvService;
		
		//------------------------------------------------------------------------//
		// UnbilledCalls
		//------------------------------------------------------------------------//
		/**
		 * UnbilledCalls()
		 *
		 * Constructor to create a new UnbilledCalls collation
		 *
		 * Constructor to create a new UnbilledCalls collation
		 *
		 * @param	AuthenticatedContact	$cntContact	The AuthenticatedContact wishing to view unbilled calls
		 * @param	Service			$srvService	A Service Object containing information about which calls to view
		 *
		 * @method
		 */
		
		function __construct (&$cntContact, &$srvService)
		{
			$this->_cntContact =& $cntContact;
			$this->_srvService =& $srvService;
			
			$selUnbilledCalls = new StatementSelect(
				"CDR", 
				"count(*) AS collationLength", 
				"InvoiceRun IS NULL AND Service = <Service> AND (Status = <Status1> OR Status = <Status2>)"
			);
			
			$selUnbilledCalls->Execute(
				Array(
					"Service"	=> $this->_srvService->Pull ("Id")->getValue (),
					"Status1"	=> CDR_RATED,
					"Status2"	=> INVOICE_TEMP
				)
			);
			
			$intLength = $selUnbilledCalls->Fetch ();
			
			// Construct the collation with the number of CDRs that are unbilled
			parent::__construct ("UnbilledCalls", "CDR", $intLength ['collationLength']);
		}
		
		//------------------------------------------------------------------------//
		// ItemId
		//------------------------------------------------------------------------//
		/**
		 * ItemId()
		 *
		 * Shortcut for Getting CDRs
		 *
		 * A shortcut method to easily get a new CDR object with record information
		 *
		 * @param	Integer		$intId		The Id of the CDR wishing to be retrieved
		 *
		 * @return	CDR
		 *
		 * @method
		 */

		public function ItemId ($intId)
		{
			return new CDR ($this->_cntContact, $intId);
		}
		
		//------------------------------------------------------------------------//
		// ItemIndex
		//------------------------------------------------------------------------//
		/**
		 * ItemIndex()
		 *
		 * Get an item (Identified by its Index)
		 *
		 * Get a CDR record that is Unbilled (Identified by its Index)
		 *
		 * @param	Integer		$intIndex	The Index of the CDR wishing to be retrieved
		 *
		 * @return	CDR
		 *
		 * @method
		 */
		
		public function ItemIndex ($intIndex)
		{
			// Get the Actual Id of the CDR, rather than an Index
			
			$selCDRId = new StatementSelect (
				"CDR", 
				"Id", 
				"InvoiceRun IS NULL AND Service = <Service> AND (Status = <Status1> OR Status = <Status2>)",
				null, 
				$intIndex . ", 1"
			);
			
			$selCDRId->Execute(
				Array( 
					"Service"	=> $this->_srvService->Pull ("Id")->getValue (),
					"Status1"	=> CDR_RATED,
					"Status2"	=> INVOICE_TEMP
				)
			);
			
			// If the CDR could not be found by Index, we've reached past the end of the list. So return null.
			if (!$arrCDRId = $selCDRId->Fetch ())
			{
				return null;
			}
			
			return $this->ItemId ($arrCDRId ['Id']);
		}
	}
	
?>
