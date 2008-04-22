<?php

require_once dirname(__FILE__) . "/Flex_Pdf_Template_Wrapped_Header.php";
require_once dirname(__FILE__) . "/Flex_Pdf_Template_Wrapped_Footer.php";


class Flex_Pdf_Template_Page_Wrap_Content extends Flex_Pdf_Template_Element
{
	// Progress of rendering...
	// The next content element to be rendered
	private	$nextChildIndex = 0;
	// Whether or not the initial headers have been rendered
	// (Note: If footers have been printed, it's a safe bet that headers will have been printed)
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

	function initialize()
	{
		// Find if there is a break required after the sequence and, if so, what kind
		$breakAfter = $this->dom->hasAttribute("break-after") ? strtolower($this->dom->getAttribute("break-after")) : "none";
		$this->breakApplied = FALSE;
		echo "<br>$breakAfter";
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
		echo " = $this->requiredBreak<br>";
		
		
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
			
			switch (strtoupper($node->tagName))
			{
				case "SPAN":
					// Span elements shouldn't be at this level, they should only be in P elements.
					$node = $this->wrapNode($node, "P");
					// This still isn't right, so let's go to the next case to sort it out...
				case "P":
					$this->contentElements[] = new Flex_Pdf_Template_Paragraph($node, $this);
					break;

				case "DIV":
					$this->contentElements[] = new Flex_Pdf_Template_Div($node, $this);
					break;

				case "IMG":
					$this->contentElements[] = new Flex_Pdf_Template_Image($node, $this);
					break;

				case "BARCODE":
					$this->contentElements[] = new Flex_Pdf_Template_Barcode($node, $this);
					break;

				case "PAGE-WRAP-INCLUDE":
					$this->getTemplate()->registerPageWrapContentNode($node->getAttribute("content"), $this);
					$this->contentElements[] = new Flex_Pdf_Template_Page_Wrap_Include($node, $this);
					break;
					
				default:
					// It's not in the right place! 
					// Just ignore it for now...
					break;
			}
		}
	}
	
	function initializeHeader($node)
	{
		$this->headerElements[] = new Flex_Pdf_Template_Wrapped_Header($node, $this);
	}
	
	function initializeFooter($node)
	{
		$this->footerElements[] = new Flex_Pdf_Template_Wrapped_Footer($node, $this);
		$this->footersPrinted = FALSE;
	}
	
	public function registerParent($parent)
	{
		$parentId = count($this->parents);
		$this->parents[$parentId] = $parent;
		echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . "... " . $this->dom->getAttribute("id") . " ... $parentId ... " . get_class($this->parents[$parentId]) . "<br>";
		
		$page = $parent->getPage();
		if ($page instanceof Flex_Pdf_Template_Page)
		{
			$pageType = $page->getType();
			if (!array_key_exists($pageType, $this->pageWrapperCount))
			{
				$this->pageWrapperCount[$pageType] = 0;
			}
			$this->pageWrapperCount[$pageType]++;
			
			$this->pageWrapperIndex[$parentId] = $this->pageWrapperCount[$pageType] - 1;
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
			return $this->parent->getIndexOfWrapperOnPage();
		}
		return $this->pageWrapperIndex[$this->currentParentId];
	}
	
	public function isFirstWrapperOnPage()
	{
		return $this->getIndexOfWrapperOnPage() === 0;
	}
	
	public function isLastWrapperOnPage()
	{
		return $this->getIndexOfWrapperOnPage() === ($this->getNumberOfWrappersOnPage() - 1);
	}
	
	public function setCurrentParent($parentId)
	{
		$this->currentParentId = $parentId;
		$this->parent = $this->parents[$this->currentParentId];
		//echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . "... " . $this->dom->getAttribute("id") . "  ... $parentId ... " . get_class($this->parents[$this->currentParentId]) . "<br>";
		$this->currentPageColumn = $this->getIndexOfWrapperOnPage();
	}

	public function isComplete()
	{
		echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . " ... " . $this->dom->getAttribute("id") . "... Is complete? [$this->footersPrinted, " .($this->nextChildIndex >= count($this->contentElements)). "]" . ($this->headersPrinted && $this->footersPrinted && $this->nextChildIndex >= count($this->contentElements)) . "<hr>";
		return $this->footersPrinted && $this->nextChildIndex >= count($this->contentElements);
	}
	
	public function requiresBreak()
	{
		echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . " ... " . $this->dom->getAttribute("id") . "... [!{$this->breakApplied} && (($this->breakColumnId !== NULL && $this->requiredBreak == 2) || ($this->currentPageColumn == $this->breakColumnId))] ";
		echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . " ... " . $this->dom->getAttribute("id") . "... Requires break? ";
		if ($this->reqPageBreak)
		{
			return TRUE;
		}
		if (!$this->breakApplied && (($this->breakColumnId !== NULL && $this->requiredBreak == self::PAGE_BREAK_AFTER) || ($this->currentPageColumn == $this->breakColumnId)))
		{
			echo "Yes! <hr>";
			return TRUE;
		}
		if (array_key_exists($this->currentPageColumn, $this->lastChildRendered) && $this->lastChildRendered[$this->currentPageColumn] >= 0 && $this->contentElements[$this->lastChildRendered[$this->currentPageColumn]]->requiresBreak())
		{
			echo "Yes, because child does! <hr>";
			return TRUE;
		}
		echo "No. <hr>";
		return FALSE;
	}

	public function requiresPageBreak()
	{
		echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . " ... " . $this->dom->getAttribute("id") . "... Requires break? ";
		if ($this->reqPageBreak)
		{
			echo "Yes! (because the last rendered child did)<hr>";
			return TRUE;
		}
		if (!$this->breakApplied && ($this->breakColumnId !== NULL && $this->requiredBreak == self::PAGE_BREAK_AFTER))
		{
			echo "Yes! (because I do)<hr>";
			return TRUE;
		}
		
		echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . " ... " . $this->dom->getAttribute("id") . "... $this->currentPageColumn :: " . ($this->lastChildRendered[$this->currentPageColumn]) . " :: " . get_class($this->contentElements[$this->lastChildRendered[$this->currentPageColumn]]);
		if (array_key_exists($this->currentPageColumn, $this->lastChildRendered) && $this->lastChildRendered[$this->currentPageColumn] >= 0 && $this->contentElements[$this->lastChildRendered[$this->currentPageColumn]]->requiresPageBreak())
		{
			echo "Yes! (because one of my kids does)<hr>";
			return TRUE;
		}
		return FALSE;
	}

	public function prepareSize($offsetTop=0)
	{
		//echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . "... " . $this->dom->getAttribute("id") . " ...<br>";

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
		 
		echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: START currentPageColumn = " . $this->currentPageColumn . "... " . $this->dom->getAttribute("id") . " ...<br>";
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
		echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: END (complete) currentPageColumn = " . $this->currentPageColumn . "... " . $this->dom->getAttribute("id") . " ...<br>";
			return;
		}
		
		$firstPage = $this->nextChildIndex == 0;
		$firstSection = $this->isFirstWrapperOnPage();
		$lastSection = $this->isLastWrapperOnPage();
		
		$availableHeight = $this->parent->getAvailablePreparedHeightForChildElement($this);
		//echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: currentPageColumn = " . $this->currentPageColumn . "... availableHeight = $availableHeight... " . $this->dom->getAttribute("id") . " ...<br>";
		
		for ($i = 0, $l = count($this->headerElements); $i < $l; $i++)
		{
		//echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: checking header " . ($i+1) . " $firstSection $firstPage...<br>";
			if ($this->headerElements[$i]->displayForSection($firstSection, $firstPage))
			{
				$this->headerElements[$i]->prepareSize();
				$this->sectionElements[$this->currentPageColumn]["headers_and_footers"][] = $this->headerElements[$i];
				$this->sectionElements[$this->currentPageColumn]["all"][] = $this->headerElements[$i];
				
				if (!$this->headerElements[$i]->hasAbsoluteVertical())
				{
					$availableHeight -= $this->headerElements[$i]->getPreparedHeight();
			//echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: currentPageColumn = " . $this->currentPageColumn . "... availableHeight = $availableHeight... " . $this->dom->getAttribute("id") . " (after header $i) ...<br>";
					$this->requiredHeaderHeight[$this->currentPageColumn] += $this->headerElements[$i]->getPreparedHeight();
					$this->preparedHeights[$this->currentPageColumn] += $this->headerElements[$i]->getPreparedHeight();
				}
				
				$this->preparedWidths[$this->currentPageColumn] = max($this->preparedWidths[$this->currentPageColumn], $this->headerElements[$i]->getPreparedWidth());
			}
		//else echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: header " . ($i+1) . " not to be rendered<br>";
		}
		
		$this->requiredNonEndFooterHeight[$this->currentPageColumn] = 0;
		$this->requiredEndFooterHeight[$this->currentPageColumn] = 0;
		
		for ($i = 0, $l = count($this->footerElements); $i < $l; $i++)
		{
			$this->footerElements[$i]->prepareSize();
			if ($this->footerElements[$i]->displayForSection($lastSection, FALSE))
			{
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
					$this->requiredEndFooterHeight[$this->currentPageColumn] += $this->footerElements[$i]->getPreparedHeight();
					//$nrEndFooters++;
				}
			}
		}
		
		//echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: currentPageColumn = " . $this->currentPageColumn . "... end footers require " . $this->requiredEndFooterHeight[$this->currentPageColumn] . "... " . $this->dom->getAttribute("id") . " <br>";
		//echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: currentPageColumn = " . $this->currentPageColumn . "... non-end footers require " . $this->requiredNonEndFooterHeight[$this->currentPageColumn] . "... " . $this->dom->getAttribute("id") . " <br>";
		//echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: currentPageColumn = " . $this->currentPageColumn . "... available height = " . $availableHeight . "... " . $this->dom->getAttribute("id") . " <br>";
		
		$requiredHeight = 0;
		
		$i = $this->nextChildIndex;
		$l = count($this->contentElements);
		$lastChildFinished = TRUE;
		$lastChildRequiresBreak = FALSE;
		for (; $lastChildFinished && !$lastChildRequiresBreak && $i < $l && $requiredHeight <= $availableHeight; $i++)
		{
		//echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: currentPageColumn = " . $this->currentPageColumn . "... preparing child height " . $i . " ...  " . $this->dom->getAttribute("id") . " ...<br>";
			$this->contentElements[$i]->prepareSize($this);
			$requiredHeight += $this->contentElements[$i]->getPreparedHeight();
			$lastChildFinished = $this->contentElements[$i]->isComplete();
			echo "<br>Class $i = " . get_class($this->contentElements[$i]) . ",";
			$lastChildRequiresBreak = $this->contentElements[$i]->requiresBreak();
		}
		
		if ($lastChildRequiresBreak)
		{
			echo "<br>Las Break Child Class $i = " . get_class($this->contentElements[$i - 1]) . "!";
			$this->reqPageBreak = $this->contentElements[$i - 1]->requiresPageBreak();
		}

		$canFinish = ($lastChildFinished && $i == $l && (($availableHeight - $requiredHeight) > $this->requiredEndFooterHeight[$this->currentPageColumn]));
		//echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: height required for contents to complete = " . $requiredHeight . "<br>";
		
		if ($canFinish)
		{
			
			echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: currentPageColumn = " . $this->currentPageColumn . "... <span style='color:green'>Can finish!!!... " . $this->dom->getAttribute("id") . " </span><br>";
			for ($i = $this->nextChildIndex, $l = count($this->contentElements); $i < $l; $i++)
			{
				$this->sectionElements[$this->currentPageColumn]["all"][] = $this->contentElements[$i];
				$this->preparedWidths[$this->currentPageColumn] = max($this->preparedWidths[$this->currentPageColumn], $this->contentElements[$i]->getPreparedWidth());
				$this->preparedHeights[$this->currentPageColumn] += $this->contentElements[$i]->getPreparedHeight();
				$this->lastChildRendered[$this->currentPageColumn] = $this->nextChildIndex;
				$this->nextChildIndex++;
				//echo " incrementing next child index to $this->nextChildIndex ... ";
			}
			for ($i = 0, $l = count($this->footerElements); $i < $l; $i++)
			{
				if ($this->footerElements[$i]->displayForSection(TRUE, TRUE))
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
			$this->footersPrinted = TRUE;
			
			$this->breakColumnId = $this->currentPageColumn;
			echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . " ... " . $this->dom->getAttribute("id") . "... set breakColumnId to $this->breakColumnId<hr>";
			echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . " ... " . $this->dom->getAttribute("id") . "... $this->currentPageColumn :: " . $this->lastChildRendered[$this->currentPageColumn] . " :: " . get_class($this->contentElements[$this->lastChildRendered[$this->currentPageColumn]]);
			
		}
		else 
		{
			
		//echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: currentPageColumn = " . $this->currentPageColumn . "... <span style='color:red'>Can NOT finish!!!</span><br>";
			$childHeight = 0;
			$lastChildCompleted = FALSE;

			for ($i = $this->nextChildIndex, $l = count($this->contentElements); $i < $l; $i++)
			{
				$availableHeight -= $this->contentElements[$i]->getPreparedHeight();
		//echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: currentPageColumn = " . $this->currentPageColumn . "... availableHeight = $availableHeight... " . $this->dom->getAttribute("id") . " (before adding child $i) ...<br>";
				if ($availableHeight <= $this->requiredNonEndFooterHeight[$this->currentPageColumn]) break;
				$this->sectionElements[$this->currentPageColumn]["all"][] = $this->contentElements[$i];
				$this->preparedWidths[$this->currentPageColumn] = max($this->preparedWidths[$this->currentPageColumn], $this->contentElements[$i]->getPreparedWidth());
				$this->preparedHeights[$this->currentPageColumn] += $this->contentElements[$i]->getPreparedHeight();
				$childHeight += $this->contentElements[$i]->getPreparedHeight();
				if ($this->contentElements[$i]->isComplete()) 
				{
					$lastChildCompleted = TRUE;
					$this->lastChildRendered[$this->currentPageColumn] = $this->nextChildIndex;
					$this->nextChildIndex++;
					if ($this->contentElements[$i]->requiresBreak())
					{
						break;
					}
					//echo " incrementing next child index to $this->nextChildIndex ... ";
				}
				else
				{
					//echo " breaking because last child did not complete. ";
					$lastChildCompleted = FALSE;
					break;
				}
			}
			
			// If nothing has been output and there is something to output, don't display the headers and footers
			if ($childHeight === 0 && !$lastChildCompleted)
			{
		//echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: currentPageColumn = " . $this->currentPageColumn . " ... " . $this->dom->getAttribute("id") . " nothing to wrap! ...<br>";
				$this->sectionElements[$this->currentPageColumn]["headers_and_footers"] = array();
				$this->sectionElements[$this->currentPageColumn]["all"] = array();
				$this->preparedHeights[$this->currentPageColumn] = 0;
				$this->preparedWidths[$this->currentPageColumn] = 0;
				$this->requiredEndFooterHeight[$this->currentPageColumn] = 0;
				$this->requiredNonEndFooterHeight[$this->currentPageColumn] = 0;
				$this->requiredHeaderHeight[$this->currentPageColumn] = 0;
		echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: END (early) currentPageColumn = " . $this->currentPageColumn . "... " . $this->dom->getAttribute("id") . " ...<br>";
				return;
			}
		//else echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: currentPageColumn = " . $this->currentPageColumn . " ... " . $this->dom->getAttribute("id") . " wrapping " . $childHeight . " " . ($lastChildCompleted ? "complete" : "unfinished") . " ...<br>";
			
			for ($i = 0, $l = count($this->footerElements); $i < $l; $i++)
			{
				if ($this->footerElements[$i]->displayForSection($lastSection, FALSE))
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
		}
		
		echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: END currentPageColumn = " . $this->currentPageColumn . "... " . $this->dom->getAttribute("id") . " ...<br>";
		//echo "<br>" . __CLASS__ . "::" . __FUNCTION__ . ":: " . $this->dom->getAttribute("id") . " :: currentPageColumn = " . $this->currentPageColumn . " ... prepared height = " . $this->preparedHeights[$this->currentPageColumn] . "<br>";
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
		//echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . " has not been implemented.<hr>";

		//echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . ":: " . $this->dom->getAttribute("id") . " :: currentPageColumn = " . $this->currentPageColumn . " ... <hr>";
		
		$this->preparedAbsTops[$this->currentPageColumn] = $offsetTop;
		$this->preparedAbsLefts[$this->currentPageColumn] = $offsetLeft;
	}

	public function prepareChildPositions()
	{
		//echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . ":: " . $this->dom->getAttribute("id") . " :: currentPageColumn = " . $this->currentPageColumn . " ... <hr>";
		$offsetTop = $this->getPreparedAbsTop();
		$offsetLeft = $this->getPreparedAbsLeft();
		$childElements = $this->getChildElements();
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			$childElements[$i]->preparePosition($this->getPreparedWidth(), $this->getPreparedHeight(), $offsetTop, $offsetLeft);
			if (!$childElements[$i]->hasAbsoluteVertical())
			{
				$offsetTop += $childElements[$i]->getPreparedHeight();
				//echo "<hr>Child of " . $this->currentPageColumn . " class " . get_class($childElements[$i]) . " does not have absolute vertical!<br>";
			}
			//else
			//{
			//	echo "<hr>Child of " . $this->currentPageColumn . " class " . get_class($childElements[$i]) . " has absolute vertical!<br>";
			//}
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
		return $this->sectionElements[$this->currentPageColumn]["all"];
	}
	
	function renderOnPage($page, $parent=NULL)
	{
		
		// Increment the rendering counter for the current page
		//echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . ":: " . $this->dom->getAttribute("id") . " :: currentPageColumn = " . $this->currentPageColumn . " ... <hr>";

		// Prepare the widths and heights of the headers and footers to be rendered, 
		// as these could have been reset for other wrappers
		for ($i = 0, $l = count($this->sectionElements[$this->currentPageColumn]["headers_and_footers"]); $i < $l; $i++)
		{
			$this->sectionElements[$this->currentPageColumn]["headers_and_footers"][$i]->prepareSize($this);
		}
		$this->headersPrinted = TRUE;
		echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . ":: " . $this->dom->getAttribute("id") . " :: currentPageColumn = " . $this->currentPageColumn . " ... set headers to printed ($this->headersPrinted) ... <hr>";
		
		// Prepare the child positions as this has not yet been done
		$this->prepareChildPositions();
		
		// Render the child elements
		$childElements = $this->getChildElements();
		//echo "<hr>Rendering wrapped contents (" . count($childElements) . ")... <hr>";
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
		
		echo "<hr>" . __CLASS__ . "::" . __FUNCTION__ . ":: " . $this->dom->getAttribute("id") . " :: clear temp details... <hr>";
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
		//echo "<hr> " . $this->dom->getAttribute("id") . " ...Available height is $availableHeight<hr>";
		
		// We need to deduct from this the height required for headers
		$availableHeight -= $this->requiredHeaderHeight[$this->currentPageColumn];
		//echo "<hr> " . $this->dom->getAttribute("id") . " ...Available height after headers is $availableHeight<hr>";

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
		//echo "<hr> " . $this->dom->getAttribute("id") . " ...Available height after earlier child elements is $availableHeight<hr>";
		
		// HACK! HACK! HACK!
		// This should really determine whether or not the remaining child elements (including and after childElement)
		// can fit in the remaining space, to determine which footer height to deduct... or does that matter? 
		// Only if the non-end footers are longer than the end footers! Unlikely?
		// Checking that is a bit fiddly and could easily end up with a recursive loop.
		
		$availableHeight -= $this->requiredNonEndFooterHeight[$this->currentPageColumn];
		//echo "<hr> " . $this->dom->getAttribute("id") . " ...Available height after allowing for footers (" . $this->requiredNonEndFooterHeight[$this->currentPageColumn] . ") is $availableHeight<hr>";
		
		// Whatever is left over is the height available for child elements
		return $availableHeight;
	}
}

?>
