<?
	
	class dataString extends dataPrimitive
	{
		
		function __construct ($nodeName, $nodeValue="")
		{
			parent::__construct ($nodeName);
			$this->setValue ($nodeValue);
		}
		
		public function setValue ($nodeValue)
		{
			if (!is_string ($nodeValue))
			{
				return false;
			}
			
			return parent::setValue
			(
				htmlentities 
				(
					$nodeValue
				)
			);
		}
	}
	
?>