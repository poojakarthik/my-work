<?php
	
//----------------------------------------------------------------------------//
// invoiceservicecalls.php
//----------------------------------------------------------------------------//
/**
 * invoiceservicecalls.php
 *
 * Has a class which shows Calls charged to a service on an invoice
 *
 * Has a class which shows Calls charged to a service on an invoice
 *
 * @file	invoiceservicecalls.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// InvoiceServiceCalls
	//----------------------------------------------------------------------------//
	/**
	 * InvoiceServiceCalls
	 *
	 * Displays CDR records made on a Service on an Invoice
	 *
	 * Allows the viewing of CDR records made on a Service on an Invoice
	 *
	 *
	 * @prefix	sch
	 *
	 * @package	client_app
	 * @extends	dataObject
	 */

	class InvoiceServiceCalls extends dataCollation
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
		// InvoiceServiceCalls
		//------------------------------------------------------------------------//
		/**
		 * InvoiceServiceCalls()
		 *
		 * The constructor for an InvoiceServiceCalls List
		 *
		 * The constructor for a new InvoiceServiceCalls Paginated Controller Representation
		 *
		 * @param	AuthenticatedContact	$cntContact		The Authenticated Contact logged into the System
		 * @param	Invoice			$invInvoice		The Invoice Object which helps Identify the Service
		 * @param	InvoiceService		$ivsService		The Id of the Service being requested
		 *
		 * @method
		 */
		
		function __construct (&$cntContact, &$invInvoice, &$ivsService)
		{
			$this->_cntContact =& $cntContact;
			$this->_invInvoice =& $invInvoice;
			$this->_ivsService =& $ivsService;
			
			$selCDRLength = new StatementSelect(
				"CDR", 
				"count(*) AS collationLength", 
				"Invoice = <Invoice> AND Service = <Service> AND (Status = <Status1> OR Status = <Status2>)"
			);
			
			$selCDRLength->Execute(
				Array(
					"Invoice"	=> $this->_invInvoice->Pull ("Id")->getValue (), 
					"Service"	=> $this->_ivsService->Pull ("Id")->getValue (),
					"Status1"	=> CDR_RATED,
					"Status2"	=> INVOICE_TEMP
				)
			);
			
			$intLength = $selCDRLength->Fetch ();
			
			parent::__construct ("InvoiceServiceCalls", "CDR", $intLength ['collationLength']);
		}
		
		//------------------------------------------------------------------------//
		// ItemId
		//------------------------------------------------------------------------//
		/**
		 * ItemId()
		 *
		 * Method for Retrieving a CDR by Id
		 *
		 * Works as a shortcut to allow CDR objects to be retrieved easily.
		 *
		 * @param	Integer		$intId		The Id of the CDR being Requested
		 *
		 * @return	CDR
		 *
		 * @method
		 */
		
		public function ItemId ($itemId)
		{
			return new CDR ($this->_cntContact, $itemId);
		}
		
		//------------------------------------------------------------------------//
		// ItemIndex
		//------------------------------------------------------------------------//
		/**
		 * ItemIndex()
		 *
		 * Method for Retrieving a CDR by Index
		 *
		 * Gets a CDR in an Invoice charged to a Service at a particular position in a list
		 *
		 * @param	Integer		$intIndex	The Index of the CDR being Requested
		 *
		 * @return	CDR
		 *
		 * @method
		 */
		
		public function ItemIndex ($itemIndex)
		{
			$selCDRId = new StatementSelect (
				"CDR", 
				"Id", 
				"Invoice = <Invoice> AND Service = <Service> AND (Status = <Status1> OR Status = <Status2>)", 
				null, 
				$itemIndex . ", 1"
			);
			
			$selCDRId->Execute(
				Array(
					"Invoice"	=> $this->_invInvoice->Pull ("Id")->getValue (), 
					"Service"	=> $this->_ivsService->Pull ("Id")->getValue (),
					"Status1"	=> CDR_RATED,
					"Status2"	=> INVOICE_TEMP
				)
			);
			
			if (!$arrCDRId = $selCDRId->Fetch ())
			{
				return null;
			}
			
			return $this->ItemId ($arrCDRId ['Id']);
		}
	}
	
?>
