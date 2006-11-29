<?php
	
	class RateGroup extends dataObject
	{
		
		function __construct ($intId)
		{
			parent::__construct ('RateGroup', $intId);
			
			$selAccount = new StatementSelect (
				'RateGroup', 
				'*', 
				'Id = <Id>'
			);
			
			$selAccount->useObLib (TRUE);
			$selAccount->Execute (Array ('Id' => $intId));
			
			$selAccount->Fetch ($this);
		}
	}
	
?>
