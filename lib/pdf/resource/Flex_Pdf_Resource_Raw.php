<?php

require_once "Zend/Pdf/Resource.php";

class Flex_Pdf_Resource_Raw extends Zend_Pdf_Resource
{
	private static $rawResources = array();

	public function createRawResource($rawContent)
	{
		$md5 = md5($rawContent);

		if (!array_key_exists($md5, self::$rawResources))
		{
			self::$rawResources[$md5] = new Flex_Pdf_Resource_Raw($rawContent);
		}

		return self::$rawResources[$md5];
	}

	private $_content;

	public function __construct($rawContent)
	{
		$this->_content = trim($rawContent)."\n";

        parent::__construct($rawContent);

        $_0 = new Zend_Pdf_Element_Numeric(0);
        $_1 = new Zend_Pdf_Element_Numeric(1);
        $_1k = new Zend_Pdf_Element_Numeric(1000);
        $matrix = array($_1, $_0, $_0, $_1, $_0, $_0);
        $bbox = array($_0, $_0, $_1k, $_1k);
        $resources = array('ProcSet' => new Zend_Pdf_Element_Array(array(new Zend_Pdf_Element_Name('PDF'))));

        $this->_resource->dictionary->Type    = new Zend_Pdf_Element_Name('XObject');
        $this->_resource->dictionary->Subtype = new Zend_Pdf_Element_Name('Form');
        $this->_resource->dictionary->Filter  = new Zend_Pdf_Element_Name('FlateDecode');
        $this->_resource->dictionary->FormType = $_1;
        $this->_resource->dictionary->BBox = new Zend_Pdf_Element_Array($bbox);
        $this->_resource->dictionary->Matrix = new Zend_Pdf_Element_Array($matrix);
        $this->_resource->dictionary->Resources = new Zend_Pdf_Element_Dictionary($resources);
	}
}


?>
