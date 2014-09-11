<?php

/** Zend_Pdf_Element */
require_once 'Zend/Pdf/Element.php';


/**
 * PDF file raw content implementation
 * 
 * This is intended for experimental development purposes only 
 * and SHOULD NOT BE USED in a finished solution!! Instead, use the
 * Zend_Pdf_Element's classes provided by Zend, or other sub-classes
 * that provide type checking. 
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Flex_Pdf_Element_Raw extends Zend_Pdf_Element
{
    /**
     * Object value
     *
     * @var string
     */
    public $value;

    /**
     * Object constructor
     *
     * @param string $val
     */
    public function __construct($val)
    {
        $this->value   = (string)$val;
    }


    /**
     * Return type of the element.
     *
     * @return integer
     */
    public function getType()
    {
        return Zend_Pdf_Element::TYPE_DICTIONARY;
    }


    /**
     * Return object as string
     *
     * @param Zend_Pdf_Factory $factory
     * @return string
     */
    public function toString($factory = null)
    {
        return $this->value;
    }

}
