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
				$selService = new StatementSelect ("Service", "*", "Id = <Id> AND AccountGroup = <AccountGroup>", null, "1");
				$selService->Execute(Array("Id" => $intService, "AccountGroup" => $this->_cntContact->Pull ("AccountGroup")->getValue ()));
			}
			else
			{
				$selService = new StatementSelect ("Service", "*", "Id = <Id> AND Account = <Account>", "1");
				$selService->Execute(Array("Id" => $intService, "Account" => $this->_cntContact->Pull ("Account")->getValue ()));
			}
			
			$selService->useObLib (TRUE);
			
			if ($selService->Count () <> 1)
			{
				throw new Exception ("Class Service could not be instantiated because its ID could not be found in the database");
			}
			
			$selService->Fetch ($this);
			
			$fltTotalCharge = 0;
			
			if ($this->Pull ("ChargeCap")->getValue () > 0)
			{
				$fltTotalCharge = floatval (
					min ($this->Pull ("CappedCharge")->getValue (), $this->Pull ("ChargeCap")->getValue ()) +
					$this->Pull ("UncappedCharge")->getValue ()
				);
				
				if ($this->Pull ("UsageCap")->getValue () > 0 && $this->Pull ("UsageCap")->getValue () < $this->Pull ("CappedCharge")->getValue ())
				{
					$fltTotalCharge += floatval ($this->Pull ("UncappedCharge")->getValue () - $this->Pull ("UseageCap")->getValue ());
				}
			}
			else 
			{
				$fltTotalCharge = floatval ($this->Pull ("CappedCharge")->getValue () + $this->Pull ("UncappedCharge")->getValue ());
			}
			
			$this->Push (new dataFloat ("TotalCharge", $fltTotalCharge));
			
			$this->Push (new ServiceType ("NamedServiceType", $this->Pull ("ServiceType")->getValue ()));
		}
		
		public function getCharges ($rangePage=1, $rangeLength=10)
		{
			return $this->Push (new UnbilledCharges ($this->_cntContact, $this));
		}
		
		public function getCalls ($rangePage=1, $rangeLength=10)
		{
			$oblcoaUnbilledCalls = new UnbilledCalls ($this->_cntContact, $this);
			return $this->Push ($oblcoaUnbilledCalls->Sample ($rangePage, $rangeLength));
		}
	}
	
?>
