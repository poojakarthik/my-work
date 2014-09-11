<?php

//namespace Symfony\Component\CssSelector\Node;

//use Symfony\Component\CssSelector\CSS_XPathExpr;
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
 * CSS_Node_AttribNode represents a "selector[namespace|attrib operator value]" node.
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class CSS_Node_AttribNode implements CSS_Node_NodeInterface
{
    protected $selector;
    protected $namespace;
    protected $attrib;
    protected $operator;
    protected $value;

    public function __construct($selector, $namespace, $attrib, $operator, $value)
    {
        $this->selector = $selector;
        $this->namespace = $namespace;
        $this->attrib = $attrib;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function __toString()
    {
        if ($this->operator == 'exists') {
            return sprintf('%s[%s[%s]]', __CLASS__, $this->selector, $this->formatAttrib());
        } else {
            return sprintf('%s[%s[%s %s %s]]', __CLASS__, $this->selector, $this->formatAttrib(), $this->operator, $this->value);
        }
    }

    /**
     * @throws CSS_SyntaxError When unknown operator is found
     */
    public function toXpath()
    {
        $path = $this->selector->toXpath();
        $attrib = $this->xpathAttrib();
        $value = $this->value;
        if ($this->operator == 'exists') {
            $path->addCondition($attrib);
        } elseif ($this->operator == '=') {
            $path->addCondition(sprintf('%s = %s', $attrib, CSS_XPathExpr::xpathLiteral($value)));
        } elseif ($this->operator == '!=') {
            // FIXME: this seems like a weird hack...
            if ($value) {
                $path->addCondition(sprintf('not(%s) or %s != %s', $attrib, $attrib, CSS_XPathExpr::xpathLiteral($value)));
            } else {
                $path->addCondition(sprintf('%s != %s', $attrib, CSS_XPathExpr::xpathLiteral($value)));
            }
            // path.addCondition('%s != %s' % (attrib, xpathLiteral(value)))
        } elseif ($this->operator == '~=') {
            $path->addCondition(sprintf("contains(concat(' ', normalize-space(%s), ' '), %s)", $attrib, CSS_XPathExpr::xpathLiteral(' '.$value.' ')));
        } elseif ($this->operator == '|=') {
            // Weird, but true...
            $path->addCondition(sprintf('%s = %s or starts-with(%s, %s)', $attrib, CSS_XPathExpr::xpathLiteral($value), $attrib, CSS_XPathExpr::xpathLiteral($value.'-')));
        } elseif ($this->operator == '^=') {
            $path->addCondition(sprintf('starts-with(%s, %s)', $attrib, CSS_XPathExpr::xpathLiteral($value)));
        } elseif ($this->operator == '$=') {
            // Oddly there is a starts-with in XPath 1.0, but not ends-with
            $path->addCondition(sprintf('substring(%s, string-length(%s)-%s) = %s', $attrib, $attrib, strlen($value) - 1, CSS_XPathExpr::xpathLiteral($value)));
        } elseif ($this->operator == '*=') {
            // FIXME: case sensitive?
            $path->addCondition(sprintf('contains(%s, %s)', $attrib, CSS_XPathExpr::xpathLiteral($value)));
        } else {
            throw new CSS_SyntaxError(sprintf('Unknown operator: %s', $this->operator));
        }

        return $path;
    }

    protected function xpathAttrib()
    {
        // FIXME: if attrib is *?
        if ($this->namespace == '*') {
            return '@'.$this->attrib;
        }

        return sprintf('@%s:%s', $this->namespace, $this->attrib);
    }

    protected function formatAttrib()
    {
        if ($this->namespace == '*') {
            return $this->attrib;
        }

        return sprintf('%s|%s', $this->namespace, $this->attrib);
    }
}
