<?php

require_once "pdf/Flex_Pdf.php";
require_once "pdf/Flex_Pdf_Style.php";
require_once "pdf/Flex_Pdf_Page_Order.php";
require_once "pdf/template/Flex_Pdf_Template_Page.php";
require_once "pdf/template/Flex_Pdf_Template_Page_Wrap_Content.php";

class Flex_Pdf_Template
{
	private $templateName;
	private $dom = null;
	private $templatePages = null;
	private $style = null;
	private $pageOrder = null;
	private $pageWrapContents = null;
	
	private $targetMedia = Flex_Pdf_Style::MEDIA_ALL;

	public function __construct($templateName, $xmlData, $bolTransformXML=TRUE, $targetMedia=Flex_Pdf_Style::MEDIA_ALL)
	{
		$this->templateName = $templateName;
		
		$this->targetMedia = $targetMedia;

		if ($xmlData != NULL)
		{
			// Load the data
			$this->loadData($xmlData, $bolTransformXML, $targetMedia);
		}
	}

	public function loadData($xmlData, $bolTransformXML, $targetMedia=Flex_Pdf_Style::MEDIA_ALL)
	{
		$this->targetMedia = $targetMedia;

		if ($bolTransformXML)
		{
			// Load the xsl file for the template
			$xslFilename = $this->getTemplateBase() . "template.xsl";
	
			// Create a document DOM to load the XSL as a DOM
			$doc = new DOMDocument();
			$doc->load($xslFilename);
			
			// Create an XSLT processor for the XSL DOM
			$xsl = new XSLTProcessor();
			$xsl->importStyleSheet($doc);
			
			// Load the data into a DOM
			$doc->loadXML($xmlData);
			
			// Transform the data DOM into a template DOM using the template XSLT processor
			$this->dom = $xsl->transformToDoc($doc);
		}
		else
		{
			// Create a document DOM
			$this->dom = new DOMDocument();
			
			// Load the data into the DOM
			$this->dom->loadXML($xmlData);
		}
		
		// Initialize style...
		$this->initializeStyle();
		
		// Load page order...
		$this->loadPageOrder();
		
		// Load page order...
		$this->loadPageWrapContents();

		// Load pages...
		$this->loadPages();
	}

	private function initializeStyle()
	{
		// Load up resource fonts (non-standard ones)
		$fonts = $this->dom->getElementsByTagName("embedded-font");
		$fontResources = array();
		foreach ($fonts as $font)
		{
			$fontResources[strtoupper($font->getAttribute("name"))] = $this->getTemplateBase() . $font->getAttribute("path");
		}
		
		$this->style = new Flex_Pdf_Style();
		$this->style->setFontResources($fontResources);		

		// Check for a default style attribute on the "pages" element
		$body = $this->dom->getElementsByTagName("body");
		if ($body->item(0) === NULL)
		{
			throw new Exception("Document has no body!");
		}

		$pages = $this->dom->getElementsByTagName("pages");
		if ($pages->item(0) === NULL)
		{
			throw new Exception("Document has no pages!");
		}
		
		if ($body->item(0)->hasAttribute("style"))
		{
			$this->style->applyStyleAttribute($body->item(0)->getAttribute("style"));
		}
		// ... assume a default style if one has not been specified
		else
		{
			$this->style->applyStyleAttribute("font-family: Helvetica; font-size: 12pt;");
		}

		if ($pages->item(0)->hasAttribute("style"))
		{
			$this->style->applyStyleAttribute($pages->item(0)->getAttribute("style"));
		}
	}

	private function loadPages()
	{
		$this->templatePages = array();

		$pages = $this->dom->getElementsByTagName("page");
		if ($pages->item(0) === NULL)
		{
			throw new Exception("Template '$this->templateName' has no pages!");
		}

		// Load up the template pages
		foreach ($pages as $page)
		{
			$this->templatePages[$page->getAttribute("type")] = new Flex_Pdf_Template_Page($page, $this);
		}
	}
	
	public function getTargetMedia()
	{
		return $this->targetMedia;
	}
	
	private $pageWrapContentNodes = array();
	
	private function loadPageWrapContents()
	{
		$this->pageWrapContents = array();

		$pageWrapContents = $this->dom->getElementsByTagName("page-wrap-content");
		
		foreach ($pageWrapContents as $pageWrapContent)
		{
			$this->pageWrapContentNodes[$pageWrapContent->getAttribute("id")] = $pageWrapContent;
		}

		// Load up the page wrap contents
		foreach ($this->pageWrapContentNodes as $id => $pageWrapContent)
		{
			if (!array_key_exists($id, $this->pageWrapContents))
			{
				$this->registerPageWrapContentNode($id, $this);
			}
		}
	}
	
	public function registerPageWrapContentNode($id, $parent)
	{
		if (!array_key_exists($id, $this->pageWrapContents) && array_key_exists($id, $this->pageWrapContentNodes))
		{
			$pageWrapContentNode = $this->pageWrapContentNodes[$id];
			$this->pageWrapContents[$id] = new Flex_Pdf_Template_Page_Wrap_Content($pageWrapContentNode, $parent);
		}
		return $this->pageWrapContents[$id];
	}
	
	public function getPageWrapContent($id)
	{
		return $this->pageWrapContents[$id];
	}

	private function loadPageOrder()
	{
		$pageOrders = $this->dom->getElementsByTagName("page-order");
		if ($pageOrders->length == 0)
		{
			// If no page order is specified then we will create a page order including all pages
			$pageOrderHolder = $this->dom->createElement("page-order-holder");
			$pageOrder = $this->dom->createElement("page-order");
			$pageOrderHolder->appendChild($pageOrder);
			// We will make sure that the pages have unique types...
			$pages = $this->dom->getElementsByTagName("page");
			// Itterate through the pages
			for ($i = 0, $l = $pages->length; $i < $l; $i++)
			{
				$type = "Page" . ($i + 1);
				$pages->item($i)->setAttribute("type", $type);
				$pageObject = $this->dom->createElement("page-object");
				$pageObject->setAttribute("type", $type);
				$pageObject->setAttribute("include", "always");
				$pageOrder->appendChild($pageObject);
			}
			$pageOrders = $pageOrderHolder->getElementsByTagName("page-order");
		}
		$this->pageOrder = new Flex_Pdf_Page_Order($this, $pageOrders->item(0));
	}
	
	public function getTemplate()
	{
		return $this;
	}

	public function getStyle()
	{
		return $this->style;
	}

	public function createElement($tagName)
	{
		return $this->dom->createElement($tagName);
	}
	
	public function createTextNode($text)
	{
		return $this->dom->createTextNode($text);
	}

	public function getTemplatePage($pageType)
	{
		return $this->templatePages[$pageType];
	}

	/**
	 * Return the template pages for the template
	 */
	public function getTemplatePages()
	{
		// TODO return an array of pdf template pages indexed by number/name
		return $this->templatePages;
	}

	/**
	 * Return the template resource base
	 */
	public function getTemplateBase()
	{
		// TODO return the path of the template resource base 
		return dirname(__FILE__) . "/pdf_templates/" . $this->templateName . "/";
	}

	public function getTemplateName()
	{
		return $this->templateName;
	}
	
	public function createDocument()
	{
		// Reset the page order
		$this->pageOrder->resetIndex();
		
		$pdfDocument = new Flex_Pdf();
		$this->appendToDocument($pdfDocument);
		return $pdfDocument;
	}
	
	public function createDocumentXML()
	{
		// Reset the page order
		$this->pageOrder->resetIndex();
		
		// We don't need to create a PDF, so a slimline version would do for this
		
		// What we do need to do is transform the template page based DOM
		// to a page based DOM. This is because the element styles are unique per page (re: position & size)
		// and because wrapped content will vary between renderings of template pages
		$doc = new DOMDocument('1.0', 'iso-8859-1');
		
		// Copy over details of any embedded fonts
		$embeddedFonts = $this->dom->getElementsByTagName("embedded-fonts");
		if ($embeddedFonts->length > 0)
		{
			$doc->appendChild($doc->importNode($embeddedFonts->item(0), TRUE));
		}
		
		// Create the basic html/body elements
		$html = $doc->createElement("html");
		$body = $doc->createElement("body");
		$doc->appendChild($html);
		$html->appendChild($body);
		
		$this->appendToDom($doc, $body);
		return $doc->saveXML();
	}
	
	
	private $intCurrentPageNumber = 0;
	
	public function getCurrentPageNumber()
	{
		return $this->intCurrentPageNumber;
	}
	
	public function appendToDom($doc, $node)
	{
		$this->intCurrentPageNumber = 0;
		do
		{
			$pageTemplateIdentifier = $this->pageOrder->nextPage();
			if ($pageTemplateIdentifier != null)
			{
				$this->intCurrentPageNumber++;
				$pageTemplate = $this->getTemplatePage($pageTemplateIdentifier->getType());
				$pageTemplate->appendToDom($doc, $node);
			}
		} while ($pageTemplateIdentifier != null);
	}
	
	public function appendToDocument($pdfDocument)
	{
		$pages = array();
		$this->intCurrentPageNumber = 0;
		do
		{
			$pageTemplateIdentifier = $this->pageOrder->nextPage();
			if ($pageTemplateIdentifier != null)
			{
				$this->intCurrentPageNumber++;
				$pageTemplate = $this->getTemplatePage($pageTemplateIdentifier->getType());
				$page = $pdfDocument->newPage($pageTemplate->getPageSize());
				$pages[] = $page;
				//echo "<hr><b><span style='color:blue; font-size:24pt;'>Page " . $this->intCurrentPageNumber . "</span></b><hr>";
				$pdfDocument->pages[] = $page;
				$pageTemplate->renderOnPage($page);
			}
		} while ($pageTemplateIdentifier != null);
	}
	
	public function getPageColumn()
	{
		return "@";
	}
}

?>
