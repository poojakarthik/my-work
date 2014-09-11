<?php

class Flex_Pdf_Template_Paragraph extends Flex_Pdf_Template_Element
{
	private $_width_ = 0;
	private $_height_ = 0;
	private $rowWidths = array();
	private $rowHeights = array();

	function initialize()
	{
		// Need to parse the text for permitted elements (span's)
		for($i = 0, $l = $this->dom->childNodes->length; $i < $l; $i++)
		{
			$node = $this->dom->childNodes->item($i);
			// Text shouldn't be out here on its own, but we don't want to ignore it...
			if ($node instanceof DOMText)
			{
				// Stick the text into a span to be handled properly
				$node = $this->wrapNode($node, "span");
			}

			$child = NULL;

			switch (strtoupper($node->tagName))
			{
				case "BR":
					// BR elements shouldn't be at this level, they should only be in SPAN or LINK elements.
					$node = $this->wrapNode($node, "span");
					// This still isn't right, so let's go to the next case to sort it out...
				case "SPAN":
					// Span elements can be at this level.
					$child = new Flex_Pdf_Template_Span($node, $this);
					break;

				case "A":
					// A elements can also be at this level.
					$child = new Flex_Pdf_Template_Link($node, $this);
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
					$this->childElements[] = $child;
				}
			}
		}
	}


	function prepare()
	{
		// In order to render the contents, we first need to prepare them.
		// This means telling each of them the available width for the first row.
		// The available width will be 0 for the first span and the available width
		// minus the width of the last row subsequently.
		// Whilst doing this we also need to keep track of the max line heights for
		// each row to be rendered, as this can vary between rows.
		// We also need to keep track of the widths of the rows so that we know the
		// width of this element.
		// (Oh, is that all)
		$availableWidth = $this->getAvailableWidth();

		$this->rowHeights = array();
		$this->rowHeights[] = 0;
		$this->rowWidths = array();
		$this->rowWidths[] = 0;
		$lastRowWidth = 0;

		$childElements = $this->getChildElements();
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			// Get the prepared content for the span (the span will store this for using later)
			$intInitWidth = ($availableWidth - $this->rowWidths[count($this->rowWidths) - 1]);
			if ($intInitWidth == $availableWidth) $intInitWidth = -1;
			$pc = $childElements[$i]->prepare($availableWidth, $intInitWidth);

			$nrRows = count($pc["WIDTHS"]);

			// If there are no rows for this span, ignore it
			if (!$nrRows) continue;

			// Get the row height for this span
			$rowHeight = $childElements[$i]->getStyle()->getLineHeight();

			// Combine widths of this spans first row and the previous row
			$this->rowWidths[count($this->rowWidths) - 1] += $pc["WIDTHS"][0];
			// If there is more than one row, add their lengths to the list of row lengths
			if ($nrRows > 1)
			{
				$this->rowWidths = array_merge($this->rowWidths, array_slice($pc["WIDTHS"], 1));
			}

			// The first row is on the same row as the previous spans last row.
			// The height of that row will be the max of the two row heights
			$this->rowHeights[count($this->rowHeights) - 1] = max($rowHeight, $this->rowHeights[count($this->rowHeights) - 1]);

			// Fill in the row heights for the other rows
			for ($r = 1; $r < $nrRows; $r++)
			{
				$this->rowHeights[] = $rowHeight;
			}
		}

		$this->_width_ = max($this->rowWidths);
		$this->_height_ = array_sum($this->rowHeights);
	}

	function renderOnPage($page, $parent=NULL)
	{
		$this->renderAsLinkTarget($page);

		// To render the text we need to know the top and left position of this paragraph
		$top = $this->getPreparedAbsTop();
		$left = $this->getPreparedAbsLeft();

		// We also need to know the height and width of this paragraph
		$height = $this->getPreparedHeight();
		$width = $this->getPreparedWidth();

		// Get available height
		$availableHeight = $this->getAvailableHeight();

		// Get the alignment for this paragraph
		$align = $this->getStyle()->getTextAlign();

		$overflow = $this->getStyle()->getOverflow();

		$lastRowWidth = 0;
		$row = 0;

		$y = $top;

		$usedHeight = 0;
		$usedWidth = 0;

		$drawX = $left;

		$childElements = $this->getChildElements();
		// We now need to render the content of each span
		for ($c = 0, $cl = count($childElements); $c < $cl; $c++)
		{
			// Get the prepared contents for the span
			$pc = $childElements[$c]->preparedContents;
			$strings = $pc["STRINGS"];
			$widths = $pc["WIDTHS"];

			// Itterate through the rows for this span
			for ($i = 0, $l = count($strings); $i < $l; $i++)
			{
				// If this is truly a new row (and not just a continuation of the previous span) increment the row counter
				if ($i)
				{
					$row++;
					$usedWidth = 0;
				}


				$lineHeight = $this->rowHeights[$row];

				$drawY = $top + $usedHeight;

				// Set the style
				if ($i == 0) $page->setStyle($childElements[$c]->getStyle());

				// Set the text line height for the page
				$page->setTextLineHeight($this->rowHeights[$row]);

				$strW = $widths[$i];

				if ($this->getStyle()->isTextAlignLeft())
				{
					$drawX = $left + $usedWidth;
				}
				else
				{
					$lineWidth = $this->rowWidths[$row];
					if ($this->getStyle()->isTextAlignRight())
					{
						$drawX = $left + $width - $lineWidth + $usedWidth;
					}
					else // Must be text align centre
					{
						$drawX = $left + (($width - $lineWidth)/2) + $usedWidth;
					}
				}

				if ($page->getStyle()->isRotated())
				{
					// Rotate page
					$page->rotate($drawX, $availableHeight - $drawY, $page->getStyle()->getRotateAngle());
				}
				
				if ($page->getStyle()->hasOpacity())
				{
					// Set page alpha
					$page->setAlpha($page->getStyle()->getOpacity());
				}
				
				// Draw text
				$page->drawText($drawY, $drawX, $strings[$i], $widths[$i], $this->getStyle());
				
				if ($page->getStyle()->isRotated())
				{
					// Rotate page back
					$page->rotate($drawX, $availableHeight - $drawY, 0 - $page->getStyle()->getRotateAngle());
				}
				
				if ($page->getStyle()->hasOpacity())
				{
					// Reset page alpha to fully opaque
					$page->setAlpha(1);
				}
				
				// If the span is a link target...
				if ($i === 0 && $childElements[$c]->isLinkTarget())
				{
					$fltTmpTop = $childElements[$c]->preparedAbsTop;
					$fltTmpLeft = $childElements[$c]->preparedAbsLeft;

					$childElements[$c]->preparedAbsTop = $drawY;
					$childElements[$c]->preparedAbsLeft = $drawX;
					$childElements[$c]->renderAsLinkTarget($page);
					
					$childElements[$c]->preparedAbsTop = $fltTmpTop;
					$childElements[$c]->preparedAbsLeft = $fltTmpLeft;
				}
				
				// If the element is a link to somewhere else...
				if ($childElements[$c] instanceof Flex_Pdf_Template_Link)
				{
					$fltTmpTop = $childElements[$c]->preparedAbsTop;
					$fltTmpLeft = $childElements[$c]->preparedAbsLeft;
					$fltTmpWidth = $childElements[$c]->preparedWidth;
					$fltTmpHeight = $childElements[$c]->preparedHeight;
					
					$childElements[$c]->preparedAbsTop = $drawY;
					$childElements[$c]->preparedAbsLeft = $drawX;
					$childElements[$c]->preparedWidth = $widths[$i];
					$childElements[$c]->preparedHeight = $lineHeight;
					
					$childElements[$c]->renderAsLink($page);
					
					$childElements[$c]->preparedAbsTop = $fltTmpTop;
					$childElements[$c]->preparedAbsLeft = $fltTmpLeft;
					$childElements[$c]->preparedWidth = $fltTmpWidth;
					$childElements[$c]->preparedHeight = $fltTmpHeight;
				}
			
				$usedWidth += $strW;

				// If we might not be rendering everything...
				if ($overflow === Flex_Pdf_Style::OVERFLOW_HIDDEN)
				{
					// Update the span with the content it still has left to be rendered
					$remainingString = ltrim(substr($remainingString, strlen($strings[$i])), " ");
					//$childElements[$c]->setContent($remainingString);
				}

				// If there is another line after this, this row is full, so increase the 'usedHeight'
				if ($i + 1 < $l)
				{
					$usedHeight += $lineHeight;

					// Check that the next line will fit if overflow is hidden
					if ($overflow === Flex_Pdf_Style::OVERFLOW_HIDDEN)
					{
						// Get the next row height
						$lineHeight = $this->rowHeights[$row + 1];
						if (($usedHeight + $lineHeight) > $availableHeight && $overflow === Flex_Pdf_Style::OVERFLOW_HIDDEN)
						{
							return FALSE;
						}
					}
				}
				else
				{
					// This span is all rendered! Blitz it!!
					//$childElements[$c]->setContent("");
				}
			}
		}

		return TRUE;
	}

	public function clearTemporaryDetails()
	{
		$this->rowWidths = array();
		$this->rowHeights = array();
		$this->_width_ = 0;
		$this->_height_ = 0;
	}

	public function prepareSize()
	{
		$this->prepare();

		$this->preparedWidth = ($this->getStyle()->hasFixedWidth()) ? $this->getStyle()->getWidth() : $this->_width_;
		$this->preparedHeight = ($this->getStyle()->hasFixedHeight()) ? $this->getStyle()->getHeight() : min($this->getAvailablePreparedHeightForChildElement($this), $this->_height_);

		$this->requiredWidth = $this->preparedWidth + ($this->getOffsetLeft() ? $this->getOffsetLeft(): $this->getOffsetRight());
		$this->requiredHeight = $this->preparedHeight + ($this->getOffsetTop() ? $this->getOffsetTop() : $this->getOffsetBottom());
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
	}

}

?>
