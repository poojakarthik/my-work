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
 * CSS_Node_CombinedSelectorNode represents a combinator node.
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class CSS_Node_CombinedSelectorNode implements CSS_Node_NodeInterface
{
    static protected $_method_mapping = array(
        ' ' => 'descendant',
        '>' => 'child',
        '+' => 'direct_adjacent',
        '~' => 'indirect_adjacent',
    );

    protected $selector;
    protected $combinator;
    protected $subselector;

    public function __construct($selector, $combinator, $subselector)
    {
        $this->selector = $selector;
        $this->combinator = $combinator;
        $this->subselector = $subselector;
    }

    public function __toString()
    {
        $comb = $this->combinator == ' ' ? '<followed>' : $this->combinator;

        return sprintf('%s[%s %s %s]', __CLASS__, $this->selector, $comb, $this->subselector);
    }

    /**
     * @throws CSS_SyntaxError When unknown combinator is found
     */
    public function toXpath()
    {
        if (!isset(self::$_method_mapping[$this->combinator])) {
            throw new CSS_SyntaxError(sprintf('Unknown combinator: %s', $this->combinator));
        }

        $method = '_xpath_'.self::$_method_mapping[$this->combinator];
        $path = $this->selector->toXpath();

        return $this->$method($path, $this->subselector);
    }

    protected function _xpath_descendant($xpath, $sub)
    {
        // when sub is a descendant in any way of xpath
        $xpath->join('/descendant::', $sub->toXpath());

        return $xpath;
    }

    protected function _xpath_child($xpath, $sub)
    {
        // when sub is an immediate child of xpath
        $xpath->join('/', $sub->toXpath());

        return $xpath;
    }

    protected function _xpath_direct_adjacent($xpath, $sub)
    {
        // when sub immediately follows xpath
        $xpath->join('/following-sibling::', $sub->toXpath());
        $xpath->addNameTest();
        $xpath->addCondition('position() = 1');

        return $xpath;
    }

    protected function _xpath_indirect_adjacent($xpath, $sub)
    {
        // when sub comes somewhere after xpath as a sibling
        $xpath->join('/following-sibling::', $sub->toXpath());

        return $xpath;
    }
}
