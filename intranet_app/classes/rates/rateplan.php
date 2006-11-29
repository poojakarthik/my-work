<?php
	
	class RatePlan extends dataObject
	{
		
		function __construct ($intId)
		{
			parent::__construct ('RatePlan', $intId);
			
			$selAccount = new StatementSelect (
				'RatePlan', 
				'*', 
				'Id = <Id>'
			);
			
			$selAccount->useObLib (TRUE);
			$selAccount->Execute (Array ('Id' => $intId));
			
			$selAccount->Fetch ($this);
			
			$this->Push (new NamedServiceType ($this->Pull ('ServiceType')->getValue ()));
		}
	}
	
?>
