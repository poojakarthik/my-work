<?
	
	abstract class dataCollation extends data
	{
		
		private $nodeType;
		private $collationLength;
		
		private $_DATA = Array ();
		
		function __construct ($nodeName, $nodeType=null, $collationLength)
		{
			parent::__construct ($nodeName);
			
			if (!is_numeric ($collationLength))
			{
				throw new Exception ('Collation Length is not a Numerical Value:' . $collationLength);
			}
			
			$this->collationLength = intval ($collationLength);
			
			if ($nodeType !== null)
			{
				if (!class_exists ($nodeType))
				{
					throw new Exception ('Class does not exist: ' . $nodeType);
				}
				
				if (!($nodeType instanceOf data) && !(is_subclass_of ($nodeType, 'data')))
				{
					throw new Exception ('Class is not inheritance of data: ' . $nodeType);
				}
				
				$this->nodeType = $nodeType;
			}
		}
		
		abstract public function ItemIndex ($indexID);
		
		public function Sample ($rangePage=1, $rangeLength=null)
		{
			return new dataSample (
				$this, 
				$this->tagName (),
				$this->nodeType,
				$this->collationLength,
				$rangePage,
				$rangeLength
			);
		}
		
		protected function &Push (&$itemObj)
		{
			$this->_DATA [] =& $itemObj;
			return $itemObj;
		}
		
		protected function Pop ($itemID)
		{
			unset ($this->_DATA [$itemID]);
		}
		
		protected function Pull ($itemID)
		{
			return isset ($this->_DATA [$itemID]) ? $this->_DATA [$itemID] : null;
		}
		
		public function Output ()
		{
			return $this->Sample ()->Output ();
		}
	}
	
?>
