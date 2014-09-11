<?php

//require_once "pdf/resource/image/Flex_Pdf_Resource_Image_SVG.php";

class Flex_Pdf_Template_Image extends Flex_Pdf_Template_Element
{
	protected $objImage = NULL;
	protected $strSource = "";
	protected $fltWidth = 0;
	protected $fltHeight = 0;

	protected static $imageResourceCache = array();

	function initialize()
	{
		// Need to get the src for the image.
		$this->strSource = $this->getResourcePath($this->dom->getAttribute("src"));

		$this->prepare();
	}

	function prepare()
	{
		// Need to load up the image to find out the dimensions
		if ($this->objImage === NULL)
		{
			if (array_key_exists($this->strSource, self::$imageResourceCache))
			{
				$this->objImage = self::$imageResourceCache[$this->strSource];
				return;
			}
			try
			{
				if (strtolower(substr($this->strSource, strlen($this->strSource) - 4)) == ".svg")
				{
					// TODO:: Add support for SVG images. Current implementation is VERY broken
					$this->objImage = FALSE;
					return;

					$this->objImage = new Flex_Pdf_Image_Resource_SVG($this->strSource);
					$this->fltWidth = $this->objImage->getWidth();
					$this->fltHeight = $this->objImage->getHeight();
				}
				else
				{
					$this->objImage = Zend_Pdf_Image::imageWithPath($this->strSource);
					$this->fltWidth = $this->objImage->getPixelWidth() * 0.75;
					$this->fltHeight = $this->objImage->getPixelHeight() * 0.75;
				}
			}
			catch (Exception $e)
			{
				$this->objImage = FALSE;
			}
			self::$imageResourceCache[$this->strSource] = $this->objImage;
		}
	}

	public function prepareSize($offsetTop=0)
	{
		$this->preparedWidth = $this->getStyle()->hasFixedWidth() ? $this->getStyle()->getWidth() : $this->fltWidth;
		$this->preparedHeight = $this->getStyle()->hasFixedHeight() ? $this->getStyle()->getHeight() : $this->fltHeight;

		$this->requiredWidth = $this->preparedWidth + ($this->getOffsetLeft() ? $this->getOffsetLeft(): $this->getOffsetRight());
		$this->requiredHeight = $this->preparedHeight + ($this->getOffsetTop() ? $this->getOffsetTop() : $this->getOffsetBottom());
	}

	public function prepareChildPositions()
	{
	}

	public function appendToDom($doc, $parentNode, $parent=NULL)
	{
		if ($this->dom == NULL) return;

		// Create a node for this element
		$node = $doc->createElement($this->dom->tagName);

		// Apply the style to this node
		$node->setAttribute("style", $this->getStyle()->getHTMLStyleAttributeValue());

		// Apply the style to this node
		$node->setAttribute("src", $this->dom->getAttribute("src"));

		// Append this node to the parentNode
		$parentNode->appendChild($node);
	}

	function renderOnPage($page, $parent=NULL)
	{
		$page->drawBackground($this);

		$this->renderAsLinkTarget($page);

		if (!$this->objImage)
		{
			// We were unable to load the image, so nothing to render here!
			return;
		}

		// Need to get the top, left, width and height
		$page->drawImage($this->objImage, $this->getPreparedAbsTop(), $this->getPreparedAbsLeft(), $this->getPreparedHeight(), $this->getPreparedWidth());
	}

	public function clearTemporaryDetails()
	{
		// Probably nothing to clear out... makes sense to keep the image just in case we need it again
	}

}

?>
