<?php

class Flex_Pdf_Annotation_Link_From extends Zend_Pdf_Resource
{
	public function __construct(Zend_Pdf_Element_Numeric $objX1, Zend_Pdf_Element_Numeric $objY1, Zend_Pdf_Element_Numeric $objX2, Zend_Pdf_Element_Numeric $objY2, Zend_Pdf_Element_String $objTargetName)
	{
		$objZero = new Zend_Pdf_Element_Numeric(0);
		$dictionary = new Zend_Pdf_Element_Dictionary();
        $dictionary->Type		= new Zend_Pdf_Element_Name('Annot');
        $dictionary->Subtype	= new Zend_Pdf_Element_Name('Link');
        $dictionary->Rect		= new Zend_Pdf_Element_Array(Array($objX1, $objY1, $objX2, $objY2));
        $dictionary->Dest		= $objTargetName;
        $dictionary->Border		= new Zend_Pdf_Element_Array(Array($objZero, $objZero, $objZero));

		parent::__construct($dictionary);
	}
}

?>
