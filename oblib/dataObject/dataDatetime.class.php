<?
	
	class dataDatetime extends dataObject
	{
		
		private $Year;
		private $Month;
		private $Day;
		
		private $Hour;
		private $Minute;
		private $Second;
		
		private $Timestamp;
		
		function __construct ($nodeName, $nodeValue)
		{
			parent::__construct ($nodeName, $nodeName);
			
			$this->Year 		= $this->Push (new dataString ("year", "00"));
			$this->Month 		= $this->Push (new dataString ("month", "00"));
			$this->Day			= $this->Push (new dataString ("day", "00"));
			
			$this->Hour 		= $this->Push (new dataString ("hour", "00"));
			$this->Minute 		= $this->Push (new dataString ("minute", "00"));
			$this->Second 		= $this->Push (new dataString ("second", "00"));
			
			$this->Timestamp	= $this->Push (new dataString ("timestamp", ""));
			
			$this->setValue ($nodeValue);
		}
		
		public function getValue ()
		{
			return mktime (
				$this->Hour->getValue (),
				$this->Minute->getValue (),
				$this->Second->getValue (),
				$this->Month->getValue (),
				$this->Day->getValue (),
				$this->Year->getValue ()
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
			
			$this->Hour->setValue		(date ("H", $nodeValue));
			$this->Minute->setValue		(date ("i", $nodeValue));
			$this->Second->setValue		(date ("s", $nodeValue));
			
			$this->Timestamp->setValue	(date ("Y-m-d", $nodeValue) . "T" . date ("H:i:s", $nodeValue));
		}
	}
	
?>
