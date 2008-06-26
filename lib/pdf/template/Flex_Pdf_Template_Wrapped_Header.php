<?php

class Flex_Pdf_Template_Wrapped_Header extends Flex_Pdf_Template_Div
{
	private $intInclude = 0;

	// If first section on first page has a value of 1
	const FIRST_SECTION_FIRST_PAGE 		= 1;
	// If first sections of subsequent pages have values of 2
	const FIRST_SECTION_OTHER_PAGE 		= 2;
	// If non-first sections on each page have values of 4
	const NON_FIRST_SECTION_ANY_PAGE	= 4;
	// ... binary comparrison with the following should determine
	//     if the header should be included.
	const INCLUDE_FIRST_SECTION 		= 1; // self::FIRST_SECTION_FIRST_PAGE;
	const INCLUDE_AFTER_FIRST_PAGE 		= 2; // self::FIRST_SECTION_OTHER_PAGE;
	const INCLUDE_EACH_PAGE 			= 3; // self::FIRST_SECTION_FIRST_PAGE | self::FIRST_SECTION_OTHER_PAGE;
	const INCLUDE_AFTER_FIRST_SECTION 	= 6; // self::FIRST_SECTION_OTHER_PAGE | self::NON_FIRST_SECTION_ANY_PAGE;
	const INCLUDE_AFTER_FIRST_PAGE_SECTION 	= 4; // self::NON_FIRST_SECTION_ANY_PAGE;
	const INCLUDE_EACH_SECTION 			= 7; // self::FIRST_SECTION_FIRST_PAGE | self::FIRST_SECTION_OTHER_PAGE | self::NON_FIRST_SECTION_ANY_PAGE;
	const INCLUDE_ODD_PAGE				= 8;
	const INCLUDE_EVEN_PAGE				= 16;

	function initialize()
	{
		parent::initialize();

		$include = $this->dom->hasAttribute("include") ? $this->dom->getAttribute("include") : "";

		switch (strtolower($include))
		{
			case "first-page":
			case "first-section":
				$this->intInclude = self::INCLUDE_FIRST_SECTION;
				break;

			case "first-page-section":
				$this->intInclude = self::INCLUDE_FIRST_SECTION;
				break;

			case "after-first-page":
				$this->intInclude = self::INCLUDE_AFTER_FIRST_PAGE;
				break;

			case "every-page":
			case "first-section-on-page":
				$this->intInclude = self::INCLUDE_EACH_PAGE;
				break;

			case "other-section-on-page":
				$this->intInclude = self::INCLUDE_AFTER_FIRST_PAGE_SECTION;
				break;

			case "after-first-section":
				$this->intInclude = self::INCLUDE_AFTER_FIRST_SECTION;
				break;

			case "every-section":
				$this->intInclude = self::INCLUDE_EACH_SECTION;
				break;

			case "odd-page":
				$this->intInclude = self::INCLUDE_ODD_PAGE;
				break;

			case "even-page":
				$this->intInclude = self::INCLUDE_EVEN_PAGE;
				break;
		}

	}

	function displayForSection($bolFirstSectionOnPage, $bolFirstHeaders)
	{
		if ($this->intInclude == self::INCLUDE_ODD_PAGE)
		{
			return ($this->getCurrentPageNumber() % 2) == 1;
		}
		if ($this->intInclude == self::INCLUDE_EVEN_PAGE)
		{
			return ($this->getCurrentPageNumber() % 2) == 0;
		}
		
		$section = $bolFirstHeaders ? self::FIRST_SECTION_FIRST_PAGE : ($bolFirstSectionOnPage ? self::FIRST_SECTION_OTHER_PAGE : self::NON_FIRST_SECTION_ANY_PAGE);
		//$section = $bolFirstSectionOnPage ? ($bolFirstHeaders ? self::FIRST_SECTION_FIRST_PAGE : self::FIRST_SECTION_OTHER_PAGE) : self::NON_FIRST_SECTION_ANY_PAGE;
		return $section & $this->intInclude;
	}
}


?>
