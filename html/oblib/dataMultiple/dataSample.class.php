<?
	
	class dataSample extends dataObject
	{
		
		private $_COLLATION;
		
		private $nodeName;
		private $nodeType;
		
		private $collationLength;
		
		private $rangePage;
		private $rangePages;
		
		private $rangeStart;
		private $rangeLength;
		
		private $_DATA;
		
		
		/*
						$this, 
				$this->tagName (),
				$this->nodeType,
				$this->collationLength,
				$rangePage,
				$rangeLength
				*/
		function __construct (&$_COLLATION, $nodeName, $nodeType, $collationLength, $rangePage=1, $rangeLength=null)
		{
			parent::__construct ($nodeName, $rangePage);
			
			if ($nodeType !== null)
			{
				if (!is_subclass_of ($nodeType, 'data'))
				{
					throw new Exception ('Collation Node Type not instance of Data: ' . $nodeType);
				}
			}
			
			if ($rangeLength === null)
			{
				$rangeLength = $collationLength;
			}
			
			$this->_COLLATION =& $_COLLATION;
			
			$this->collationLength = $this->Push (new dataInteger ('collationLength', $collationLength));
			
			$this->rangePage = $this->Push (new dataInteger ('rangePage', $rangePage));
			$this->rangePages = $this->Push (new dataInteger ('rangePages', ($rangeLength <> 0 && $collationLength <> 0) ? ceil ($collationLength / $rangeLength) : 0));
			
			$this->rangeStart = $this->Push (new dataInteger ('rangeStart', ($rangePage * $rangeLength) - $rangeLength));
			$this->rangeLength = $this->Push (new dataInteger ('rangeLength', $rangeLength));
			
			$this->_DATA = new dataArray ('rangeSample', $nodeType);
			$this->Push ($this->_DATA);
			
			if (method_exists ($this->_COLLATION, 'ItemList'))
			{
				$ItemList = $this->_COLLATION->ItemList (
					$this->rangeStart->getValue (),
					$this->rangeLength->getValue ()
				);
				
				foreach ($ItemList as &$Item)
				{
					$this->_DATA->Push ($Item);
				}
			}
			else
			{
				$_ITEM = Array ();
				
				for ($i=0; $i < $this->rangeLength->getValue (); ++$i)
				{
					$_ITEM [$i] = $this->_COLLATION->ItemIndex ($i + $this->rangeStart->getValue ());
					
					if ($_ITEM [$i] !== null)
					{
						$this->_DATA->Push ($_ITEM [$i]);
					}
				}
			}
		}
		
		public function Count ()
		{
			return $this->collationLength->getValue ();
		}
	}
	
?>
