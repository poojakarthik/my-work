<?
	
	class dataDate extends dataObject
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
			
			$this->Year 	= $this->Push (new dataInteger ("year", 0));
			$this->Month 	= $this->Push (new dataInteger ("month", 0));
			$this->Day	= $this->Push (new dataInteger ("day", 0));
			
			$this->setValue ($nodeValue);
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
			
			$this->Year->setValue	(intval (date ("Y", $nodeValue)));
			$this->Month->setValue	(intval (date ("m", $nodeValue)));
			$this->Day->setValue	(intval (date ("d", $nodeValue)));
		}
	}
	
?>
