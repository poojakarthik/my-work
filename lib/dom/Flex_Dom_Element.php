<?php

// Include the parent class
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Flex_Dom_Object.php";


/**
 * Protected Class that wraps DOMElement objects
 *
 * The purpose of this class is to simplify the creation of XML texts.
 * 
 * Usage:
 * 
 * The class makes use of PHP's magic methods to allow the simple manipulation of 
 * child nodes and attributes of DOMElement. See the sample code below for details of how 
 * to do this. 
 * 
 * <code>
 * // Note: $dom is an instance of Flex_Dom_Document	
 * $element = $dom->MyElementName;
 * 
 * // Create child element (Flex_Pdf_Element) with the name MyChildElement
 * $element->MyChildElement;
 * 
 * // Assign a value (textual content) to the child element
 * $element->MyChildElement->setValue("child element value");
 * 
 * // Assign an attribute value to this element
 * $element->MyAttributeName = "attribute value";
 * 
 * // Add several child elements of the same name but different attributes
 * $element->SameNameChild()->DifferentAttribute = 0;
 * $element->SameNameChild()->DifferentAttribute = 1;
 * $element->SameNameChild()->DifferentAttribute = 2;
 * 
 * // Access the second SameNameChild (index 1) and change the attribute value
 * $element->SameNameChild(1)->DifferentAttribute = 6;
 * 
 * </code>
 * 
 * The above code would create the following DOM extract: -
 * 
 * <code>
 *     <MyElementName MyAttributeName="attribute value">
 *         <MyChildElement>child element value</MyChildElement>
 *         <SameNameChild DifferentAttribute="0" />
 *         <SameNameChild DifferentAttribute="6" />
 *         <SameNameChild DifferentAttribute="2" />
 *     </MyElementName>
 * </code> 
 * 
 * @author Hadrian Oliver
 * @link http://au.php.net/manual/en/class.domelement.php Class synopsis of DOMElement
 */

class Flex_Dom_Element extends Flex_Dom_Object
{
	/**
	 * The wrapped DOMElement
	 */
	private $_objDomNode = NULL;

	/**
	 * The parent Flex_Dom_Object
	 */
	private $_objDomParent = NULL;

	/**
	 * Constructor
	 * 
	 * Constructor only to be invoked by parent wrapper
	 * 
	 * @param DomElement $domNode to be wrapped
	 * @param Flex_Dom_Object $parent wrapper of DOMNode that contains the passed $domNode
	 * 
	 * @return void
	 */
	protected function __construct(DomElement $domNode, Flex_Dom_Object $parent)
	{
		$this->_objDomNode = $domNode;
		$this->_objDomParent = $parent;
	}


	/**
	 * Accessor function for the wrapped DOMNode object (i.e. the DOMElement)
	 * 
	 * Accessor function for the wrapped DOMNode object (i.e. the DOMElement)
	 * 
	 * @param void
	 * 
	 * @return DOMNode wrapped by this Flex_Dom_Object
	 */
	protected function _getDomNode()
	{
		return $this->_objDomNode;
	}

	/**
	 * Accessor function for the DOMDocument object to which the wrapped DOMNode object belongs
	 * 
	 * Accessor function for the DOMDocument object to which the wrapped DOMNode (i.e. the DOMElement) object belongs
	 * 
	 * @param void
	 * 
	 * @return DOMDocument to which the wrapped DOMNode object belongs
	 */
	public function getDomDocument()
	{
		// Escalate this responsibility to the parent... 
		return $this->_objDomParent->getDomDocument();
	}

	/**
	 * Magic function that creates and/or sets the value of an attribute.
	 * 
	 * Magic function that creates and/or sets the value of an attribute of
	 * a given name to a given value.
	 * 
	 * @param String $strName of attribute to be created and/or set
	 * @param mixed $mxdValue of attribute to be created and/or set
	 * 
	 * @return void
	 */
	public function __set($strName, $mxdValue)
	{
		// Set the attribute of the wrapped DOMElement accordingly...
		$this->_getDomNode()->setAttribute($strName, strval($mxdValue));
	}


	/**
	 * Sets the value of the wrapped DOMElement.
	 * 
	 * Sets the value of the wrapped DOMElement to the String value of the passed
	 * passed value.
	 * 
	 * @param mixed $mxdValue of element
	 * 
	 * @return void
	 */
	public function setValue($mxdValue)
	{
		// This element will have a value, so it should not have other child elements,
		// including any previous 'values'. Remove any that exist...
		for ($i = $this->_getDomNode()->childNodes->length - 1; $i >= 0; $i--)
		{
			$this->_getDomNode()->removeChild($this->_getDomNode()->childNodes->item($i));
		}
		$this->_arrChildren = array();
		
		// Create a DOMTextNode for the given value
		$objTextNode = $this->getDomDocument()->createTextNode(strval($mxdValue));
		// Append the DOMTextNode to the wrapped DOMElement
		$this->_getDomNode()->appendChild($objTextNode);
	}
}


?>
