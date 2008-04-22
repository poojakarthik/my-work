<?php

require_once "barcode/Flex_Barcode.php";
require_once "pdf/template/resource/Flex_Pdf_Resource_Image_Png.php";

class Flex_Pdf_Template_Barcode extends Flex_Pdf_Template_Image
{
	private $strType = "";
	private $strValue = "";
	
	function initialize()
	{
		// Need to get the type and value the barcode.
		$this->strType = $this->dom->getAttribute("type");
		$this->strValue = $this->dom->getAttribute("value");
		
		$this->prepare();
	}

	public function appendToDom($doc, $parentNode, $parent=NULL)
	{
		// Create a node for this element
		$node = $doc->createElement($this->dom->nodeName);
		
		// Apply the style to this node
		$node->setAttribute("style", $this->getStyle()->getHTMLStyleAttributeValue());

		// Apply the barcode attributes to this node
		$node->setAttribute("type", $this->dom->getAttribute("type"));
		$node->setAttribute("value", $this->dom->getAttribute("value"));

		// Append this node to the parentNode
		$parentNode->appendChild($node);
	}

	function prepare()
	{
		// Need to load up the image to find out the dimensions
		if ($this->objImage === NULL)
		{
			try 
			{
				// Create a Flex_Barcode object for the appropriate barcode type 
				$barcode = Flex_Barcode::create($this->strType);
				
				// Create the image resource
				$barcodeImg = $barcode->draw($this->strValue, "png");
				
				// Create a temporary file for the image
				$imageFileResource = tmpfile();
				
				// Output the image into the buffer
				ob_start();
				imagepng($barcodeImg);
				
				// Write the buffer to the temporary file
				fwrite($imageFileResource, ob_get_contents());
				
				// Clean the buffer
				ob_end_clean();
				
				// Move the file pointer to the start of the file (just to be tidy)		
				fseek($imageFileResource, 0);
				
				// Create an image resource for the temporary image file resource
				$this->objImage = new Flex_Pdf_Resource_Image_Png($imageFileResource);
				
				// Close the temporary file to release the resources
				fclose($imageFileResource);

				// Get the dimensions of the image (approx. point size)
				$this->fltWidth = $this->objImage->getPixelWidth() * 0.75;
				$this->fltHeight = $this->objImage->getPixelHeight() * 0.75;
			}
			catch (Exception $e)
			{
				// Set objOmage to FALSE to prevent subsequent attempts to load the image
				$this->objImage = FALSE;
			}
		}
	}

}

?>
