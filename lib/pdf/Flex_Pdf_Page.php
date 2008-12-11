<?php

require_once "Flex_Pdf_Text.php";
require_once "resource/Flex_Pdf_Resource_Raw.php";
require_once "element/Flex_Pdf_Element_Array.php";
require_once "resource/annotation/Flex_Pdf_Annotation_Link_From.php";
require_once "resource/annotation/Flex_Pdf_Annotation_Link_To.php";


class Flex_Pdf_Page extends Zend_Pdf_Page
{
	const LINE_HEIGHT_DEFAULT = 0;
	const TEXT_BLOCK_OVERFLOW_VISIBLE = 0;
	const TEXT_BLOCK_OVERFLOW_HIDDEN = 1;
	const TEXT_BLOCK_OVERFLOW_ALL_OR_NOTHING = 2;
	const TEXT_ALIGN_LEFT = 0;
	const TEXT_ALIGN_RIGHT = 1;
	const TEXT_ALIGN_CENTRE = 2;

	private $fltTextLineHeight = self::LINE_HEIGHT_DEFAULT;

	private $fltLineWidth = 0;

	private $objFillColour = NULL;
	private $objLineColour = NULL;

	private $saveFillColours = array();
	private $saveLineColours = array();
	
	protected $_arrLinkTargets = array();
	
	public function drawImage($zendPdfImageResource, $top, $left, $height, $width)
	{
		parent::drawImage($zendPdfImageResource, $left, $this->getHeight() - ($top + $height), $left + $width, $this->getHeight() - $top);
	}

	public function drawBackground($templateElement)
	{
		$t = $templateElement->getStyle()->getBorderWidthTop();
		$r = $templateElement->getStyle()->getBorderWidthRight();
		$b = $templateElement->getStyle()->getBorderWidthBottom();
		$l = $templateElement->getStyle()->getBorderWidthLeft();

		$fillColor = $templateElement->getStyle()->getBackgroundColor();
		$lineColor = $templateElement->getStyle()->getBorderColor();

		if ($fillColor == NULL && ($lineColor == NULL || (!$t && !$b && !$l && !$r)))
		{
			// Nothing to render!
			return;
		}

		$pt = $templateElement->getStyle()->getPaddingTop();
		$pr = $templateElement->getStyle()->getPaddingRight();
		$pb = $templateElement->getStyle()->getPaddingBottom();
		$pl = $templateElement->getStyle()->getPaddingLeft();

		$top = $templateElement->getPreparedAbsTop() - $pt;
		$left = $templateElement->getPreparedAbsLeft() - $pl;
		$height = $templateElement->getPreparedHeight() + $pt + $pb;
		$width = $templateElement->getPreparedWidth() + $pl + $pr;

		$cornerRadius = $templateElement->getStyle()->getCornerRadius();
		$cornerRadius = (2 * $cornerRadius) > $width  ? ($width  / 2) : $cornerRadius;
		$cornerRadius = (2 * $cornerRadius) > $height ? ($height / 2) : $cornerRadius;

		// Rectangular area contained entirely within border (corners cropped by corner radius curves)
		$x1 = $left;
		$x2 = $left + $width;
		$y1 = $this->getHeight() - $top;
		$y2 = $y1 - $height;

		$this->saveGS();

		$this->setStyle($templateElement->getStyle());

		// Draw a box with uneven borders
		if ($t != $r || $r != $b || $b != $l)
		{
			$bx1 = $x1 - $l;
			$bx2 = $x2 + $r;
			$by1 = $y1 + $t;
			$by2 = $y2 - $b;

			if ($lineColor != NULL)
			{
				$rawInnerBoxData = $this->getBoxBoundsData($x1, $y1, $x2, $y2, $cornerRadius);

				$this->saveGS();
				$this->setFillColor($lineColor);

				if ($cornerRadius)
				{
					$crtl = $cornerRadius + min($t, $l);
					$crtr = $cornerRadius + min($t, $r);
					$crbr = $cornerRadius + min($b, $r);
					$crbl = $cornerRadius + min($b, $l);

					$rawDataOuterBox = $this->getBoxBoundsDataFourRadii($bx1, $by1, $bx2, $by2, $crtl, $crtr, $crbr, $crbl);

					// Draw the box filled
					$this->appendToRawContents($rawDataOuterBox);
					$this->appendToRawContents($rawInnerBoxData);
					$this->appendToRawContents(' f*');
				}
				else
				{
					$this->appendToRawContents($this->getBoxBorderBounds($x1, $y1, $x2, $y2, $t, $r, $b, $l));
					$this->appendToRawContents(' f*');
				}

				$this->restoreGS();
			}

			// Draw the fill area for a box with uneven sides
			if ($fillColor != NULL)
			{
				$rawInnerBoxData = $this->getBoxBoundsData($x1, $y1, $x2, $y2, $cornerRadius);

				$this->saveGS();
				$this->setFillColor($fillColor);

				if ($cornerRadius)
				{
					$this->appendToRawContents($rawInnerBoxData);
					// Set the fill style
					$this->appendToRawContents(' f*');
				}
				else
				{
					parent::drawRectangle($x1, $y1, $x2, $y2, Flex_Pdf_Page::SHAPE_DRAW_FILL);
				}

				$this->restoreGS();
			}
		}


		// Draw a box with even borders (even if they are 'none')
		if ($t == $r && $r == $b && $b == $l)
		{
			$this->saveGS();
			$borderWidth = floatval($t);
			$halfBorder = $borderWidth / 2;
			$x1 -= $halfBorder;
			$x2 += $halfBorder;
			$y1 += $halfBorder;
			$y2 -= $halfBorder;

			$this->setLineWidth(floatval($borderWidth));

			if ($fillColor != NULL) $this->setFillColor($fillColor);
			if ($lineColor != NULL) $this->setLineColor($lineColor);

			if ($cornerRadius)
			{
				// Get the box bounds data...
				$rawData = $this->getBoxBoundsData($x1, $y1, $x2, $y2, $cornerRadius);
				$this->appendToRawContents($rawData);

				// Set the fill style
				$this->appendToRawContents($fillColor == NULL ? ' S' : (($lineColor != NULL && $t) ? ' B*' : ' f*'));
			}
			else
			{
				parent::drawRectangle($x1, $y1, $x2, $y2, $fillColor == NULL ? Flex_Pdf_Page::SHAPE_DRAW_STROKE : (($lineColor != NULL && $t) ? Flex_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE : Flex_Pdf_Page::SHAPE_DRAW_FILL));
			}
			$this->restoreGS();
		}

		$this->restoreGS();
	}


	private function getBoxBoundsDataFourRadii($x1, $y1, $x2, $y2, $cornerRadiusTopLeft, $cornerRadiusTopRight, $cornerRadiusBottomRight, $cornerRadiusBottomLeft)
	{
		$deltaTL  = 2*(M_SQRT2 - 1)*(2 * $cornerRadiusTopLeft)/3.;
		$deltaTR  = 2*(M_SQRT2 - 1)*(2 * $cornerRadiusTopRight)/3.;
		$deltaBR  = 2*(M_SQRT2 - 1)*(2 * $cornerRadiusBottomRight)/3.;
		$deltaBL  = 2*(M_SQRT2 - 1)*(2 * $cornerRadiusBottomLeft)/3.;

		$xLeft  = new Zend_Pdf_Element_Numeric($x1);
		$xRight = new Zend_Pdf_Element_Numeric($x2);
		$yUp	= new Zend_Pdf_Element_Numeric($y1);
		$yDown  = new Zend_Pdf_Element_Numeric($y2);

		$xlt	= new Zend_Pdf_Element_Numeric($x1 + $cornerRadiusTopLeft);
		$xrt	= new Zend_Pdf_Element_Numeric($x2 - $cornerRadiusTopRight);
		$yul	= new Zend_Pdf_Element_Numeric($y1 - $cornerRadiusTopLeft);
		$ydl	= new Zend_Pdf_Element_Numeric($y2 + $cornerRadiusBottomLeft);

		$xlb	= new Zend_Pdf_Element_Numeric($x1 + $cornerRadiusBottomLeft);
		$xrb	= new Zend_Pdf_Element_Numeric($x2 - $cornerRadiusBottomRight);
		$yur	= new Zend_Pdf_Element_Numeric($y1 - $cornerRadiusTopRight);
		$ydr	= new Zend_Pdf_Element_Numeric($y2 + $cornerRadiusBottomRight);

		$xdlt	= new Zend_Pdf_Element_Numeric($x1 + $deltaTL);
		$xdrt	= new Zend_Pdf_Element_Numeric($x2 - $deltaTR);
		$ydul	= new Zend_Pdf_Element_Numeric($y1 - $cornerRadiusTopLeft + $deltaTL);
		$yddl	= new Zend_Pdf_Element_Numeric($y2 + $cornerRadiusBottomLeft - $deltaBL);

		$xdlb	= new Zend_Pdf_Element_Numeric($x1 + $deltaBL);
		$xdrb	= new Zend_Pdf_Element_Numeric($x2 - $deltaBR);
		$ydur	= new Zend_Pdf_Element_Numeric($y1 - $cornerRadiusTopRight + $deltaTR);
		$yddr	= new Zend_Pdf_Element_Numeric($y2 + $cornerRadiusBottomRight - $deltaBR);

		// Create the set of rules to draw the area, starting at the top left and going clockwise
		$raw = "";

		// Starting after the top left corner
		$raw .= $xlt->toString() . ' ' . $yUp->toString() . " m\n";

		// Draw a line to just before the top right corner
		$raw .= $xrt->toString() . ' ' . $yUp->toString() . " l\n";

		// Curve to just after the top right corner
		$raw .= $xdrt->toString() . ' ' . $yUp->toString() .  ' '
				. $xRight->toString() . ' ' . $ydur->toString() .  ' '
				. $xRight->toString() . ' ' . $yur->toString() .  ' ' . " c\n";

		// Draw a line to just before the bottom right corner
		$raw .= $xRight->toString() . ' ' . $ydr->toString() . " l\n";

		// Curve to just after the bottom right corner
		$raw .= $xRight->toString() . ' ' . $yddr->toString() .  ' '
				. $xdrb->toString() . ' ' . $yDown->toString() .  ' '
				. $xrb->toString() . ' ' . $yDown->toString() .  ' ' . " c\n";

		// Draw a line to just before the bottom left corner
		$raw .= $xlb->toString() . ' ' . $yDown->toString() . " l\n";

		// Curve to just after the bottom left corner
		$raw .= $xdlb->toString() . ' ' . $yDown->toString() .  ' '
				. $xLeft->toString() . ' ' . $yddl->toString() .  ' '
				. $xLeft->toString() . ' ' . $ydl->toString() .  ' ' . " c\n";

		// Draw a line to just before the top left corner
		$raw .= $xLeft->toString() . ' ' . $yul->toString() . " l\n";

		// Curve to just after the top left corner
		$raw .= $xLeft->toString() . ' ' . $ydul->toString() .  ' '
				. $xdlt->toString() . ' ' . $yUp->toString() .  ' '
				. $xlt->toString() . ' ' . $yUp->toString() .  ' ' . " c";

		return $raw;
	}

	private function getBoxBoundsData($x1, $y1, $x2, $y2, $cornerRadiusTopLeft, $cornerRadiusTopRight=NULL, $cornerRadiusBottomRight=NULL, $cornerRadiusBottomLeft=NULL)
	{
		return $this->getBoxBoundsDataFourRadii($x1, $y1, $x2, $y2, $cornerRadiusTopLeft, $cornerRadiusTopLeft, $cornerRadiusTopLeft, $cornerRadiusTopLeft);
	}

	private function getBoxBorderBounds($x1, $y1, $x2, $y2, $t, $r, $b, $l)
	{
		$xLeft  = new Zend_Pdf_Element_Numeric($x1);
		$xBLeft  = new Zend_Pdf_Element_Numeric($x1 - $l);
		$xRight = new Zend_Pdf_Element_Numeric($x2);
		$xBRight = new Zend_Pdf_Element_Numeric($x2 + $r);
		$yUp	= new Zend_Pdf_Element_Numeric($y1);
		$yBUp	= new Zend_Pdf_Element_Numeric($y1 + $t);
		$yDown  = new Zend_Pdf_Element_Numeric($y2);
		$yBDown  = new Zend_Pdf_Element_Numeric($y2 - $b);

		$raw = '';

		if ($t)
		{
			$raw .= $xBLeft->toString() . ' ' . $yUp->toString() . " m\n";
			$raw .= $xBRight->toString() . ' ' . $yUp->toString() . " l\n";
			$raw .= $xBRight->toString() . ' ' . $yBUp->toString() . " l\n";
			$raw .= $xBLeft->toString() . ' ' . $yBUp->toString() . " l\n";
			$raw .= " h\n";
		}

		if ($b)
		{
			$raw .= $xBLeft->toString() . ' ' . $yDown->toString() . " m\n";
			$raw .= $xBRight->toString() . ' ' . $yDown->toString() . " l\n";
			$raw .= $xBRight->toString() . ' ' . $yBDown->toString() . " l\n";
			$raw .= $xBLeft->toString() . ' ' . $yBDown->toString() . " l\n";
			$raw .= " h\n";
		}

		if ($l)
		{
			$raw .= $xBLeft->toString() . ' ' . $yUp->toString() . " m\n";
			$raw .= $xLeft->toString() . ' ' . $yUp->toString() . " l\n";
			$raw .= $xLeft->toString() . ' ' . $yDown->toString() . " l\n";
			$raw .= $xBLeft->toString() . ' ' . $yDown->toString() . " l\n";
			$raw .= " h\n";
		}

		if ($r)
		{
			$raw .= $xBRight->toString() . ' ' . $yUp->toString() . " m\n";
			$raw .= $xRight->toString() . ' ' . $yUp->toString() . " l\n";
			$raw .= $xRight->toString() . ' ' . $yDown->toString() . " l\n";
			$raw .= $xBRight->toString() . ' ' . $yDown->toString() . " l\n";
			$raw .= " h\n";
		}

		return rtrim($raw);
	}

	/**
	 * Append raw data to the PDF document content stream
	 *
	 * @param String $raw data to date to content stream, without trailing new line char
	 *
	 * @note Expected to work like parent rawWrite() method which has not been implemented
	 * @note Relies on _contents variable being 'protected'. It is 'private' in the original
	 * 		 Zend source code.
	 */
	public function appendToRawContents($raw)
	{
		$this->_contents .= rtrim($raw) . "\n";
	}

	public function saveGS()
	{
		$this->saveFillColours[] = $this->getFillColor();
		$this->saveLineColours[] = $this->getLineColor();
		parent::saveGS();
	}

	public function restoreGS()
	{
		parent::restoreGS();
		$fill = array_pop($this->saveFillColours);
		$line = array_pop($this->saveLineColours);
		$this->setFillColor($fill);
		$this->setLineColor($line);
	}

	private $pageCountStyles = array();

	private function registerPageCount($wrapperStyle=NULL)
	{
		$this->pageCountStyles[] = $wrapperStyle;
	}

	public function applyPageCounts($nrPages)
	{
 		$pageCountMatches = array();
		preg_match_all("/\nBT\n([0-9\.]+) +([0-9\.]+) +Td\n *\(([^\n]*\<\\0?\<\\0?p\\0?c\\0?\>\\0?\>[^\n]*)\) +Tj\nET\n/", $this->_contents, $pageCountMatches, PREG_SET_ORDER);

		for ($i = 0, $l = count($this->pageCountStyles); $i < $l; $i++)
		{
			$pageCountMatch = $pageCountMatches[$i];
			$x = $pageCountMatch[1];

			$string = $pageCountMatch[3];

			$nrPageCounts = preg_match_all("/\<\\0?\<\\0?p\\0?c\\0?\>\\0?\>/", $string, $array=array());
			
			$isNullSplit = strpos($string, "\0") !== FALSE;

			$style = $this->pageCountStyles[$i];

			// If style is text-align left...
			if (!$style->isTextAlignLeft() || $style->getRight() !== NULL)
			{
 				$x = floatval($x);

			 	// Get the width of the string "<<pc>>"
			 	$pcWidth = Flex_Pdf_Text::widthForStringUsingFontSize("<<pc>>", $style->getFont(), $style->getFontSize());
				// Get the width of the new page number string
			 	$pnWidth = Flex_Pdf_Text::widthForStringUsingFontSize("$nrPages", $style->getFont(), $style->getFontSize());

				$shift = $pcWidth - $pnWidth;

				if ($style->isTextAlignCentre())
				{
					$shift = $shift/2;
				}

				$x += ($shift * $nrPageCounts);

				$val = new Zend_Pdf_Element_Numeric($x);
				$x = $val->toString();
			}


			$textElement = $pageCountMatch[0];
			
			if ($isNullSplit)
			{
				$nrPages = implode("\0", str_split($nrPages, 1));
			}
			
			$textElement = str_replace("BT\n".$pageCountMatch[1], "BT\n".$x, $textElement);

			$textElement = str_replace(($isNullSplit ? "<\0<\0p\0c\0>\0>" : "<<pc>>"), $nrPages, $textElement);


			$pos = strpos( $this->_contents, $pageCountMatch[0]);
			$len = strlen($pageCountMatch[0]);
			$this->_contents = substr( $this->_contents, 0, $pos) . $textElement . substr( $this->_contents, $pos + $len);
		}
	}

   	public function drawText($top, $left, $text, $width=0, $wrapperStyle=NULL)
	{
		if (strpos($text, "<<pc>>") !== FALSE)
		{
			$this->registerPageCount($wrapperStyle);
		}

		$x = $left;
		$y = $this->getHeight() - $top - $this->getCurrentTextLineHeight();

		$this->setFillColor($this->getStyle()->getColor());

		parent::drawText($text, $x, $y);

		if ($this->getStyle()->hasTextDecoration())
		{
			$this->saveGS();
			// Calculate a line width for the font
			$font = $this->getFont();

			$this->setLineColor($this->getStyle()->getColor());

			// If underlined...
			if ($this->getStyle()->hasUnderline())
			{
				// Set the line width (double width for a bolt font)
				$lineWidth = $this->getFontSize() * ($font->getUnderlineThickness() / $font->getUnitsPerEm());
				$lineWidth = $lineWidth * ($font->isBold() ? 2 : 1);
				$this->setLineWidth($lineWidth);

				// Get the vertical position of the line
				//$v = $y + ($font->getDescent() / $font->getUnitsPerEm()) - $lw;
				$v = $y + ($this->getFontSize() * ($font->getUnderlinePosition() / $font->getUnitsPerEm())) - ($lineWidth / 2);

				// Draw the line
				parent::drawLine($x, $v, $x + $width, $v);
			}

			// If overlined...
			if ($this->getStyle()->hasOverline())
			{
				// Set the line width (double width for a bolt font)
				$lineWidth = $this->getFontSize() * ($font->getUnderlineThickness() / $font->getUnitsPerEm());
				$lineWidth = $lineWidth * ($font->isBold() ? 2 : 1);
				$this->setLineWidth($lineWidth);

				// Get the vertical position of the line
				//$v = $y + $this->getFontSize() - $lw;
				$v = $y + ($this->getFontSize() * ($font->getAscent() / $font->getUnitsPerEm())) + $lineWidth;

				// Draw the line
				parent::drawLine($x, $v, $x + $width, $v);
			}

			// If line-through...
			if ($this->getStyle()->hasLineThrough())
			{
				// Set the line width (normal width for all fonts)
				$lineWidth = $this->getFontSize() * ($font->getStrikeThickness() / $font->getUnitsPerEm());
				$this->setLineWidth($lineWidth);

				// Get the vertical position of the line
				$v = $y + ($this->getFontSize() * ($font->getStrikePosition() / $font->getUnitsPerEm())) + ($lineWidth/2);

				// Draw the line
				parent::drawLine($x, $v, $x + $width, $v);
			}
			$this->restoreGS();
		}
	}

	/**
	 * Draw text in a block, wrapping on word breaks and hiding overflow if desired
	 *
	 * @param mixed $string String or array of strings to be displayed
	 * @param integer $x X-coordinate of bottom left corner of block in pixels
	 * @param integer $y Y-coordinate of bottom left corner of block in pixels
	 * @param integer $w Width of block in pixels
	 * @param integer $h Height of block in pixels
	 * @param boolean $overflow Whether or not to show text that overflows the box
	 * 							(TEXT_BLOCK_OVERFLOW_VISIBLE or TEXT_BLOCK_OVERFLOW_HIDDEN),
	 * 							or render anything if it doesn't all fit (TEXT_BLOCK_OVERFLOW_ALL_OR_NOTHING)
	 *
	 * @param String Any part of the string that was not rendered due to overflow being hidden, or NULL
	 */
	private function drawTextBlock($string, $x1, $y1, $x2, $y2, $align=self::TEXT_ALIGN_LEFT, $overflow=self::TEXT_BLOCK_OVERFLOW_VISIBLE)
	{
		$w = abs($x2 - $x1);
		$h = abs($y2 - $y1);
		$x = min($x1, $x2);
		$y = min($y1, $y2);
		$font = $this->getFont();
		$stringsAndWidths = Flex_Pdf_Text::splitStringToLengths($string, $font, $this->getFontSize(), $w);
		$strings = $stringsAndWidths["STRINGS"];
		$widths = $stringsAndWidths["WIDTHS"];
		$lineHeight = $this->getCurrentTextLineHeight();
		if ($overflow === self::TEXT_BLOCK_OVERFLOW_ALL_OR_NOTHING)
		{
			if ((count($strings) * $lineHeight) > $h) return $string;
		}
		if ($overflow === self::TEXT_BLOCK_OVERFLOW_HIDDEN)
		{
			//$remainingString = str_replace("\t", " ", ltrim($string));
			$remainingString = str_replace("\r", "", ltrim($string));
		}
		$t = $y + $h - $lineHeight;
		for ($i = 0, $l = count($strings); $i < $l; $i++)
		{
			$drawX = $x;
			if ($align != self::TEXT_ALIGN_LEFT)
			{
				$strW = $widths[$i];
				if ($align == self::TEXT_ALIGN_RIGHT)
				{
					$drawX += ($w - $strW);
				}
				else
				{
					$drawX += ($w - $strW)/2;
				}
			}
			parent::drawText($strings[$i], $drawX, $t);

			if ($overflow === self::TEXT_BLOCK_OVERFLOW_HIDDEN)
			{
				$remainingString = ltrim(substr($remainingString, strlen($strings[$i])));
			}
			$t -= $lineHeight;
			if ($overflow === self::TEXT_BLOCK_OVERFLOW_HIDDEN && $t < $y)
			{
				//$remainingString = str_replace(array("\t", "\r"), array(" ", ""), ltrim($string));
				//$remainingString = substr($remainingString, $endIndicies[$i]);
				return $remainingString;
			}
		}
		return NULL;
	}

	/**
	 * Set the user defined line height
	 *
	 * @param float Line height in pixels (approx)
	 */
	public function setTextLineHeight($fltTextLineHeight=self::LINE_HEIGHT_DEFAULT)
	{
		$this->fltTextLineHeight = $fltTextLineHeight;
	}

	/**
	 * Get the user defined line height
	 *
	 * @return float Line height in pixels (approx), default is self::LINE_HEIGHT_DEFAULT (0 = zero)
	 */
	public function getTextLineHeight()
	{
		return $this->fltTextLineHeight;
	}

	/**
	 * Get the  calculated text line height for the current font.
	 *
	 * @return float Line height in pixels (approx)
	 */
	public function getCalculatedTextLineHeight()
	{
		$font = $this->getFont();
		return ($font->getLineHeight() / $font->getUnitsPerEm()) * $this->getFontSize();
	}

	/**
	 * Get the  current text line height to be used.
	 *
	 * @return float Line height in pixels (approx)
	 */
	public function getCurrentTextLineHeight()
	{
		if ($this->fltTextLineHeight == self::LINE_HEIGHT_DEFAULT)
		{
			return $this->getCalculatedTextLineHeight();
		}
		return $this->getTextLineHeight();
	}

	/**
	 * Set current font size (requires a font to have been set first).
	 *
	 * @param float $fontSize
	 */
	public function setFontSize($fontSize)
	{
		$this->setFont($this->getFont(), $fontSize);
	}

	/**
	 * Set current font.
	 *
	 * @param Zend_Pdf_Resource_Font $font
	 * @param float $fontSize
	 */
	/*public function setFont(Zend_Pdf_Resource_Font $font, $fontSize)
	{
		if ($this->getFont() == $font && $this->getFontSize() == $fontSize)
		{
			return;
		}

		parent::setFont($font, $fontSize);
   }*/


	public function setFillColor($colour)
	{
		$this->objFillColour = $colour;
		if ($colour != NULL) parent::setFillColor($colour);
	}

	public function getFillColor()
	{
		return $this->objFillColour;
	}

	public function setLineColor($colour)
	{
		$this->objLineColour = $colour;
		if ($colour != NULL) parent::setLineColor($colour);
	}

	public function getLineColor()
	{
		return $this->objLineColour;
	}

	public function getPageColumn()
	{
		return "@";
	}


	/**
	 * Add raw pdf commands as content for the page
	 *
	 * @param Zend_Pdf_Image $image
	 */
	public function drawRawContent($rawContent)
	{
		$resource = Flex_Pdf_Resource_Raw::createRawResource($rawContent);

		$this->_addProcSet('PDF');

		$resourceName	= $this->_attachResource('XObject', $resource);
		$resourceNameObj = new Zend_Pdf_Element_Name($resourceName);

		$this->_contents .= "q\n"
						 .  $resourceNameObj->toString() . " Do\n"
						 .  "Q\n";
	}



	/**
	 * Attach annotation to the page
	 *
	 * @param string $strAnnotationName
	 * @param Zend_Pdf_Resource $resource
	 * @return string
	 */
	protected function _attachAnnotation(Flex_Pdf_Annotation_Link_From $objLinkFrom)
	{
		$pageDictionary = $this->getPageDictionary();
		
		if (!($pageDictionary->Annots instanceof Flex_Pdf_Element_Array))
		{
			if ($pageDictionary->Annots instanceof Zend_Pdf_Element_Array)
			{
				$objAnnots = $pageDictionary->Annots;
				$pageDictionary->Annots = new Flex_Pdf_Element_Array();
				$pageDictionary->Annots->adoptItems($objAnnots);
			}
			else if ($pageDictionary->Annots == NULL)
			{
				$pageDictionary->Annots = new Flex_Pdf_Element_Array();
			}
		}
		
        $this->_objFactory->attach($objLinkFrom->getFactory());
        $pageDictionary->Annots->add(null, $objLinkFrom->getResource());
	}

	/**
	 * Draw a link at the specified position on the page to the named target.
	 *
	 * @param $strTargetName String name of target to link to 
	 * @param $top		float
	 * @param $left		float
	 * @param $height	float
	 * @param $width	float
	 */
	public function drawLinkFrom($strTargetName, $top=0, $left=0, $height=0, $width=0)
	{
		$this->_addProcSet('PDF');

		$objName = new Zend_Pdf_Element_String($strTargetName);

		$h = $this->getHeight();
		$objX1 = new Zend_Pdf_Element_Numeric($left);
		$objY1 = new Zend_Pdf_Element_Numeric($h - $top - $height);
		$objX2 = new Zend_Pdf_Element_Numeric($left + $width);
		$objY2 = new Zend_Pdf_Element_Numeric($h - $top);
		
		$objLinkFrom = new Flex_Pdf_Annotation_Link_From($objX1, $objY1, $objX2, $objY2, $objName);
		$this->_attachAnnotation($objLinkFrom);
	}

	/**
	 * Draw a link target at the specified position on the page
	 *
	 * @param $strTargetName String name of target to link to 
	 * @param $top		float
	 * @param $left		float
	 */
	public function drawLinkTo($strTargetName, $top=0, $left=0)
	{
		$this->_addProcSet('PDF');

		$objName = new Zend_Pdf_Element_Name($strTargetName);

		$h = $this->getHeight();
		$objX1 = new Zend_Pdf_Element_Numeric($left);
		$objY1 = new Zend_Pdf_Element_Numeric($h - $top);
		
		$objLinkTo = new Flex_Pdf_Annotation_Link_To($this->getPageDictionary(), $objX1, $objY1, $objName);
		$this->_arrLinkTargets[$strTargetName] = $objLinkTo->getResource();
        $this->_objFactory->attach($objLinkTo->getFactory());
	}

	public function getLinkTargets()
	{
		return $this->_arrLinkTargets;
	}

}


?>
