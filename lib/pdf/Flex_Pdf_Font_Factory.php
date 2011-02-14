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
					//throw new Exception("Looking for '{$fontName}'...");
					if (file_exists($fontName))
					{
						// Try to load the file as a font file
						try
						{
							// WARNING: We are forcing font embedding for fonts we possibly don't have a licence to
							$font = Zend_Pdf_Font::fontWithPath($fontName/*, Zend_Pdf_Font::EMBED_SUPPRESS_EMBED_EXCEPTION*/);
							if (!($font instanceof Zend_Pdf_Resource_Font))
							{
								throw new Exception("Failed to create font resource for font name '$fontName'. The font file may be invalid.");
							}
						}
						catch (Exception $e)
						{
							throw $e;
						}
					}
					else
					{
						throw new Exception("Font resource not found: $fontName. \nCheck the font name and ensure the font has been listed in <embedded-fonts>.");
					}
			}
			self::$fonts[strtoupper($fontName)] = $font;
		}

		return self::$fonts[strtoupper($fontName)];
	}
}

?>
