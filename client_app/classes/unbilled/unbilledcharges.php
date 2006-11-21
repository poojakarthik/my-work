<?php

//----------------------------------------------------------------------------//
// unbilledcharges.php
//----------------------------------------------------------------------------//
/**
 * unbilledcharges.php
 *
 * Contains Unbilled Charges Class
 *
 * Contains the Class for the collation of Unbilled Charges
 *
 * @file	unbilledcharges.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim 'Bash' Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// UnbilledCharges
	//----------------------------------------------------------------------------//
	/**
	 * UnbilledCharges
	 *
	 * Holds a collation of Unbilled Charges
	 *
	 * Holds a collation of Charges which have not yet been Invoiced
	 *
	 * @prefix	uch
	 *
	 * @package	client_app
	 * @class	UnbilledCharges
	 * @extends	dataCollation
	 */
	
	class UnbilledCharges extends dataCollation
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
		 * The Service object for Charges that we wish to View
		 *
		 * The Service Object which tells us what Charges to retrieve
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
		 * Constructor to create a new UnbilledCharges collation
		 *
		 * Constructor to create a new UnbilledCharges collation
		 *
		 * @param	AuthenticatedContact	$cntContact	The AuthenticatedContact wishing to view unbilled charges
		 * @param	Service			$srvService	A Service Object containing information about which charges to view
		 *
		 * @method
		 */
		
		function __construct (AuthenticatedContact &$cntContact, Service &$srvService)
		{
			$this->_cntContact =& $cntContact;
			$this->_srvService =& $srvService;
			
			$selChargesLength = new StatementSelect("Charge", "count(*) AS collationLength", "Invoice IS NULL AND Service = <Service>");
			$selChargesLength->Execute(
				Array(
					"Service"	=> $this->_srvService->Pull ("Id")->getValue ()
				)
			);
			
			$intLength = $selChargesLength->Fetch ();
			
			// Create the Collation with the number of Charges relating to this service
			parent::__construct ("UnbilledCharges", "Charge", $intLength ['collationLength']);
		}
		
		//------------------------------------------------------------------------//
		// ItemId
		//------------------------------------------------------------------------//
		/**
		 * ItemId()
		 *
		 * Shortcut for Getting Charges
		 *
		 * A shortcut method to easily get a new Charge object with record information
		 *
		 * @param	Integer		$intId		The Id of the Charge wishing to be retrieved
		 *
		 * @return	Charge
		 *
		 * @method
		 */
		
		public function ItemId ($intId)
		{
			return new Charge ($this->_cntContact, $intId);
		}
		
		//------------------------------------------------------------------------//
		// ItemIndex
		//------------------------------------------------------------------------//
		/**
		 * ItemIndex()
		 *
		 * Get an item (Identified by its Index)
		 *
		 * Get a Charge record that is Unbilled (Identified by its Index)
		 *
		 * @param	Integer		$intIndex	The Index in the list of Charges in the Service wishing to be retrieved
		 *
		 * @return	Charge
		 *
		 * @method
		 */
		
		public function ItemIndex ($intIndex)
		{
			$selChargeId = new StatementSelect (
				"Charge", 
				"Id", 
				"Invoice IS NULL AND Service = <Service>", 
				null, 
				$intIndex . ", 1"
			);
			
			$selChargeId->Execute(
				Array(
					"Service"	=> $this->_srvService->Pull ("Id")->getValue ()
				)
			);
			
			if (!$arrChargeId = $selChargeId->Fetch ())
			{
				return null;
			}
			
			return $this->ItemId ($arrChargeId ['Id']);
		}
	}
	
?>
