<?php
	
	class UnbilledCalls extends dataCollation
	{
		
		private $_cntContact;
		private $_srvService;
		
		function __construct (&$cntContact, &$srvService)
		{
			$this->_cntContact =& $cntContact;
			$this->_srvService =& $srvService;
			
			$selUnbilledCalls = new StatementSelect(
				"CDR", 
				"count(*) AS collationLength", 
				"Invoice IS NULL AND Service = <Service> AND Status = <Status>"
			);
			
			$selUnbilledCalls->Execute(
				Array(
					"Service"	=> $this->_srvService->Pull ("Id")->getValue (),
					"Status"	=> CDR_RATED
				)
			);
			
			$intLength = $selUnbilledCalls->Fetch ();
			
			parent::__construct ("UnbilledCalls", "CDR", $intLength ['collationLength']);
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
				"Invoice IS NULL AND Service = <Service> AND Status = <Status>", 
				null, 
				$itemIndex . ", 1"
			);
			
			$selCDRId->Execute(
				Array( 
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
