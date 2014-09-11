<?php

abstract class Flex_Pdf_Template_Element
{
	public $dom = NULL;
	public $style = NULL;
	public $parent = NULL;
	public $childElements = array();

	public $_depth = 0;

	private $_bolInGetWidth = FALSE;

	private $_bolSuitableForTargetMedia = NULL;
	
	private $_strLinkTargetName = NULL;

	public $requiredWidth = 0;
	public $requiredHeight = 0;
	public $preparedWidth = 0;
	public $preparedHeight = 0;

	public $preparedAbsTop = 0;
	public $preparedAbsLeft = 0;

	public function __construct($domNode, $parentElement)
	{
		$this->dom = $domNode;
		$this->parent = $parentElement;

		$this->prepareStyle($this->parent->getStyle());

		if (!$this->includeForCurrentMedia())
		{
			return;
		}
		
		if ($domNode->hasAttribute('id'))
		{
			$this->_strLinkTargetName = $domNode->getAttribute('id');
		}

		$this->initialize();
	}

	public function getDepth()
	{
		return ($this->parent == NULL ? 0 : $this->parent->getDepth()) + 1;
	}

	protected function prepareStyle($inheritedStyle)
	{
		$this->style = new Flex_Pdf_Style($inheritedStyle);

		if ($this->dom->hasAttribute("style"))
		{
			$this->style->applyStyleAttribute($this->dom->getAttribute("style"));
		}
	}

	public function getStyle()
	{
		return $this->style;
	}

	function getTemplate()
	{
		return $this->parent->getTemplate();
	}

	public function getResourcePath($relativePath)
	{
		return $this->getTemplate()->getResourcePath($relativePath);
	} 

	function getCurrentPageNumber()
	{
		return $this->getTemplate()->getCurrentPageNumber();
	}

	function getPage()
	{
		if ($this->parent instanceof Flex_Pdf_Template_Element)
		{
			return $this->parent->getPage();
		}
		return null;
	}

	public function getChildElements()
	{
		if (!isset($this->childElements))
		{
			return NULL;
		}
		return $this->childElements;
	}

	abstract function initialize();

	abstract function renderOnPage($page, $parent=NULL);

	protected function renderAsLinkTarget($page)
	{
		if ($this->isLinkTarget() && $this->getTemplate()->getTargetMedia() != Flex_Pdf_Style::MEDIA_PRINT)
		{
			$page->drawLinkTo($this->_strLinkTargetName, $this->getPreparedAbsTop(), $this->getPreparedAbsLeft());
		}
	}
	
	public function isLinkTarget()
	{
		return $this->_strLinkTargetName !== NULL;
	}
	
	public function getLinkTargetName()
	{
		return $this->_strLinkTargetName;
	}

	public function appendToDom($doc, $parentNode, $parent=NULL)
	{
		// Create a node for this element
		$node = $doc->createElement($this->dom->nodeName);

		$childElements = $this->getChildElements();
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			$childElements[$i]->appendToDom($doc, $node, $this);
		}

		// Apply the style to this node
		$node->setAttribute("style", $this->getStyle()->getHTMLStyleAttributeValue());

		// Append this node to the parentNode
		$parentNode->appendChild($node);
	}


	protected function wrapNode($node, $newNodeTagName)
	{
		$wrap = $this->getTemplate()->createElement($newNodeTagName);
		$node->parentNode->replaceChild($wrap, $node);
		$wrap->appendChild($node);
		if (!$node instanceof DOMText && $node->hasAttribute("style"))
		{
			$style = " " . $node->getAttribute("style") . " ";
			if (preg_match("/[^a-z0-9]+(top|bottom) *\:/i", $style))
			{
				$wrap->setAttribute("style", "top: 0pt;");
				$wrap->setAttribute("forced-to-top", "true");
			}
		}

		return $wrap;
	}

	public function hasAbsoluteVertical()
	{
		return $this->getStyle()->getTop() !== NULL || $this->getStyle()->getBottom() !== NULL;
	}

	public function getAvailableWidth()
	{
		if ($this->getStyle()->getWidth() !== NULL) return $this->getStyle()->getWidth();
		return $this->parent->getAvailableWidth();
	}

	public function getAvailableHeight()
	{
		if ($this->getStyle()->getHeight() !== NULL) return $this->getStyle()->getHeight();
		return $this->parent->getAvailableHeight();
	}

	public final function resetForNextPage()
	{
		$this->clearTemporaryDetails();
		$childElements = $this->getChildElements();
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			$childElements[$i]->resetForNextPage();
		}
	}

	abstract function clearTemporaryDetails();


	public function prepareSize($offsetTop=0)
	{
		$requiredWidths = array();
		$requiredHeights = array();
		$requiredWidths[0] = 0;
		$requiredHeights[0] = 0;
		$childElements = $this->getChildElements();
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			$childElements[$i]->prepareSize($requiredHeights[0]);
			$requiredWidths[] = $childElements[$i]->getRequiredWidth();
			if ($childElements[$i]->hasAbsoluteVertical())
			{
				$requiredHeights[] = $childElements[$i]->getRequiredHeight();
			}
			else
			{
				$requiredHeights[0] += $childElements[$i]->getRequiredHeight();
			}
		}

		if (!$this->getStyle()->hasFixedWidth())
		{
			$this->preparedWidth = max($requiredWidths);
		}
		else
		{
			$this->preparedWidth = $this->getStyle()->getWidth();
		}
		$this->requiredWidth = $this->preparedWidth + ($this->getOffsetLeft() ? $this->getOffsetLeft(): $this->getOffsetRight());


		if (!$this->getStyle()->hasFixedHeight())
		{
			$this->preparedHeight = max($requiredHeights);
		}
		else
		{
			$this->preparedHeight = $this->getStyle()->getHeight();
		}
		$this->requiredHeight = $this->preparedHeight + ($this->getOffsetTop() ? $this->getOffsetTop() : $this->getOffsetBottom());
	}

	public function getOffsetLeft()
	{
		if ($this->getStyle()->getLeft() === NULL) return 0;
		return $this->getStyle()->getLeft();
	}

	public function getOffsetRight()
	{
		if ($this->getStyle()->getRight() === NULL) return 0;
		return $this->getStyle()->getRight();
	}

	public function getOffsetTop()
	{
		if ($this->getStyle()->getTop() === NULL) return 0;
		return $this->getStyle()->getTop();
	}

	public function getOffsetBottom()
	{
		if ($this->getStyle()->getBottom() === NULL) return 0;
		return $this->getStyle()->getBottom();
	}

	public function getRequiredWidth()
	{
		return $this->requiredWidth;
	}

	public function getRequiredHeight()
	{
		return $this->requiredHeight;
	}

	public function getPreparedWidth()
	{
		return $this->preparedWidth;
	}

	public function getPreparedHeight()
	{
		return $this->preparedHeight;
	}

	public function preparePosition($parentWidth=0, $parentHeight=0, $offsetTop=0, $offsetLeft=0)
	{
		$this->preparedAbsTop = $offsetTop;
		$this->preparedAbsLeft = $offsetLeft;

		if ($this->getStyle()->getLeft() !== NULL)
		{
			$this->preparedAbsLeft += $this->getStyle()->getLeft();
		}
		else if ($this->getStyle()->getRight() !== NULL)
		{
			$this->preparedAbsLeft += $parentWidth - $this->getStyle()->getRight() - $this->getPreparedWidth();
		}
		else if ($this->parent != NULL)
		{
			if ($this->parent->getStyle()->isTextAlignRight())
			{
				$this->preparedAbsLeft += $parentWidth - $this->getPreparedWidth();
			}
			else if ($this->parent->getStyle()->isTextAlignCentre())
			{
				$this->preparedAbsLeft += ($parentWidth - $this->getPreparedWidth()) / 2;
			}
		}

		if ($this->getStyle()->getTop() !== NULL)
		{
			$this->preparedAbsTop = (($this->parent == NULL) ? 0 : $this->parent->getPreparedAbsTop()) + $this->getStyle()->getTop();
		}
		else if ($this->getStyle()->getBottom() !== NULL)
		{
			$this->preparedAbsTop = (($this->parent == NULL) ? 0 : $this->parent->getPreparedAbsTop()) + $parentHeight - $this->getStyle()->getBottom() - $this->getPreparedHeight();
		}

		$this->prepareChildPositions();
	}

	public function prepareChildPositions()
	{
		$offsetTop = $this->preparedAbsTop;
		$offsetLeft = $this->preparedAbsLeft;
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
		return $this->preparedAbsTop;
	}

	public function getPreparedAbsLeft()
	{
		return $this->preparedAbsLeft;
	}

	public function getAvailablePreparedHeightForChildElement($childElement)
	{
		$availableHeight = $this->getAvailableHeight();
		$childElements = $this->getChildElements();
		//echo "<hr>" . get_class($this) . " :: getAvailablePreparedHeightForChildElement :: \$availableHeight = $availableHeight<br>";
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			// If we've reached the child element concerned then we don't need to look further
			if ($childElements[$i] === $childElement || ($childElements[$i] instanceof Flex_Pdf_Template_Page_Wrap_Include && $childElements[$i]->getPageWrapContent() === $childElement))
			{
				break;
			}
			// Ignore child elements that do not flow vertically in the layout
			if (!$childElements[$i]->hasAbsoluteVertical())
			{
		//echo "" . get_class($childElements[$i]) . " :: \$childElements[\$i]->getPreparedHeight() = " . $childElements[$i]->getPreparedHeight() . "<hr>";
				$availableHeight -= $childElements[$i]->getPreparedHeight();
			}
		}
		return $availableHeight;
	}


	public function _destroy()
	{
		$childElements = $this->getChildElements();
		if ($childElements !== NULL)
		{
			for ($i = count($childElements) - 1; $i >= 0; $i--)
			{
				$childElements[$i]->_destroy();
				unset($childElements[$i]);
			}
		}
		unset($this->dom, $this->style, $this->parent, $this->childElements, $this->_depth, $this->_bolInGetWidth, $this->_bolSuitableForTargetMedia);
	}



	// The folloing functions are for included wrapped content elements,
	// so that they may inspect the lineage above and below them in the dom.

	public function isComplete()
	{
		$childElements = $this->getChildElements();
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			if (!$childElements[$i]->isComplete()) return FALSE;
		}
		return TRUE;
	}

	public function isStarted()
	{
		return FALSE;
	}

	public function getNumberOfWrappersOnPage()
	{
		return $this->parent->getNumberOfWrappersOnPage();
	}

	public function getIndexOfWrapperOnPage()
	{
		return $this->parent->getIndexOfWrapperOnPage();
	}

	public function requiresBreak()
	{
		return FALSE;
	}

	public function requiresPageBreak()
	{
		return FALSE;
	}

	protected function includeForCurrentMedia()
	{
		if ($this->_bolSuitableForTargetMedia === NULL)
		{
			if ($this->getStyle() === NULL)
			{
				$this->_bolSuitableForTargetMedia = TRUE;
			}
			else
			{
				$this->_bolSuitableForTargetMedia = $this->getStyle()->suitableForMedia($this->getTemplate()->getTargetMedia());
			}
		}
		return $this->_bolSuitableForTargetMedia;
	}

}


?>
