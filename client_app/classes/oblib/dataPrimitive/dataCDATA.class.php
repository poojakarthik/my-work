<?
	
	class dataCDATA extends dataPrimitive
	{
		
		function __construct ($nodeName, $nodeValue="")
		{
			parent::__construct ($nodeName);
			
			$this->_DOMNode = $this->_DOMDocument->createCDATASection ($nodeValue);
			$this->_DOMNode = $this->_DOMElement->appendChild ($this->_DOMNode);
		}
		
		public function setValue ($nodeValue)
		{
			if (!is_string ($nodeValue))
			{
				return false;
			}
			
			return parent::setValue
			(
				$nodeValue
			);
		}
	}
	
?>