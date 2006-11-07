<?php
	
	class InvoiceService extends dataObject
	{
		
		private $_cntContact;
		private $_invInvoice;
		
		private $_oblarrInvoiceServices;
		
		private $_oblcoaCalls;
		
		function __construct (&$cntContact, &$invInvoice, $intService)
		{
			parent::__construct ("InvoiceService");
			
			$this->_cntContact =& $cntContact;
			$this->_invInvoice =& $invInvoice;
			
			// Firstly, we want to get the information about this invoice. If
			// we are a contact for the account group, we are authenticated to
			// do this through the account group variable. Otherwise this must
			// be authenticated through the user's Account.
			
			$selServiceDetails = new StatementSelect ("ServiceTotal", "*", "Invoice = <Invoice> AND Service = <Service>");
			$selServiceDetails->Execute(Array("Invoice" => $this->_invInvoice->Pull ("Id")->getValue (), "Service" => $intService));
			
			// Use ObLib and set all this information in the object
			$selServiceDetails->useObLib (TRUE);
			
			if ($selServiceDetails->Count () <> 1)
			{
				throw new Exception ("There is no service on the invoice with the ID values you requested");
			}
			
			$selServiceDetails->Fetch ($this);
			
			$this->_oblcoaCalls = new InvoiceServiceCalls ($this->_cntContact, $this->_invInvoice, $this);
		}
		
		public function getCalls ($rangePage=1, $rangeLength=10)
		{
			return $this->Push ($this->_oblcoaCalls->Sample ($rangePage, $rangeLength));
		}
		
		public function getCharges ()
		{
			return $this->Push (new InvoiceServiceCharges ($this->_cntContact, $this->_invInvoice, $this));
		}
	}
	
?>
