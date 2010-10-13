<?php

//namespace Symfony\Component\CssSelector\Node;

//use Symfony\Component\CssSelector\CSS_SyntaxError;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
Copyright (c) 2004-2010 Fabien Potencier

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
 */

/**
 * CSS_Node_PseudoNode represents a "selector:ident" node.
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class CSS_Node_PseudoNode implements CSS_Node_NodeInterface
{
    static protected $unsupported = array(
        'indeterminate', 'first-line', 'first-letter',
        'selection', 'before', 'after',
        'active', 'focus', 'hover', 'link', 'visited'
    );

    protected $element;
    protected $type;
    protected $ident;

    /**
     * @throws CSS_SyntaxError When incorrect CSS_Node_PseudoNode type is given
     */
    public function __construct($element, $type, $ident)
    {
        $this->element = $element;

        if (!in_array($type, array(':', '::'))) {
            throw new CSS_SyntaxError(sprintf('The CSS_Node_PseudoNode type can only be : or :: (%s given).', $type));
        }

        $this->type = $type;
        $this->ident = $ident;
    }

    public function __toString()
    {
        return sprintf('%s[%s%s%s]', __CLASS__, $this->element, $this->type, $this->ident);
    }

    /**
     * @throws CSS_SyntaxError When unsupported or unknown pseudo-class is found
     */
    public function toXpath()
    {
        $el_xpath = $this->element->toXpath();

        if (in_array($this->ident, self::$unsupported)) {
            throw new CSS_SyntaxError(sprintf('The pseudo-class %s is unsupported', $this->ident));
        }
        $method = 'xpath_'.str_replace('-', '_', $this->ident);
        if (!method_exists($this, $method)) {
            throw new CSS_SyntaxError(sprintf('The pseudo-class %s is unknown', $this->ident));
        }

        return $this->$method($el_xpath);
    }




    protected function xpath_checked($xpath)
    {
        // FIXME: is this really all the elements?
        $xpath->addCondition("(@selected or @checked) and (name(.) = 'input' or name(.) = 'option')");

        return $xpath;
    }

    /**
     * @throws CSS_SyntaxError If this element is the root element
     */
    protected function xpath_root($xpath)
    {
        // if this element is the root element
        throw new CSS_SyntaxError();
    }

    protected function xpath_first_child($xpath)
    {
        $xpath->addStarPrefix();
        $xpath->addNameTest();
        $xpath->addCondition('position() = 1');

        return $xpath;
    }

    protected function xpath_last_child($xpath)
    {
        $xpath->addStarPrefix();
        $xpath->addNameTest();
        $xpath->addCondition('position() = last()');

        return $xpath;
    }

    protected function xpath_first_of_type($xpath)
    {
        if ($xpath->getElement() == '*') {
            throw new CSS_SyntaxError('*:first-of-type is not implemented');
        }
        $xpath->addStarPrefix();
        $xpath->addCondition('position() = 1');

        return $xpath;
    }

    /**
     * @throws CSS_SyntaxError Because *:last-of-type is not implemented
     */
    protected function xpath_last_of_type($xpath)
    {
        if ($xpath->getElement() == '*') {
            throw new CSS_SyntaxError('*:last-of-type is not implemented');
        }
        $xpath->addStarPrefix();
        $xpath->addCondition('position() = last()');

        return $xpath;
    }

    protected function xpath_only_child($xpath)
    {
        $xpath->addNameTest();
        $xpath->addStarPrefix();
        $xpath->addCondition('last() = 1');

        return $xpath;
    }

    /**
     * @throws CSS_SyntaxError Because *:only-of-type is not implemented
     */
    protected function xpath_only_of_type($xpath)
    {
        if ($xpath->getElement() == '*') {
            throw new CSS_SyntaxError('*:only-of-type is not implemented');
        }
        $xpath->addCondition('last() = 1');

        return $xpath;
    }

    protected function xpath_empty($xpath)
    {
        $xpath->addCondition('not(*) and not(normalize-space())');

        return $xpath;
    }
}
