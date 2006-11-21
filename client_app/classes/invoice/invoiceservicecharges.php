<?php
	
//----------------------------------------------------------------------------//
// invoiceservicecharges.php
//----------------------------------------------------------------------------//
/**
 * invoiceservicecharges.php
 *
 * Has a class which shows Charges applied to a service on an invoice
 *
 * Has a class which shows Charges applied to a service on an invoice
 *
 * @file	invoiceservicecharges.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// InvoiceServiceCharges
	//----------------------------------------------------------------------------//
	/**
	 * InvoiceServiceCharges
	 *
	 * Controls Charges made to a Service on an Invoice
	 *
	 * Allows the viewing of Charges made to a Service on an Invoice
	 *
	 *
	 * @prefix	sch
	 *
	 * @package	client_app
	 * @class	InvoiceServiceCharges
	 * @extends	dataObject
	 */

	class InvoiceServiceCharges extends dataCollation
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
		// _invInvoice
		//------------------------------------------------------------------------//
		/**
		 * _invInvoice
		 *
		 * The Invoice object that is being called with the service
		 *
		 * The Invoice Object which helps to identify the Service being requested
		 *
		 * @type	Invoice
		 *
		 * @property
		 */
		
		private $_invInvoice;
		
		//------------------------------------------------------------------------//
		// _ivsService
		//------------------------------------------------------------------------//
		/**
		 * _ivsService
		 *
		 * The Service object for CDRs that we wish to View
		 *
		 * The Service Object which tells us what CDRs to retrieve
		 *
		 * @type	InvoiceService
		 *
		 * @property
		 */
		
		private $_ivsService;
		
		//------------------------------------------------------------------------//
		// InvoiceServiceCharges
		//------------------------------------------------------------------------//
		/**
		 * InvoiceServiceCharges()
		 *
		 * The constructor for an InvoiceServiceCharges Collation
		 *
		 * Creates a container to hold pagination of CDR records which are associated with this Invoice/Service combination
		 *
		 * @param	AuthenticatedContact	$cntContact		The Authenticated Contact logged into the System
		 * @param	Invoice			$invInvoice		The Invoice Object which helps Identify the Service
		 * @param	InvoiceService		$ivsService		The Id of the Service being requested
		 *
		 * @method
		 */

		function __construct (AuthenticatedContact &$cntContact, Invoice &$invInvoice, InvoiceService &$ivsService)
		{
			// Assign the Variables to the Object
			$this->_cntContact =& $cntContact;
			$this->_invInvoice =& $invInvoice;
			$this->_ivsService =& $ivsService;
			
			// Get a list of the number of charges that are associated with this invoice-service pair
			$selChargesLength = new StatementSelect("Charge", "count(*) AS collationLength", "Invoice = <Invoice> AND Service = <Service>");
			$selChargesLength->Execute(
				Array(
					"Invoice"	=> $this->_invInvoice->Pull ("Id")->getValue (), 
					"Service"	=> $this->_ivsService->Pull ("Id")->getValue ()
				)
			);
			
			$intLength = $selChargesLength->Fetch ();
			
			// Construct the dataCollation with the collationLength
			parent::__construct ("InvoiceServiceCharges", "Charge", $intLength ['collationLength']);
		}

		//------------------------------------------------------------------------//
		// ItemId
		//------------------------------------------------------------------------//
		/**
		 * ItemId()
		 *
		 * Method for Retrieving a Charge by Id
		 *
		 * Works as a shortcut to allow charge objects to be retrieved easily.
		 *
		 * @param	Integer		$intId		The Id of the Charge being Requested
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
		 * Retieves a Charge from a list based on its Index
		 *
		 * Retieves a Charge from a list based on its Index
		 *
		 * @param	Integer		$intIndex	The Index of the Charge being Requested
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
				"Invoice = <Invoice> AND Service = <Service>", 
				null, 
				$intIndex . ", 1"
			);
			
			$selChargeId->Execute(
				Array(
					"Invoice"	=> $this->_invInvoice->Pull ("Id")->getValue (), 
					"Service"	=> $this->_ivsService->Pull ("Id")->getValue ()
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
