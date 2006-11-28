<?php
	
	class Account extends dataObject
	{
		
		function __construct ($intId)
		{
			parent::__construct ('Account', $intId);
			
			$selAccount = new StatementSelect (
				"Account", 
				"*", 
				"Id = <Id>"
			);
			
			$selAccount->useObLib (TRUE);
			$selAccount->Execute (Array ("Id" => $intId));
			
			$selAccount->Fetch ($this);
		}
	}
	
?>
