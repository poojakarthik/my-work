<?php
	
	class InvoiceServiceCharges extends dataCollation
	{
		
		private $_cntContact;
		private $_invInvoice;
		private $_srvService;
		
		function __construct (&$cntContact, &$invInvoice, &$srvService)
		{
			$this->_cntContact =& $cntContact;
			$this->_invInvoice =& $invInvoice;
			$this->_srvService =& $srvService;
			
			$selChargesLength = new StatementSelect("Charge", "count(*) AS collationLength", "Invoice = <Invoice> AND Service = <Service>");
			$selChargesLength->Execute(
				Array(
					"Invoice"	=> $this->_invInvoice->Pull ("Id")->getValue (), 
					"Service"	=> $this->_srvService->Pull ("Id")->getValue ()
				)
			);
			
			$intLength = $selChargesLength->Fetch ();
			
			parent::__construct ("InvoiceServiceCharges", "Charge", $intLength ['collationLength']);
		}
		
		public function ItemId ($intId)
		{
			return new Charge ($this->_cntContact, $intId);
		}
		
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
