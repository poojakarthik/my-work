<?php


class Flex_Pdf_Template_Span extends Flex_Pdf_Template_Element
{
	private $content = "";
	private $originalContent = "";

	public $preparedContents;

	function initialize()
	{

		$textContent = "";

		// Need to parse the text for permitted elements textNodes and <br>'s
		for($i = 0, $l = $this->dom->childNodes->length; $i < $l; $i++)
		{
			$node = $this->dom->childNodes->item($i);
			// Text is fine just as it is here, so continue...
			if ($node instanceof DOMText)
			{
				$textContent .= $node->wholeText;
				continue;
			}

			switch (strtoupper($node->tagName))
			{
				case "BR":
					// BR elements should be replaced by new line text nodes at this level
					// (Note: The additional space char forces the \n to be rendered.
					// A space at the start of a line is always ignored)
					$textContent .= "<<br>>";
					break;

				case "SP":
				case "NBSP":
				case "SPACE":
					// BR elements should be replaced by new line text nodes at this level
					// (Note: The additional space char forces the \n to be rendered.
					// A space at the start of a line is always ignored)
					$textContent .= "<<sp>>";
					break;

				case "PAGE-NR":
					// PAGE-NR elements need to be replaced with the current page number at the time of rendering
					$textContent .= "<<pn>>";
					break;

				case "PAGE-COUNT":
					// PAGE-COUNT elements need to be replaced with the current page number at the time of rendering
					$textContent .= "<<pc>>";
					break;

				case "A":
				case "SPAN":
					// Span and A elements shouldn't be at this level, they should only be in the level above
					// In effect, a nested span/a splits the parent span/a (the one we are in) into 3 spans/as!
					// It would be nice if we could handle this.
					break;

				default:
					// It's not in the right place!
					// Just ignore it for now...
					break;
			}
		}

		$cleanContent = str_replace("\n", " ", str_replace("\r", "", str_replace("\t", " ", $textContent)));
		$cleanContent = preg_replace(array("/ *\<\<br *\/?\>\> */", "/ +/", "/\<\<sp *\/?\>\>/"), array("\n ", " ", " "), $cleanContent);

		$this->originalContent = $cleanContent;
		$this->setContent($cleanContent);
	}

	public function appendToDom($doc, $parentNode, $parent=NULL)
	{
		// Create a node for this element
		$node = $doc->importNode($this->dom, TRUE);

		// Apply the style to this node
		$node->setAttribute("style", $this->getStyle()->getHTMLStyleAttributeValue());

		// Should also apply the current page number
		$pageNrs = $node->getElementsByTagName("page-nr");
		for ($i = 0, $l = $pageNrs->length; $i < $l; $i++)
		{
			$pageNrTag = $pageNrs->item($i);
			$pageNr = $doc->createTextNode($this->getCurrentPageNumber());
			$pageNrTag->parentNode->replaceChild($pageNr, $pageNrTag);
		}

		// Append this node to the parentNode
		$parentNode->appendChild($node);
	}

	function prepare($availableWidth, $firstRowWidth)
	{
		$this->preparedContents = Flex_Pdf_Text::splitStringToLengths($this->content, $this->getStyle()->getFont(), $this->getStyle()->getFontSize(), $availableWidth, $firstRowWidth);
		return $this->preparedContents;
	}

	function setContent($content)
	{
		$this->content = $content;
	}

	function renderOnPage($page, $parent=NULL)
	{
		// Span rendering is currently handled by the paragraph object.
		// Bit messy - could do with improving this as it makes rendring border/background a bit tricky.
	}

	public function clearTemporaryDetails()
	{
		$this->preparedContents = NULL;

		$this->setContent(str_replace("<<pn>>", $this->getCurrentPageNumber(), $this->originalContent));
	}


	public function hasAbsoluteVertical()
	{
		return false;
	}

}

?>
