<?php

//namespace Symfony\Component\CssSelector\Node;

//use Symfony\Component\CssSelector\CSS_SyntaxError;
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
 * CSS_Node_FunctionNode represents a "selector:name(expr)" node.
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class CSS_Node_FunctionNode implements CSS_Node_NodeInterface
{
    static protected $unsupported = array('target', 'lang', 'enabled', 'disabled');

    protected $selector;
    protected $type;
    protected $name;
    protected $expr;

    public function __construct($selector, $type, $name, $expr)
    {
        $this->selector = $selector;
        $this->type = $type;
        $this->name = $name;
        $this->expr = $expr;
    }

    public function __toString()
    {
        return sprintf('%s[%s%s%s(%s)]', __CLASS__, $this->selector, $this->type, $this->name, $this->expr);
    }

    /**
     * @throws CSS_SyntaxError When unsupported or unknown pseudo-class is found
     */
    public function toXpath()
    {
        $sel_path = $this->selector->toXpath();
        if (in_array($this->name, self::$unsupported)) {
            throw new CSS_SyntaxError(sprintf('The pseudo-class %s is not supported', $this->name));
        }
        $method = '_xpath_'.str_replace('-', '_', $this->name);
        if (!method_exists($this, $method)) {
            throw new CSS_SyntaxError(sprintf('The pseudo-class %s is unknown', $this->name));
        }

        return $this->$method($sel_path, $this->expr);
    }

    protected function _xpath_nth_child($xpath, $expr, $last = false, $addNameTest = true)
    {
        list($a, $b) = $this->parseSeries($expr);
        if (!$a && !$b && !$last) {
            // a=0 means nothing is returned...
            $xpath->addCondition('false() and position() = 0');

            return $xpath;
        }

        if ($addNameTest) {
            $xpath->addNameTest();
        }

        $xpath->addStarPrefix();
        if ($a == 0) {
            if ($last) {
                $b = sprintf('last() - %s', $b);
            }
            $xpath->addCondition(sprintf('position() = %s', $b));

            return $xpath;
        }

        if ($last) {
            // FIXME: I'm not sure if this is right
            $a = -$a;
            $b = -$b;
        }

        if ($b > 0) {
            $b_neg = -$b;
        } else {
            $b_neg = sprintf('+%s', -$b);
        }

        if ($a != 1) {
            $expr = array(sprintf('(position() %s) mod %s = 0', $b_neg, $a));
        } else {
            $expr = array();
        }

        if ($b >= 0) {
            $expr[] = sprintf('position() >= %s', $b);
        } elseif ($b < 0 && $last) {
            $expr[] = sprintf('position() < (last() %s)', $b);
        }
        $expr = implode($expr, ' and ');

        if ($expr) {
            $xpath->addCondition($expr);
        }

        return $xpath;
        /* FIXME: handle an+b, odd, even
             an+b means every-a, plus b, e.g., 2n+1 means odd
             0n+b means b
             n+0 means a=1, i.e., all elements
             an means every a elements, i.e., 2n means even
             -n means -1n
             -1n+6 means elements 6 and previous */
    }

    protected function _xpath_nth_last_child($xpath, $expr)
    {
        return $this->_xpath_nth_child($xpath, $expr, true);
    }

    protected function _xpath_nth_of_type($xpath, $expr)
    {
        if ($xpath->getElement() == '*') {
            throw new CSS_SyntaxError('*:nth-of-type() is not implemented');
        }

        return $this->_xpath_nth_child($xpath, $expr, false, false);
    }

    protected function _xpath_nth_last_of_type($xpath, $expr)
    {
        return $this->_xpath_nth_child($xpath, $expr, true, false);
    }

    protected function _xpath_contains($xpath, $expr)
    {
        // text content, minus tags, must contain expr
        if ($expr instanceof CSS_Node_ElementNode) {
            $expr = $expr->formatElement();
        }

        // FIXME: lower-case is only available with XPath 2
        //$xpath->addCondition(sprintf('contains(lower-case(string(.)), %s)', CSS_XPathExpr::xpathLiteral(strtolower($expr))));
        $xpath->addCondition(sprintf('contains(string(.), %s)', CSS_XPathExpr::xpathLiteral($expr)));

        // FIXME: Currently case insensitive matching doesn't seem to be happening

        return $xpath;
    }

    protected function _xpath_not($xpath, $expr)
    {
        // everything for which not expr applies
        $expr = $expr->toXpath();
        $cond = $expr->getCondition();
        // FIXME: should I do something about element_path?
        $xpath->addCondition(sprintf('not(%s)', $cond));

        return $xpath;
    }

    // Parses things like '1n+2', or 'an+b' generally, returning (a, b)
    protected function parseSeries($s)
    {
        if ($s instanceof CSS_Node_ElementNode) {
            $s = $s->formatElement();
        }

        if (!$s || $s == '*') {
            // Happens when there's nothing, which the CSS parser thinks of as *
            return array(0, 0);
        }

        if (is_string($s)) {
            // Happens when you just get a number
            return array(0, $s);
        }

        if ($s == 'odd') {
            return array(2, 1);
        }

        if ($s == 'even') {
            return array(2, 0);
        }

        if ($s == 'n') {
            return array(1, 0);
        }

        if (false === strpos($s, 'n')) {
            // Just a b

            return array(0, intval((string) $s));
        }

        list($a, $b) = explode('n', $s);
        if (!$a) {
            $a = 1;
        } elseif ($a == '-' || $a == '+') {
            $a = intval($a.'1');
        } else {
            $a = intval($a);
        }

        if (!$b) {
            $b = 0;
        } elseif ($b == '-' || $b == '+') {
            $b = intval($b.'1');
        } else {
            $b = intval($b);
        }

        return array($a, $b);
    }
}
