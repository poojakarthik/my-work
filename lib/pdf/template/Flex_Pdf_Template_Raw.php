<?php


class Flex_Pdf_Template_Raw extends Flex_Pdf_Template_Element
{
	private $rawData = "";

	public function __construct($rawData)
	{
		$this->rawData = $rawData;
	}

	public function prepare()
	{
		parent::prepare();
	}

	public function prepareSize($offsetTop=0)
	{
		$this->preparedWidth = 0;
		$this->preparedHeight = 0;
		
		$this->requiredWidth = 0;
		$this->requiredHeight = 0;
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
	
	function renderOnPage($page, $parent=NULL)
	{
		$page->appendToRawContents($this->rawData);
	}

	function initialize()
	{
		return;
	}

	function clearTemporaryDetails()
	{
		
	}

	// If RAW elements are supported elsewhere in the document 
	// (as opposed to just in the stationery), this will need changing
	protected function includeForCurrentMedia()
	{
		return $this->getTemplate()->getTargetMedia() !== Flex_Pdf_Style::MEDIA_PRINT;
	} 
}


?>
