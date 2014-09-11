<?php


class Flex_Pdf_Template_Raw extends Flex_Pdf_Template_Image
{
	protected $rawData = NULL;

	public function __construct($arg1, $arg2=NULL)
	{
		if (is_string($arg1))
		{
			$this->rawData = rtrim(str_replace("\r", "", $arg1));
		}
		else
		{
			parent::__construct($arg1, $arg2);
		}
	}

	function prepare()
	{
		// Need to load up the raw pdf commands
		if ($this->rawData === NULL)
		{
			try
			{
				$this->rawData = file_get_contents($this->strSource);
			}
			catch (Exception $e)
			{
				$this->rawData = "";
			}
		}
	}

	public function prepareSize($offsetTop=0)
	{
		$this->preparedWidth = 0;
		$this->preparedHeight = 0;

		$this->requiredWidth = 0;
		$this->requiredHeight = 0;
	}

	public function preparePosition($parentWidth=0, $parentHeight=0, $offsetTop=0, $offsetLeft=0)
	{
		$this->preparedAbsTop = 0;
		$this->preparedAbsLeft = 0;
	}

	function renderOnPage($page, $parent=NULL)
	{
		$this->renderAsLinkTarget($page);

		if ($this->rawData !== "")
		{
			$page->drawRawContent($this->rawData);
		}
	}
}


?>
