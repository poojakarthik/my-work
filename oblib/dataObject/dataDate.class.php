<?
	
	class dataDate extends dataObject
	{
		
		private $Year;
		private $Month;
		private $Day;
		
		private $Timestamp;
		
		function __construct ($nodeName, $nodeValue)
		{
			parent::__construct ($nodeName, $nodeName);
			
			$this->Year 		= $this->Push (new dataString ("year", "00"));
			$this->Month 		= $this->Push (new dataString ("month", "00"));
			$this->Day			= $this->Push (new dataString ("day", "00"));
			
			$this->Timestamp	= $this->Push (new dataString ("timestamp", ""));
			
			$this->setValue ($nodeValue);
		}
		
		public function getValue ()
		{
			return mktime (
				0,
				0,
				0,
				$this->Pull ("month")->getValue (),
				$this->Pull ("day")->getValue (),
				$this->Pull ("year")->getValue ()
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
			
			$this->Year->setValue		(date ("Y", $nodeValue));
			$this->Month->setValue		(date ("m", $nodeValue));
			$this->Day->setValue		(date ("d", $nodeValue));
			
			$this->Timestamp->setValue	(date ("Y-m-d", $nodeValue));
		}
	}
	
?>
