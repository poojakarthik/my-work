<?php
	
	class Invoice extends dataObject
	{
		
		private $_cntContact;
		private $_actAccount;
		
		private $_oblarrInvoiceServices;
		
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
			
			$this->Push ($this->getServices ());
		}
		
		public function getServices ()
		{
			// Get all the information about services that were charged to this invoice
			
			$oblarrInvoiceServices = new dataArray ("InvoiceServices", "InvoiceService");
			
			$selServices = new StatementSelect ("ServiceTotal", "Service, Invoice", "Invoice = <Invoice>");
			$selServices->Execute(Array("Invoice" => $this->Pull ("Id")->getValue ()));
			
			while ($arrService = $selServices->Fetch ())
			{
				$oblarrInvoiceServices->Push (new InvoiceService ($this->_cntContact, $this, $arrService ['Service']));
			}
			
			return $oblarrInvoiceServices;
		}
		
		public function getService ($Id)
		{
			$selService = new StatementSelect ("ServiceTotal", "Invoice, Service", "Invoice = <Invoice> AND Service = <Service>");
			$selService->Execute(Array("Invoice" => $this->Pull ("Id")->getValue (), "Service" => $Id));
			
			if ($selService->Count () <> 1)
			{
				throw new Exception ("There is no service with the ID you requested");
			}
			
			$arrService = $selService->Fetch ();
			
			return new InvoiceService ($this->_cntContact, $this, $arrService ['Service']);
		}
	}
	
?>
