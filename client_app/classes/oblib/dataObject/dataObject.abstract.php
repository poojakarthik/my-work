<?
	
	abstract class dataObject extends data
	{
		
		private $_DATA = Array ();
		
		function __construct ($nodeName)
		{
			parent::__construct ($nodeName);
		}
		
		protected function Push (data &$nodeItem)
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
		
		protected function Pop (data $nodeItem)
		{
			unset ($this->_DATA [$nodeItem->tagName ()]);
		}
		
		protected function Pull ($indexID)
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
	}
	
?>