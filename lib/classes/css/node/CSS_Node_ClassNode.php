<?php

//namespace Symfony\Component\CssSelector\Node;

//use Symfony\Component\CssSelector\CSS_XPathExpr;

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
 * CSS_Node_ClassNode represents a "selector.className" node.
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class CSS_Node_ClassNode implements CSS_Node_NodeInterface
{
    protected $selector;
    protected $className;

    public function __construct($selector, $className)
    {
        $this->selector = $selector;
        $this->className = $className;
    }

    public function __toString()
    {
        return sprintf('%s[%s.%s]', __CLASS__, $this->selector, $this->className);
    }

    public function toXpath()
    {
        $selXpath = $this->selector->toXpath();
        $selXpath->addCondition(sprintf("contains(concat(' ', normalize-space(@class), ' '), %s)", CSS_XPathExpr::xpathLiteral(' '.$this->className.' ')));

        return $selXpath;
    }
}
