<?php

class Flex_Pdf_Template_Link extends Flex_Pdf_Template_Span
{
	private $strTargetName = NULL;
	
	public function initialize()
	{
		parent::initialize();
		if ($this->dom->hasAttribute('href'))
		{
			$strTargetName = $this->dom->getAttribute('href');
			if ($strTargetName[0] == "#" && strlen($strTargetName) > 1)
			{
				$this->strTargetName = substr($strTargetName, 1);
			}
		}
	}

	public function renderAsLink($page)
	{
		if ($this->strTargetName !== NULL && $this->getTemplate()->getTargetMedia() != Flex_Pdf_Style::MEDIA_PRINT)
		{
			$page->drawLinkFrom($this->strTargetName, $this->getPreparedAbsTop(), $this->getPreparedAbsLeft(), $this->getPreparedHeight(), $this->getPreparedWidth());
		}
	}
	
}