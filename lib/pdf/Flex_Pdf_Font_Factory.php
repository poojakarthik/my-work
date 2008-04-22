<?php


class Flex_Pdf_Font_Factory
{
	private static $fonts = array();
	
	public static function get($fontName)
	{
		if (!array_key_exists(strtoupper($fontName), self::$fonts))
		{
			$font = null;
			switch (strtoupper($fontName))
			{
				case "HELVETICA":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
					break;
				case "HELVETICA ITALIC":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
					break;
				case "HELVETICA BOLD ITALIC":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
					break;
				case "HELVETICA BOLD":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
					break;
				case "COURIER":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
					break;
				case "COURIER ITALIC":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_ITALIC);
					break;
				case "COURIER BOLD ITALIC":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD_ITALIC);
					break;
				case "COURIER BOLD":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD);
					break;
				case "TIMES":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
					break;
				case "TIMES ITALIC":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ITALIC);
					break;
				case "TIMES BOLD ITALIC":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD_ITALIC);
					break;
				case "TIMES BOLD":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
					break;
				case "SYMBOL":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_SYMBOL);
					break;
				case "ZAPFDINGBATS":
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_ZAPFDINGBATS);
					break;
				default:
					if (file_exists($fontName))
					{
						// Try to load the file as a font file
						$font = Zend_Pdf_Font::fontWithPath($fontName);
					}
					else
					{
						throw new Exception("Font resource not found: $fontName");
					}
			}
			self::$fonts[strtoupper($fontName)] = $font;
		}

		return self::$fonts[strtoupper($fontName)];
	}
}

?>
