<?
	
	class dataTime extends dataObject
	{
		
		private $Year;
		private $Month;
		private $Day;
		
		private $Hour;
		private $Minute;
		private $Second;
		
		function __construct ($nodeName, $nodeValue)
		{
			parent::__construct ($nodeName, $nodeName);
			
			$this->Hour 	= $this->Push (new dataInteger ("hour", 0));
			$this->Minute 	= $this->Push (new dataInteger ("minute", 0));
			$this->Second 	= $this->Push (new dataInteger ("second", 0));
			
			$this->setValue ($nodeValue);
		}
		
		public function getValue ()
		{
			return mktime (
				$this->Pull ("hour")->getValue (),
				$this->Pull ("minute")->getValue (),
				$this->Pull ("second")->getValue ()
			);
		}
		
		public function setValue ($nodeValue)
		{
			if (!is_string ($nodeValue))
			{
				return false;
			}
			
			if (!strtotime ($nodeValue))
			{
				return;
			}
			
			$nodeValue = strtotime ($nodeValue);
			
			$this->Hour->setValue	(intval (date ("H", $nodeValue)));
			$this->Minute->setValue	(intval (date ("i", $nodeValue)));
			$this->Second->setValue	(intval (date ("s", $nodeValue)));
		}
	}
	
?>
