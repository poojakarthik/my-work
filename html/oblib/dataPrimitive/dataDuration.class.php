<?
	
	class dataDuration extends dataPrimitive
	{
		
		private $Hours;
		private $Minutes;
		private $Seconds;
		
		function __construct ($nodeName, $nodeValue)
		{
			parent::__construct ($nodeName, $nodeName);
			
			$this->setValue ($nodeValue);
		}
		
		public function setValue ($nodeValue)
		{
			$nodeValue = intval ($nodeValue);
			
			$Hours =	intval ($nodeValue / (60 * 60));
			$Minutes =	intval ($nodeValue / 60) - ($Hours * 60);
			$Seconds =	intval ($nodeValue) - ($Minutes * 60) - ($Hours * 60);
			
			parent::setValue (
				sprintf ("%02d", $Hours) . ":" . 
				sprintf ("%02d", $Minutes) . ":" . 
				sprintf ("%02d", $Seconds)
			);
		}
	}
	
?>
