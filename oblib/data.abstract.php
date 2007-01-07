<?
	
	abstract class data
	{
		
		protected $_DOMDocument;
		protected $_DOMElement;
		
		function __construct ($nodeTag)
		{
			$this->_DOMDocument = new DOMDocument ('1.0', 'utf-8');
			$this->_DOMElement = new DOMElement ($nodeTag);
			$this->_DOMDocument->formatOutput = true;
			
			$this->_DOMDocument->appendChild
			(
				$this->_DOMElement
			);
		}
		
		public function tagName ()
		{
			return $this->_DOMElement->tagName;
		}
		
		public function setAttribute ($strAttributeName, $strAttributeValue)
		{
			$this->_DOMElement->setAttribute ($strAttributeName, $strAttributeValue);
		}
		
		public function getAttribute ($strAttributeName)
		{
			return $this->_DOMElement->getAttribute ($strAttributeName);
		}
		
		public function removeAttribute ($strAttributeName)
		{
			return $this->_DOMElement->removeAttribute ($strAttributeName);
		}
		
		public function __toString ()
		{
			return '<pre>' . htmlentities ($this->Output ()->SaveXML ()) . '</pre>';
		}
		
		abstract public function Output ();
	}
	
?>
