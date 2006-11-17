<?
	
	abstract class dataCollection extends data
	{
		
		private $_DATA = Array ();
		
		private $nodeType;
		
		function __construct ($nodeName, $nodeType='data')
		{
			parent::__construct ($nodeName);
			
			if (!($nodeType instanceOf data) && !is_subclass_of ($nodeType, 'data'))
			{
				return null;
			}
			
			$this->nodeType = $nodeType;
		}
		
		protected function Push (&$itemObj)
		{
			$this->_DATA [] =& $itemObj;
			return $itemObj;
		}
		
		protected function Pop (&$itemObj)
		{
			foreach ($this->_DATA AS &$_DATA)
			{
				if ($_DATA === $itemObj)
				{
					unset ($_DATA);
					return true;
				}
			}
			
			return false;
		}
		
		protected function Pull ($itemObj)
		{
			foreach ($this->_DATA AS &$_DATA)
			{
				if ($_DATA === $itemObj)
				{
					return $_DATA;
				}
			}
			
			return null;
		}
		
		public function Output ()
		{
			foreach ($this->_DATA AS $arrayItem)
			{
				$this->_DOMElement->appendChild
				(
					$this->_DOMDocument->importNode
					(
						$arrayItem->Output ()->documentElement, true
					)
				);
			}
			
			return $this->_DOMDocument;
		}
	}
	
?>
