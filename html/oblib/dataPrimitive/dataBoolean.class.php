<?
	
	class dataBoolean extends dataPrimitive
	{
		
		function __construct ($nodeName, $nodeValue=false)
		{
			parent::__construct ($nodeName);
			$this->setValue ($nodeValue);
		}
		
		public function setValue ($nodeValue)
		{
			parent::setValue (($nodeValue == true) ? "1" : "0");
		}
		
		public function setTrue ()
		{
			$this->setValue (true);
		}
		
		public function setFalse ()
		{
			$this->setValue (false);
		}
		
		public function isTrue ()
		{
			return $this->getValue () == 1;
		}
		
		public function isFalse ()
		{
			return $this->getValue () == 0;
		}
	}
	
?>
