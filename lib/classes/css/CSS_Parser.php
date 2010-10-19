<?php


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
 * Parser is the main entry point of the component and can convert CSS
 * selectors to XPath expressions.
 *
 * $xpath = Parser::cssToXpath('h1.foo');
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class CSS_Parser
{
    /**
     * @throws CSS_SyntaxError When got None for xpath expression
     */
    static public function cssToXpath($cssExpr, $prefix = 'descendant-or-self::')
    {
        if (is_string($cssExpr)) {
            if (preg_match('#^\w+\s*$#u', $cssExpr, $match)) {
                return $prefix.trim($match[0]);
            }

            if (preg_match('~^(\w*)#(\w+)\s*$~u', $cssExpr, $match)) {
                return sprintf("%s%s[@id = '%s']", $prefix, $match[1] ? $match[1] : '*', $match[2]);
            }

            if (preg_match('#^(\w*)\.(\w+)\s*$#u', $cssExpr, $match)) {
                return sprintf("%s%s[contains(concat(' ', normalize-space(@class), ' '), ' %s ')]", $prefix, $match[1] ? $match[1] : '*', $match[2]);
            }

            $parser = new self();
            $cssExpr = $parser->parse($cssExpr);
        }

        $expr = $cssExpr->toXpath();

        // @codeCoverageIgnoreStart
        if (!$expr) {
            throw new CSS_SyntaxError(sprintf('Got None for xpath expression from %s.', $cssExpr));
        }
        // @codeCoverageIgnoreEnd

        if ($prefix) {
            $expr->addPrefix($prefix);
        }

        return (string) $expr;
    }

    /**
     * @throws \Exception When CSS_Tokenizer throws it while parsing
     */
    public function parse($string)
    {
        $CSS_Tokenizer = new CSS_Tokenizer();

        $stream = new CSS_TokenStream($CSS_Tokenizer->CSS_Tokenize($string), $string);

       // try {
            return $this->parseSelectorGroup($stream);
       // } catch (Exception $e) {
       //     $class = get_class($e);

        //    throw new $class(sprintf('%s at %s -> %s', $e->getMessage(), implode($stream->getUsed(), ''), $stream->peek()), 0, $e);
       // }
    }

    protected function parseSelectorGroup($stream)
    {
        $result = array();
        while (1) {
            $result[] = $this->parseSelector($stream);
            if ($stream->peek() == ',') {
                $stream->next();
            } else {
                break;
            }
        }

        if (count($result) == 1) {
            return $result[0];
        }

        return new CSS_Node_OrNode($result);
    }

    /**
     * @throws CSS_SyntaxError When expected selector but got something else
     */
    protected function parseSelector($stream)
    {
        $result = $this->parseSimpleSelector($stream);

        while (1) {
            $peek = $stream->peek();
            if ($peek == ',' || $peek === null) {
                return $result;
            } elseif (in_array($peek, array('+', '>', '~'))) {
                // A combinator
                $combinator = (string) $stream->next();
            } else {
                $combinator = ' ';
            }
            $consumed = count($stream->getUsed());
            $next_selector = $this->parseSimpleSelector($stream);
            if ($consumed == count($stream->getUsed())) {
                throw new CSS_SyntaxError(sprintf("Expected selector, got '%s'", $stream->peek()));
            }

            $result = new CSS_Node_CombinedSelectorNode($result, $combinator, $next_selector);
        }

        return $result;
    }

    /**
     * @throws CSS_SyntaxError When expected symbol but got something else
     */
    protected function parseSimpleSelector($stream)
    {
        $peek = $stream->peek();
        if ($peek != '*' && !$peek->isType('Symbol')) {
            $element = $namespace = '*';
        } else {
            $next = $stream->next();
            if ($next != '*' && !$next->isType('Symbol')) {
                throw new CSS_SyntaxError(sprintf("Expected symbol, got '%s'", $next));
            }

            if ($stream->peek() == '|') {
                $namespace = $next;
                $stream->next();
                $element = $stream->next();
                if ($element != '*' && !$next->isType('Symbol')) {
                    throw new CSS_SyntaxError(sprintf("Expected symbol, got '%s'", $next));
                }
            } else {
                $namespace = '*';
                $element = $next;
            }
        }

        $result = new CSS_Node_ElementNode($namespace, $element);
        $has_hash = false;
        while (1) {
            $peek = $stream->peek();
            if ($peek == '#') {
                if ($has_hash) {
                    /* You can't have two hashes
                        (FIXME: is there some more general rule I'm missing?) */
                    // @codeCoverageIgnoreStart
                    break;
                    // @codeCoverageIgnoreEnd
                }
                $stream->next();
                $result = new CSS_Node_HashNode($result, $stream->next());
                $has_hash = true;

                continue;
            } elseif ($peek == '.') {
                $stream->next();
                $result = new CSS_Node_ClassNode($result, $stream->next());

                continue;
            } elseif ($peek == '[') {
                $stream->next();
                $result = $this->parseAttrib($result, $stream);
                $next = $stream->next();
                if ($next != ']') {
                    throw new CSS_SyntaxError(sprintf("] expected, got '%s'", $next));
                }

                continue;
            } elseif ($peek == ':' || $peek == '::') {
                $type = $stream->next();
                $ident = $stream->next();
                if (!$ident || !$ident->isType('Symbol')) {
                    throw new CSS_SyntaxError(sprintf("Expected symbol, got '%s'", $ident));
                }

                if ($stream->peek() == '(') {
                    $stream->next();
                    $peek = $stream->peek();
                    if ($peek->isType('String')) {
                        $selector = $stream->next();
                    } elseif ($peek->isType('Symbol') && is_int($peek)) {
                        $selector = intval($stream->next());
                    } else {
                        // FIXME: parseSimpleSelector, or selector, or...?
                        $selector = $this->parseSimpleSelector($stream);
                    }
                    $next = $stream->next();
                    if ($next != ')') {
                        throw new CSS_SyntaxError(sprintf("Expected ')', got '%s' and '%s'", $next, $selector));
                    }

                    $result = new CSS_Node_FunctionNode($result, $type, $ident, $selector);
                } else {
                    $result = new CSS_Node_PseudoNode($result, $type, $ident);
                }

                continue;
            } else {
                if ($peek == ' ') {
                    $stream->next();
                }

                break;
            }
            // FIXME: not sure what "negation" is
        }

        return $result;
    }

    /**
     * @throws CSS_SyntaxError When encountered unexpected selector
     */
    protected function parseAttrib($selector, $stream)
    {
        $attrib = $stream->next();
        if ($stream->peek() == '|') {
            $namespace = $attrib;
            $stream->next();
            $attrib = $stream->next();
        } else {
            $namespace = '*';
        }

        if ($stream->peek() == ']') {
            return newAttribNode($selector, $namespace, $attrib, 'exists', null);
        }

        $op = $stream->next();
        if (!in_array($op, array('^=', '$=', '*=', '=', '~=', '|=', '!='))) {
            //throw new CSS_SyntaxError(sprintf("Operator expected, got '%s'", $op));
            throw new Exception(sprintf("Operator expected, got '%s'", $op));
        }

        $value = $stream->next();
        if (!$value->isType('Symbol') && !$value->isType('String')) {
            throw new CSS_SyntaxError(sprintf("Expected string or symbol, got '%s'", $value));
        }

        return new CSS_Node_AttribNode($selector, $namespace, $attrib, $op, $value);
    }
}
