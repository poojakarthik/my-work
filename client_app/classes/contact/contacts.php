<?php
	
	class Contacts extends dataCollation
	{
		
		private $_cntContact;
		
		function __construct (&$cntContact)
		{
			$this->_cntContact =& $cntContact;
			
			if (!$this->_cntContact->isCustomerContact ())
			{
				throw new Exception ("You are not a primary account group contact.");
			}
			
			$selContactLength = new StatementSelect(
				"Contact", 
				"count(*) AS collationLength", 
				"AccountGroup = <AccountGroup>"
			);
			
			$selContactLength->Execute(
				Array(
					"AccountGroup"	=> $this->_cntContact->Pull ("AccountGroup")->getValue ()
				)
			);
			
			$arrLength = $selContactLength->Fetch ();
			
			parent::__construct ("Contacts", "Contact", $arrLength ['collationLength']);
		}
		
		public function ItemId ($itemId)
		{
			return new Contact ($this->_cntContact, $itemId);
		}
		
		public function ItemIndex ($itemIndex)
		{
			$selCDRId = new StatementSelect (
				"Contact", 
				"Id", 
				"AccountGroup = <AccountGroup>", 
				null, 
				$itemIndex . ", 1"
			);
			
			$selCDRId->Execute(
				Array( 
					"AccountGroup"	=> $this->_cntContact->Pull ("AccountGroup")->getValue ()
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
