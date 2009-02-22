<?php

// Include the parent class
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Flex_Dom_Object.php";
// Include the element class (this class is pretty useless without it!)
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Flex_Dom_Element.php";


/**
 * Class that wraps DOMDocument objects
 *
 * The purpose of this class is to simplify the creation of XML texts.
 * 
 * Usage:
 * 
 * The class makes use of PHP's magic methods to allow the simple addition of 
 * root nodes to a DOM structure. See the sample code below for details of how 
 * to do this. 
 * 
 * <code>
 * // Create a new instance	
 * $dom = new Flex_Dom_Document();
 * 
 * // Create a DOM root element called 'RootElement'
 * // Note: root element is an instance of Flex_Pdf_Dom_Element
 * // See Flex_Pdf_Dom_Element for usage of Flex_Pdf_Dom_Element's
 * $root = $dom->RootElement 
 * 
 * // Get the XML string to represent the DOM 
 * $strXML = $dom->saveXML();
 * 
 * </code> 
 * 
 * The above code would create the following DOM extract: -
 * 
 * <code>
 *     <?xml version="1.0"?>
 *     <RootElement />
 * </code> 
 * 
 * @author Hadrian Oliver
 * @link http://au.php.net/manual/en/class.domdocument.php Class synopsis of DOMDocument
 */

class Flex_Dom_Document extends Flex_Dom_Object
{
	/**
	 * The wrapped DOMDocument
	 */
	private $_objDomDocument = NULL;

	
	/**
	 * Constructor
	 * 
	 * Constructor
	 * 
	 * @param void
	 * 
	 * @return void
	 */
	public function __construct()
	{
		// Create the wrapped DOMDocument object
		$this->_objDomDocument = new DomDocument();
	}


	/**
	 * Creates and returns a string representing the XML document.
	 * 
	 * Creates and returns a string representing the XML document.
	 * 
	 * @param void
	 * 
	 * @return String XML document
	 */
	public function saveXML()
	{
		// Return the XML string for the wrapped DOMDocument
		return $this->_objDomDocument->saveXML();
	}

	/**
	 * Creates and returns a string representing the HTML document.
	 * 
	 * Creates and returns a string representing the HTML document.
	 * 
	 * @param void
	 * 
	 * @return String HTML document
	 */
	public function saveHTML()
	{
		// Return the XML string for the wrapped DOMDocument
		return $this->_objDomDocument->saveHTML();
	}

	/**
	 * Accessor function for the wrapped DOMNode object
	 * 
	 * Accessor function for the wrapped DOMNode object (i.e. the DOMDocument)
	 * 
	 * @param void
	 * 
	 * @return DOMNode wrapped by this Flex_Dom_Object
	 */
	protected function _getDomNode()
	{
		return $this->_objDomDocument;
	}

	/**
	 * Accessor function for the wrapped DOMDocument object
	 * 
	 * Accessor function for the wrapped DOMDocument object
	 * 
	 * @param void
	 * 
	 * @return DOMDocument to which the wrapped DOMNode object belongs
	 */
	public function getDomDocument()
	{
		return $this->_objDomDocument;
	}
}

?>
