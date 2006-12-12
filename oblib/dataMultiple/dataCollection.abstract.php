<?
	
	abstract class dataCollection extends data
	{
		
		protected $_DATA = Array ();
		
		private $nodeType;
		
		function __construct ($nodeName, $nodeType=null)
		{
			parent::__construct ($nodeName);
			
			if ($nodeType !== null)
			{
				if (!is_subclass_of ($nodeType, 'data'))
				{
					throw new Exception ('could not load datacollection');
				}
				
				$this->nodeType = $nodeType;
			}
		}
		
		protected function Push (&$itemObj)
		{
			if (($this->nodeType !== null && (is_subclass_of ($this->nodeType, $itemObj) || $itemObj instanceOf $this->nodeType)) || $this->nodeType === null)
			{
				$this->_DATA [] =& $itemObj;
				return $itemObj;
			}
			
			return null;
		}
		
		protected function Pop (&$itemObj)
		{
			foreach ($this->_DATA as $id => &$_DATA)
			{
				if ($_DATA === $itemObj)
				{
					unset ($this->_DATA [$id]);
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
