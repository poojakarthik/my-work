<?
	
	class dataTime extends dataObject
	{
		
		private $Hour;
		private $Minute;
		private $Second;
		
		private $Timestamp;
		
		function __construct ($nodeName, $nodeValue)
		{
			parent::__construct ($nodeName, $nodeName);
			
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
				$this->Second->getValue ()
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
			
			$this->Hour->setValue		(date ("H", $nodeValue));
			$this->Minute->setValue		(date ("i", $nodeValue));
			$this->Second->setValue		(date ("s", $nodeValue));
			
			$this->Timestamp->setValue	(date ("H:i:s", $nodeValue));
		}
	}
	
?>
