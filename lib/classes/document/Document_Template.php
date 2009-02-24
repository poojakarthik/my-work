<?php
/**
 * Document_Template
 *
 * Parses a string with the Flex Document Templating System
 *
 * @class	Document_Template
 */
class Document_Template
{
	// Private Constructor to enforce Static-ness
	private function __construct(){}
	private function Document_Template(){}
	
	/**
	 * render()
	 *
	 * Renders an XSL Template and returns the output
	 * 
	 * @param	string	$strTemplate				XSL Template
	 * @param	string
	 *
	 * @method
	 */
	public static function render($strTemplate, $mixData)
	{
		// Cast passed data to a Flex_Dom_Document
		if (is_array($mixData))
		{
			// Convert to a Flex_Dom_Document
			throw new Exception("Converting of Arrays to Flex_Dom_Document trees are not supported yet");
		}
		elseif ($mixData instanceof Flex_Dom_Document)
		{
			$objDomDocument	= $mixData;
		}
		else
		{
			// Unhandled format/type
			$strType	= (is_object($mixData)) ? get_class($mixData) : gettype($mixData);
			throw new Exception("Dataset is in an unhandled format '{$strType}'");
		}
		
		// Load the Template into a DOMDocument
		$objXSLTemplate	= new DOMDocument();
		$objXSLTemplate->loadXML($strTemplate);
		
		
		// Process the XSL template and return the rendered document
		$objXSLTProcessor	= new XSLTProcessor();
		$objXSLTProcessor->importStylesheet($objXSLTemplate);
		
		$strXML	= $objXSLTProcessor->transformToXML($objDomDocument->getDomDocument());
		
		
		
		return $strXML;
	}
}
?>