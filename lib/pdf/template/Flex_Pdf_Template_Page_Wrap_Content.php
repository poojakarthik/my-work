<?php

require_once dirname(__FILE__) . "/Flex_Pdf_Template_Wrapped_Header.php";
require_once dirname(__FILE__) . "/Flex_Pdf_Template_Wrapped_Footer.php";


class Flex_Pdf_Template_Page_Wrap_Content extends Flex_Pdf_Template_Element
{
	// Progress of rendering...
	// The next content element to be rendered
	private	$nextChildIndex = 0;
	// Whether or not the initial headers have been rendered
	// (Note: If footers have been printed, it's a safe bet that headers will have been printed)'
	private $footersPrinted = TRUE;

	// Possible breaks
	const PAGE_BREAK_AFTER = 2;
	const SECTION_BREAK_AFTER = 1;
	const NO_BREAK_AFTER = 0;

	// Details of optional break
	private $breakApplied = TRUE;
	private $breakWillBeApplied = FALSE;
	private $requiredBreak = 0;
	private $breakColumnId = null;
	private $reqPageBreak = FALSE;

	// All elements that may be rendered
	private $headerElements = array();
	private $contentElements = array();
	private $footerElements = array();

	// Current page details
	private $pageCount = 0;
	private $clearedDown = FALSE;

	// Record of all parent elements
	private $parents = array();

	// Record of all pages this is included in
	// and at what index
	private $pageWrapperCount = array();
	private $pageWrapperIndex = array();
	private $pageTypes = array();

	// Variables for identifying the current page section
	private $currentParentId = 0;
	private $currentPageColumn = "";

	// Variables for storing values specific to a page section
	private $sectionElements = array();
	private $preparedWidths = array();
	private $preparedHeights = array();
	private $preparedAbsTops = array();
	private $preparedAbsLefts = array();
	private $requiredHeaderHeight = array();
	private $requiredNonEndFooterHeight = array();
	private $requiredEndFooterHeight = array();
	private $lastChildRendered = array();

	public $id = null;

	function initialize()
	{
		$this->id = $this->dom->getAttribute("identifier");

		// Find if there is a break required after the sequence and, if so, what kind
		$breakAfter = $this->dom->hasAttribute("break-after") ? strtolower($this->dom->getAttribute("break-after")) : "none";
		$this->breakApplied = FALSE;

		switch ($breakAfter)
		{
			case "page":
				$this->requiredBreak = self::PAGE_BREAK_AFTER;
				break;
			case "section":
				$this->requiredBreak = self::SECTION_BREAK_AFTER;
				break;
			default:
				$this->breakApplied = TRUE;
		}


		for($i = 0, $l = $this->dom->childNodes->length; $i < $l; $i++)
		{
			$node = $this->dom->childNodes->item($i);
			// Text shouldn't be out there on its own, so just ignore it...
			if ($node instanceof DOMText)
			{
				continue;
			}

			switch (strtoupper($node->tagName))
			{
				case "WRAPPED-HEADER":
					// Span elements shouldn't be at this level, they should only be in P elements.
					$this->initializeHeader($node);
					break;

				case "WRAPPED-FOOTER":
					$this->initializeFooter($node);
					break;

				case "WRAPPED-CONTENT":
					$this->initializeContents($node);
					break;
			}
		}
	}

	function initializeContents($contentNode)
	{
		// Need to parse the page-wrap-content for permitted elements (div, text or table)
		for($i = 0, $l = $contentNode->childNodes->length; $i < $l; $i++)
		{
			$node = $contentNode->childNodes->item($i);
			// Text shouldn't be out there on its own, but we don't want to ignore it...
			if ($node instanceof DOMText)
			{
				// If it is only whitespace, ignore it
				if ($node->isWhitespaceInElementContent())
				{
					continue;
				}
				// Stick the text into a span to be handled properly
				$node = $this->wrapNode($node, "SPAN");
			}

			$child = NULL;

			switch (strtoupper($node->tagName))
			{
				case "A":
				case "SPAN":
					// Span elements shouldn't be at this level, they should only be in P elements.
					$node = $this->wrapNode($node, "P");
					// This still isn't right, so let's go to the next case to sort it out...
				case "P":
					$child = new Flex_Pdf_Template_Paragraph($node, $this);
					break;

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

				case "PAGE-WRAP-INCLUDE":
					//echo("Flex_Pdf_Template_Page_Wrap_Content::initializeContents:: ".$node->getAttribute("content")."\n");
					$this->getTemplate()->registerPageWrapContentNode($node->getAttribute("content"), $this);
					$child = new Flex_Pdf_Template_Page_Wrap_Include($node, $this);
					//echo("Flex_Pdf_Template_Page_Wrap_Content::initializeContents:: COMPLETE (".$node->getAttribute("content").")\n");
					break;

				default:
					// It's not in the right place!
					// Just ignore it for now...
					break;
			}

			if ($child !== NULL)
			{
				if ($child->includeForCurrentMedia())
				{
					$this->contentElements[] = $child;
				}
			}
		}
	}

	function initializeHeader($node)
	{
		$header = new Flex_Pdf_Template_Wrapped_Header($node, $this);
		if ($header->includeForCurrentMedia())
		{
			$this->headerElements[] = $header;
		}
	}

	function initializeFooter($node)
	{
		$footer = new Flex_Pdf_Template_Wrapped_Footer($node, $this);
		if ($footer->includeForCurrentMedia())
		{
			$this->footerElements[] = $footer;
			$this->footersPrinted = FALSE;
		}
	}

	public function registerParent($parent)
	{
		$parentId = count($this->parents);
		$this->parents[$parentId] = $parent;

		$page = $parent->getPage();
		if ($page instanceof Flex_Pdf_Template_Page)
		{
			$pageType = $page->getType();
			if (!array_key_exists($pageType, $this->pageWrapperCount))
			{
				$this->pageWrapperCount[$pageType] = 0;
			}

			$this->pageWrapperIndex[$parentId] = $this->pageWrapperCount[$pageType];

			$this->pageWrapperCount[$pageType]++;

			$this->pageTypes[$parentId] = $pageType;
		}

		return $parentId;
	}

	public function getNumberOfWrappersOnPage()
	{
		if (!array_key_exists($this->currentParentId, $this->pageTypes))
		{
			return $this->parent->getNumberOfWrappersOnPage();
		}
		return $this->pageWrapperCount[$this->pageTypes[$this->currentParentId]];
	}

	public function getIndexOfWrapperOnPage()
	{
		if (!array_key_exists($this->currentParentId, $this->pageTypes))
		{
			/*
			echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . " [$this->id]: Getting index of wrapper from parent";
			if ($this->parent->dom->hasAttribute("where"))
			{
			  echo "<b> " . $this->parent->dom->getAttribute("where") . "</b>";
			}
			*/

			return $this->parent->getIndexOfWrapperOnPage();
		}
		/*
		echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . " [$this->id]: Index of wrapper on page is " .$this->pageWrapperIndex[$this->currentParentId];
		if ($this->parent->dom->hasAttribute("where"))
		{
		  echo "<b> " . $this->parent->dom->getAttribute("where") . "</b>";
		}
		*/

		return $this->pageWrapperIndex[$this->currentParentId];
	}

	public function isFirstWrapperOnPage()
	{
		return $this->currentPageColumn === 0;
	}

	public function isLastWrapperOnPage()
	{
		return $this->currentPageColumn === ($this->getNumberOfWrappersOnPage() - 1);
	}

	public function setCurrentParent($parentId)
	{
		$this->currentParentId = $parentId;
		$this->parent = $this->parents[$this->currentParentId];

		/*
		echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . " [$this->id]: Set parent to $this->currentParentId";
		if ($this->parent->dom->hasAttribute("where"))
		{
		  echo "<b> " . $this->parent->dom->getAttribute("where") . "</b>";
		}
		*/

		$this->currentPageColumn = $this->getIndexOfWrapperOnPage();
	}

	public function isComplete()
	{
		return $this->footersPrinted && $this->nextChildIndex >= count($this->contentElements);
	}

	public function requiresBreak()
	{
		if ($this->reqPageBreak)
		{
			return TRUE;
		}
		if (!$this->breakApplied && (($this->breakColumnId !== NULL && $this->requiredBreak == self::PAGE_BREAK_AFTER) || ($this->currentPageColumn == $this->breakColumnId)))
		{
			return TRUE;
		}
		if (array_key_exists($this->currentPageColumn, $this->lastChildRendered) && $this->lastChildRendered[$this->currentPageColumn] >= 0 && $this->contentElements[$this->lastChildRendered[$this->currentPageColumn]]->requiresBreak())
		{
			return TRUE;
		}
		return FALSE;
	}

	public function requiresPageBreak()
	{
		if ($this->reqPageBreak)
		{
			return TRUE;
		}
		if (!$this->breakApplied && ($this->breakColumnId !== NULL && $this->requiredBreak == self::PAGE_BREAK_AFTER))
		{
			return TRUE;
		}

		if (array_key_exists($this->currentPageColumn, $this->lastChildRendered) && $this->lastChildRendered[$this->currentPageColumn] >= 0 && $this->contentElements[$this->lastChildRendered[$this->currentPageColumn]]->requiresPageBreak())
		{
			return TRUE;
		}
		return FALSE;
	}


	public function isStarted()
	{
		if ($this->nextChildIndex > 0) return TRUE;
		if ($this->isComplete() || $this->reqPageBreak) return TRUE;
		if (!count($this->contentElements)) return FALSE;
		return $this->contentElements[0]->isStarted();
	}

	public function prepareSize($offsetTop=0)
	{
		/*
		 * We need to find the size that the contents of this element **which will fit** into the
		 * parent container will actually use up in the container.
		 *
		 * Note that we need to prepare all headers, footers and contents that could possibly be included,
		 * even if they ultimately will not be.
		 *
		 * Flow contents should not have vertical settings, so it should be enough to process just enough
		 * elements required to fill the available height.
		 *
		 * Flow contents cannot be in-line elements, so width is irrelevant (if it's too wide - too bad, if overflows!)
		 *
		 * If some contents have already been processed, they should be skipped.
		 *
		 * If some contents have been reserved for processiIng within another parent, they should also be skipped.
		 *
		 */

		$this->sectionElements[$this->currentPageColumn] = array();
		$this->sectionElements[$this->currentPageColumn]["headers_and_footers"] = array();
		$this->sectionElements[$this->currentPageColumn]["all"] = array();
		$this->preparedWidths[$this->currentPageColumn] = 0;
		$this->preparedHeights[$this->currentPageColumn] = 0;
		$this->requiredHeaderHeight[$this->currentPageColumn] = 0;
		$this->requiredNonEndFooterHeight[$this->currentPageColumn] = 0;
		$this->requiredEndFooterHeight[$this->currentPageColumn] = 0;
		$this->lastChildRendered[$this->currentPageColumn] =  $this->nextChildIndex - 1;

		if ($this->isComplete() || $this->reqPageBreak)
		{
			return;
		}

		// First page for this wrapper?...
		$firstPage = !$this->isStarted();

		// First section on the page?...
		$firstSection = $this->isFirstWrapperOnPage();

		// Last section on the page?...
		$lastSection = $this->isLastWrapperOnPage();

		/*
		echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . " [$this->id] ";
		if ($this->parent->dom->hasAttribute("where"))
		{
		  echo "<b> " . $this->parent->dom->getAttribute("where") . "</b>";
		}
		echo "<br><span style='color:red; font-weight: bold;'>";

		echo __CLASS__ . "::" . __FUNCTION__ . " [$this->id] :: \$this->nextChildIndex = " . $this->nextChildIndex . "<br>";
		echo __CLASS__ . "::" . __FUNCTION__ . " [$this->id] :: First section for this content? " . ($firstPage ? "Yes" : "No") . "<br>";
		echo __CLASS__ . "::" . __FUNCTION__ . " [$this->id] :: First section on page? " . ($firstSection ? "Yes" : "No") . "<br>";
		echo __CLASS__ . "::" . __FUNCTION__ . " [$this->id] :: Last section on page? " . ($lastSection ? "Yes" : "No") . "<br>";

		echo "</span><hr>";
		*/

		$availableHeight = $this->parent->getAvailablePreparedHeightForChildElement($this);
		$origAvailableHeight = $availableHeight;

		for ($i = 0, $l = count($this->headerElements); $i < $l; $i++)
		{
			if ($this->headerElements[$i]->displayForSection($firstSection, $firstPage))
			{
				$this->headerElements[$i]->prepareSize();
				$this->sectionElements[$this->currentPageColumn]["headers_and_footers"][] = $this->headerElements[$i];
				$this->sectionElements[$this->currentPageColumn]["all"][] = $this->headerElements[$i];

				if (!$this->headerElements[$i]->hasAbsoluteVertical())
				{
					$availableHeight -= $this->headerElements[$i]->getPreparedHeight();
					$this->requiredHeaderHeight[$this->currentPageColumn] += $this->headerElements[$i]->getPreparedHeight();
					$this->preparedHeights[$this->currentPageColumn] += $this->headerElements[$i]->getPreparedHeight();
				}

				$this->preparedWidths[$this->currentPageColumn] = max($this->preparedWidths[$this->currentPageColumn], $this->headerElements[$i]->getPreparedWidth());
			}
		}

		$this->requiredNonEndFooterHeight[$this->currentPageColumn] = 0;
		$this->requiredEndFooterHeight[$this->currentPageColumn] = 0;

		$optionalEndFooterHeight = 0;

		for ($i = 0, $l = count($this->footerElements); $i < $l; $i++)
		{
			$this->footerElements[$i]->prepareSize();
			if ($this->footerElements[$i]->displayForSection($lastSection, FALSE))
			{
				if ($this->footerElements[$i]->isOptional())
				{
					continue;
				}
				if (!$this->footerElements[$i]->hasAbsoluteVertical())
				{
					$this->requiredNonEndFooterHeight[$this->currentPageColumn] += $this->footerElements[$i]->getPreparedHeight();
					//$nrNonEndFooters++;
				}
			}
			if ($this->footerElements[$i]->displayForSection(TRUE, TRUE))
			{
				if (!$this->footerElements[$i]->hasAbsoluteVertical())
				{
					if ($this->footerElements[$i]->isOptional())
					{
						$optionalEndFooterHeight += $this->footerElements[$i]->getPreparedHeight();
						continue;
					}
					$this->requiredEndFooterHeight[$this->currentPageColumn] += $this->footerElements[$i]->getPreparedHeight();
					//$nrEndFooters++;
				}
			}
		}

		$requiredHeight = 0;
		$headerHeight = $requiredHeight;
		$absoluteRequiredHeight = 0;

		$i = $this->nextChildIndex;
		$l = count($this->contentElements);
		$lastChildFinished = TRUE;
		$lastChildRequiresBreak = FALSE;
		for (; $lastChildFinished && !$lastChildRequiresBreak && $i < $l && $requiredHeight <= $availableHeight && $absoluteRequiredHeight <= $origAvailableHeight; $i++)
		{
			$this->contentElements[$i]->prepareSize($this);
			if (!$this->contentElements[$i]->hasAbsoluteVertical())
			{
				$requiredHeight += $this->contentElements[$i]->getPreparedHeight();
			}
			else
			{
				$absoluteRequiredHeight = max($absoluteRequiredHeight, $this->contentElements[$i]->getRequiredHeight());
			}
			$lastChildFinished = $this->contentElements[$i]->isComplete();
			$lastChildRequiresBreak = $this->contentElements[$i]->requiresBreak();
		}

		$requiredHeight = max($requiredHeight, $absoluteRequiredHeight - $headerHeight);

		if ($lastChildRequiresBreak)
		{
			$this->reqPageBreak = $this->contentElements[$i - 1]->requiresPageBreak();
		}

		$heightAvailableForFooters = $availableHeight - $requiredHeight;

		$canFinish = ($lastChildFinished && $i == $l && ($heightAvailableForFooters >= $this->requiredEndFooterHeight[$this->currentPageColumn]));

		if ($canFinish)
		{
			$heightAvailableForOptionalFooters = $heightAvailableForFooters - $this->requiredEndFooterHeight[$this->currentPageColumn];

			for ($i = $this->nextChildIndex, $l = count($this->contentElements); $i < $l; $i++)
			{
				$this->sectionElements[$this->currentPageColumn]["all"][] = $this->contentElements[$i];
				$this->preparedWidths[$this->currentPageColumn] = max($this->preparedWidths[$this->currentPageColumn], $this->contentElements[$i]->getPreparedWidth());
				if (!$this->contentElements[$i]->hasAbsoluteVertical())
				{
					$this->preparedHeights[$this->currentPageColumn] += $this->contentElements[$i]->getPreparedHeight();
				}
				$this->lastChildRendered[$this->currentPageColumn] = $this->nextChildIndex;
				$this->nextChildIndex++;
			}

			for ($i = 0, $l = count($this->footerElements); $i < $l; $i++)
			{
				$hasAbsVertical = $this->footerElements[$i]->hasAbsoluteVertical();
				$prepHeight = $this->footerElements[$i]->getPreparedHeight();
				if (!$hasAbsVertical && $this->footerElements[$i]->isOptional())
				{
					if ($heightAvailableForOptionalFooters < $prepHeight)
					{
						continue;
					}
					$heightAvailableForOptionalFooters -= $prepHeight;
				}
				if ($this->footerElements[$i]->displayForSection(TRUE, TRUE))
				{
					$this->sectionElements[$this->currentPageColumn]["headers_and_footers"][] = $this->footerElements[$i];
					$this->sectionElements[$this->currentPageColumn]["all"][] = $this->footerElements[$i];
					$this->preparedWidths[$this->currentPageColumn] = max($this->preparedWidths[$this->currentPageColumn], $this->footerElements[$i]->getPreparedWidth());
					if (!$hasAbsVertical)
					{
						$this->preparedHeights[$this->currentPageColumn] += $prepHeight;
					}
				}
			}

			if ($absoluteRequiredHeight > $this->preparedHeights[$this->currentPageColumn])
			{
				$this->preparedHeights[$this->currentPageColumn] = $absoluteRequiredHeight;
			}

			$this->footersPrinted = TRUE;

			$this->breakColumnId = $this->currentPageColumn;
		}
		else
		{
			$childHeight = 0;
			$absChildHeight = 0;
			$lastChildCompleted = FALSE;
			$origAvailableHeight = $availableHeight;

			for ($i = $this->nextChildIndex, $l = count($this->contentElements); $i < $l; $i++)
			{
				if (!$this->contentElements[$i]->hasAbsoluteVertical())
				{
					// If the height is only sufficient for footers, then there is no point trying to add more relatively positioned content
					// and chances are, if there was no height available for it, it won't have been prepared yet!
					if ($availableHeight <= $this->requiredNonEndFooterHeight[$this->currentPageColumn]) break;
					$availableHeight -= $this->contentElements[$i]->getPreparedHeight();
					// If there isn't enough height for the footers, then we need to break
					if ($availableHeight < $this->requiredNonEndFooterHeight[$this->currentPageColumn]) break;
				}
				else
				{
					if ($origAvailableHeight < $this->contentElements[$i]->getRequiredHeight()) break;
				}
				$this->sectionElements[$this->currentPageColumn]["all"][] = $this->contentElements[$i];
				$this->preparedWidths[$this->currentPageColumn] = max($this->preparedWidths[$this->currentPageColumn], $this->contentElements[$i]->getPreparedWidth());
				if (!$this->contentElements[$i]->hasAbsoluteVertical())
				{
					$this->preparedHeights[$this->currentPageColumn] += $this->contentElements[$i]->getPreparedHeight();
					$childHeight += $this->contentElements[$i]->getPreparedHeight();
				}
				else
				{
					$absChildHeight = max($absChildHeight, $this->contentElements[$i]->getRequiredHeight());
				}




				if ($this->contentElements[$i]->isComplete())
				{
					$lastChildCompleted = TRUE;
					$this->lastChildRendered[$this->currentPageColumn] = $this->nextChildIndex;
					$this->nextChildIndex++;
					if ($this->contentElements[$i]->requiresBreak())
					{
						break;
					}
				}
				else
				{
					$lastChildCompleted = FALSE;
					break;
				}
			}

			// If nothing has been output and there is something to output, don't display the headers and footers
			if ($childHeight === 0 && $absChildHeight === 0 && !$lastChildCompleted)
			{
				$this->sectionElements[$this->currentPageColumn]["headers_and_footers"] = array();
				$this->sectionElements[$this->currentPageColumn]["all"] = array();
				$this->preparedHeights[$this->currentPageColumn] = 0;
				$this->preparedWidths[$this->currentPageColumn] = 0;
				$this->requiredEndFooterHeight[$this->currentPageColumn] = 0;
				$this->requiredNonEndFooterHeight[$this->currentPageColumn] = 0;
				$this->requiredHeaderHeight[$this->currentPageColumn] = 0;
				return;
			}

			for ($i = 0, $l = count($this->footerElements); $i < $l; $i++)
			{
				if (!$this->footerElements[$i]->isOptional() && $this->footerElements[$i]->displayForSection($lastSection, FALSE))
				{
					$this->sectionElements[$this->currentPageColumn]["headers_and_footers"][] = $this->footerElements[$i];
					$this->sectionElements[$this->currentPageColumn]["all"][] = $this->footerElements[$i];
					$this->preparedWidths[$this->currentPageColumn] = max($this->preparedWidths[$this->currentPageColumn], $this->footerElements[$i]->getPreparedWidth());
					if (!$this->footerElements[$i]->hasAbsoluteVertical())
					{
						$this->preparedHeights[$this->currentPageColumn] += $this->footerElements[$i]->getPreparedHeight();
					}
				}
			}

			if ($absoluteRequiredHeight > $this->preparedHeights[$this->currentPageColumn])
			{
				$this->preparedHeights[$this->currentPageColumn] = $absoluteRequiredHeight;
			}

		}
		//echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . " [$this->id] :END:";
	}

	public function getPreparedWidth()
	{
		return $this->preparedWidths[$this->currentPageColumn];
	}

	public function getPreparedHeight()
	{
		return $this->preparedHeights[$this->currentPageColumn];
	}

	public function getRequiredWidth()
	{
		return $this->getPreparedWidth();
	}

	public function getRequiredHeight()
	{
		return $this->getPreparedHeight();
	}

	public function getOffsetLeft()
	{
		return 0;
	}

	public function getOffsetRight()
	{
		return 0;
	}

	public function getOffsetTop()
	{
		return 0;
	}

	public function getOffsetBottom()
	{
		return 0;
	}

	public function preparePosition($parentWidth=0, $parentHeight=0, $offsetTop=0, $offsetLeft=0)
	{
		$this->preparedAbsTops[$this->currentPageColumn] = $offsetTop;
		$this->preparedAbsLefts[$this->currentPageColumn] = $offsetLeft;
	}

	public function prepareChildPositions()
	{
		$offsetTop = $this->getPreparedAbsTop();
		$offsetLeft = $this->getPreparedAbsLeft();
		$childElements = $this->getChildElements();
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			$childElements[$i]->preparePosition($this->getPreparedWidth(), $this->getPreparedHeight(), $offsetTop, $offsetLeft);
			if (!$childElements[$i]->hasAbsoluteVertical())
			{
				$offsetTop += $childElements[$i]->getPreparedHeight();
			}
		}
	}

	public function getPreparedAbsTop()
	{
		return $this->preparedAbsTops[$this->currentPageColumn];
	}

	public function getPreparedAbsLeft()
	{
		return $this->preparedAbsLefts[$this->currentPageColumn];
	}

	public function getChildElements()
	{
		return array_key_exists($this->currentPageColumn, $this->sectionElements) ? $this->sectionElements[$this->currentPageColumn]["all"] : array();
	}

	function renderOnPage($page, $parent=NULL)
	{

		// Increment the rendering counter for the current page

		// Prepare the widths and heights of the headers and footers to be rendered,
		// as these could have been reset for other wrappers
		for ($i = 0, $l = count($this->sectionElements[$this->currentPageColumn]["headers_and_footers"]); $i < $l; $i++)
		{
			$this->sectionElements[$this->currentPageColumn]["headers_and_footers"][$i]->prepareSize($this);
		}
		$this->headersPrinted = TRUE;

		// Prepare the child positions as this has not yet been done
		$this->prepareChildPositions();

		// Render the child elements
		$childElements = $this->getChildElements();
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			$childElements[$i]->renderOnPage($page, $this);
		}

		// Reset the cleardown flag ready for the next page
		$this->clearedDown = FALSE;
	}

	public function appendToDom($doc, $parentNode, $parent=NULL)
	{
		// Prepare the widths and heights of the headers and footers to be rendered,
		// as these could have been reset for other wrappers
		for ($i = 0, $l = count($this->sectionElements[$this->currentPageColumn]["headers_and_footers"]); $i < $l; $i++)
		{
			$this->sectionElements[$this->currentPageColumn]["headers_and_footers"][$i]->prepareSize($this);
		}
		$this->headersPrinted = TRUE;

		// Prepare the child positions as this has not yet been done
		$this->prepareChildPositions();

		// Create a node for this element
		$node = $parentNode;

		$childElements = $this->getChildElements();
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			$childElements[$i]->appendToDom($doc, $node, $this);
		}

		// Apply the style to this node
		//$node->setAttribute("style", $this->getStyle()->getHTMLStyleAttributeValue());

		// Append this node to the parentNode
		//$parentNode->appendChild($node);
	}


	public function clearTemporaryDetails()
	{
		// This function should be invoked just once for each of the wrappers on the page

		// Only allow one clear down per page
		if ($this->clearedDown) return;

		$childElements = $this->getChildElements();
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			$childElements[$i]->resetForNextPage();
		}

		$this->sectionElements = array();
		$this->preparedWidths = array();
		$this->preparedHeights = array();
		$this->requiredHeaderHeight = array();
		$this->requiredNonEndFooterHeight = array();
		$this->requiredEndFooterHeight = array();
		$this->lastChildRendered = array();

		if ($this->breakColumnId !== NULL)
		{
			$this->breakApplied = TRUE;
		}
		$this->reqPageBreak = FALSE;
		$this->breakColumnId = NULL;

		if ($this->nextChildIndex > 0)
		{
			$this->pageCount++;
		}
		$this->clearedDown = TRUE;
	}

	public function getAvailablePreparedHeightForChildElement($childElement)
	{
		$availableHeight = $this->parent->getAvailablePreparedHeightForChildElement($this);

		// We need to deduct from this the height required for headers
		$availableHeight -= $this->requiredHeaderHeight[$this->currentPageColumn];

		// We also need to allow for footers, which depends on whether or not this is the last page!
	//	$this->requiredNonEndFooterHeight[$this->currentPageColumn] = 0;
	//	$this->requiredEndFooterHeight[$this->currentPageColumn] = 0;

		// We also need to deduct the height of non-vertically-absolutely positioned earlier child elements
		for ($i = $this->nextChildIndex, $l = count($this->contentElements); $i < $l; $i++)
		{
			// If we've reached the child element concerned then we don't need to look further
			if ($this->contentElements[$i] === $childElement || ($this->contentElements[$i] instanceof Flex_Pdf_Template_Page_Wrap_Include && $this->contentElements[$i]->getPageWrapContent() === $childElement))
			{
				break;
			}
			// Ignore child elements that do not flow vertically in the layout
			if (!$this->contentElements[$i]->hasAbsoluteVertical())
			{
				$availableHeight -= $this->contentElements[$i]->getPreparedHeight();
			}
		}

		// HACK! HACK! HACK!
		// This should really determine whether or not the remaining child elements (including and after childElement)
		// can fit in the remaining space, to determine which footer height to deduct... or does that matter?
		// Only if the non-end footers are longer than the end footers! Unlikely?
		// Checking that is a bit fiddly and could easily end up with a recursive loop.

		$availableHeight -= $this->requiredNonEndFooterHeight[$this->currentPageColumn];

		// Whatever is left over is the height available for child elements
		return $availableHeight;
	}

	public function _destroy()
	{
		$childElements =& $this->headerElements;
		for ($i = count($childElements) - 1; $i >= 0; $i--)
		{
			$childElements[$i]->_destroy();
			unset($childElements[$i]);
		}
		$childElements =& $this->contentElements;
		for ($i = count($childElements) - 1; $i >= 0; $i--)
		{
			$childElements[$i]->_destroy();
			unset($childElements[$i]);
		}
		$childElements =& $this->footerElements;
		for ($i = count($childElements) - 1; $i >= 0; $i--)
		{
			$childElements[$i]->_destroy();
			unset($childElements[$i]);
		}

		parent::_destroy();

		unset($this->pageWrapperCount, $this->pageWrapperIndex, $this->pageTypes, $this->currentParentId, $this->currentPageColumn, $this->sectionElements, $this->preparedWidths, $this->id);
		unset($this->preparedHeights, $this->preparedAbsTops, $this->preparedAbsLefts, $this->requiredHeaderHeight, $this->requiredNonEndFooterHeight, $this->requiredEndFooterHeight, $this->lastChildRendered);
	}

}

?>
