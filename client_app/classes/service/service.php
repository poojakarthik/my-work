<?php
	
	class Service extends dataObject
	{
		
		private $_cntContact;
		
		function __construct (&$cntContact, $intService)
		{
			parent::__construct ("Service");
			
			$this->_cntContact =& $cntContact;
			
			if ($this->_cntContact->Pull ("CustomerContact")->isTrue ())
			{
				$selService = new StatementSelect ("Service", "*", "Id = <Id> AND AccountGroup = <AccountGroup>");
				$selService->Execute(Array("Id" => $intService, "AccountGroup" => $this->_cntContact->Pull ("AccountGroup")->getValue ()));
			}
			else
			{
				$selService = new StatementSelect ("Service", "*", "Id = <Id> AND Account = <Account>");
				$selService->Execute(Array("Id" => $intService, "Account" => $this->_cntContact->Pull ("Account")->getValue ()));
			}
			
			$selService->useObLib (TRUE);
			
			if ($selService->Count () <> 1)
			{
				throw new Exception ("Class Account could not be instantiated because it could not be found in the database");
			}
			
			$selService->Fetch ($this);
		}
	}
	
?>
