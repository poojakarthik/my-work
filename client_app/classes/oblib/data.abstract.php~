<?
	
	abstract class data extends ApplicationBaseClass
	{
		
		protected $_DOMDocument;
		protected $_DOMElement;
		
		function __construct ($nodeTag)
		{
			$this->_DOMDocument = new DOMDocument ();
			$this->_DOMElement = new DOMElement ($nodeTag);
			
			$this->_DOMDocument->appendChild
			(
				$this->_DOMElement
			);
			
			parent::__construct ();
		}
		
		public function tagName ()
		{
			return $this->_DOMElement->tagName;
		}
		
		public function __toString ()
		{
			return '<pre>' . htmlentities ($this->Output ()->SaveXML ()) . '</pre>';
		}
		
		abstract public function Output ();
	}
	
?>
