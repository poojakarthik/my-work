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
		echo "<hr><hr><hr>Writing raw data to pdf: <pre>\n" . $this->rawData . "\n</pre><hr><hr><hr>";
		$page->appendToRawContents($this->rawData);
	}

	function initialize()
	{
		return;
	}

	function clearTemporaryDetails()
	{
		
	}
}


?>
