<?php

require_once "Zend/Pdf/Style.php";
require_once "Flex_Pdf_Font_Factory.php";
require_once "Flex_Pdf_Colour.php";


class Flex_Pdf_Style extends Zend_Pdf_Style
{
	const TEXT_ALIGN_LEFT = 0;
	const TEXT_ALIGN_RIGHT = 1;
	const TEXT_ALIGN_CENTRE = 2;

	const OVERFLOW_VISIBLE = 0;
	const OVERFLOW_HIDDEN = 1;
	const OVERFLOW_ALL_OR_NOTHING = 2;

	const TEXT_DECORATION_NONE 			= 0;
	const TEXT_DECORATION_OVERLINE 		= 1;
	const TEXT_DECORATION_UNDERLINE 	= 2;
	const TEXT_DECORATION_LINE_THROUGH 	= 4;

	const FONT_FAMILY = 0;
	const FONT_WEIGHT = 1;
	const FONT_STYLE = 2;

	const MEDIA_PRINT = DOCUMENT_TEMPLATE_MEDIA_TYPE_PRINT;
	const MEDIA_EMAIL = DOCUMENT_TEMPLATE_MEDIA_TYPE_EMAIL;
	const MEDIA_ALL = DOCUMENT_TEMPLATE_MEDIA_TYPE_ALL;
	
	// Inherited properties
	private $intTextDecoration = self::TEXT_DECORATION_NONE;
	private $lineHeight = 0;
	private $fontResources = array();
	private $fontParts = array(self::FONT_FAMILY => "helvetica", self::FONT_WEIGHT => "", self::FONT_STYLE => "");

	// Non-inherited properties
	private $intTop 	= NULL;
	private $intLeft 	= NULL;
	private $intBottom 	= NULL;
	private $intRight 	= NULL;
	private $intWidth 	= NULL;
	private $intHeight 	= NULL;
	private $intBorderCornerRadius	= 0;
	private $intBorderWidthTop		= 0;
	private $intBorderWidthBottom	= 0;
	private $intBorderWidthLeft		= 0;
	private $intBorderWidthRight	= 0;
	private $objBorderColor			= NULL;
	private $intPaddingTop		= 0;
	private $intPaddingBottom	= 0;
	private $intPaddingLeft		= 0;
	private $intPaddingRight	= 0;
	private $objBackgroundColor	= NULL;
	private $objColor	= NULL;
	private $intTextAlign = self::TEXT_ALIGN_LEFT;
	private $intOverflow = self::OVERFLOW_VISIBLE;
	private $intCornerRadius = 0;
	private $strPageSize = Zend_Pdf_Page::SIZE_A4;
	private $intMedia = self::MEDIA_ALL;

	private $bolInheritedFromStyle = FALSE;

	function __construct($anotherStyle=NULL)
	{
		parent::__construct($anotherStyle);
		if ($anotherStyle !== NULL)
		{
			$this->intTextDecoration = $anotherStyle->intTextDecoration;
			$this->lineHeight 		= $anotherStyle->lineHeight;
			$this->fontResources	= $anotherStyle->fontResources;
			$this->fontParts		= $anotherStyle->fontParts;
			$this->intTextAlign		= $anotherStyle->intTextAlign;
			$this->intOverflow		= $anotherStyle->intOverflow;
			$this->objColor			= $anotherStyle->objColor;
			$this->intMedia			= $anotherStyle->intMedia;

			$this->bolInheritedFromStyle = TRUE;
		}
		else
		{
			$this->setColor(new Flex_Pdf_Colour("black"));
		}
	}

	function applyStyleAttribute($styleAttribute)
	{
		$style = explode(";", $styleAttribute);
		$styleParts = array();

		foreach ($style as $stylePart)
		{
			$styleParts = explode(":", $stylePart);
			
			/* This should really be uncommented, but I don't want to break any existing templates =\
			if ($stylePart && count($styleParts) < 2)
			{
				throw new Exception("Invalid Style Definition '{$stylePart}' in node style '{$styleAttribute}'");
			}
			*/
			
			switch (strtoupper(trim($styleParts[0])))
			{
				case "MEDIA":
					$this->setMedia($styleParts[1]);
					break;

				case "FONT-FAMILY":
					$this->fontParts[self::FONT_FAMILY] = strtoupper(trim($styleParts[1]));
					break;

				case "FONT-WEIGHT":
					$value = strtoupper(trim($styleParts[1]));
					$this->fontParts[self::FONT_WEIGHT] = $value == "NORMAL" ? "" : (" " . $value);
					break;

				case "FONT-STYLE":
					$value = strtoupper(trim($styleParts[1]));
					$this->fontParts[self::FONT_STYLE] = $value == "NORMAL" ? "" : (" " . $value);
					break;

				case "FONT-SIZE":
					$this->setFontSize($this->getPointSize($styleParts[1]));
					break;

				case "COLOR":
				case "COLOUR":
					$this->setColor(new Flex_Pdf_Colour($styleParts[1]));
					break;

				case "BORDER-WIDTH":
					$this->setBorderWidth($styleParts[1]);
					break;

				case "CORNER-RADIUS":
					$this->setCornerRadius($styleParts[1]);
					break;

				case "BORDER-WIDTH-TOP":
					$this->setBorderWidthTop($styleParts[1]);
					break;

				case "BORDER-WIDTH-RIGHT":
					$this->setBorderWidthRight($styleParts[1]);
					break;

				case "BORDER-WIDTH-BOTTOM":
					$this->setBorderWidthBottom($styleParts[1]);
					break;

				case "BORDER-WIDTH-LEFT":
					$this->setBorderWidthLeft($styleParts[1]);
					break;

				case "PADDING":
					$this->setPadding($styleParts[1]);
					break;

				case "PADDING-TOP":
					$this->setPaddingTop($styleParts[1]);
					break;

				case "PADDING-RIGHT":
					$this->setPaddingRight($styleParts[1]);
					break;

				case "PADDING-BOTTOM":
					$this->setPaddingBottom($styleParts[1]);
					break;

				case "PADDING-LEFT":
					$this->setPaddingLeft($styleParts[1]);
					break;

				case "BORDER-COLOR":
				case "BORDER-COLOUR":
					$this->setBorderColor(new Flex_Pdf_Colour($styleParts[1]));
					break;

				case "BACKGROUND-COLOR":
				case "BACKGROUND-COLOUR":
					$this->setBackgroundColor(new Flex_Pdf_Colour($styleParts[1]));
					break;

				case "TEXT-DECORATION":
					$this->setTextDecoration($styleParts[1]);
					break;

				case "TEXT-ALIGN":
					$this->setTextAlign($styleParts[1]);
					break;

				case "OVERFLOW":
					$this->setOverflow($styleParts[1]);
					break;

				case "LINE-HEIGHT":
					$this->setLineHeight($styleParts[1]);
					break;

				case "TOP":

					$this->setTop($styleParts[1]);
					break;

				case "LEFT":
					$this->setLeft($styleParts[1]);
					break;

				case "BOTTOM":
					$this->setBottom($styleParts[1]);
					break;

				case "RIGHT":
					$this->setRight($styleParts[1]);
					break;

				case "WIDTH":
					$this->setWidth($styleParts[1]);
					break;

				case "HEIGHT":
					$this->setHeight($styleParts[1]);
					break;

				case "PAGE-SIZE":
					$this->setPageSize($styleParts[1]);
					break;

					// Ignore - not supported!

			}
		}
		$fontName = $this->fontParts[self::FONT_FAMILY] . $this->fontParts[self::FONT_WEIGHT] . $this->fontParts[self::FONT_STYLE];
		if (array_key_exists($fontName, $this->fontResources))
		{
			$fontName = $this->fontResources[$fontName];
		}
		if ($fontName)
		{
			try
			{
				$font = Flex_Pdf_Font_Factory::get($fontName);
			}
			catch (Exception $e)
			{
				throw $e;
			}
		}
		if ($font != null)
		{
			$this->setFont($font, $this->getFontSize());
		}
	}

	function setTextDecoration($textDecoration)
	{
		$textDecoration = preg_replace("/ +/", " ", strtolower(trim($textDecoration)));

		if (!$textDecoration) return;

		$newTextDecoration = self::TEXT_DECORATION_NONE;

		$textDecorations = explode(" ", $textDecoration);

		for ($i = 0, $l = count($textDecorations); $i < $l; $i++)
		{
			$textDecoration = $textDecorations[$i];
			switch($textDecoration)
			{
				case "overline":
					$newTextDecoration = $newTextDecoration | self::TEXT_DECORATION_OVERLINE;
					break;

				case "underline":
					$newTextDecoration = $newTextDecoration | self::TEXT_DECORATION_UNDERLINE;
					break;

				case "line-through":
					$newTextDecoration = $newTextDecoration | self::TEXT_DECORATION_LINE_THROUGH;
					break;

				case "none":
					$newTextDecoration = self::TEXT_DECORATION_NONE;
					break;

				default:
					if (!trim($textDecoration)) continue;
					// Ignore - Invalid value!
					return;
			}
		}

		$this->intTextDecoration = $newTextDecoration;
	}

	function getTextDecoration()
	{
		return $this->intTextDecoration;
	}

	function hasOverline()
	{
		return ($this->intTextDecoration & self::TEXT_DECORATION_OVERLINE);
	}

	function hasUnderline()
	{
		return ($this->intTextDecoration & self::TEXT_DECORATION_UNDERLINE);
	}

	function hasLineThrough()
	{
		return ($this->intTextDecoration & self::TEXT_DECORATION_LINE_THROUGH);
	}

	function hasTextDecoration()
	{
		return ($this->intTextDecoration & (self::TEXT_DECORATION_LINE_THROUGH | self::TEXT_DECORATION_OVERLINE | self::TEXT_DECORATION_UNDERLINE));
	}

	function setLineHeight($lineHeight)
	{
		$this->lineHeight = $this->getPointSize($lineHeight);
	}

	function getLineHeight()
	{
		if ($this->lineHeight) return $this->lineHeight;
		$font = $this->getFont();
		return ($font->getLineHeight() / $font->getUnitsPerEm()) * $this->getFontSize();
	}

	function setFontResources($fontResources)
	{
		$this->fontResources = $fontResources;
	}

	function setTop($int)
	{
		$this->intTop = $this->getPointSize($int);
	}

	function getTop()
	{
		return $this->intTop;
	}

	function setLeft($int)
	{
		$this->intLeft = $this->getPointSize($int);
	}

	function getLeft()
	{
		return $this->intLeft;
	}

	function setBottom($int)
	{
		$this->intBottom = $this->getPointSize($int);
	}

	function getBottom()
	{
		return $this->intBottom;
	}

	function setRight($int)
	{
		$this->intRight = $this->getPointSize($int);
	}

	function getRight()
	{
		return $this->intRight;
	}

	function setWidth($int)
	{
		$this->intWidth = $this->getPointSize($int);
	}

	function getWidth()
	{
		return $this->intWidth;
	}

	function hasFixedWidth()
	{
		return $this->intWidth !== NULL;
	}

	function setHeight($int)
	{
		$this->intHeight = $this->getPointSize($int);
	}

	function getHeight()
	{
		return $this->intHeight;
	}

	function hasFixedHeight()
	{
		return $this->intHeight !== NULL;
	}

	function setCornerRadius($int)
	{
		$this->intCornerRadius = $this->getPointSize($int);
	}

	function getCornerRadius()
	{
		return $this->intCornerRadius;
	}

	function setBorderWidth($int)
	{
		$this->setBorderWidthTop($int);
		$this->setBorderWidthRight($int);
		$this->setBorderWidthBottom($int);
		$this->setBorderWidthLeft($int);
	}

	function setBorderWidthTop($int)
	{
		$this->intBorderWidthTop = $this->getPointSize($int);
	}

	function setBorderWidthRight($int)
	{
		$this->intBorderWidthRight = $this->getPointSize($int);
	}

	function setBorderWidthBottom($int)
	{
		$this->intBorderWidthBottom = $this->getPointSize($int);
	}

	function setBorderWidthLeft($int)
	{
		$this->intBorderWidthLeft = $this->getPointSize($int);
	}

	function getBorderWidthTop()
	{
		return $this->intBorderWidthTop;
	}

	function getBorderWidthRight()
	{
		return $this->intBorderWidthRight;
	}

	function getBorderWidthBottom()
	{
		return $this->intBorderWidthBottom;
	}

	function getBorderWidthLeft()
	{
		return $this->intBorderWidthLeft;
	}

	function setColor($objColor)
	{
		$this->objColor = $objColor;
	}

	function getColor()
	{
		return $this->objColor;
	}

	function setBorderColor($objColor)
	{
		$this->objBorderColor = $objColor;
	}

	function getBorderColor()
	{
		return $this->objBorderColor;
	}

	function setBackgroundColor($objColor)
	{
		$this->objBackgroundColor = $objColor;
	}

	function getBackgroundColor()
	{
		return $this->objBackgroundColor;
	}

	function setTextAlign($mixTextAlign)
	{
		$intTextAlign = self::TEXT_ALIGN_LEFT;
		switch (strtoupper(trim($mixTextAlign)))
		{
			case "RIGHT":
				$intTextAlign = self::TEXT_ALIGN_RIGHT;
				break;
			case "CENTER":
			case "CENTRE":
				$intTextAlign = self::TEXT_ALIGN_CENTRE;
				break;
			case "LEFT":
				$intTextAlign = self::TEXT_ALIGN_LEFT;
				break;
			default:
				if (!is_int($mixTextAlign) || $mixTextAlign < 0 || $mixTextAlign > 2)
				{
					$intTextAlign = self::TEXT_ALIGN_LEFT;
				}
				else
				{
					$intTextAlign = $mixTextAlign;
				}
		}
		$this->intTextAlign = $intTextAlign;
	}

	function getTextAlign()
	{
		return $this->intTextAlign;
	}

	function isTextAlignLeft()
	{
		return $this->intTextAlign == self::TEXT_ALIGN_LEFT;
	}

	function isTextAlignRight()
	{
		return $this->intTextAlign == self::TEXT_ALIGN_RIGHT;
	}

	function isTextAlignCentre()
	{
		return $this->intTextAlign == self::TEXT_ALIGN_CENTRE;
	}

	function setOverflow($mixOverflow)
	{
		$intTextAlign = self::OVERFLOW_VISIBLE;
		switch (strtoupper(trim($mixOverflow)))
		{
			case "HIDDEN":
				$intOverflow = self::OVERFLOW_HIDDEN;
				break;
			case "ALL-OR-NOTHING":
				$intOverflow = self::OVERFLOW_ALL_OR_NOTHING;
				break;
			case "VISIBLE":
				$intOverflow = self::OVERFLOW_VISIBLE;
				break;
			default:
				if (!is_int($mixOverflow) || $mixOverflow < 0 || $mixOverflow > 2)
				{
					$intOverflow = self::OVERFLOW_VISIBLE;
				}
				else
				{
					$intOverflow = $mixOverflow;
				}
		}
		$this->intOverflow = $intOverflow;
	}

	function getOverflow()
	{
		return $this->intOverflow;
	}

	function setPadding($int)
	{
		$this->setPaddingTop($int);
		$this->setPaddingRight($int);
		$this->setPaddingBottom($int);
		$this->setPaddingLeft($int);
	}

	function setPaddingTop($int)
	{
		$this->intPaddingTop = $this->getPointSize($int);
	}

	function setPaddingRight($int)
	{
		$this->intPaddingRight = $this->getPointSize($int);
	}

	function setPaddingBottom($int)
	{
		$this->intPaddingBottom = $this->getPointSize($int);
	}

	function setPaddingLeft($int)
	{
		$this->intPaddingLeft = $this->getPointSize($int);
	}

	function getPaddingTop()
	{
		return $this->intPaddingTop;
	}

	function getPaddingRight()
	{
		return $this->intPaddingRight;
	}

	function getPaddingBottom()
	{
		return $this->intPaddingBottom;
	}

	function getPaddingLeft()
	{
		return $this->intPaddingLeft;
	}

	function setPageSize($strSize)
	{
		$tmpStrSize = preg_replace("/[^A-Z0-9]+/", " ", trim(strtoupper($strSize)));
		switch ($tmpStrSize)
		{
			case "A4 LANDSCAPE":
				$this->strPageSize = Flex_Pdf_Page::SIZE_A4_LANDSCAPE;
				break;

			case "LETTER":
				$this->strPageSize = Flex_Pdf_Page::SIZE_LETTER;
				break;

			case "LETTER LANDSCAPE":
				$this->strPageSize = Flex_Pdf_Page::SIZE_LETTER_LANDSCAPE;
				break;

			case "A4":
				$this->strPageSize = Flex_Pdf_Page::SIZE_A4;
				break;

			default:
				$strSize = trim(preg_replace(array("/[^0-9]/", "/ +/", "/^ +/", "/ +$/", "/ /"), array(" ", " ", "", "", ":"), $strSize)).":";
				if (preg_match("/^[0-9]+:[0-9]+:$/", $strSize))
				{
					$this->strPageSize = $strSize;
				}
		}
	}

	function getPageSize()
	{
		return $this->strPageSize;
	}

	function getPageWidth()
	{
		$dims = explode(":", $this->strPageSize);
		return $dims[0];
	}

	function getPageHeight()
	{
		$dims = explode(":", $this->strPageSize);
		return $dims[1];
	}

	function setMedia($strMedia)
	{
		switch(strtoupper(trim($strMedia)))
		{
			case "PRINT":
				$this->intMedia = self::MEDIA_PRINT;
				break;

			case "EMAIL":
				$this->intMedia = self::MEDIA_EMAIL;
				break;

			case "BOTH":
			case "ANY":
			case "ALL":
				$this->intMedia = self::MEDIA_ALL;
				break;
		}
	}

	function getMedia()
	{
		return $this->intMedia;
	}

	function suitableForMedia($media)
	{
		// We only include printable media when the media is print.
		return $this->intMedia & $media;
	}

	static function getPointSize($strSize)
	{
		$strSize = trim($strSize);
		$mm = preg_match("/mm$/", $strSize);
		$fltSize = floatval($strSize);
		if ($mm) $fltSize = $fltSize * 2.83464567;
		return $fltSize;
	}

	function getHTMLStyleAttributeValue()
	{
		$style = array();


		// Position
		if ($this->getTop() !== NULL)
		{
			$style[] = "top: " . $this->getTop() . "pt";
		}
		else if ($this->getBottom() !== NULL)
		{
			$style[] = "bottom: " . $this->getBottom() . "pt";
		}
		if ($this->getLeft() !== NULL)
		{
			$style[] = "left: " . $this->getLeft() . "pt";
		}
		else if ($this->getRight() !== NULL)
		{
			$style[] = "right: " . $this->getRight() . "pt";
		}


		// Size
		if ($this->getWidth() !== NULL)
		{
			$style[] = "width: " . $this->getWidth() . "pt";
		}
		if ($this->getHeight() !== NULL)
		{
			$style[] = "height: " . $this->getHeight() . "pt";
		}
		if ($this->getHeight() !== NULL)
		{
			$style[] = "height: " . $this->getHeight() . "pt";
		}

		if (!$this->bolInheritedFromStyle || $this->getPageSize() != Flex_Pdf_Page::SIZE_A4)
		{
			$style[] = "page-size: " . str_replace(":", "pt ", substr($this->getPageSize(), -1)) . "pt";
		}

		// Font
		if ($this->fontParts[self::FONT_FAMILY])
		{
			$style[] = "font-family: " . strtolower(trim($this->fontParts[self::FONT_FAMILY]));
		}
		if ($this->fontParts[self::FONT_FAMILY])
		{
			$f = strtolower(trim($this->fontParts[self::FONT_WEIGHT]));
			$style[] = "font-weight: " . ($f ? $f : "normal");
		}
		if ($this->fontParts[self::FONT_FAMILY])
		{
			$f = strtolower(trim($this->fontParts[self::FONT_STYLE]));
			$style[] = "font-style: " . ($f ? $f : "normal");
		}

		$style[] = "font-size: " . $this->getFontSize() . "pt";

		// Text decoration
		$textDec = "";
		$textDec .= $this->hasOverline() ? "overline " : "";
		$textDec .= $this->hasUnderline() ? "undeline " : "";
		$textDec .= $this->hasLineThrough() ? "line-through " : "";
		if (!$textDec) $textDec = "none";
		$style[] = "text-decoration: " . trim($textDec);


		// Text alignment
		switch ($this->getTextAlign())
		{
			case self::TEXT_ALIGN_RIGHT:
				$style[] = "text-align: right";
				break;

			case self::TEXT_ALIGN_CENTRE:
				$style[] = "text-align: center";
				break;

			case self::TEXT_ALIGN_LEFT:
			default:
				$style[] = "text-align: left";
				break;
		}


		// Line height
		if ($this->getLineHeight()) $style[] = "line-height: " . $this->getLineHeight() . "pt";

		// Media
		switch ($this->getMedia())
		{
			case self::MEDIA_PRINT:
				$style[] = "media: print";
				break;

			case self::MEDIA_EMAIL:
				$style[] = "media: email";
				break;

			case self::MEDIA_ALL:
			default:
				$style[] = "media: all";
				break;
		}


		// Overflow
		switch ($this->getOverflow())
		{
			case self::OVERFLOW_HIDDEN:
				$style[] = "overflow: hidden";
				break;

			case self::OVERFLOW_ALL_OR_NOTHING:
				$style[] = "overflow: all-or-nothing";
				break;

			case self::OVERFLOW_VISIBLE:
			default:
				break;
		}

		// Corners
		$style[] = "corner-radius: " . $this->getCornerRadius() . "pt";


		// Padding
		$style[] = "padding-top: " . $this->getPaddingTop() . "pt";
		$style[] = "padding-right: " . $this->getPaddingRight() . "pt";
		$style[] = "padding-bottom: " . $this->getPaddingBottom() . "pt";
		$style[] = "padding-left: " . $this->getPaddingLeft() . "pt";


		// Color
		if ($this->getColor() != NULL)
		{
			$style[] = "color: " . $this->getColor()->getHTMLColour();
		}
		if ($this->getBackgroundColor() != NULL)
		{
			$style[] = "background-color: " . $this->getBackgroundColor()->getHTMLColour();
		}
		if ($this->getBorderColor() != NULL)
		{
			$style[] = "border-color: " . $this->getBorderColor()->getHTMLColour();
		}


		// Borders
		$style[] = "border-width-top: " . $this->getBorderWidthTop() . "pt";
		$style[] = "border-width-right: " . $this->getBorderWidthRight() . "pt";
		$style[] = "border-width-bottom: " . $this->getBorderWidthBottom() . "pt";
		$style[] = "border-width-left: " . $this->getBorderWidthLeft() . "pt";

		return implode("; ", $style);
	}


	public static function mediaForMediaName($strMediaName)
	{
		switch (strtoupper(trim($strMediaName)))
		{
			case "EMAIL":
				return self::MEDIA_EMAIL;
			case "PRINT":
				return self::MEDIA_PRINT;
			default:
				return self::MEDIA_EMAIL;
		}
	}

}