<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/** Zend_Pdf_Element */
require_once 'Zend/Pdf/Element.php';


/**
 * PDF file 'string' element implementation
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Element_Raw extends Zend_Pdf_Element
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
