<?
	
	abstract class dataObject extends data
	{
		
		private $_DATA = Array ();
		
		public $_sleepTagName;
		public $_sleepObjectData;
		
		function __construct ($nodeName)
		{
			parent::__construct ($nodeName);
		}
		
		public function Push (data &$nodeItem)
		{
			if (!is_object ($nodeItem))
			{
				throw new Exception ("Passed item is not an object: " . $nodeItem);
			}
			
			if (!($nodeItem instanceOf data))
			{
				throw new Exception ("Passed item is not an inheritance of the data class: " . $nodeItem);
			}
			
			if (isset ($this->_DATA [$nodeItem->tagName ()]))
			{
				throw new Exception ("An object with the tag name you are passing already exists: " . $nodeItem);
			}
			
			$this->_DATA [$nodeItem->tagName ()] =& $nodeItem;
			
			return $this->_DATA [$nodeItem->tagName ()];
		}
		
		public function Pop ($nodeName)
		{
			$nodeItem = $this->_DATA [$nodeName];
			
			if ($nodeItem === null)
			{
				return null;
			}
			
			unset ($this->_DATA [$nodeName]);
			
			return $nodeItem;
		}
		
		public function Pull ($indexID)
		{
			return (isset ($this->_DATA [$indexID]) ? $this->_DATA [$indexID] : null);
		}
		
		public function Output ()
		{
			foreach ($this->_DATA AS $nodeItem)
			{
				$this->_DOMElement->appendChild
				(
					$this->_DOMDocument->importNode
					(
						$nodeItem->Output ()->documentElement, 
						true
					)
				);
			}
			
			return $this->_DOMDocument;
		}
		
		public function __sleep ()
		{
			$this->_sleepTagName = $this->tagName ();
			$this->_sleepObjectData = $this->_DATA;
			
			return Array (
				"_sleepTagName",
				"_sleepObjectData"
			);
		}
		
		public function __wakeup ()
		{
			$this->__construct (
				$this->_sleepTagName
			);
			
			$this->_DATA = Array ();
			
			if ($this->_sleepObjectData)
			{
				$this->_DATA = $this->_sleepObjectData;
			}
			
			unset ($this->_sleepTagName);
			unset ($this->_sleepObjectData);
		}
	}
	
?>
