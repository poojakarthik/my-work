<?
	
	abstract class dataPrimitive extends data
	{
		
		protected $_DOMNode;
		
		function __construct ($tagName)
		{
			parent::__construct ($tagName);
			
			$this->_DOMNode = $this->_DOMDocument->createTextNode ("");
			$this->_DOMNode = $this->_DOMElement->appendChild ($this->_DOMNode);
		}
		
		public function getValue ()
		{
			return $this->_DOMNode->data;
		}
		
		public function setValue ($nodeValue)
		{
			$this->_DOMNode->replaceData
			(
				0, 
				$this->_DOMNode->length, $nodeValue
			);
		}
		
		public function Output ()
		{
			return $this->_DOMDocument;
		}
	}
	
?>