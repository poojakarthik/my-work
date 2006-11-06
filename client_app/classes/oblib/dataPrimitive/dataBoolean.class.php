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
			parent::setValue (($nodeValue == true) ? TRUE : FALSE);
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
			return $this->getValue () == TRUE;
		}
		
		public function isFalse ()
		{
			return $this->getValue () == FALSE;
		}
	}
	
?>
