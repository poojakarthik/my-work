<?
	
	class dataInteger extends dataPrimitive
	{
		
		function __construct ($nodeName, $nodeValue=0)
		{
			parent::__construct ($nodeName);
			$this->setValue ($nodeValue);
		}
		
		public function setValue ($nodeValue)
		{
			if (!is_numeric ($nodeValue))
			{
				return;
			}
			
			return parent::setValue
			(
				intval
				(
					$nodeValue
				)
			);
		}
	}
	
?>