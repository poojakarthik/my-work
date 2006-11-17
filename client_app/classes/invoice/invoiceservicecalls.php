<?php
	
	class InvoiceServiceCalls extends dataCollation
	{
		
		private $_cntContact;
		private $_invInvoice;
		private $_srvService;
		
		function __construct (&$cntContact, &$invInvoice, &$srvService)
		{
			$this->_cntContact =& $cntContact;
			$this->_invInvoice =& $invInvoice;
			$this->_srvService =& $srvService;
			
			$selCDRLength = new StatementSelect(
				"CDR", 
				"count(*) AS collationLength", 
				"Invoice = <Invoice> AND Service = <Service> AND (Status = <Status1> OR Status = <Status2>)"
			);
			
			$selCDRLength->Execute(
				Array(
					"Invoice"	=> $this->_invInvoice->Pull ("Id")->getValue (), 
					"Service"	=> $this->_srvService->Pull ("Id")->getValue (),
					"Status1"	=> CDR_RATED,
					"Status2"	=> INVOICE_TEMP
				)
			);
			
			$intLength = $selCDRLength->Fetch ();
			
			parent::__construct ("InvoiceServiceCalls", "CDR", $intLength ['collationLength']);
		}
		
		public function ItemId ($itemId)
		{
			return new CDR ($this->_cntContact, $itemId);
		}
		
		public function ItemIndex ($itemIndex)
		{
			$selCDRId = new StatementSelect (
				"CDR", 
				"Id", 
				"Invoice = <Invoice> AND Service = <Service> AND Status = <Status>", 
				null, 
				$itemIndex . ", 1"
			);
			
			$selCDRId->Execute(
				Array(
					"Invoice"	=> $this->_invInvoice->Pull ("Id")->getValue (), 
					"Service"	=> $this->_srvService->Pull ("Id")->getValue (),
					"Status"	=> CDR_RATED
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
