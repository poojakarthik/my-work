<?php

require_once "Flex_Pdf_Template_Element.php";
require_once "Flex_Pdf_Template_Div.php";
require_once "Flex_Pdf_Template_Image.php";
require_once "Flex_Pdf_Template_Barcode.php";
require_once "Flex_Pdf_Template_Paragraph.php";
require_once "Flex_Pdf_Template_Span.php";
require_once "Flex_Pdf_Template_Link.php";
require_once "Flex_Pdf_Template_Page_Wrap_Include.php";
require_once "Flex_Pdf_Template_Stationery.php";
require_once "Flex_Pdf_Template_Raw.php";

class Flex_Pdf_Template_Page extends Flex_Pdf_Template_Element
{
	private $type = NULL;
	private $pageWrapIncludeIds = NULL;
	private $stationeries = NULL;
	private $pageSize = NULL;
	private $page = null;

	public function initialize()
	{
		$this->type = $this->dom->getAttribute("type");

		// Find the pageWrapIncludes used by this page
		$this->listPageWrapIncludes();

		// Need to parse the page for top level elements (div or table)
		for($i = 0, $l = $this->dom->childNodes->length; $i < $l; $i++)
		{
			$node = $this->dom->childNodes->item($i);
			// Text shouldn't be out there on its own, but we don't want to ignore it...
			if ($node instanceof DOMText)
			{
				// If it is only whitespace, ignore it
				if ($node->isWhitespaceInElementContent())
				{
					continue;
				}
				// Stick the text into a span to be handled properly
				$node = $this->wrapNode($node, "span");
			}

			$child = NULL;

			switch (strtoupper($node->tagName))
			{
				case "BR":
					// BR elements shouldn't be at this level, they should only be in SPAN elements.
					$node = $this->wrapNode($node, "span");
					// This still isn't right, so let's go to the next case to sort it out...
				case "A":
				case "SPAN":
					// Span elements shouldn't be at this level, they should only be in P elements.
					$node = $this->wrapNode($node, "p");
					// This still isn't right, so let's go to the next case to sort it out...
				case "P":
					// Text elements shouldn't be at this level, they should only be in div elements.
					$node = $this->wrapNode($node, "div");
					// This is now ok, so let's handle it properly as a div...
				case "DIV":
					$child = new Flex_Pdf_Template_Div($node, $this);
					break;

				case "IMG":
					$child = new Flex_Pdf_Template_Image($node, $this);
					break;

				case "RAW":
					$child = new Flex_Pdf_Template_Raw($node, $this);
					break;

				case "BARCODE":
					$child = new Flex_Pdf_Template_Barcode($node, $this);
					break;

				//case "TABLE":
				//	$child = new Flex_Pdf_Template_Table($node, $this);
				//	break;

				default:
					// It's not in the right place!
					// Shove it in a DIV and let the DIV handler deal with it???
					// Just ignore it for now...
					break;
			}

			if ($child !== NULL)
			{
				if ($child->includeForCurrentMedia())
				{
					$this->childElements[] = $child;
				}
			}
		}

		$this->getStationery();
	}

	public function getType()
	{
		return $this->type;
	}

	private function listPageWrapIncludes()
	{
		$pageWrapIncludes = $this->dom->getElementsByTagName("page-wrap-include");
		$this->pageWrapIncludeIds = array();

		for ($i = 0, $l = $pageWrapIncludes->length; $i < $l; $i++)
		{
			$node = $pageWrapIncludes->item($i);
			$this->pageWrapIncludeIds[$node->getAttribute("content")] = $node->getAttribute("content");
		}

		// We only want to record each one once, even if there is more than one include for the wrapped content!
		$this->pageWrapIncludeIds = array_keys($this->pageWrapIncludeIds);
	}

	public function hasIncompletePageWrapIncludes()
	{
		for ($i = 0, $l = count($this->pageWrapIncludeIds); $i < $l; $i++)
		{
			$pageWrapContent = $this->getTemplate()->getPageWrapContent($this->pageWrapIncludeIds[$i]);
			if (!$pageWrapContent->isComplete())
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	public function getPage()
	{
		return $this;
	}

	/**
	 * Get the stationery to be used for the page
	 *
	 * @return string The path[s] to the image &/or raw PDF command file[s] used as stationery
	 */
	public function getStationery()
	{
		if ($this->stationeries !== NULL)
		{
			return $this->stationeries;
		}

		// Don't print stationery for the printers (they have pre-printed paper)
		if ($this->getTemplate()->getTargetMedia() == Flex_Pdf_Style::MEDIA_PRINT)
		{
			return;
		}

		$stationery = $this->dom->getAttribute("stationery");
		if (!$stationery) return NULL;

		$paths = explode("|", $stationery);

		$this->stationeries = array();

		for ($i = 0, $l = count($paths); $i < $l; $i++)
		{
			$stationery = $this->getResourcePath($paths[$i]);

			$fileExt = @substr($stationery, strrpos($stationery, ".") + 1);

			if ($fileExt != "raw")
			{
				$this->stationeries[] = new Flex_Pdf_Template_Stationery($stationery);
			}
			else
			{
				$fileContents = file_get_contents($stationery);
				if ($fileContents === FALSE)
				{
					if (!file_exists($stationery))
					{
						throw new Exception("Raw resource file '$stationery' does not exist.");
					}
					throw new Exception("Raw resource file '$stationery' cannot be read. Check file permissions.");
				}
				$this->stationeries[] = new Flex_Pdf_Template_Raw(file_get_contents($stationery));
			}
		}

		return $this->stationeries;
	}

	/**
	 * Get the stationery to be used for the page
	 *
	 * @return string The path to the image used as stationery
	 */
	public function getPageSize()
	{
		return $this->getStyle()->getPageSize();
	}

	public function renderOnPage($page, $parent=NULL)
	{
		$this->resetForNextPage();

		$this->page = $page;

		$this->prepareSize();
		$this->preparePosition();

		if ($this->stationeries != NULL)
		{
			for ($i = 0, $l = count($this->stationeries); $i < $l; $i++)
			{
				$this->stationeries[$i]->renderOnPage($page, $this);
			}
		}

		$childElements = $this->getChildElements();
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			$childElements[$i]->renderOnPage($page);
		}
	}

	public function appendToDom($doc, $parentNode, $parent=NULL)
	{
		$this->resetForNextPage();

		$this->prepareSize();
		$this->preparePosition();

		// Create a node for this element
		$node = $doc->createElement("page");


		$childElements = $this->getChildElements();
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			$childElements[$i]->appendToDom($doc, $node, $this);
		}

		// Apply the style to this node
		$node->setAttribute("style", $this->getStyle()->getHTMLStyleAttributeValue());

		// Apply any stationery for the page
		$stationery = $this->dom->getAttribute("stationery");
		if (!$stationery)
		{
			$node->setAttribute("stationery", $stationery);
		}

		// Append this node to the parentNode
		$parentNode->appendChild($node);
	}

	public function getPageWidth()
	{
		// Width is the width of the page
		return $this->getStyle()->getPageWidth();
	}

	public function getPageHeight()
	{
		// Height is the height of the page
		return $this->getStyle()->getPageHeight();
	}

	public function getAvailableWidth()
	{
		// The whole width of the page is always available
		return $this->getStyle()->getPageWidth();
	}

	public function getAvailableHeight()
	{
		// The whole height of the page is always available
		return $this->getStyle()->getPageHeight();
	}

	public function clearTemporaryDetails()
	{
	}

	public function prepareSize($offsetTop=0)
	{
		parent::prepareSize($offsetTop=0);

		$this->preparedWidth = $this->requiredWidth = $this->getPageWidth();
		$this->preparedHeight = $this->requiredHeight = $this->getPageHeight();
	}

	public function preparePosition($parentWidth=0, $parentHeight=0, $offsetTop=0, $offsetLeft=0)
	{
		$this->preparedAbsTop = 0;
		$this->preparedAbsLeft = 0;

		$this->prepareChildPositions();
	}

}

?>
