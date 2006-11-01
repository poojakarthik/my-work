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
			
			function __construct (&$_COLLATION, $nodeName, $nodeType, $collationLength, $rangePage=1, $rangeLength=null)
				{
					parent::__construct ($nodeName, $rangePage);
					
					if (!($nodeType instanceOf data) && !(is_subclass_of ($nodeType, 'data')))
						{
							throw new Exception ('Collation Node Type not instance of Data: ' . $nodeType);
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
					
					$this->_DATA = new dataArray ('rangeSample', $collationType);
					$this->Push ($this->_DATA);
					
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
	
?>