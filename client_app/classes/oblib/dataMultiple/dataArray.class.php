<?
	
	class dataArray extends data implements Iterator
		{
		
			private $nodeType;
			
			private $_DATA = Array ();
			
			function __construct ($nodeName, $nodeType=null)
				{
					parent::__construct ($nodeName);
					
					if ($nodeType !== null && !class_exists ($nodeType))
						{
							throw new Exception
								(
									'Class does not exist: ' . $nodeType
								);
						}
					
					if ($nodeType !== null && !(is_subclass_of ($nodeType, 'data')))
						{
							throw new Exception 
								(
									'Class is not inheritance of data: ' . $nodeType
								);
						}
			
					$this->nodeType = ($nodeType === null) ? "data" : $nodeType;
				}
				
			public function Push (&$arrayItem)
				{
					if (!is_object ($arrayItem))
						{
							throw new Exception (
								'Variable is not an object: ' . $arrayItem
							);
						}
					
					if (!($arrayItem instanceOf $this->nodeType))
						{
							throw new Exception (
								'Variable is not an instance of ' . $this->nodeType . ': ' . $arrayItem
							);
						}
					
					return $this->_DATA [] =& $arrayItem;
				}
				
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
					foreach ($this->_DATA AS $arrayItem)
						{
							$this->_DOMElement->appendChild
								(
									$this->_DOMDocument->importNode
										(
											$arrayItem->Output ()->documentElement, 
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
	}
	
?>
