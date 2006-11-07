<?php
	
	class CDR extends dataObject
	{
		
		private $_cntContact;
		
		function __construct (&$cntContact, $Id)
		{
			$this->_cntContact =& $cntContact;
			
			parent::__construct ("CDR");
			
			if ($this->_cntContact->Pull ("CustomerContact")->isTrue ())
			{
				$selCDR = new StatementSelect("CDR", "*", "Id = <Id> AND AccountGroup = <AccountGroup> AND Status = <Status>");
				$selCDR->Execute(
					Array(
						"Id"			=> $Id, 
						"AccountGroup"	=> $this->_cntContact->Pull ("AccountGroup")->getValue (),
						"Status"		=> CDR_RATED
					)
				);
			}
			else
			{			
				$selCDR = new StatementSelect("CDR", "*", "Id = <Id> AND Account = <Account> AND Status = <Status>");
				$selCDR->Execute(
					Array(
						"Id"			=> $Id, 
						"Account"		=> $this->_cntContact->Pull ("Account")->getValue (),
						"Status"		=> CDR_RATED
					)
				);
			}
			
			if ($selCDR->Count () <> 1)
			{
				throw new Exception ("The CDR you requested does not exist: " . $Id);
			}
			
			$selCDR->useObLib (TRUE);
			$selCDR->Fetch ($this);
			
			$this->Push (
				new dataDuration (
					"Duration",
					$this->PUll ("EndDatetime")->getValue () - $this->PUll ("StartDatetime")->getValue ()
				)
			);
		}
	}
	
?>
