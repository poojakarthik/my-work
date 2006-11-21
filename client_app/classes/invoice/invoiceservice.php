<?php
	
//----------------------------------------------------------------------------//
// invoiceservice.php
//----------------------------------------------------------------------------//
/**
 * invoiceservice.php
 *
 * Access to Invoice Service
 *
 * Provides Access to Viewing a Service that has been attached to an Invoice
 *
 * @file	invoiceservice.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// InvoiceService
	//----------------------------------------------------------------------------//
	/**
	 * InvoiceService
	 *
	 * Controls Services charged to an Invoice
	 *
	 * Allows the viewing of Services that have been charged to an Invoice
	 *
	 *
	 * @prefix	ivs
	 *
	 * @package	client_app
	 * @extends	dataObject
	 */
	
	class InvoiceService extends dataObject
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
		// _isaCalls
		//------------------------------------------------------------------------//
		/**
		 * _isaCalls
		 *
		 * Calls in an ObLib Collation
		 *
		 * All calls for this InvoiceService in an InvoiceServiceCalls Object
		 *
		 * @type	InvoiceServiceCalls
		 *
		 * @property
		 */
		private $_isaCalls;
		
		//------------------------------------------------------------------------//
		// InvoiceService
		//------------------------------------------------------------------------//
		/**
		 * InvoiceService()
		 *
		 * The constructor for an InvoiceService
		 *
		 * The constructor for a new InvoiceService Representation
		 *
		 * @param	AuthenticatedContact	$cntContact		The Authenticated Contact logged into the System
		 * @param	Invoice					$invInvoice		The Invoice Object which helps Identify the Service
		 * @param	Integer					$intService		The Id of the Servoce being requested
		 *
		 * @method
		 */
		
		function __construct (&$cntContact, $invInvoice, $intService)
		{
			parent::__construct ("InvoiceService");
			
			$this->_cntContact =& $cntContact;
			$this->_invInvoice =& $invInvoice;
			
			// Firstly, we want to get the information about this invoice. If
			// we are a contact for the account group, we are authenticated to
			// do this through the account group variable. Otherwise this must
			// be authenticated through the user's Account.
			
			$selServiceDetails = new StatementSelect ("ServiceTotal", "*", "InvoiceRun = <InvoiceRun> AND Service = <Service>");
			$selServiceDetails->Execute(Array("InvoiceRun" => $invInvoice->Pull ("InvoiceRun")->getValue (), "Service" => $intService));
			
			// Use ObLib and set all this information in the object
			$selServiceDetails->useObLib (TRUE);
			
			if ($selServiceDetails->Count () <> 1)
			{
				throw new Exception ("There is no service on the invoice with the ID values you requested");
			}
			
			$selServiceDetails->Fetch ($this);
			
			// Get the InvoiceServiceCalls for later use
			$this->_isaCalls = new InvoiceServiceCalls ($this->_cntContact, $this->_invInvoice, $this);
		}

		//------------------------------------------------------------------------//
		// getCalls
		//------------------------------------------------------------------------//
		/**
		 * getCalls()
		 *
		 * Gets a list of Calls for Invoice Service
		 *
		 * Retrieves a [data]Sample of Calls (in a Range) and attaches it to the InvoiceService object ($this).
		 *
		 * @param	Integer		$intPage		The Authenticated Contact logged into the System
		 * @param	Integer		$intLength		The Id of the Service being requested
		 *
		 * @return	dataSample
		 *
		 * @method
		 */
		
		public function getCalls ($intPage=1, $intLength=10)
		{
			return $this->Push ($this->_isaCalls->Sample ($intPage, $intLength));
		}
		
		//------------------------------------------------------------------------//
		// getCharges
		//------------------------------------------------------------------------//
		/**
		 * getCharges()
		 *
		 * Gets a list of Charges for Invoice Service
		 *
		 * Shortcut method for retrieving an InvoiceServiceCharges object
		 *
		 * @return	InvoiceServiceCharges
		 *
		 * @method
		 * @see		InvoiceServiceCharges
		 */
		
		public function getCharges ()
		{
			return $this->Push (new InvoiceServiceCharges ($this->_cntContact, $this->_invInvoice, $this));
		}
	}
	
?>
