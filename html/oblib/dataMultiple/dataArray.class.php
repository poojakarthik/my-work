<?
	
	class dataArray extends data implements Iterator
	{
	
		private $nodeType;
		
		private $_DATA = Array ();
		
		
		public $_sleepTagName;
		public $_sleepArrayType;
		public $_sleepArrayData;
		
		function __construct ($nodeName, $nodeType=null)
		{
			parent::__construct ($nodeName);
			
			if ($nodeType !== null && !class_exists ($nodeType))
			{
				throw new Exception ('Class does not exist: ' . $nodeType);
			}
			
			if ($nodeType !== null && !(is_subclass_of ($nodeType, 'data')))
			{
				throw new Exception ('Class is not inheritance of data: ' . $nodeType);
			}
	
			$this->nodeType = ($nodeType === null) ? "data" : $nodeType;
		}
		
		public function Push (&$arrayItem)
		{
			if (!is_object ($arrayItem))
			{
				throw new Exception ('Variable is not an object: ' . $arrayItem);
			}
			
			if (!($arrayItem instanceOf $this->nodeType))
			{
				throw new Exception ('Variable is not an instance of ' . $this->nodeType . ': ' . $arrayItem);
			}
			
			return $this->_DATA [] =& $arrayItem;
		}
		/*
				public function PushArray ($arrNode)
		{
		
			$this->_DATA ['accountPaymentus'] =& $arrNode;
			
			return $this->_DATA ['accountPaymentus'];
		}
		*/
		public function Pop (&$arrayItem)
		{
			foreach ($this->_DATA AS $index => &$_DATA)
			{
				if ($_DATA === $arrayItem)
				{
					unset ($this->_DATA [$index]);
					return;
				}
			}
		}
			
		public function Pull ($indexID)
		{
		}
			
		public function Output ()
		{
			foreach ($this->_DATA AS $arrItem)
			{
				$this->_DOMElement->appendChild
				(
					$this->_DOMDocument->importNode
					(
						$arrItem->Output ()->documentElement, 
						true
					)
				);
			}
		
			return $this->_DOMDocument;
		}
		
		private $Valid = false;
		
		public function current ()
		{
			return current ($this->_DATA);
		}
		
		public function key ()
		{
			return key ($this->_DATA);
		}
		
		public function next ()
		{
			$this->Valid = (next ($this->_DATA) !== false);
		}
		
		public function rewind ()
		{
			$this->Valid = (reset ($this->_DATA) !== false);
		}
		
		public function valid ()
		{
			return $this->Valid;
		}
		

		
		public function __sleep ()
		{
			$this->_sleepTagName = $this->tagName ();
			$this->_sleepArrayType = ($this->nodeType === 'data') ? null : $this->nodeType;
			$this->_sleepArrayData = $this->_DATA;
			
			return Array (
				"_sleepTagName",
				"_sleepArrayType",
				"_sleepArrayData"
			);
		}
		
		public function __wakeup ()
		{
			$this->__construct (
				$this->_sleepTagName,
				$this->_sleepArrayType
			);
			
			$this->_DATA = $this->_sleepArrayData;
			
			unset ($this->_sleepTagName);
			unset ($this->_sleepArrayType);
			unset ($this->_sleepArrayData);
		}
	}
	
?>
