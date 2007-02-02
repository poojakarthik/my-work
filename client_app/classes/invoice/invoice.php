<?php
	
//----------------------------------------------------------------------------//
// invoice.php
//----------------------------------------------------------------------------//
/**
 * invoice.php
 *
 * Access to Invoices
 *
 * Provides Access to Invoices
 *
 * @file	invoice.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// Invoice
	//----------------------------------------------------------------------------//
	/**
	 * Invoice
	 *
	 * Provides an Invoice system to allow 
	 *
	 * Outlet for someone who is logged into the system to view Invoices which they have access to
	 *
	 *
	 * @prefix	inv
	 *
	 * @package	client_app
	 * @class	Invoice
	 * @extends	dataObject
	 */
	
	class Invoice extends dataObject
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
		// _oblarrInvoiceServices
		//------------------------------------------------------------------------//
		/**
		 * _oblarrInvoiceServices
		 *
		 * An ObLib Array containing Invoice Services
		 *
		 * An ObLib Array that contains a list of services that 
		 * are attached to this invoice
		 *
		 * @type	dataArray
		 *
		 * @property
		 */
		 
		private $_oblarrInvoiceServices;
		
		//------------------------------------------------------------------------//
		// Invoice
		//------------------------------------------------------------------------//
		/**
		 * Invoice()
		 *
		 * The constructor for Invoices
		 *
		 * The constructor for a new Invoice Representation
		 *
		 * @param	AuthenticatedContact	$cntContact		The Authenticated Contact logged into the System
		 * @param	Integer					$intInvoice		The Id of the Invoice being requested
		 *
		 * @method
		 */
		
		function __construct (AuthenticatedContact $cntContact, $intInvoice)
		{
			parent::__construct ("Invoice");
			
			$this->_cntContact =& $cntContact;
			
			// Firstly, we want to get the information about this invoice. If
			// we are a contact for the accoutn group, we are authenticated to
			// do this through the account group variable. Otherwise this must
			// be authenticated through the user's Account.
			
			if ($cntContact->Pull ("CustomerContact")->isTrue ())
			{
				$selInvoice = new StatementSelect ("Invoice", "*", "Id = <Id> AND AccountGroup = <AccountGroup>");
				$selInvoice->Execute(Array("Id" => $intInvoice, "AccountGroup" => $this->_cntContact->Pull ("AccountGroup")->getValue ()));
			}
			else
			{
				$selInvoice = new StatementSelect ("Invoice", "*", "Id = <Id> AND Account = <Account>");
				$selInvoice->Execute(Array("Id" => $intInvoice, "Account" => $this->_cntContact->Pull ("Account")->getValue ()));				
			}
			
			// Use ObLib and set all this information in the object
			$selInvoice->useObLib (TRUE);
			
			if ($selInvoice->Count () <> 1)
			{
				throw new Exception ("There is no invoice with the ID you requested");
			}
			
			$selInvoice->Fetch ($this);
		}
		
		//------------------------------------------------------------------------//
		// getServices
		//------------------------------------------------------------------------//
		/**
		 * getServices()
		 *
		 * Gets a lits of Services that are Specifically Associated with this Invoice
		 *
		 * Gets a lits of Services that are Specifically Associated with this Invoice
		 *
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function getServices ()
		{
			// Get all the information about services that were charged to this invoice
			
			$oblarrInvoiceServices = new dataArray ("InvoiceServices", "InvoiceService");
			
			$selServices = new StatementSelect (
				"ServiceTotal", 
				"Service", 
				"AccountGroup = <AccountGroup> AND Account = <Account> AND InvoiceRun = <InvoiceRun>"
			);
			
			$selServices->Execute(
				Array (
					"AccountGroup"	=> $this->Pull ("AccountGroup")->getValue (),
					"Account"		=> $this->Pull ("Account")->getValue (),
					"InvoiceRun"	=> $this->Pull ("InvoiceRun")->getValue ()
				)
			);
			
			while ($arrService = $selServices->Fetch ())
			{
				$oblarrInvoiceServices->Push (new InvoiceService ($this->_cntContact, $this, $arrService ['Service']));
			}
			
			return $this->Push ($oblarrInvoiceServices);
		}
		
		//------------------------------------------------------------------------//
		// getService
		//------------------------------------------------------------------------//
		/**
		 * getService()
		 *
		 * Gets a Service that is Specifically Associated with this Invoice
		 *
		 * Gets a Service that is Specifically Associated with this Invoice
		 *
		 * @param	Integer		$Id			The ID of the Service in the Invoice being Viewed
		 *
		 * @return	InvoiceService
		 *
		 * @method
		 */
		
		public function getService ($Id)
		{
			$selService = new StatementSelect (
				"ServiceTotal", 
				"Service", 
				"AccountGroup = <AccountGroup> AND Account = <Account> AND InvoiceRun = <InvoiceRun> AND Service = <Service>"
			);
			$selService->Execute (
				Array (
					"AccountGroup"	=> $this->Pull ("AccountGroup")->getValue (),
					"Account"		=> $this->Pull ("Account")->getValue (),
					"InvoiceRun"	=> $this->Pull ("InvoiceRun")->getValue (),
					"Service"		=> $Id
				)
			);
			if ($selService->Count () <> 1)
			{
				throw new Exception ("There is no service with the ID you requested");
			}
			
			$arrService = $selService->Fetch ();
			
			return new InvoiceService ($this->_cntContact, $this, $arrService ['Service']);
		}
	}
	
?>
