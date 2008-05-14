<?php

require_once "Zend/Pdf.php";

class Flex_Pdf_Text
{
	const CHAR_BREAK_BEFORE		= 1;
	const CHAR_BREAK_AFTER		= 2;
	const CHAR_BREAK_ALWAYS		= 4;

	/**
	* Returns the total width in points of the string using the specified font and
	* size.
	*
	* This is not the most efficient way to perform this calculation. I'm 
	* concentrating optimization efforts on the upcoming layout manager class.
	* Similar calculations exist inside the layout manager class, but widths are
	* generally calculated only after determining line fragments.
	*
	* @param string $string
	* @param Zend_Pdf_Resource_Font $font
	* @param float $fontSize Font size in points
	* @return float
	* 
	* @authur Willie Alberty (http://framework.zend.com/issues/browse/ZF-313)
	*/
	static function widthForStringUsingFontSize($string, $font, $fontSize)
	{
		$characters = Flex_Pdf_Text::stringToCharacters($string);
		$glyphs = $font->glyphNumbersForCharacters($characters);
		$widths = $font->widthsForGlyphs($glyphs);
		$stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
		return $stringWidth;
	}

	/**
	* Splits a string into substrings of a specified width (or shorter) for
	* a specified font and size.
	*
	* @param string $string
	* @param Zend_Pdf_Resource_Font $font
	* @param float $fontSize Font size in points
	* @param float $maxWidth Maximum allowed substring width in pixels
	* @param float $initialWidth Length allowed for first line if different from the rest
	* @return array
	*/
	static function splitStringToLengths($string, $font, $fontSize, $maxRowWidth, $initialWidth=-1)
	{
		// Replace any \t chars with spaces
		$string = str_replace("\t", " ", $string);
		// Strip out any \r chars (we only break lines on \n's)
		$string = str_replace("\r", "", $string);
		// If width was specified in pixels, convert to points (approx.!!)

		// Break the string down to characters
		$characters = Flex_Pdf_Text::stringToCharacters($string);

		// Get the glyphs for the characters in the specified font
		$glyphs = $font->glyphNumbersForCharacters($characters);
		// Get the widths in 'em's for the glyphs in the specified font
		$widths = $font->widthsForGlyphs($glyphs);
		// Get the units per em for this font
		$unitsPerEm = $font->getUnitsPerEm();

		// Convert the 'em' widths to point widths for the specified font and font size
		$pointWidths = array();
		for ($i = 0, $l = count($widths); $i < $l; $i++)
		{
			$pointWidths[] = ($widths[$i] / $unitsPerEm) * $fontSize;
		} 

		$fltTotalWidth = 0;
		$strBeforeLastBreak = "";
		$strAfterLastBreak = "";
		$fltWidthAfterLastBreak = 0;

		$strings = array();
		$stringWidths = array();
		$subString = "";
		
		if ($maxRowWidth == $initialWidth)
		{
			$initialWidth = -1;
		}
		
		$firstRowDone = FALSE;
		$doingAnInitialRow = FALSE;
		if ($initialWidth >= 0)
		{
			$doingAnInitialRow = TRUE;
		}
		else
		{
			$initialWidth = $maxRowWidth;
		}

		for ($i = 0, $l = count($characters); $i < $l; $i++)
		{
			$char = substr($string, $i, 1);
			$breakRules = Flex_Pdf_Text::getCharacterBreakRules($char);
			
			// Allow a break before the first char if this is an 'initial'
			// (usually shorter than normal) row.
			if ($doingAnInitialRow && $i == 0)
			{
				$breakRules = $breakRules | Flex_Pdf_Text::CHAR_BREAK_BEFORE;
			}
			
			$width = $pointWidths[$i];
			$maxWidth = $firstRowDone ? $maxRowWidth : $initialWidth;

			if ($breakRules & Flex_Pdf_Text::CHAR_BREAK_ALWAYS)
			{
				$strings[] = ltrim($strBeforeLastBreak . $strAfterLastBreak, " ");
				$firstRowDone = TRUE;
				$stringWidths[] = $fltTotalWidth;
				$strBeforeLastBreak = "";
				$strAfterLastBreak = "";
				$fltWidthAfterLastBreak = 0;
				$fltTotalWidth = 0;
				continue;
			}

			// If we need to break the subString...
			if ($fltTotalWidth + $width > $maxWidth)
			{
				// If we can (or have to) break before this char...
				if (($breakRules & Flex_Pdf_Text::CHAR_BREAK_BEFORE) || ($strAfterLastBreak && !$strBeforeLastBreak))
				{
					// If this is the first row and it is an initial row, we don't want to force a break
					if (!($breakRules & Flex_Pdf_Text::CHAR_BREAK_BEFORE) && !$firstRowDone && $doingAnInitialRow)
					{
						$strings[] = "";
						$stringWidths[] = 0;
						$i--;
						$firstRowDone = TRUE;
						continue;
					}
					
					$firstRowDone = TRUE;
					$strings[] = ltrim($strBeforeLastBreak . $strAfterLastBreak, " ");
					$stringWidths[] = $fltTotalWidth;
					$strAfterLastBreak = $char;
					$strBeforeLastBreak = "";
					$fltWidthAfterLastBreak = $width;
					$fltTotalWidth = $width;
					continue;
				}

				// There must have been a good break point

				// If there is anything before the last break...
				if (ltrim($strBeforeLastBreak, " "))
				{
					$strings[] = ltrim($strBeforeLastBreak, " ");
					$firstRowDone = TRUE;
					$stringWidths[] = $fltTotalWidth - $fltWidthAfterLastBreak;
					$strBeforeLastBreak = "";
				}

				// Add the last char to the totals...
				$strAfterLastBreak .= $char;
				$fltWidthAfterLastBreak += $width;
				$fltTotalWidth = $fltWidthAfterLastBreak;
				
				// If this latest char can be broken after...
				if ($breakRules & Flex_Pdf_Text::CHAR_BREAK_AFTER)
				{
					$strBeforeLastBreak = $strAfterLastBreak;
					$strAfterLastBreak = "";
					$fltWidthAfterLastBreak = 0;
				}

				continue;
			}

			// If this char can be broken before
			if ($breakRules & Flex_Pdf_Text::CHAR_BREAK_BEFORE)
			{
   				$strBeforeLastBreak .= $strAfterLastBreak;
   				$strAfterLastBreak = $char;
   				$fltWidthAfterLastBreak = $width;
			}

			// If this char can be broken after
			else if ($breakRules & Flex_Pdf_Text::CHAR_BREAK_AFTER)
			{
   				$strBeforeLastBreak .= $strAfterLastBreak . $char;
   				$strAfterLastBreak = "";
   				$fltWidthAfterLastBreak = 0;
			}
			// Add the new char to the substring
			else
			{
				$strAfterLastBreak .= $char;
				$fltWidthAfterLastBreak += $width;
			}

			$fltTotalWidth += $width;
		}

		if (ltrim($strBeforeLastBreak . $strAfterLastBreak, " "))
		{
			$strings[] = ltrim($strBeforeLastBreak . $strAfterLastBreak, " ");
			$stringWidths[] = $fltTotalWidth;
		}
		else if($strBeforeLastBreak)
		{
			$strings[] = "";
			$stringWidths[] = 0;
		}

		return array("STRINGS" => $strings, "WIDTHS" => $stringWidths);
	}

	private static function stringToCharacters($strString)
	{
		$drawingString = iconv('', 'UTF-16BE', $strString);
		$characters = array();
		for ($i = 0, $l = strlen($drawingString); $i < $l; $i++) {
			$characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
		}
		return $characters;
	} 
	
	private static $breakRules = array();
	private static $strBreakBefore = "({[<";
	private static $strBreakAfter   = ")}]>.,?!:;- ";
	private static $strBreakAlways  = "\n";
	
	static function getCharacterBreakRules($char)
	{
		if (!array_key_exists($char, self::$breakRules))
		{
			self::$breakRules[$char] = 0;
			if (strpos(self::$strBreakBefore, $char) !== FALSE) self::$breakRules[$char] += Flex_Pdf_Text::CHAR_BREAK_BEFORE;
			if (strpos(self::$strBreakAfter,  $char) !== FALSE) self::$breakRules[$char] += Flex_Pdf_Text::CHAR_BREAK_AFTER;
			if (strpos(self::$strBreakAlways, $char) !== FALSE) self::$breakRules[$char] += Flex_Pdf_Text::CHAR_BREAK_ALWAYS;
		}
		return self::$breakRules[$char];
	}

}


?>
