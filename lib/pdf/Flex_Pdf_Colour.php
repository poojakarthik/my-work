<?php


class Flex_Pdf_Colour extends Zend_Pdf_Color_Html
{
	
	private $_strColour = "";
	
	/**
	 * 
	 * Override the parent constructor to allow colors in format #rgb as well as #rrggbb and all pre-defined HTML colours
	 *
	 * @param $htmlColour String in format #rgb, #rrggbb, rgb(r, g, b) or the name of an HTML colour
	 **/
	function __construct($htmlColour)
	{
		$htmlColour = trim($htmlColour);
		if (preg_match("/^#[0-9a-f]{3,3}$/i", $htmlColour))
		{
			$htmlColour = "#".$htmlColour[1].$htmlColour[1].$htmlColour[2].$htmlColour[2].$htmlColour[3].$htmlColour[3];
		}
		$this->_strColour = $htmlColour;
		parent::__construct($htmlColour);
	}
	
	function getHTMLColour()
	{
		return $this->_strColour;
	}
}

?>
