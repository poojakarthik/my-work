<?php
	
	class Charge extends dataObject
	{
		
		private $_cntContact;
		
		function __construct (&$cntContact, $Id)
		{
			$this->_cntContact =& $cntContact;
			
			parent::__construct ("Charge");
			
			if ($this->_cntContact->Pull ("CustomerContact")->isTrue ())
			{
				$selCharge = new StatementSelect("Charge", "*", "Id = <Id> AND AccountGroup = <AccountGroup>");
				$selCharge->Execute(
					Array(
						"Id"			=> $Id,
						"AccountGroup"	=> $this->_cntContact->Pull ("AccountGroup")->getValue ()
					)
				);
			}
			else
			{
				$selCharge = new StatementSelect("Charge", "*", "Id = <Id> AND Account = <Account>");
				$selCharge->Execute(
					Array(
						"Id"			=> $Id,
						"Account"		=> $this->_cntContact->Pull ("Account")->getValue ()
					)
				);
			}
			
			if ($selCharge->Count () <> 1)
			{
				throw new Exception ("We did not find a charge by the ID of: " . $Id);
			}
			
			$selCharge->useObLib (TRUE);
			$selCharge->Fetch ($this);
		}
	}
	
?>
