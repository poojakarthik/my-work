<?php

abstract class Flex_Pdf_Page_Orderable
{
	public $dom = null;
	public $template = null;

	function __construct($template, $domNode)
	{
		$this->template = $template;
		$this->dom = $domNode;
		$this->parseDomNode();
	}
	
	abstract function parseDomNode();
}

class Flex_Pdf_Page_Order extends Flex_Pdf_Page_Orderable
{
	public $pages = array();
	public $currentIndex = -1;
	public $nrPages = 0;

	function parseDomNode()
	{
		for ($i = 0, $l = $this->dom->childNodes->length; $i < $l; $i++)
		{
			$node = $this->dom->childNodes->item($i);
			switch (strtoupper($node->tagName))
			{
				case "PAGE-OBJECT":
					$this->pages[] = new Flex_Pdf_Page_Object($this->template, $node);
					$this->nrPages++;
					break;
					
				case "PAGE-REPEAT":
					$this->pages[] = new Flex_Pdf_Page_Repeat($this->template, $node);
					$this->nrPages++;
					break;
			}
		}
	}
	
	function nextPage()
	{
		// If there are no pages or we are beyond the last one, return NULL
		if ($this->nrPages == 0 || $this->currentIndex >= $this->nrPages)
		{
			return NULL;
		}
		// Get the current page at this level
		$currentPage = null;
		if ($this->currentIndex >= 0) 
		{
			$currentPage = $this->pages[$this->currentIndex];
		}
		// If it is a 'repeat'...
		if ($currentPage instanceof Flex_Pdf_Page_Repeat)
		{
			// Get its current page
			$currentPage = $currentPage->nextPage();
			// If it hasn't got one, it is complete
			if ($currentPage == NULL)
			{
				// ... so get the next current page at this level
				do
				{
					$this->currentIndex++;
					// If we are beyond the end of the pages, return NULL
					if ($this->currentIndex >= $this->nrPages)
					{
						return NULL;
					}
					// Get the current page
					$currentPage = $this->pages[$this->currentIndex];
					// If it is a 'repeat' then recurse back into this function
					if ($currentPage instanceof Flex_Pdf_Page_Repeat)
					{
						return $this->nextPage();
					}
				} while (!$currentPage->doInclude()); // While the current page should not be included
			}
		}
		// Else it is a single page, which is always finished with
		else
		{
			// ... so get the next page
			do
			{
				$this->currentIndex++;
				// If we are beyond the end of the pages, return NULL
				if ($this->currentIndex >= $this->nrPages)
				{
					return NULL;
				}
				// Get the current page
				$currentPage = $this->pages[$this->currentIndex];
				// If it is a 'repeat' then recurse back into this function
				if ($currentPage instanceof Flex_Pdf_Page_Repeat)
				{
					return $this->nextPage();
				}
			} while (!$currentPage->doInclude()); // While the current page should not be included
		}
		// Return the 'new current page'
		return $currentPage;
	}
	
	function currentPage()
	{
		// If there are no pages, return NULL
		if ($this->nrPages == 0)
		{
			return NULL;
		}
		if ($this->currentIndex < 0)
		{
			return $this->nextPage();
		}
		// Get the page at the current index
		$currentPage = $this->pages[$this->currentIndex];
		// If it is a 'repeat' then get its current page
		if ($currentPage instanceof Flex_Pdf_Page_Repeat)
		{
			$currentPage = $currentPage->currentPage();
		}
		// Return the current page
		return $currentPage;
	}
	
	function __toString()
	{
		return var_export($this->pages, true);
	}
	
	function resetIndex()
	{
		$this->currentIndex = -1;
		for ($i = 0, $l = count($this->pages); $i < $i; $i++)
		{
			if ($this->pages[$i] instanceof Flex_Pdf_Page_Repeat)
			{
				return $this->pages[$i]->resetIndex();
			}
		}
	}
}

class Flex_Pdf_Page_Repeat extends Flex_Pdf_Page_Order
{
	// Default to TRUE to ensure we loop through the pages at least once
	public $loopRequiredOverflowOutput = TRUE;

	function nextPage()
	{
		if ($this->nrPages == 0)
		{
			//echo "<hr>No pages to repeat<hr>";
			return NULL;
		}
		if ($this->currentIndex >= $this->nrPages)
		{
			if ($this->loopRequiredOverflowOutput)
			{
				// Restart the loop
				$this->currentIndex = -1;
				$this->loopRequiredOverflowOutput = FALSE;
			}
			else
			{
				//echo "<hr>Returning null next page as last loop required no overflow output!<hr>";
				return null;
			}
		}
		$currentPage = NULL;
		if ($this->currentIndex >= 0) 
		{
			$currentPage = $this->pages[$this->currentIndex];
		}
		if ($currentPage instanceof Flex_Pdf_Page_Repeat)
		{
			$currentPage = $currentPage->nextPage();
			if ($currentPage == NULL)
			{
				do
				{
					$this->currentIndex++;
					if ($this->currentIndex >= $this->nrPages)
					{
						return $this->nextPage();
					}
					$currentPage = $this->pages[$this->currentIndex];
					if ($currentPage instanceof Flex_Pdf_Page_Repeat)
					{
						return $this->nextPage();
					}
				} while (!$currentPage->doInclude()); // While the current page should not be included
			}
			//echo "<hr>Checking to see if current page has incomplete page wrap includes...<hr>";
			$this->loopRequiredOverflowOutput = $this->loopRequiredOverflowOutput || $currentPage->pageHasIncompletePageWrapIncludes();
		}
		else
		{
			do
			{
				$this->currentIndex++;
				if ($this->currentIndex >= $this->nrPages)
				{
					return $this->nextPage();
				}
				$currentPage = $this->pages[$this->currentIndex];
				if ($currentPage instanceof Flex_Pdf_Page_Repeat)
				{
					return $this->nextPage();
				}
			} while (!$currentPage->doInclude()); // While the current page should not be included
			
			$this->loopRequiredOverflowOutput = $this->loopRequiredOverflowOutput || $currentPage->pageHasIncompletePageWrapIncludes();
		}
		return $currentPage;
	}
	
	function resetIndex()
	{
		$this->loopRequiredOverflowOutput = TRUE;
		$this->currentIndex = -1;
		for ($i = 0, $l = count($this->pages); $i < $i; $i++)
		{
			if ($this->pages[$i] instanceof Flex_Pdf_Page_Repeat)
			{
				return $this->pages[$i]->resetIndex();
			}
		}
	}

}

class Flex_Pdf_Page_Object extends Flex_Pdf_Page_Orderable
{
	public $strType = NULL;
	public $strInclude = "";
	public $intInclusions = 0;

	function parseDomNode()
	{
		$this->strType = $this->dom->getAttribute("type");
		$this->strInclude = strtoupper($this->dom->getAttribute("include"));
	}
	
	function pageHasIncompletePageWrapIncludes()
	{
		//echo "<hr>";
		$page = $this->template->getTemplatePage($this->strType);
		//echo (!$page ? "No template page $this->strType!" : "Got template page $this->strType...") . "<br>...";
		if (!$page) return false;
		//echo "<br>...".($page->hasIncompletePageWrapIncludes() ? " is incomplete!" : " is complete.") . "<hr>";
		return $page->hasIncompletePageWrapIncludes();
	}
	
	function alwaysInclude()
	{
		return $this->strInclude == "ALWAYS";
	}
	
	function doInclude()
	{
		if ($this->alwaysInclude() || $this->pageHasIncompletePageWrapIncludes())
		{
			$this->intInclusions++;
			return TRUE;
		}
		return FALSE;
	}
	
	function getType()
	{
		return $this->strType;
	}
}




?>
