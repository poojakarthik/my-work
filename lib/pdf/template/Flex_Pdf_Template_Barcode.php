<?php

require_once dirname(__FILE__) . "/../Flex_Pdf_Barcode.php";


class Flex_Pdf_Template_Barcode extends Flex_Pdf_Template_Element
{
	private $strType = "";
	private $strValue = "";
	private $rawData = NULL;

	function initialize()
	{
		// Need to get the type and value the barcode.
		$this->strType = $this->dom->getAttribute("type");
		$this->strValue = $this->dom->getAttribute("value");

	}

	public function appendToDom($doc, $parentNode, $parent=NULL)
	{
		// Create a node for this element
		$node = $doc->createElement($this->dom->nodeName);

		// Apply the style to this node
		$node->setAttribute("style", $this->getStyle()->getHTMLStyleAttributeValue());

		// Apply the barcode attributes to this node
		$node->setAttribute("type", $this->dom->getAttribute("type"));
		$node->setAttribute("value", $this->dom->getAttribute("value"));

		// Append this node to the parentNode
		$parentNode->appendChild($node);
	}

	public function prepareSize($offsetTop=0)
	{
		$this->preparedWidth = $this->getStyle()->hasFixedWidth() ? $this->getStyle()->getWidth() : ((strlen($this->strValue) + 2.2) * 4.4);
		$this->preparedHeight = $this->getStyle()->hasFixedHeight() ? $this->getStyle()->getHeight() : 20;

		$this->requiredWidth = $this->preparedWidth + ($this->getOffsetLeft() ? $this->getOffsetLeft(): $this->getOffsetRight());
		$this->requiredHeight = $this->preparedHeight + ($this->getOffsetTop() ? $this->getOffsetTop() : $this->getOffsetBottom());
	}

	public function prepareChildPositions()
	{
	}

	function prepare($pageHeight)
	{
		// Need to load up the image to find out the dimensions
		if ($this->rawData === NULL)
		{
			// Create a Flex_Pdf_Barcode object for the appropriate barcode type
			$barcode = Flex_Pdf_Barcode::create($this->strType);

			$bottom = $pageHeight - $this->getPreparedAbsTop() - $this->getPreparedHeight();

			// Create the image resource
			$this->rawData = $barcode->getRaw($this->strValue, $bottom, $this->getPreparedAbsLeft(), $this->getPreparedHeight(), $this->getPreparedWidth());
		}
	}

	function renderOnPage($page, $parent=NULL)
	{
		$this->prepare($page->getHeight());

		$this->renderAsLinkTarget($page);

		if ($this->rawData !== "")
		{
			$page->drawRawContent($this->rawData);
		}
	}

	public function clearTemporaryDetails()
	{
		// Nothing to clear out... makes sense to keep the raw data just in case we need it again
	}
}

?>
