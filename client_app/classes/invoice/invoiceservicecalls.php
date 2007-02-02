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
	 * @class	InvoiceServiceCalls
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
				"count(Id) AS collationLength", 
				"AccountGroup = <AccountGroup> AND Account = <Account> AND InvoiceRun = <InvoiceRun> AND Service = <Service> AND Status = <Status>"
			);
			
			$selCDRLength->Execute(
				Array(
					"AccountGroup"	=> $this->_invInvoice->Pull ("AccountGroup")->getValue (), 
					"Account"		=> $this->_invInvoice->Pull ("Account")->getValue (), 
					"InvoiceRun"	=> $this->_invInvoice->Pull ("InvoiceRun")->getValue (), 
					"Service"		=> $this->_ivsService->Pull ("Service")->getValue (), 
					"Status"		=> CDR_INVOICED
				)
			);
			
			$arrLength = $selCDRLength->Fetch ();
			
			parent::__construct ("InvoiceServiceCalls", "CDR", $arrLength ['collationLength']);
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
			$selCDR = new StatementSelect (
				"CDR", 
				"Id", 
				"AccountGroup = <AccountGroup> AND Account = <Account> AND InvoiceRun = <InvoiceRun> AND Service = <Service> AND Status = <Status>",
				"StartDatetime", 
				$itemIndex . ", 1"
			);
			
			$selCDR->Execute(
				Array(
					"AccountGroup"	=> $this->_invInvoice->Pull ("AccountGroup")->getValue (), 
					"Account"		=> $this->_invInvoice->Pull ("Account")->getValue (), 
					"InvoiceRun"	=> $this->_invInvoice->Pull ("InvoiceRun")->getValue (), 
					"Service"		=> $this->_ivsService->Pull ("Service")->getValue (),
					"Status"		=> CDR_INVOICED
				)
			);
			
			$arrCDR = $selCDR->Fetch ();
			
			if ($arrCDR == null)
			{
				return null;
			}
			
			return $this->ItemId ($arrCDR ['Id']);
		}
	}
	
?>
