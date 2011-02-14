<?php

// Add the lib directory to the include path, as it is required for the Zend library
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/' . ".." . '/'));

require_once "Flex_Pdf.php";
require_once "Flex_Pdf_Style.php";
require_once "Flex_Pdf_Page_Order.php";
require_once "template/Flex_Pdf_Template_Page.php";
require_once "template/Flex_Pdf_Template_Page_Wrap_Content.php";
require_once "Flex_Pdf_Resource_Manager.php";

class Flex_Pdf_Template
{
	//***********************************************************************************************************//
	// $_intCustomerGroupId integer Customer Group Id that the document relates to
	//***********************************************************************************************************//
	private $_intCustomerGroupId;

	//***********************************************************************************************************//
	// $_strEffectiveDate String containing effective date for document in 'YYYY-MM-DD hh:ii:ss' format
	//***********************************************************************************************************//
	private $_strEffectiveDate;

	//***********************************************************************************************************//
	// $_intTargetMedia integer target media for the document currently being rendered 
	//***********************************************************************************************************//
	private $_intTargetMedia = Flex_Pdf_Style::MEDIA_ALL;

	//***********************************************************************************************************//
	// $_domDocument DOMDocument containing XML in renderable format (i.e. post XSLT)
	//***********************************************************************************************************//
	private $_domDocument = NULL;

	//***********************************************************************************************************//
	// $_arrTemplatePages Array containing the Flex_Pdf_Template_Page objects for the document
	//***********************************************************************************************************//
	private $_arrTemplatePages = NULL;

	//***********************************************************************************************************//
	// $_objStyle Flex_Pdf_Style containing default style details for document
	//***********************************************************************************************************//
	private $_objStyle = NULL;

	//***********************************************************************************************************//
	// $_objPageOrder Flex_Pdf_Page_Order containing details of document page sequence
	//***********************************************************************************************************//
	private $_objPageOrder = NULL;

	//***********************************************************************************************************//
	// $_arrPageWrapContents Array of Flex_Pdf_Template_Page_Wrap_Content elements, indexed by their 
	// 						 identifier attribute.
	//***********************************************************************************************************//
	private $_arrPageWrapContents = NULL;

	//***********************************************************************************************************//
	// $_arrarrPageWrapContentNodes Array of DOMNodes indexed by their Id attribute 
	// 						 identifier attribute.
	//***********************************************************************************************************//
	private $arrPageWrapContentNodes = array();

	//***********************************************************************************************************//
	// $_strXsltFilename String name of the XSLT file, or md5 of passed XSLT string 
	//***********************************************************************************************************//
	private $_strXsltFilename = NULL;

	//***********************************************************************************************************//
	// $_intCurrentPageNumber integer number of the page currently being rendered
	//***********************************************************************************************************//
	private $_intCurrentPageNumber = 0;

	//***********************************************************************************************************//
	// $_objResourceManager Flex_Pdf_Resource_Manager which identifies file resource locations
	//***********************************************************************************************************//
	private $_objResourceManager = NULL;

	//***********************************************************************************************************//
	// $_arrErrors Array of error messages caught by internal error handler
	//***********************************************************************************************************//
	private static $_arrErrors = NULL; 

	//***********************************************************************************************************//
	// $_arrXSLTs Array cache of loaded XSLTProcessor's
	//***********************************************************************************************************//
	private static $_arrXSLTs = array();
	
	
	//***********************************************************************************************************//
	// Function destroy()
	//***********************************************************************************************************//
	/**
	 * destroy()
	 * 
	 * Forcibly release resources by unsetting references between all associated objects
	 * 
	 * Forcibly release resources by unsetting references between all associated objects.
	 * This function should be invoked by the user before it releases the reference on this instance, or
	 * before the reference to this instance goes out of scope.
	 * 
	 * Note: This should not be required, but Zend fails to identify that all references have been released.
	 * 
	 * @param void
	 * 
	 * @return void
	 */
	public function destroy()
	{
		// Destroy associated Flex_Pdf_Page_Wrap_Content objects
		foreach ($this->_arrPageWrapContents as $id => $pageWrap)
		{
			$pageWrap->_destroy();
			unset($this->_arrPageWrapContents[$id]);
		}
		// Destroy associated Flex_Pdf_Page_Template objects
		foreach ($this->_arrTemplatePages as $type => $page)
		{
			$page->_destroy();
			unset($this->_arrTemplatePages[$type]);
		}
		// Destroy remaining member variable
		unset($this->_arrPageWrapContents, $this->_arrTemplatePages);
		unset($this->_domDocument, $this->_objStyle, $this->_objPageOrder, $this->arrPageWrapContentNodes);
	}
	

	//***********************************************************************************************************//
	// Function getResourcePath($strRelativePath)
	//***********************************************************************************************************//
	/**
	 * Returns the absolute file path for a resource identified by a relative path
	 * 
	 * Returns the absolute file path for a resource identified by a relative path.
	 * This takes account of the CustomerGroup and EffectiveDate for the document being rendered.
	 * This function is intended for internal use only.
	 * 
	 * @param $relativePath String relative path or resource identifier as obtained from a template
	 * 
	 * @return String indetifying the full path to the resource file, or NULL if no resource is found
	 */
	public function getResourcePath($strRelativePath)
	{
		return $this->_objResourceManager->getResourcePath($strRelativePath);
	}

	//***********************************************************************************************************//
	// Function __construct(...)
	//***********************************************************************************************************//
	/**
	 * __construct(...)
	 * 
	 * Constructor
	 * 
	 * Returns the absolute file path for a resource identified by a relative path.
	 * This takes account of the CustomerGroup and EffectiveDate for the document being rendered.
	 * This function is intended for internal use only.
	 * 
	 * @param $intCustomerGroupId 			integer Customer group Id
	 * @param $mxdEffectiveDate 			mixed 	Effective date in db format "YYYY-MM-DD hh:ii:ss" or as Unix timestamp
	 * @param $mxdDocumentTypeIdOrXSLString mixed 	Integer DocumentType::Id or String containing the XSLT document
	 * 												Can be NULL if $bolTransformXML is FALSE
	 * @param $strXmlData 					String 	containing data for document in XML format. 
	 * 												NULL if it will be supplied via the loadXML() function.
	 * @param $mxdTargetMedia 				mixed	Media type to render document for. Current options are: -
	 * 													Flex_Pdf_Style::MEDIA_PRINT or "PRINT" for pages without stationery
	 * 												or	Flex_Pdf_Style::MEDIA_EMAIL or "EMAIL" for pages with stationery includes  
	 * @param $bolTransformXML 				boolean	FALSE if $strXmlData is in renderable format already,
	 * 												TRUE if $strXmlData requires XSLT transformation
	 * 
	 * @return void
	 */
	public function __construct($intCustomerGroupId, $mxdEffectiveDate, $mxdDocumentTypeIdOrXSLString, $strXmlData=NULL, $mxdTargetMedia=Flex_Pdf_Style::MEDIA_ALL, $bolTransformXML=TRUE)
	{
		// Convert the effective date to a string date
		if (is_numeric($mxdEffectiveDate))
		{
			$strEffectiveDate = date("Y-m-d H:i:s", intval($mxdEffectiveDate));
		}
		else
		{
			$strEffectiveDate = strval($mxdEffectiveDate);
		}
		
		if (is_numeric($mxdTargetMedia))
		{
			$intTargetMedia = intval($mxdTargetMedia);
		}
		else
		{
			$intTargetMedia = Flex_Pdf_Style::mediaForMediaName(strval($mxdTargetMedia));
		}
		
		// Get a resource manager for locating file resources
		$this->_objResourceManager = Flex_Pdf_Resource_Manager::getResourceManager($intCustomerGroupId, $strEffectiveDate);
		
		// If XML requires transformation...
		if ($bolTransformXML)
		{
			// If the XSL was passed as a string...
			if (is_string($mxdDocumentTypeIdOrXSLString))
			{
				$this->_strXsltFilename = md5($mxdDocumentTypeIdOrXSLString);
				$strXslXml = $mxdDocumentTypeIdOrXSLString;
			}
			// A DocumentType::Id was passed which can be used to obtain the appropriate XSL from the database
			else
			{
				// Need to load the XSL from the database, based on  customer group id, effective date and document type id.
				// XSL File name and be a combination  of these keys
				$this->_strXsltFilename =  "xslt:$intCustomerGroupId:$strEffectiveDate:$mxdDocumentTypeIdOrXSLString";
				if (!array_key_exists($this->_strXsltFilename, self::$_arrXSLTs))
				{
					$strXslXml = $this->_objResourceManager->getXSLT($mxdDocumentTypeIdOrXSLString);
				}
			}

			// If the XSLTProcessor is not already loaded...
			if (!array_key_exists($this->_strXsltFilename, self::$_arrXSLTs))
			{
				try 
				{
					// Create a DOMDocument to load the XSL as an XML DOM
					$this->_startErrorHandler();
					$domDocument = new DOMDocument();
					$bolOK = $domDocument->loadXML($strXslXml);
					$strErrors = $this->_stopErrorHandler();
		
					if ($bolOK === FALSE || $strErrors)
					{
						throw new Exception("Failed to load template XSLT as XML" . ($strErrors ? ":\n" . $strErrors : ""));
					}
				}
				catch (Exception $e)
				{
					$this->_stopErrorHandler();
					throw $e;
				}
				try 
				{
					// Create an XSLTProcessor for the XSL DOMDocument
					$this->_startErrorHandler();
					$objXsltProcessor = new XSLTProcessor();
					$bolOK = $objXsltProcessor->importStyleSheet($domDocument);
					$strErrors = $this->_stopErrorHandler();
		
					if ($bolOK === FALSE || $strErrors)
					{
						throw new Exception("Failed to import template XSLT as style sheet" . ($strErrors ? ":\n" . $strErrors : ""));
					}
				}
				catch (Exception $e)
				{
					$this->_stopErrorHandler();
					throw $e;
				}

				// Add the XSLTProcessor to the cache
				self::$_arrXSLTs[$this->_strXsltFilename] = $objXsltProcessor;
			}			
		}
		
		// Record the passed parameters
		$this->_strEffectiveDate = $strEffectiveDate;
		$this->_intCustomerGroupId = $intCustomerGroupId;
		$this->_intTargetMedia = $intTargetMedia;
		
		// If there is data to load, load it
		if ($strXmlData != NULL)
		{
			// Load the data
			$this->loadData($strXmlData, $bolTransformXML, $intTargetMedia);
		}
	}

	//***********************************************************************************************************//
	// Function loadData($strXmlData, $bolTransformXML, $intTargetMedia=Flex_Pdf_Style::MEDIA_ALL)
	//***********************************************************************************************************//
	/**
	 * loadData($strXmlData, $bolTransformXML, $intTargetMedia=Flex_Pdf_Style::MEDIA_ALL)
	 * 
	 * Loads the data for the document into the template, performing any required XSL transformation
	 * 
	 * Loads the data for the document into the template, performing any required XSL transformation
	 * 
	 * @param $strXmlData 					String 	containing data for document in XML format.
	 * @param $intTargetMedia 				integer	Media type to render document for. Current options are: -
	 * 													Flex_Pdf_Style::MEDIA_PRINT for pages without stationery
	 * 												or	Flex_Pdf_Style::MEDIA_EMAIL for pages with stationery includes  
	 * @param $bolTransformXML 				boolean	FALSE if $strXmlData is in renderable format already,
	 * 												TRUE if $strXmlData requires XSLT transformation
	 * 
	 * @return void
	 */
	public function loadData($strXmlData, $bolTransformXML=TRUE, $intTargetMedia=Flex_Pdf_Style::MEDIA_ALL)
	{
		// Update the target media property
		$this->_intTargetMedia = $intTargetMedia;

		// If the data need to be transformed...
		if ($bolTransformXML)
		{
			// Load the xsl file for the template
			$xsltProcessor = self::$_arrXSLTs[$this->_strXsltFilename];

			// Create a document DOM
			$domDocument = new DOMDocument();

			try 
			{
				// Load the data into the DOM
				$this->_startErrorHandler();
				$bolOK = $domDocument->loadXML($strXmlData);
				$strErrors = $this->_stopErrorHandler();

				if ($bolOK === FALSE || $strErrors)
				{
					throw new Exception("Failed to load XML data" . ($strErrors ? ":\n" . $strErrors : ""));
				}
			}
			catch (Exception $e)
			{
				$this->_stopErrorHandler();
				throw $e;
			}
	
			try 
			{
				// Transform the data DOM into a template DOM using the template XSLT processor
				$this->_startErrorHandler();
				@$this->_domDocument = $xsltProcessor->transformToDoc($domDocument);
				$strErrors = $this->_stopErrorHandler();

				if ($this->_domDocument === FALSE || $strErrors)
				{
					throw new Exception("Failed to transform XML data using template XSLT" . ($strErrors ? ":\n" . $strErrors : ""));
				}
			}
			catch (Exception $e)
			{
				$this->_stopErrorHandler();
				throw $e;
			}
		}
		else
		{
			// Create a document DOM
			$this->_domDocument = new DOMDocument();
	
			try 
			{
				// Load the data into the DOM
				$this->_startErrorHandler();
				$bolOK = $this->_domDocument->loadXML($strXmlData);
				$strErrors = $this->_stopErrorHandler();

				if ($bolOK === FALSE || $strErrors)
				{
					throw new Exception("Failed to load renderable XML data" . ($strErrors ? ":\n" . $strErrors : ""));
				}
			}
			catch (Exception $e)
			{
				$this->_stopErrorHandler();
				throw $e;
			}
		}
		
		// Initialize style...
		$this->_initializeStyle();
		
		// Load page order...
		$this->_loadPageOrder();
		
		// Load page order...
		$this->loadPageWrapContents();
		
		// Load pages...
		$this->_loadPages();
		
		//fwrite($f = fopen("output.xml", "w+b"), $this->_domDocument->saveXML());
		//fclose($f);
	}

	//***********************************************************************************************************//
	// Function _initializeStyle()
	//***********************************************************************************************************//
	/**
	 * _initializeStyle()
	 * 
	 * Loads the default style information for the document
	 * 
	 * Loads the default style information for the document
	 * 
	 * @param void
	 * 
	 * @return void
	 */
	private function _initializeStyle()
	{
		// Load up resource fonts (non-standard ones)
		$fonts = $this->_domDocument->getElementsByTagName("embedded-font");
		$fontResources = array();
		foreach ($fonts as $font)
		{
			// If a media has been specified...
			if ($font->hasAttribute("media"))
			{
				$intMedia = Flex_Pdf_Style::mediaForMediaName($font->getAttribute("media"));
				// Check that the font is suitable for this media
				if (!($intMedia & $this->_intTargetMedia))
				{
					// The font is not suitable for this media, so ignore it
					continue;
				}
			}
			if ($font->hasAttribute("path"))
			{
				$path = $this->getResourcePath($font->getAttribute("path"));
			}
			else if ($font->hasAttribute("default"))
			{
				$path = $font->getAttribute("default");
			}
			else
			{
				$path = "HELVETICA";
			}
			$fontResources[strtoupper($font->getAttribute("name"))] = $path;
		}
		//throw new Exception(print_r($fontResources, true));

		$this->_objStyle = new Flex_Pdf_Style();
		$this->_objStyle->setFontResources($fontResources);

		// Check for a default style attribute on the "pages" element
		$body = $this->_domDocument->getElementsByTagName("body");
		if ($body->item(0) === NULL)
		{
			throw new Exception("Document has no body!");
		}

		$pages = $this->_domDocument->getElementsByTagName("pages");
		if ($pages->item(0) === NULL)
		{
			throw new Exception("Document has no pages!");
		}

		if ($body->item(0)->hasAttribute("style"))
		{
			$this->_objStyle->applyStyleAttribute($body->item(0)->getAttribute("style"));
		}
		// ... assume a default style if one has not been specified
		else
		{
			$this->_objStyle->applyStyleAttribute("font-family: Helvetica; font-size: 12pt;");
		}

		if ($pages->item(0)->hasAttribute("style"))
		{
			$this->_objStyle->applyStyleAttribute($pages->item(0)->getAttribute("style"));
		}
	}

	//***********************************************************************************************************//
	// Function _loadPages()
	//***********************************************************************************************************//
	/**
	 * _loadPages()
	 * 
	 * Loads the page templates for the document
	 * 
	 * Loads the page templates for the document
	 * 
	 * @param void
	 * 
	 * @return void
	 */
	private function _loadPages()
	{
		$this->_arrTemplatePages = array();

		$pages = $this->_domDocument->getElementsByTagName("page");
		if ($pages->item(0) === NULL)
		{
			throw new Exception("Template has no pages!");
		}

		// Load up the template pages
		foreach ($pages as $page)
		{
			$this->_arrTemplatePages[$page->getAttribute("type")] = new Flex_Pdf_Template_Page($page, $this);
		}
	}


	//***********************************************************************************************************//
	// Function getTargetMedia()
	//***********************************************************************************************************//
	/**
	 * getTargetMedia()
	 * 
	 * Returns the target media for the document being rendered
	 * 
	 * Returns the target media for the document being rendered
	 * NOTE: This function is not intended for 'user land'. It is for package members only.
	 * 
	 * @param void
	 * 
	 * @return integer identifying the target media
	 */
	public function getTargetMedia()
	{
		return $this->_intTargetMedia;
	}


	//***********************************************************************************************************//
	// Function loadPageWrapContents()
	//***********************************************************************************************************//
	/**
	 * loadPageWrapContents()
	 * 
	 * Loads the <page-wrap-content> elements from the renderable XML DOM
	 * 
	 * Loads the <page-wrap-content> elements from the renderable XML DOM, and registers each one
	 * 
	 * @param void
	 * 
	 * @return void
	 */
	private function loadPageWrapContents()
	{
		$this->_arrPageWrapContents = array();

		$pageWrapContents = $this->_domDocument->getElementsByTagName("page-wrap-content");

		foreach ($pageWrapContents as $pageWrapContent)
		{
			//echo("loadPageWrapContents::".$pageWrapContent->getAttribute("identifier")."\n");
			$this->arrPageWrapContentNodes[$pageWrapContent->getAttribute("identifier")] = $pageWrapContent;
		}
		
		// Load up the page wrap contents
		foreach ($this->arrPageWrapContentNodes as $identifier => $pageWrapContent)
		{
			if (!array_key_exists($identifier, $this->_arrPageWrapContents))
			{
				$this->registerPageWrapContentNode($identifier, $this);
			}
		}
	}

	//***********************************************************************************************************//
	// Function registerPageWrapContentNode($identifier, $parent)
	//***********************************************************************************************************//
	/**
	 * registerPageWrapContentNode($identifier, $parent)
	 * 
	 * Creates and registers a Flex_Pdf_Template_Page_Wrap_Content for the <page-wrap-content> element
	 * 
	 * Creates and registers a Flex_Pdf_Template_Page_Wrap_Content for the <page-wrap-content> element identified
	 * by the given 'identifier' attribute value.
	 * NOTE: This function is not intended for 'user land'. It is for package members only.
	 * 
	 * @param $identifier 	String 	value of 'identifier' attribute on the <page-wrap-content> to be registered
	 * @param $parent 		Object 	parent (container) of the <page-wrap-content> element. This can either be a 
	 * 								Flex_Pdf_Template ($this) or an instance of Flex_Pdf_Template_Page_Wrap_Include.
	 * 
	 * @return Flex_Pdf_Template_Page_Wrap_Content instance to manage the rendering of the <page-wrap-content> element
	 */
	public function registerPageWrapContentNode($identifier, $parent)
	{
		if (!array_key_exists($identifier, $this->_arrPageWrapContents) && array_key_exists($identifier, $this->arrPageWrapContentNodes))
		{
			$pageWrapContentNode = $this->arrPageWrapContentNodes[$identifier];
			//echo("registerPageWrapContentNode:: ".($parent->id ? $parent->id : 'TEMPLATE')." -> {$identifier}\n");
			$this->_arrPageWrapContents[$identifier] = new Flex_Pdf_Template_Page_Wrap_Content($pageWrapContentNode, $parent);
		}
		return $this->_arrPageWrapContents[$identifier];
	}

	//***********************************************************************************************************//
	// Function getPageWrapContent($identifier)
	//***********************************************************************************************************//
	/**
	 * getPageWrapContent($identifier)
	 * 
	 * Returns the Flex_Pdf_Template_Page_Wrap_Content instance associated with the give 'identifier' attribute 
	 * value
	 * 
	 * Returns the Flex_Pdf_Template_Page_Wrap_Content instance associated with the give 'identifier' attribute 
	 * value, which manages the rendering of the <page-wrap-content> element with that 'identifier' attribute value.
	 * NOTE: This function is not intended for 'user land'. It is for package members only.
	 * 
	 * @param $identifier 	String 	value of 'identifier' attribute on the <page-wrap-content> to be registered
	 * 
	 * @return Flex_Pdf_Template_Page_Wrap_Content instance that manages the rendering of the <page-wrap-content> element
	 */
	public function getPageWrapContent($identifier)
	{
		return $this->_arrPageWrapContents[$identifier];
	}

	//***********************************************************************************************************//
	// Function _loadPageOrder()
	//***********************************************************************************************************//
	/**
	 * _loadPageOrder()
	 * 
	 * Loads the page sequence information for the template document
	 * 
	 * Loads the page sequence information for the template document, as defined in the renderable document
	 * <page-order> element and it's child elements.
	 * NOTE: This function is not intended for 'user land'. It is for package members only.
	 * 
	 * @param void 
	 * 
	 * @return void
	 */
	private function _loadPageOrder()
	{
		$pageOrders = $this->_domDocument->getElementsByTagName("page-order");
		if ($pageOrders->length == 0)
		{
			// If no page order is specified then we will create a page order including all pages
			$pageOrderHolder = $this->_domDocument->createElement("page-order-holder");
			$pageOrder = $this->_domDocument->createElement("page-order");
			$pageOrderHolder->appendChild($pageOrder);
			// We will make sure that the pages have unique types...
			$pages = $this->_domDocument->getElementsByTagName("page");
			// Itterate through the pages
			for ($i = 0, $l = $pages->length; $i < $l; $i++)
			{
				$type = "Page" . ($i + 1);
				$pages->item($i)->setAttribute("type", $type);
				$pageObject = $this->_domDocument->createElement("page-object");
				$pageObject->setAttribute("type", $type);
				$pageObject->setAttribute("include", "always");
				$pageOrder->appendChild($pageObject);
			}
			$pageOrders = $pageOrderHolder->getElementsByTagName("page-order");
		}
		$this->_objPageOrder = new Flex_Pdf_Page_Order($this, $pageOrders->item(0));
	}

	//***********************************************************************************************************//
	// Function getTemplate()
	//***********************************************************************************************************//
	/**
	 * getTemplate()
	 * 
	 * Returns this instance
	 * 
	 * Returns this instance
	 * NOTE: This function is not intended for 'user land'. It is for package members only.
	 * 
	 * @param void 
	 * 
	 * @return Flex_Pdf_Template $this
	 */
	public function getTemplate()
	{
		return $this;
	}

	//***********************************************************************************************************//
	// Function getStyle()
	//***********************************************************************************************************//
	/**
	 * getStyle()
	 * 
	 * Returns the default style object for the document 
	 * 
	 * Returns the default style object for the document 
	 * NOTE: This function is not intended for 'user land'. It is for package members only.
	 * 
	 * @param void 
	 * 
	 * @return Flex_Pdf_Style object containing the documents default style information
	 */
	public function getStyle()
	{
		return $this->_objStyle;
	}

	//***********************************************************************************************************//
	// Function createElement($strTagName)
	//***********************************************************************************************************//
	/**
	 * createElement($strTagName)
	 * 
	 * Creates and returns a DOMElement associated with the renderable documents DOMDocument object 
	 * 
	 * Creates and returns a DOMElement associated with the renderable documents DOMDocument object 
	 * NOTE: This function is not intended for 'user land'. It is for package members only.
	 * 
	 * @param $strTagName String name of the DOMElement to be created
	 * 
	 * @return DOMElement with the specified tag name
	 */
	public function createElement($strTagName)
	{
		return $this->_domDocument->createElement($strTagName);
	}

	//***********************************************************************************************************//
	// Function createTextNode($strText)
	//***********************************************************************************************************//
	/**
	 * createTextNode($strText)
	 * 
	 * Creates and returns a DOMTextNode associated with the renderable documents DOMDocument object 
	 * 
	 * Creates and returns a DOMTextNode associated with the renderable documents DOMDocument object 
	 * NOTE: This function is not intended for 'user land'. It is for package members only.
	 * 
	 * @param $strText String content of the DOMTextNode to be created
	 * 
	 * @return DOMTextNode with the specified string content
	 */
	public function createTextNode($strText)
	{
		return $this->_domDocument->createTextNode($strText);
	}

	//***********************************************************************************************************//
	// Function getTemplatePage($pageType)
	//***********************************************************************************************************//
	/**
	 * getTemplatePage($pageType)
	 * 
	 * Returns the Flex_Pdf_Page_Template object for the given page type 
	 * 
	 * Returns the Flex_Pdf_Page_Template object for the given page type 
	 * NOTE: This function is not intended for 'user land'. It is for package members only.
	 * 
	 * @param $pageType String page type of template page to be retrieved
	 * 
	 * @return Flex_Pdf_Page_Template for the given page type
	 */
	public function getTemplatePage($pageType)
	{
		return $this->_arrTemplatePages[$pageType];
	}

	//***********************************************************************************************************//
	// Function createDocument()
	//***********************************************************************************************************//
	/**
	 * createDocument()
	 * 
	 * Returns a Flex_Pdf document object containing the document for the template and data
	 * 
	 * Returns a Flex_Pdf document object containing the document for the template and data
	 * 
	 * @param void 
	 * 
	 * @return Flex_Pdf containing the document for the template and data
	 */
	public function createDocument()
	{
		// Reset the page order
		$this->_objPageOrder->resetIndex();

		// Create a new PDF document
		$objPdfDocument = new Flex_Pdf();
		// Append this to the new, blank document
		$this->appendToDocument($objPdfDocument);
		// Return the document
		return $objPdfDocument;
	}

	//***********************************************************************************************************//
	// Function createDocumentXML()
	//***********************************************************************************************************//
	/**
	 * createDocumentXML()
	 * 
	 * Returns a DOMDocument for the rendered document
	 * 
	 * Returns a DOMDocument for the rendered document.
	 * This function is not intended for use in 'user land', but for development purposes only.
	 * 
	 * @param void 
	 * 
	 * @return DOMDocument containing the rendered document in XML format
	 */
	public function createDocumentXML()
	{
		// Reset the page order
		$this->_objPageOrder->resetIndex();

		// We don't need to create a PDF, so a slimline version would do for this

		// What we do need to do is transform the template page based DOM
		// to a page based DOM. This is because the element styles are unique per page (re: position & size)
		// and because wrapped content will vary between renderings of template pages
		$domDocument = new DOMDocument('1.0', 'iso-8859-1');

		// Copy over details of any embedded fonts
		$embeddedFontsDOMNodeList = $this->_domDocument->getElementsByTagName("embedded-fonts");
		if ($embeddedFontsDOMNodeList->length > 0)
		{
			$domDocument->appendChild($domDocument->importNode($embeddedFontsDOMNodeList->item(0), TRUE));
		}

		// Create the basic html/body elements
		$domHtml = $domDocument->createElement("html");
		$domBody = $domDocument->createElement("body");
		$domDocument->appendChild($domHtml);
		$html->appendChild($domBody);

		$this->appendToDom($domDocument, $domBody);
		return $domDocument->saveXML();
	}


	//***********************************************************************************************************//
	// Function getCurrentPageNumber()
	//***********************************************************************************************************//
	/**
	 * getCurrentPageNumber()
	 * 
	 * Returns the page number of the page currently being rendered, indexed from 1 for this document
	 * 
	 * Returns the page number of the page currently being rendered, indexed from 1 for this document.
	 * NOTE: This function is not intended for 'user land'. It is for package members only.
	 * 
	 * @param void
	 * 
	 * @return integer value of the current page number, indexed from 1
	 */
	public function getCurrentPageNumber()
	{
		return $this->_intCurrentPageNumber;
	}

	//***********************************************************************************************************//
	// Function appendToDom(DOMDocument $objDomDocument, DOMNode $objDomNode)
	//***********************************************************************************************************//
	/**
	 * appendToDom(DOMDocument $objDomDocument, DOMNode $objDomNode)
	 * 
	 * Appends the DOMElements that represent this template to existing DOMDocument
	 * 
	 * Appends the DOMElements that represent this template to existing DOMDocument. This
	 * function is not intended for use in 'user land', but for development purposes.
	 * 
	 * @param $objDomDocument DOMDocument to append DOMElements to 
	 * @param $objDomNode DOMNode in DOMDomcument to append DOMElements to 
	 * 
	 * @return void
	 */
	public function appendToDom(DOMDocument $objDomDocument, DOMNode $objDomNode)
	{
		$this->_intCurrentPageNumber = 0;
		do
		{
			$objPageTemplateIdentifier = $this->_objPageOrder->nextPage();
			if ($objPageTemplateIdentifier != NULL)
			{
				$this->_intCurrentPageNumber++;
				$objPageTemplate = $this->getTemplatePage($objPageTemplateIdentifier->getType());
				$objPageTemplate->appendToDom($objDomDocument, $objDomNode);
			}
		} while ($objPageTemplateIdentifier != NULL);
	}

	//***********************************************************************************************************//
	// Function appendToDocument(Zend_Pdf $objPdfDocument)
	//***********************************************************************************************************//
	/**
	 * appendToDocument(Zend_Pdf $objPdfDocument)
	 * 
	 * Appends the document created by this template to an existing PDF document
	 * 
	 * Appends the document created by this template to an existing PDF document
	 * 
	 * @param $pdfDocument Zend_Pdf document to append pages to 
	 * 
	 * @return void
	 */
	public function appendToDocument(Zend_Pdf $objPdfDocument)
	{
		$this->_intCurrentPageNumber = 0;
		do
		{
			$objPageTemplateIdentifier = $this->_objPageOrder->nextPage();
			if ($objPageTemplateIdentifier != NULL)
			{
				$this->_intCurrentPageNumber++;
				$objPageTemplate = $this->getTemplatePage($objPageTemplateIdentifier->getType());
				$objPage = $objPdfDocument->newPage($objPageTemplate->getPageSize());
				//echo "<hr><b><span style='color:blue; font-size:24pt;'>Page " . $this->_intCurrentPageNumber . "</span></b><hr>";
				$objPdfDocument->pages[] = $objPage;
				$objPageTemplate->renderOnPage($objPage);
			}
		} while ($objPageTemplateIdentifier != NULL);
	}

	//***********************************************************************************************************//
	// Function _startErrorHandler()
	//***********************************************************************************************************//
	/**
	 * _startErrorHandler()
	 * 
	 * Start the handling of errors by Flex_PDF_Temaplate
	 * 
	 * Starts the handling of errors by Flex_PDF_Temaplate. 
	 * Default PHP error handling is suspended until Flex_Pdf_Template::_stopErrorHandler() is invoked
	 * NOTE: This is not thread-safe!
	 * 
	 * @param void
	 * 
	 * @return void
	 */
	private function _startErrorHandler()
	{
		// Create an array to store the errors in
		self::$_arrErrors = array();
		// Note: Should be able to use "Flex_Pdf_Template::errorHandler" below, but this only seems to work on Windows :(
		// Register the error handler function
		set_error_handler("Flex_Pdf_Error_Handler");
	}
	
	//***********************************************************************************************************//
	// Function _stopErrorHandler()
	//***********************************************************************************************************//
	/**
	 * _stopErrorHandler()
	 * 
	 * Stop the handling of errors by Flex_PDF_Temaplate
	 * 
	 * Stops the handling of errors by Flex_PDF_Temaplate and returns a string detailing
	 * all errors that occurred since error handling started, or boolean FALSE if none 
	 * occurred.
	 * NOTE: This is not thread-safe!
	 * 
	 * @param void
	 * 
	 * @return mixed String containing details of handled errors, or FALSE if none occurred
	 */
	private function _stopErrorHandler()
	{
		// If we are not handling errors...
		if (self::$_arrErrors === NULL) 
		{
			return FALSE;
		}
		// Restore the previous error handler
		restore_error_handler();
		// Create a string for the captured error messages
		$strErrors = strip_tags(implode("\n", self::$_arrErrors));
		// Destroy the error cache to identify that we are nolonger handling errors
		self::$_arrErrors = NULL;
		// Return the error string or FALSE if empty
		return $strErrors ? $strErrors : FALSE;
	}
	
	//***********************************************************************************************************//
	// Function errorHandler(...)
	//***********************************************************************************************************//
	/**
	 * _stopErrorHandler(...)
	 * 
	 * Function for handling errors that occur during PDF generation.
	 * 
	 * This function should only be used by Flex_Pdt_Template instances, 
	 * and SHOULD ONLY EVER BE INVOKED BY PHP RUNTIME ENVIRONMENT.
	 * 
	 * @param $intErrno integer value of error that occurred
	 * @param $strError string describing the error
	 * @param $strErrfile string file name where error occurred
	 * @param $strErrfile integer line number in errored file
	 * @param $arrErrcontext array context of error
	 * 
	 * @link http://au2.php.net/manual/en/function.set-error-handler.php See PHP website for more detail.
	 */
	public static function errorHandler($errno, $strError, $strErrfile=NULL, $intErrline=NULL, $arrErrcontext=NULL)
	{
	    self::$_arrErrors[] = $strError;
	    return TRUE;
	}
}

//***********************************************************************************************************//
// Function Flex_Pdf_Error_Handler(...)
//***********************************************************************************************************//
/**
 * Flex_Pdf_Error_Handler(...)
 * 
 * Function for handling errors that occur during PDF generation.
 * 
 * This function should only be used by Flex_Pdt_Template instances, 
 * and SHOULD ONLY EVER BE INVOKED BY PHP RUNTIME ENVIRONMENT.
 * 
 * @param $intErrno integer value of error that occurred
 * @param $strError string describing the error
 * @param $strErrfile string file name where error occurred
 * @param $strErrfile integer line number in errored file
 * @param $arrErrcontext array context of error
 * 
 * @link http://au2.php.net/manual/en/function.set-error-handler.php See PHP website for more detail.
 */
function Flex_Pdf_Error_Handler($intErrno, $strError, $strErrfile=NULL, $intErrline=NULL, $arrErrcontext=NULL)
{
	return Flex_Pdf_Template::errorHandler($intErrno, $strError, $strErrfile, $intErrline, $arrErrcontext);
}

?>
