<?php
	
	class RatePlan extends dataObject
	{
		
		function __construct ($intId)
		{
			parent::__construct ('RatePlan', $intId);
			
			$selRatePlan = new StatementSelect (
				'RatePlan', 
				'*', 
				'Id = <Id>'
			);
			
			$selRatePlan->useObLib (TRUE);
			$selRatePlan->Execute (Array ('Id' => $intId));
			
			$selRatePlan->Fetch ($this);
			
			$this->Push (new NamedServiceType ($this->Pull ('ServiceType')->getValue ()));
		}
	}
	
?>
