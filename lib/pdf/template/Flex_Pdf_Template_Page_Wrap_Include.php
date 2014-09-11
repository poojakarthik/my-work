<?php

class Flex_Pdf_Template_Page_Wrap_Include extends Flex_Pdf_Template_Element
{
	public $id = null;
	public $wrapperId = 0;
	public $pageWrapContent = NULL;

	function initialize()
	{
		// Need to parse the page-wrap-include for content to be included
		$this->id = $this->dom->getAttribute("content");
		$this->pageWrapContent = $this->getTemplate()->getPageWrapContent($this->id);
		// Get a unique identifier for the wrapper to identify this instances parent by
		$this->wrapperId = $this->pageWrapContent->registerParent($this->parent);
	}

	function getPageWrapContent()
	{
		$this->pageWrapContent->setCurrentParent($this->wrapperId);
		return $this->pageWrapContent;
	}

	function isComplete()
	{
		return $this->getPageWrapContent()->isComplete();
	}

	public function isStarted()
	{
		return $this->getPageWrapContent()->isStarted();
	}

	public function getDepth()
	{
		return $this->getPageWrapContent()->getDepth();
	}

	public function appendToDom($doc, $parentNode, $parent=NULL)
	{
		return $this->getPageWrapContent()->appendToDom($doc, $parentNode, $parent);
	}

	public function hasAbsoluteVertical()
	{
		return $this->getPageWrapContent()->hasAbsoluteVertical();
	}

	public function getAvailableWidth()
	{
		return $this->getPageWrapContent()->getAvailableWidth();
	}

	public function getAvailableHeight()
	{
		return $this->getPageWrapContent()->getAvailableHeight();
	}

	function renderOnPage($page, $parent=NULL)
	{
		// Need to get the page wrap content for this include and render that
		return $this->getPageWrapContent()->renderOnPage($page, $parent);
	}

	public function clearTemporaryDetails()
	{
		return $this->getPageWrapContent()->clearTemporaryDetails();
	}

	public function prepareSize($offsetTop=0)
	{
		return $this->getPageWrapContent()->prepareSize($offsetTop=0);
	}

	public function getOffsetLeft()
	{
		return $this->getPageWrapContent()->getOffsetLeft();
	}

	public function getOffsetRight()
	{
		return $this->getPageWrapContent()->getOffsetRight();
	}

	public function getOffsetTop()
	{
		return $this->getPageWrapContent()->getOffsetTop();
	}

	public function getOffsetBottom()
	{
		return $this->getPageWrapContent()->getOffsetBottom();
	}

	public function getRequiredWidth()
	{
		return $this->getPageWrapContent()->getRequiredWidth();
	}

	public function getRequiredHeight()
	{
		return $this->getPageWrapContent()->getRequiredHeight();
	}

	public function getPreparedWidth()
	{
		return $this->getPageWrapContent()->getPreparedWidth();
	}

	public function getPreparedHeight()
	{
		return $this->getPageWrapContent()->getPreparedHeight();
	}

	public function preparePosition($parentWidth=0, $parentHeight=0, $offsetTop=0, $offsetLeft=0)
	{
		return $this->getPageWrapContent()->preparePosition($parentWidth, $parentHeight, $offsetTop, $offsetLeft);
	}

	public function prepareChildPositions()
	{
		return $this->getPageWrapContent()->prepareChildPositions();
	}

	public function getPreparedAbsTop()
	{
		return $this->getPageWrapContent()->getPreparedAbsTop();
	}

	public function getPreparedAbsLeft()
	{
		return $this->getPageWrapContent()->getPreparedAbsLeft();
	}

	public function getAvailablePreparedHeightForChildElement($childElement)
	{
		return $this->getPageWrapContent()->getAvailablePreparedHeightForChildElement($childElement);
	}

	public function requiresBreak()
	{
		return $this->getPageWrapContent()->requiresBreak();
	}

	public function requiresPageBreak()
	{
		return $this->getPageWrapContent()->requiresPageBreak();
	}
}

?>
