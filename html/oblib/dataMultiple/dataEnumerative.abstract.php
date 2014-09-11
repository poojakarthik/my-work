<?
	
	abstract class dataEnumerative extends dataCollection
	{
		
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
			
			foreach ($this->_DATA AS &$_DATA)
			{
				$_DATA->removeAttribute ('selected');
			}
			
			$selectedItem->setAttribute ('selected', 'selected');
		}
	}
	
?>
