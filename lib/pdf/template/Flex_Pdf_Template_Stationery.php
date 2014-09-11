<?php


class Flex_Pdf_Template_Stationery extends Flex_Pdf_Template_Image
{

	function __construct($imgSrc)
	{
		// Need to get the src for the image.
		$this->strSource = $imgSrc;
		
		// Get path to resource... 
		$this->strSource = $this->getResourcePath($this->strSource);
		
		$this->prepare();
	}

	public function prepareSize($offsetTop=0)
	{
		$this->preparedWidth = $this->getPage()->getWidth();
		$this->preparedHeight = $this->getPage()->getHeight();
		
		$this->requiredWidth = $this->preparedWidth;
		$this->requiredHeight = $this->preparedHeight;
	}

	public function appendToDom($doc, $parentNode, $parent=NULL)
	{
		return;
	}

	public function preparePosition($parentWidth=0, $parentHeight=0, $offsetTop=0, $offsetLeft=0)
	{
		$this->preparedAbsTop = 0;
		$this->preparedAbsLeft = 0;
	}

	public function prepareChildPositions()
	{
	}
	
	public function renderOnPage($page, $parent=NULL)
	{
		$this->prepareSize();
		$this->preparePosition();
		parent::renderOnPage($page, $parent);
	}

	protected function includeForCurrentMedia()
	{
		return $this->getTemplate()->getTargetMedia() !== Flex_Pdf_Style::MEDIA_PRINT;
	} 
}


?>
