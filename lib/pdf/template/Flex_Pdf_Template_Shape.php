<?php

class Flex_Pdf_Template_Shape extends Flex_Pdf_Template_Element
{
	private $strType = "";
	private $strValues = "";
	
	private $xs = array();
	private $ys = array();
	private $radius = 0;
	private $startAngle = 0;
	private $endAngle = 360;
	private $radius = 0;

	private $lineOn = 0;
	private $lineOff = 0;

	private $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE;
	private $fillMethod = Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING;
	
	private $minX = 0;
	private $minY = 0;
	private $maxX = 0;
	private $maxY = 0;
	
	private $isInvalid = FALSE;
	
	function initialize()
	{
		// Need to get the type and values for the shape
		$this->strType = strtoupper($this->dom->getAttribute("type"));
		$this->strValues = $this->dom->getAttribute("values");
		
		$this->prepare();
	}

	public function appendToDom($doc, $parentNode, $parent=NULL)
	{
		// Create a node for this element
		$node = $doc->createElement($this->dom->nodeName);
		
		// Apply the style to this node
		$node->setAttribute("style", $this->getStyle()->getHTMLStyleAttributeValue());

		// Apply the shape attributes to this node
		$node->setAttribute("type", $this->dom->getAttribute("type"));
		$node->setAttribute("values", $this->dom->getAttribute("values"));

		// Append this node to the parentNode
		$parentNode->appendChild($node);
	}

	public function renderOnPage($page, $parent=NULL)
	{
		if ($this->isInvalid) return;
		
		// Localize all positions
		$xs = $this->getAbsoluteXs();
		$ys = $this->getAbsoluteYs();
		
		// Record the line & fill colours on the page

		// Set the line & fill colour styles on the page
		
		switch ($this->type)
		{
			case "CIRCLE":
				$page->drawCircle($xs[0], $ys[0], $this->radius, $this->startAngle, $this->endAngle, $this->fillType);
				break;

			case "ELLIPSE":
				$page->drawEllipse($xs[0], $ys[0], $xs[1], $ys[1], $this->startAngle, $this->endAngle, $this->fillType);
				break;

			case "LINE":
				// If there is an on-off setting set line style...
				if ($this->lineOn != $this->lineOff) $page->setLineDashingPattern($this->lineOn, $this->lineOff);
				
				$page->drawLine($xs[0], $ys[0], $xs[1], $ys[1]);
				
				// If there is an on-off setting restore solid lines...
				if ($this->lineOn != $this->lineOff) $page->setLineDashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID);
				
				break;

			case "POLYGON":
				$page->drawPolygon($xs, $ys, $this->fillType, $this->fillMethod);
				break;

			case "RECTANGLE":
				$page->drawRectangle($xs[0], $ys[0], $xs[1], $ys[1], $this->fillType);
				break;

		}

		// Restore the line & fill colours on the page
	}

	function prepare()
	{
		$params = explode(",", str_replace(" ", "", $this->strValues));
		$nrParams = count($params);

		// Need to prepare the params for the shape
		switch ($this->type)
		{
			case "CIRCLE":
				if ($nrParams < 4 || $nrParams > 6)
				{
					$this->isInvalid = TRUE;
					break;
				}
				
				$this->xs[] = Flex_Pdf_Style::getPointSize($params[0]);
				$this->ys[] = Flex_Pdf_Style::getPointSize($params[1]);
				$this->radius = Flex_Pdf_Style::getPointSize($params[2]);
				
				if ($nrParams >= 4)
				{
					if ($nrParams > 4)
					{
						$this->startAngle = intval($params[3]);
						$this->endAngle = intval($params[4]);
					}
					
					if ($nrParams % 2 == 0)
					{
						$this->fillType = $this->getFillType($params[$nrParams - 1]);
					}
				
				}
				
				$this->minX = $this->xs[0] - $this->radius;
				$this->maxX = $this->xs[0] - $this->radius;
				$this->minY = $this->Ys[0] - $this->radius;
				$this->maxY = $this->Ys[0] - $this->radius;
				
				break;

			case "ELLIPSE":
				if ($nrParams < 4 || $nrParams > 7)
				{
					$this->isInvalid = TRUE;
					break;
				}
				
				$this->xs[] = Flex_Pdf_Style::getPointSize($params[0]);
				$this->ys[] = Flex_Pdf_Style::getPointSize($params[1]);
				$this->xs[] = Flex_Pdf_Style::getPointSize($params[2]);
				$this->ys[] = Flex_Pdf_Style::getPointSize($params[3]);
				
				if ($nrParams >= 5)
				{
					if ($nrParams > 5)
					{
						$this->startAngle = Flex_Pdf_Style::getPointSize($params[4]);
						$this->endAngle = Flex_Pdf_Style::getPointSize($params[5]);
					}
					
					if ($nrParams % 2 == 1)
					{
						$this->fillType = $this->getFillType($params[$nrParams - 1]);
					}
				
				}

				$this->minX = min($this->xs);
				$this->maxX = max($this->xs);
				$this->minY = min($this->Ys);
				$this->maxY = max($this->Ys);

				break;

			case "LINE":
				if ($nrParams != 4 || $nrParams != 6)
				{
					$this->isInvalid = TRUE;
					break;
				}
				
				$this->xs[] = Flex_Pdf_Style::getPointSize($params[0]);
				$this->ys[] = Flex_Pdf_Style::getPointSize($params[1]);
				$this->xs[] = Flex_Pdf_Style::getPointSize($params[2]);
				$this->ys[] = Flex_Pdf_Style::getPointSize($params[3]);
				
				if ($nrParams == 6)
				{
					$this->lineOn  = Flex_Pdf_Style::getPointSize($params[4]);
					$this->lineOff = Flex_Pdf_Style::getPointSize($params[5]);
				}

				$this->minX = min($this->xs);
				$this->maxX = max($this->xs);
				$this->minY = min($this->Ys);
				$this->maxY = max($this->Ys);

				break;

			case "POLYGON":
				if ($nrParams < 6 || !is_numeric($params[5]))
				{
					$this->isInvalid = TRUE;
					break;
				}
				
				for ($i = 0, $l = $nrParams - ($nrParams % 2); $i < $l && is_numeric($params[$i]); $i+=2)
				{
					$this->xs[] = Flex_Pdf_Style::getPointSize($params[$i]);
					$this->ys[] = Flex_Pdf_Style::getPointSize($params[$i+1]);
				}
				
				if (!is_numeric($params[$nrParams - 2]))
				{
					$this->fillType = $this->getFillType($params[$nrParams - 2]);
					$this->fillMethod = $this->getFillMethod($params[$nrParams - 1]);
				}
				else if (!is_numeric($params[$nrParams - 1]))
				{
					$this->fillType = $this->getFillType($params[$nrParams - 1]);
				}

				$this->minX = min($this->xs);
				$this->maxX = max($this->xs);
				$this->minY = min($this->Ys);
				$this->maxY = max($this->Ys);

				break;

			case "RECTANGLE":
				if ($nrParams < 4 || $nrParams > 6)
				{
					$this->isInvalid = TRUE;
					break;
				}

				$this->xs[] = Flex_Pdf_Style::getPointSize($params[0]);
				$this->ys[] = Flex_Pdf_Style::getPointSize($params[1]);
				$this->xs[] = Flex_Pdf_Style::getPointSize($params[2]);
				$this->ys[] = Flex_Pdf_Style::getPointSize($params[3]);
				
				if ($nrParams % 2 == 1)
				{
					$this->fillType = $this->getFillType($params[$nrParams - 1]);
				}

				$this->minX = min($this->xs);
				$this->maxX = max($this->xs);
				$this->minY = min($this->Ys);
				$this->maxY = max($this->Ys);

				break;

			default:
				$this->isInvalid = TRUE;
		}
		
		if (!$this->isInvalid)
		{
			$this->preparedWidth = $this->maxX - $this->minX;
			$this->preparedHeight = $this->maxY - $this->minY;
		}

	}
	
	private function getFillType($fillType)
	{
		echo "<hr>" . __CLASS__ . " :: " . __FUNCTION__ . " has not been implemented<hr>";
		return Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE;
	}

	private function getFillMethod($fillMethod)
	{
		echo "<hr>" . __CLASS__ . " :: " . __FUNCTION__ . " has not been implemented<hr>";
		return Zend_Pdf_Page::FILL_METHOD_NON_ZERO_WINDING;
	}
	
	private function getAbsoluteXs()
	{
		$xs = array();
		for ($i = 0, $l = count($this->xs); $i < $l; $i++)
		{
			$xs[] = $this->xs[$i] + $this->getPreparedAbsLeft();
		}
		return $xs;
	}
	
	private function getAbsoluteYs()
	{
		$ys = array();
		for ($i = 0, $l = count($this->ys); $i < $l; $i++)
		{
			$ys[] = $this->ys[$i] + $this->getPreparedAbsTop();
		}
		return $ys;
	}

	public function prepareSize($offsetTop=0)
	{
		if ($this->isInvalid) return;
		$this->requiredWidth = $this->preparedWidth + ($this->getOffsetLeft() ? $this->getOffsetLeft(): $this->getOffsetRight());
		$this->requiredHeight = $this->preparedHeight + ($this->getOffsetTop() ? $this->getOffsetTop() : $this->getOffsetBottom());
	}

	public function preparePosition($parentWidth=0, $parentHeight=0, $offsetTop=0, $offsetLeft=0)
	{
		if ($this->isInvalid) return;
		return parent::preparePosition($parentWidth, $parentHeight, $offsetTop, $offsetLeft);
	}
}

?>
