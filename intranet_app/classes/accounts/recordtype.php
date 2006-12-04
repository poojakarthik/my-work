<?php
	
	class RecordType extends dataObject
	{
		
		function __construct ($intId)
		{
			parent::__construct ('RecordType', $intId);
			
			$selAccount = new StatementSelect (
				"RecordType", 
				"*", 
				"Id = <Id>"
			);
			
			$selAccount->useObLib (TRUE);
			$selAccount->Execute (Array ("Id" => $intId));
			
			$selAccount->Fetch ($this);
		}
	}
	
?>
