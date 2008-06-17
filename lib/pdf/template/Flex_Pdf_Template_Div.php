<?php

class Flex_Pdf_Template_Div extends Flex_Pdf_Template_Element
{
	function initialize()
	{
		// Need to parse the div for permitted elements (div, text or table)
		for($i = 0, $l = $this->dom->childNodes->length; $i < $l; $i++)
		{
			$node = $this->dom->childNodes->item($i);
			// Text shouldn't be out there on its own, but we don't want to ignore it...
			if ($node instanceof DOMText)
			{
				// If it is only whitespace, ignore it
				if ($node->isWhitespaceInElementContent())
				{
					continue;
				}
				// Stick the text into a span to be handled properly
				$node = $this->wrapNode($node, "span");
			}

			$child = NULL;

			switch (strtoupper($node->tagName))
			{
				case "BR":
					// BR elements shouldn't be at this level, they should only be in SPAN elements.
					$node = $this->wrapNode($node, "span");
					// This still isn't right, so let's go to the next case to sort it out...
				case "A":
				case "SPAN":
					// Span elements shouldn't be at this level, they should only be in P elements.
					$node = $this->wrapNode($node, "p");
					// This still isn't right, so let's go to the next case to sort it out...
				case "P":
					$child = new Flex_Pdf_Template_Paragraph($node, $this);
					break;

				case "DIV":
					$child = new Flex_Pdf_Template_Div($node, $this);
					break;

				case "IMG":
					$child = new Flex_Pdf_Template_Image($node, $this);
					break;

				case "RAW":
					$child = new Flex_Pdf_Template_Raw($node, $this);
					break;

				case "BARCODE":
					$child = new Flex_Pdf_Template_Barcode($node, $this);
					break;

				//case "TABLE":
				//	$childElements[] = new Flex_Pdf_Template_Table($node, $this);
				//	break;

				case "PAGE-WRAP-INCLUDE":
					$child = new Flex_Pdf_Template_Page_Wrap_Include($node, $this);
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

	function renderOnPage($page, $parent=NULL)
	{
		// To render a border, we need to know how tall this div was. That could depend on the size of the contents.
		$page->drawBackground($this);
		
		$this->renderAsLinkTarget($page);

		$childElements = $this->getChildElements();
		for ($i = 0, $l = count($childElements); $i < $l; $i++)
		{
			$childElements[$i]->renderOnPage($page, $this);
		}
	}

	public function clearTemporaryDetails()
	{
	}
}