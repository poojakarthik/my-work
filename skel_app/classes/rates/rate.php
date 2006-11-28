<?php
	
	class Rate extends dataObject
	{
		
		function __construct ($intId)
		{
			parent::__construct ('Rate', $intId);
			
			$selAccount = new StatementSelect (
				'Rate', 
				'*', 
				'Id = <Id>'
			);
			
			$selAccount->useObLib (TRUE);
			$selAccount->Execute (Array ('Id' => $intId));
			
			$selAccount->Fetch ($this);
		}
	}
	
?>
