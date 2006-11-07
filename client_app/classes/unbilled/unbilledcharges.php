<?php
	
	class UnbilledCharges extends dataCollation
	{
		
		private $_cntContact;
		private $_srvService;
		
		function __construct (&$cntContact, &$srvService)
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
			
			parent::__construct ("UnbilledCharges", "Charge", $intLength ['collationLength']);
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
