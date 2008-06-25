<?php

/** Zend_Pdf_Element_Numeric */
require_once 'Zend/Pdf/Resource.php';

/** Zend_Pdf_Exception */
require_once 'Zend/Pdf/Exception.php';

/** Zend_Pdf_Element_Numeric */
require_once 'Zend/Pdf/Element/Numeric.php';

/** Zend_Pdf_Element_Name */
require_once 'Zend/Pdf/Element/Name.php';

require_once '../../element/Flex_Pdf_Element_Raw.php';

class Flex_Pdf_Resource_Image_SVG extends Zend_Pdf_Resource_Image
{

	protected $_width;
	protected $_height;

	/**
	 * Object constructor.
	 */
	public function __construct($imageFileName)
	{
        if (!file_exists($imageFileName))
        {
			throw new Zend_Pdf_Exception('Image doesn\'t exist.');
        }
        
        try
        {
			$doc = new DOMDocument();
			$doc->load($imageFileName);
			$svg = $doc->getElementsByTagName('svg')->item(0);
			$this->_width = $svg->getAttribute('width');
			$this->_height = $svg->getAttribute('height');
        }
		catch(Exception $e)
		{
			throw new Zend_Pdf_Exception('Image is corrupt or cannot be read.');
		}

		parent::__construct();

		$formDictionary = $this->_resource->dictionary;

		$formDictionary->Type    = new Zend_Pdf_Element_Name('XObject');
		$formDictionary->Subtype = new Zend_Pdf_Element_Name('Form');
		//$formDictionary->Filter = new Zend_Pdf_Element_Name('FlateDecode');
		
		try
		{
			$this->_resource->value = file_get_contents($imageFileName);
		}
		catch(Exception $e)
		{
			throw new Zend_Pdf_Exception('Image cannot be read.');
		}
		
		$zero = new Zend_Pdf_Element_Numeric(0);
		$one = new Zend_Pdf_Element_Numeric(1);
		$width = new Zend_Pdf_Element_Numeric($this->_width);
		$height = new Zend_Pdf_Element_Numeric($this->_height);



		//$innerDictionary = new Zend_Pdf_Element_Dictionary(array("CA" => $one, "ca" => $one));
		//$interDictionary = new Zend_Pdf_Element_Dictionary(array("a0" => $innerDictionary));
		//$outerDictionary = new Zend_Pdf_Element_Dictionary(array("ExtGState" => $interDictionary));
		//$formDictionary->Resources = new Zend_Pdf_Element_Dictionary($outerDictionary);
		
		$formDictionary->BBox    = new Zend_Pdf_Element_Array(array($zero, $zero, $width, $height));

		//$formDictionary->Resources = new Flex_Pdf_Element_Raw('<< /ExtGState << /a0 << /CA 1 /ca 1 >> >> >>');
		
    }

    /**
     * get the height of the image
     *
     * @return integer
     */
    public function getHeight()
    {
		return $this->_height;
    }

    /**
     * get the width of the image
     *
     * @return integer
     */
    public function getWidth()
    {
		return $this->_width;
    }
    
    public function getPixelWidth()
    {
    	return $this->getWidth() / 0.75;
    }
    
    public function getPixelHeight()
    {
    	return $this->getHeight() / 0.75;
    }
    
    public function getProperties()
    {
    	return array();
    }
}




?>
