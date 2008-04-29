<?php

class Flex_Pdf_Template_Wrapped_Footer extends Flex_Pdf_Template_Div
{
	private $intInclude = 0;
	private $bolOptional = FALSE;

	// If first section on page has a value of 1
	const LAST_SECTION_LAST_PAGE 		= 1;
	// If first section of other pages have a value of 2
	const LAST_SECTION_OTHER_PAGE 		= 2;
	// If non-first section on each page has a value of 4
	const NON_LAST_SECTION_ANY_PAGE		= 4;
	// ... binary comparrison with the following should determine
	//     if the header should be included.
	const INCLUDE_LAST_SECTION 		= 1; // self::LAST_SECTION_LAST_PAGE;
	const INCLUDE_BEFORE_LAST_PAGE 		= 2; // self::LAST_SECTION_OTHER_PAGE;
	const INCLUDE_EACH_PAGE 			= 3; // self::LAST_SECTION_LAST_PAGE  | self::LAST_SECTION_OTHER_PAGE;
	const INCLUDE_BEFORE_LAST_SECTION 	= 6; // self::LAST_SECTION_OTHER_PAGE | self::NON_LAST_SECTION_ANY_PAGE;
	const INCLUDE_EACH_SECTION 			= 7; // self::LAST_SECTION_LAST_PAGE  | self::LAST_SECTION_OTHER_PAGE | self::NON_LAST_SECTION_ANY_PAGE;

	function initialize()
	{
		parent::initialize();

		$include = $this->dom->hasAttribute("include") ? $this->dom->getAttribute("include") : "";

		switch (strtolower($include))
		{
			case "last-section-if-fits":
				$this->bolOptional = TRUE;
			case "last-page":
			case "last-section":
				$this->intInclude = self::INCLUDE_LAST_SECTION;
				break;

			case "before-last-page":
				$this->intInclude = self::INCLUDE_BEFORE_LAST_PAGE;
				break;

			case "every-page":
				$this->intInclude = self::INCLUDE_EACH_PAGE;
				break;

			case "before-last-section":
				$this->intInclude = self::INCLUDE_BEFORE_LAST_SECTION;
				break;

			case "every-section":
				$this->intInclude = self::INCLUDE_EACH_SECTION;
				break;

		}

	}

	function displayForSection($bolLastSectionOnPage, $bolLastPageForFooters)
	{
		$section = $bolLastPageForFooters ? self::LAST_SECTION_LAST_PAGE : ($bolLastSectionOnPage ? self::LAST_SECTION_OTHER_PAGE : self::NON_LAST_SECTION_ANY_PAGE);
		//$section = $bolLastSectionOnPage ? ($bolLastPageForFooters ? self::LAST_SECTION_LAST_PAGE : self::LAST_SECTION_OTHER_PAGE) : self::NON_LAST_SECTION_ANY_PAGE;
		return $section & $this->intInclude;
	}

	function isOptional()
	{
		return $this->bolOptional;
	}
}


?>
