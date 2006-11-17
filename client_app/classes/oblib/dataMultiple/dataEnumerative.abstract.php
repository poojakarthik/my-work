<?
	
	abstract class dataEnumerative extends dataCollection
		{
			
			private $selectedItem;
			
			public function __construct ($nodeName)
				{
					parent::__construct ($nodeName);
				}
			
			protected function Select (&$selectedItem)
				{
					if (!$this->Pull ($selectedItem))
						{
							return null;
						}
					
					$this->selectedItem =& $selectedItem;
					return $this->selectedItem;
				}
			
			public function &SelectedItem ()
				{
					return $this->selectedItem;
				}
			
			public function Output ()
				{
					if ($this->selectedItem !== null)
						{
							return $this->selectedItem->Output ();
						}
					
					$Document = new DOMDocument ();
					$Document->appendChild
						(
							$Document->createElement
								(
									$this->tagName ()
								)
						);
					
					return $Document;
				}
		}
	
?>
