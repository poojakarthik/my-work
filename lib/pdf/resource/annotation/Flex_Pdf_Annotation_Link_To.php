<?php

class Flex_Pdf_Annotation_Link_To extends Zend_Pdf_Resource
{
	public function __construct(Zend_Pdf_Element_Object $objPage, Zend_Pdf_Element_Numeric $objX1, Zend_Pdf_Element_Numeric $objY1)
	{
		$dictionary = new Zend_Pdf_Element_Dictionary();
        $dictionary->D = new Zend_Pdf_Element_Array(Array($objPage, new Zend_Pdf_Element_Name('XYZ'), $objX1, $objY1, new Zend_Pdf_Element_Null()));

		parent::__construct($dictionary);
	}
}

?>
