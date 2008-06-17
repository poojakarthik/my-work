<?
	
	abstract class dataPrimitive extends data
	{
		
		protected $_DOMNode;
		
		public $_sleepTagName;
		public $_sleepTagValue;
		
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
		
		public function __sleep ()
		{
			$this->_sleepTagName = $this->tagName ();
			$this->_sleepTagValue = $this->_DOMNode->data;
			
			return Array (
				"_sleepTagName",
				"_sleepTagValue"
			);
		}
		
		public function __wakeup ()
		{
			$this->__construct (
				$this->_sleepTagName,
				$this->_sleepTagValue
			);
			
			$this->_sleepTagName = null;
			$this->_sleepTagValue = null;
		}
	}
	
?>
